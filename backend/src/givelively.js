import { chromium } from 'playwright';
import { parseGiveLivelyHtml, validateCompleteDonations } from './givelively-parser.js';
import { SourceError } from './errors.js';

async function ensureReadable(page, response) {
  const status = response?.status();
  if (status === 403 || status === 429) throw new SourceError('source_blocked', `Give Lively HTTP ${status}: automated access is blocked`);
  if (status && status >= 400) throw new SourceError('source_http_error', `Give Lively HTTP ${status}`);
  const url = new URL(page.url());
  if (url.origin !== 'https://secure.givelively.org') throw new SourceError('source_redirected', 'Give Lively redirected outside the allowed origin');
  const html = await page.content();
  if (/captcha-delivery\.com|please enable js and disable any ad blocker|verify (?:that )?you are human/i.test(html)) {
    throw new SourceError('source_blocked', 'Give Lively requires access verification; collection was stopped');
  }
  return html;
}

async function visibleOne(locator) {
  for (let index = 0; index < await locator.count(); index++) {
    const candidate = locator.nth(index);
    if (await candidate.isVisible() && await candidate.isEnabled()) return candidate;
  }
  return null;
}

export async function fetchGiveLively(config) {
  let browser;
  let context;
  let deadline;
  try {
    browser = await chromium.launch({ headless: true, chromiumSandbox: true, timeout: 20000 });
    context = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await context.newPage();
    page.setDefaultTimeout(10000);
    page.setDefaultNavigationTimeout(30000);
    deadline = setTimeout(() => context.close().catch(() => {}), config.sourceTimeoutMs);
    deadline.unref();
    const response = await page.goto(config.sourceUrl, { waitUntil: 'domcontentloaded' });
    await ensureReadable(page, response);
    await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {});
    let parsed = parseGiveLivelyHtml(await ensureReadable(page), config.sourceUrl);
    const teamRaisedCents = parsed.teamRaisedCents;
    const expectedMemberCount = parsed.expectedMemberCount;
    const campaigns = new Map(parsed.campaigns.map(row => [row.url, row]));

    // Follow an observed public members-page link, never a guessed API endpoint.
    const membersLink = await visibleOne(page.getByRole('link', { name: /^(?:view|see|show)\s+(?:all\s+)?(?:team\s+)?members(?:\s*\(\d+\))?$/i }));
    if (membersLink) {
      const href = await membersLink.getAttribute('href');
      const membersUrl = new URL(href, page.url());
      if (membersUrl.origin !== new URL(config.sourceUrl).origin || !membersUrl.pathname.startsWith(new URL(config.sourceUrl).pathname.replace(/\/$/, ''))) {
        throw new SourceError('unsafe_members_link', 'Members link does not belong to the configured team');
      }
      const membersResponse = await page.goto(membersUrl.href, { waitUntil: 'domcontentloaded' });
      await ensureReadable(page, membersResponse);
      await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {});
    }

    let completed = false;
    for (let pageIndex = 0; pageIndex < 30; pageIndex++) {
      parsed = parseGiveLivelyHtml(await ensureReadable(page), config.sourceUrl, { requireTeamTotal: false });
      for (const row of parsed.campaigns) {
        const old = campaigns.get(row.url);
        if (old && old.raisedCents !== row.raisedCents) throw new SourceError('changing_snapshot', 'Fundraiser totals changed during collection; retry on the next cycle');
        campaigns.set(row.url, row);
      }
      if (expectedMemberCount !== null && campaigns.size === expectedMemberCount) { completed = true; break; }
      const more = await visibleOne(page.getByRole('button', { name: /^(?:load|show)\s+more(?:\s+(?:team\s+)?(?:members|fundraisers))?$/i }));
      const next = more || await visibleOne(page.locator('nav[aria-label*="pagination" i], [class*="pagination"]').getByRole('link', { name: /^next(?:\s+page)?$/i }));
      if (!next) { completed = true; break; }
      const previous = await page.locator('html').evaluate(element => element.outerHTML);
      await next.click();
      await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {});
      await page.waitForFunction(previousHtml => document.documentElement.outerHTML !== previousHtml, previous, { timeout: 5000 });
    }
    if (!completed) throw new SourceError('pagination_limit', 'Fundraiser pagination did not complete');
    const checked = validateCompleteDonations({ teamRaisedCents, expectedMemberCount, campaigns: [...campaigns.values()] });
    return { ...checked, currency: 'USD', sourceUrl: config.sourceUrl, fetchedAt: new Date().toISOString(), verification: expectedMemberCount === null ? 'pagination-exhausted' : 'member-count-verified' };
  } catch (error) {
    if (error instanceof SourceError) throw error;
    throw new SourceError('source_browser_error', String(error.message).slice(0, 400), { cause: error });
  } finally {
    clearTimeout(deadline);
    if (context) await context.close().catch(() => {});
    if (browser) await browser.close().catch(() => {});
  }
}
