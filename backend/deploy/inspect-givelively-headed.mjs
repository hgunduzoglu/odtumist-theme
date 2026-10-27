// Optional diagnostics: an ordinary headed Chromium session on an Xvfb display.
// No CAPTCHA actions, login, imported cookies, stealth options or proxy services.
import { chromium } from '/opt/odtumist-runtime/node_modules/playwright/index.mjs';
import { mkdir, writeFile } from 'node:fs/promises';
import { join } from 'node:path';

const DIRECTORY = '/var/lib/odtumist-bursmetre/diagnostics';
const TEAM = 'https://secure.givelively.org/teams/bridge-to-turkiye/2026-btf-marathon-steps-that-shape-futures/odtumist';
const LANDING = 'https://bridgetoturkiye.org/btf-marathon-annual-campaign-2026/';
const report = { checkedAt: new Date().toISOString(), browserMode: 'headed-chromium-xvfb', sandbox: true, visits: [], result: 'not_started' };
let browser;

async function inspect(page, response) {
  await page.waitForLoadState('domcontentloaded', { timeout: 15000 }).catch(() => {});
  const text = await page.locator('body').innerText({ timeout: 5000 }).catch(() => '');
  const challengeText = /please enable js and disable any ad blocker|verify (?:that )?you are human|confirm (?:that )?you are human|access denied|you have been blocked/i.test(text);
  let visibleChallengeFrame = false;
  const frames = page.locator('iframe[src*="captcha-delivery.com"]');
  for (let index = 0; index < await frames.count(); index++) {
    if (await frames.nth(index).isVisible()) visibleChallengeFrame = true;
  }
  const blocked = [403, 429].includes(response?.status()) || challengeText || visibleChallengeFrame;
  const headers = response ? await response.allHeaders() : {};
  const visit = {
    url: page.url(), status: response?.status() ?? null, title: await page.title(), blocked,
    responseDate: headers.date || null, protection: headers['x-datadome'] || null,
    requestReference: headers['x-amz-cf-id'] || null,
    challengeText, visibleChallengeFrame,
    visibleTextPreview: text.replace(/\s+/g, ' ').slice(0, 1200),
    visibleCurrencyCount: blocked ? 0 : (text.match(/\$\s*[\d,]+(?:\.\d{2})?/g) || []).length,
  };
  report.visits.push(visit);
  console.log(JSON.stringify(visit));
  return !blocked && (!response || response.ok());
}

try {
  await mkdir(DIRECTORY, { recursive: true, mode: 0o750 });
  browser = await chromium.launch({ headless: false, chromiumSandbox: true });
  const context = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  let page = await context.newPage();
  page.setDefaultNavigationTimeout(30000);
  let response = await page.goto(LANDING, { waitUntil: 'domcontentloaded' });
  const landingReadable = await inspect(page, response);
  let campaignOpened = false;
  if (landingReadable) {
    const link = page.getByRole('link', { name: /VISIT THE BTF MARATHON CAMPAIGN/i }).first();
    if (await link.count() && await link.isVisible()) {
      report.observedCampaignLink = await link.getAttribute('href');
      const observed = new URL(report.observedCampaignLink, LANDING);
      if (observed.origin === 'https://secure.givelively.org') {
        const responses = [];
        context.on('response', item => { if (item.request().isNavigationRequest() && item.request().resourceType() === 'document') responses.push(item); });
        const popup = page.waitForEvent('popup', { timeout: 8000 }).catch(() => null);
        await link.click({ timeout: 15000 });
        const opened = await popup;
        if (opened) page = opened;
        await page.waitForLoadState('domcontentloaded', { timeout: 15000 }).catch(() => {});
        response = responses.filter(item => item.url().startsWith('https://secure.givelively.org/')).at(-1);
        campaignOpened = true;
        if (!await inspect(page, response)) {
          report.result = 'source_blocked';
          process.exitCode = 2;
        }
      }
    }
  }
  if (report.result !== 'source_blocked') {
    response = await page.goto(TEAM, { waitUntil: 'domcontentloaded' });
    if (!await inspect(page, response)) {
      report.result = 'source_blocked';
      process.exitCode = 2;
    } else {
      await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {});
      if (!await inspect(page)) {
        report.result = 'source_blocked';
        process.exitCode = 2;
      } else {
        const html = await page.content();
        await writeFile(join(DIRECTORY, 'rendered-team.html'), html, { mode: 0o640 });
        await page.screenshot({ path: join(DIRECTORY, 'rendered-team.png'), fullPage: true });
        report.result = 'public_team_page_loaded';
        report.renderedHtmlFile = join(DIRECTORY, 'rendered-team.html');
      }
    }
  }
  report.enteredViaBtfLink = campaignOpened;
} catch (error) {
  report.result = 'diagnostic_error';
  report.error = error.message;
  process.exitCode = 1;
} finally {
  if (browser) await browser.close();
  await writeFile(join(DIRECTORY, 'headed-browser-report.json'), JSON.stringify(report, null, 2) + '\n', { mode: 0o640 });
  console.log(JSON.stringify({ result: report.result, reportFile: join(DIRECTORY, 'headed-browser-report.json') }));
}
