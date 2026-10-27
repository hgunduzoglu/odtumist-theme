import { parseHTML } from 'linkedom';
import { parseUsdCents } from './money.js';
import { SourceError } from './errors.js';

const normalize = value => String(value || '').replace(/\s+/g, ' ').trim();
const MONEY = /\$\s*(?:\d{1,3}(?:,\d{3})+|\d+)(?:\.\d{1,2})?(?![\d.,KkMm])/g;

export function normalizeFundraiserUrl(value, sourceUrl) {
  try {
    const url = new URL(value, sourceUrl);
    if (url.origin !== new URL(sourceUrl).origin || !/^\/fundraisers?\//i.test(url.pathname)) return null;
    url.search = '';
    url.hash = '';
    url.pathname = url.pathname.replace(/\/$/, '');
    return url.href;
  } catch { return null; }
}

function amountFromText(text, allowSingle = false) {
  const value = normalize(text);
  const tokens = [...value.matchAll(MONEY)];
  const labelled = [];
  for (const token of tokens) {
    const before = value.slice(Math.max(0, token.index - 40), token.index);
    const after = value.slice(token.index + token[0].length, token.index + token[0].length + 35);
    if (/(?:total\s+)?(?:raised|collected|donated)\s*:?\s*$/i.test(before)
        || /^\s*(?:raised|collected|donated)\b/i.test(after)) labelled.push(parseUsdCents(token[0]));
  }
  const values = [...new Set(labelled)];
  if (values.length === 1) return values[0];
  if (allowSingle && values.length === 0 && tokens.length === 1) return parseUsdCents(tokens[0][0]);
  return null;
}

function explicitAmount(node) {
  for (const key of ['data-raised-cents', 'data-amount-raised-cents', 'data-total-raised-cents']) {
    const value = node.getAttribute?.(key);
    if (value !== null && value !== undefined) {
      if (!/^\d+$/.test(value) || !Number.isSafeInteger(Number(value))) throw new SourceError('invalid_amount', 'Invalid amount in page data');
      return Number(value);
    }
  }
  for (const key of ['data-raised', 'data-amount-raised', 'data-total-raised']) {
    const value = node.getAttribute?.(key);
    if (value !== null && value !== undefined) return parseUsdCents(value);
  }
  return null;
}

function addCampaign(map, campaign) {
  if (!campaign.ad || !Number.isSafeInteger(campaign.raisedCents) || campaign.raisedCents < 0) return;
  const existing = map.get(campaign.url);
  if (existing && existing.raisedCents !== campaign.raisedCents) {
    throw new SourceError('ambiguous_campaign', 'The same fundraiser has conflicting amounts');
  }
  map.set(campaign.url, { id: campaign.url, ...campaign });
}

function readStructuredData(document, sourceUrl, campaigns, totals, memberCounts) {
  const source = new URL(sourceUrl);
  for (const script of document.querySelectorAll('script[type="application/json"],script[type="application/ld+json"],[data-react-props]')) {
    const raw = script.getAttribute('data-react-props') || script.textContent;
    if (!raw || raw.length > 2000000) continue;
    let root;
    try { root = JSON.parse(raw); } catch { continue; }
    let visited = 0;
    function walk(value, path = '', depth = 0, inTeamScope = false) {
      if (!value || typeof value !== 'object' || depth > 20 || ++visited > 20000) return;
      if (Array.isArray(value)) { value.forEach(row => walk(row, path, depth + 1, inTeamScope)); return; }
      const urlValue = value.url || value.fundraiser_url || value.fundraising_url;
      const fundraiserUrl = typeof urlValue === 'string' ? normalizeFundraiserUrl(urlValue, sourceUrl) : null;
      let raisedCents = null;
      for (const key of ['raised_cents', 'amount_raised_cents', 'total_raised_cents']) {
        if (value[key] !== undefined && /^\d+$/.test(String(value[key])) && Number.isSafeInteger(Number(value[key]))) raisedCents = Number(value[key]);
      }
      if (raisedCents === null) {
        for (const key of ['raised', 'amount_raised', 'total_raised', 'amountRaised', 'totalRaised']) {
          if (value[key] !== undefined && typeof value[key] !== 'object') { raisedCents = parseUsdCents(value[key]); break; }
        }
      }
      const currency = String(value.currency || value.priceCurrency || 'USD').toUpperCase();
      if (raisedCents !== null && currency !== 'USD') throw new SourceError('unsupported_currency', 'The source is not denominated in USD');
      let exactTeam = false;
      try {
        if (typeof urlValue === 'string') exactTeam = new URL(urlValue, sourceUrl).pathname.replace(/\/$/, '') === source.pathname.replace(/\/$/, '');
      } catch { /* unrelated JSON-LD URL */ }
      let ownTeam = exactTeam;
      if (exactTeam || /(?:^|\.)(?:team|team_page|teamPage)$/i.test(path)) {
        const name = normalize(value.name || value.ad).toLowerCase().replace(/[^a-z0-9]/g, '');
        const slug = source.pathname.split('/').filter(Boolean).at(-1).replace(/[^a-z0-9]/g, '');
        if (exactTeam || name === slug) {
          ownTeam = true;
          if (raisedCents !== null) totals.add(raisedCents);
          const count = value.member_count ?? value.members_count ?? value.memberCount;
          if (Number.isSafeInteger(Number(count)) && Number(count) >= 0 && count !== null && count !== undefined) memberCounts.add(Number(count));
        }
      }
      if (inTeamScope && fundraiserUrl && raisedCents !== null) {
        addCampaign(campaigns, { ad: normalize(value.ad || value.name || value.display_name || value.full_name || value.fundraiser_name), url: fundraiserUrl, raisedCents });
      }
      for (const [key, child] of Object.entries(value)) walk(child, path ? `${path}.${key}` : key, depth + 1, inTeamScope || ownTeam);
    }
    walk(root);
  }
}

export function parseGiveLivelyHtml(html, sourceUrl, { requireTeamTotal = true } = {}) {
  if (typeof html !== 'string' || html.length > 5000000) throw new SourceError('invalid_source_html', 'Source HTML is missing or too large');
  const { document } = parseHTML(html);
  for (const element of document.querySelectorAll('script[src*="captcha-delivery.com"],iframe[src*="captcha-delivery.com"],iframe[src*="recaptcha"]')) {
    throw new SourceError('source_blocked', 'Give Lively returned an access verification page');
  }
  const bodyText = normalize(document.body?.textContent || '');
  if (/please enable js and disable any ad blocker|verify (?:that )?you are human|access denied|unusual traffic/i.test(bodyText)) {
    throw new SourceError('source_blocked', 'Give Lively returned an access verification page');
  }
  const campaigns = new Map();
  const totals = new Set();
  const memberCounts = new Set();
  readStructuredData(document, sourceUrl, campaigns, totals, memberCounts);

  const main = document.querySelector('main') || document.body;
  const campaignCards = new Set();
  if (!main) throw new SourceError('source_markup_changed', 'Source page has no readable content');
  for (const anchor of main.querySelectorAll('a[href]')) {
    const url = normalizeFundraiserUrl(anchor.getAttribute('href'), sourceUrl);
    if (!url) continue;
    let card = anchor;
    for (let level = 0; card && level < 5; level++, card = card.parentElement) {
      const links = [...card.querySelectorAll('a[href]')].map(link => normalizeFundraiserUrl(link.getAttribute('href'), sourceUrl)).filter(Boolean);
      if (new Set(links).size > 1) break;
      const text = normalize(card.textContent);
      if (text.length > 1200) break;
      const amount = explicitAmount(card) ?? amountFromText(text, true);
      const nameNode = card.querySelector('h2,h3,h4,[data-fundraiser-name],[class*="fundraiser-name"]');
      const image = card.querySelector('img[alt]');
      let name = normalize(nameNode?.textContent || image?.getAttribute('alt') || anchor.getAttribute('aria-label'));
      if (!name) {
        name = normalize(anchor.textContent).replace(MONEY, '').replace(/\b(?:raised|donate|view fundraiser)\b/gi, '').trim();
      }
      if (amount !== null && name && !/^(?:donate|view|learn more)$/i.test(name)) {
        addCampaign(campaigns, { ad: name, url, raisedCents: amount });
        campaignCards.add(card);
        break;
      }
    }
  }

  const explicitTotals = main.querySelectorAll('[data-team-total-raised-cents],[data-team-total-raised],[data-testid="team-total-raised"]');
  for (const element of explicitTotals) {
    const cents = element.getAttribute('data-team-total-raised-cents');
    const amount = element.getAttribute('data-team-total-raised');
    if (cents !== null && /^\d+$/.test(cents) && Number.isSafeInteger(Number(cents))) totals.add(Number(cents));
    else if (amount !== null) totals.add(parseUsdCents(amount));
    else {
      const value = explicitAmount(element) ?? amountFromText(element.textContent, true);
      if (value !== null) totals.add(value);
    }
  }
  if (totals.size === 0) {
    for (const element of main.querySelectorAll('section,header,div,p,h1,h2,h3,span')) {
      const text = normalize(element.textContent);
      if (text.length > 350 || !/raised|collected/i.test(text)) continue;
      if (element.closest('a[href*="/fundraiser"]') || element.querySelector('a[href*="/fundraiser"]')) continue;
      if ([...campaignCards].some(card => card === element || card.contains(element))) continue;
      const amount = amountFromText(text);
      if (amount !== null) totals.add(amount);
    }
  }
  for (const match of bodyText.matchAll(/\b(\d+)\s+(?:team\s+)?members\b/gi)) memberCounts.add(Number(match[1]));
  if (totals.size > 1) throw new SourceError('ambiguous_team_total', 'Multiple conflicting team totals were found');
  if (memberCounts.size > 1) throw new SourceError('ambiguous_member_count', 'Multiple conflicting member counts were found');
  const teamRaisedCents = totals.size ? [...totals][0] : null;
  if (requireTeamTotal && teamRaisedCents === null) throw new SourceError('source_markup_changed', 'An unambiguous team total could not be found');
  return { teamRaisedCents, expectedMemberCount: memberCounts.size ? [...memberCounts][0] : null, campaigns: [...campaigns.values()] };
}

export function validateCompleteDonations(donations) {
  if (!Number.isSafeInteger(donations.teamRaisedCents) || donations.teamRaisedCents < 0) throw new SourceError('invalid_team_total', 'Invalid team total');
  const urls = new Set(donations.campaigns.map(row => row.url));
  if (urls.size !== donations.campaigns.length) throw new SourceError('duplicate_campaigns', 'Duplicate fundraisers were found');
  if (donations.expectedMemberCount !== null && donations.expectedMemberCount !== donations.campaigns.length) {
    throw new SourceError('incomplete_campaigns', `Expected ${donations.expectedMemberCount} fundraisers but read ${donations.campaigns.length}`);
  }
  if (donations.campaigns.length === 0 && donations.expectedMemberCount !== 0) {
    throw new SourceError('incomplete_campaigns', 'No fundraiser list was verified; refusing a partial snapshot');
  }
  const memberRaisedCents = donations.campaigns.reduce((sum, row) => sum + row.raisedCents, 0);
  if (!Number.isSafeInteger(memberRaisedCents) || memberRaisedCents > donations.teamRaisedCents) {
    throw new SourceError('inconsistent_totals', 'Fundraiser amounts exceed the team total; refusing an inconsistent snapshot');
  }
  return { ...donations, memberRaisedCents, directTeamRaisedCents: donations.teamRaisedCents - memberRaisedCents };
}
