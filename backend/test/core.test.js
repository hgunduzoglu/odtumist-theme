import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile, mkdtemp, rm, writeFile } from 'node:fs/promises';
import { join } from 'node:path';
import { tmpdir } from 'node:os';
import { once } from 'node:events';
import { loadConfig } from '../src/config.js';
import { parseUsdCents, convertCents } from '../src/money.js';
import { parseTcmbXml } from '../src/tcmb.js';
import { parseGiveLivelyHtml, validateCompleteDonations } from '../src/givelively-parser.js';
import { buildPayload } from '../src/payload.js';
import { Store } from '../src/store.js';
import { collect } from '../src/collector.js';
import { SourceError } from '../src/errors.js';
import { createApi } from '../src/api.js';

const SOURCE = 'https://secure.givelively.org/teams/bridge-to-turkiye/2026-btf-marathon-steps-that-shape-futures/odtumist';
const NOW = Date.parse('2026-10-27T16:00:00Z');
const XML = '<Tarih_Date Tarih="27.10.2026"><Currency Kod="USD"><Unit>1</Unit><ForexBuying>49.6997</ForexBuying><ForexSelling>99.9999</ForexSelling><BanknoteBuying>1.0000</BanknoteBuying></Currency></Tarih_Date>';
const HTML = await readFile(new URL('./fixtures/team.html', import.meta.url), 'utf8');
const donations = () => ({ ...validateCompleteDonations(parseGiveLivelyHtml(HTML, SOURCE)), currency: 'USD', sourceUrl: SOURCE, fetchedAt: new Date(NOW).toISOString(), verification: 'member-count-verified' });
const rates = () => parseTcmbXml(XML, new Date(NOW).toISOString());

test('configuration accepts only the intended public sources', () => {
  assert.equal(loadConfig({}).port, 3100);
  assert.throws(() => loadConfig({ SOURCE_URL: 'http://127.0.0.1/private' }));
  assert.throws(() => loadConfig({ SOURCE_URL: 'https://secure.givelively.org.evil.test/teams/a/b/c' }));
  assert.throws(() => loadConfig({ TCMB_URL: 'https://example.test/rates.xml' }));
  assert.throws(() => loadConfig({ PORT: 'not-a-port' }));
  assert.throws(() => loadConfig({ PORT: '99999' }));
});

test('USD amounts retain cents and reject ambiguous formats', () => {
  assert.equal(parseUsdCents('$1,234.56'), 123456);
  assert.equal(parseUsdCents('1,234'), 123400);
  assert.equal(parseUsdCents('USD 12.5'), 1250);
  assert.equal(parseUsdCents(0), 0);
  for (const value of ['$1.2K', '1.234,56', '-1', 'NaN', Infinity, 1.005]) assert.throws(() => parseUsdCents(value));
});

test('TCMB uses ForexBuying and bulletin date, not selling or banknote rates', () => {
  const fx = rates();
  assert.equal(fx.date, '2026-10-27');
  assert.deepEqual(fx.quotes.USD, { buying: '49.6997', unit: 1 });
  assert.equal(convertCents(1000000, fx.quotes.USD), 49699700);
  assert.equal(convertCents(10000, { buying: '250.00', unit: 100 }), 25000);
  assert.equal(convertCents(1, { buying: '1.5', unit: 1 }), 2);
  assert.throws(() => parseTcmbXml(XML.replace('ForexBuying', 'Missing').replace('/ForexBuying', '/Missing')));
  assert.throws(() => parseTcmbXml('<!DOCTYPE x [<!ENTITY x SYSTEM "file:///etc/passwd">]>' + XML));
  assert.throws(() => parseTcmbXml(XML.replace('27.10.2026', '31.02.2026')));
});

test('rendered team page keeps the team total separate from member sums', () => {
  const result = donations();
  assert.equal(result.teamRaisedCents, 125050);
  assert.equal(result.memberRaisedCents, 100050);
  assert.equal(result.directTeamRaisedCents, 25000);
  assert.equal(result.campaigns.length, 2);
  assert(!result.campaigns[0].url.includes('?'));
});

test('structured data is scoped to the configured team', () => {
  const data = { team: { name: 'ODTUMIST', url: SOURCE, total_raised_cents: 125050, member_count: 2, members: [
    { name: 'Example A', url: '/fundraisers/example-a/campaign', raised: '500.25' },
    { name: 'Example B', url: '/fundraisers/example-b/campaign', raised: '500.25' },
  ] }, unrelated: { name: 'Other team', url: '/fundraisers/other/campaign', raised: 999999 } };
  const html = `<html><body><main><h1>ODTUMIST</h1></main><script type="application/json">${JSON.stringify(data)}</script></body></html>`;
  const result = validateCompleteDonations(parseGiveLivelyHtml(html, SOURCE));
  assert.equal(result.campaigns.length, 2);
  assert.equal(result.teamRaisedCents, 125050);
});

test('blocked, incomplete and ambiguous pages cannot produce a snapshot', () => {
  assert.throws(() => parseGiveLivelyHtml('<html><body>Please enable JS and disable any ad blocker</body></html>', SOURCE), { code: 'source_blocked' });
  assert.throws(() => parseGiveLivelyHtml('<html><body><main>No money</main></body></html>', SOURCE), { code: 'source_markup_changed' });
  assert.throws(() => validateCompleteDonations(parseGiveLivelyHtml(HTML.replace('2 Team Members', '3 Team Members'), SOURCE)), { code: 'incomplete_campaigns' });
  assert.throws(() => validateCompleteDonations({ ...donations(), teamRaisedCents: 10 }), { code: 'inconsistent_totals' });
  assert.throws(() => parseGiveLivelyHtml(HTML.replace('</main>', '<span data-team-total-raised="999">$999 raised</span></main>'), SOURCE), { code: 'ambiguous_team_total' });
});

test('a new FX snapshot revalues an unchanged USD total without double counting', () => {
  const config = loadConfig({});
  const data = buildPayload({ donations: donations(), fx: rates(), status: {} }, config, NOW);
  assert.equal(data.totalUsd, 1250.5);
  assert.equal(data.totalTry, 62149.47);
  assert.equal(data.directTeamUsd, 250);
  assert.equal(data.campaigns[0].raised, 500.25);
  assert.equal(data.scholarship.studentsCovered, 1);
  assert.equal(data.stale, false);
  const changed = { ...rates(), quotes: { USD: { buying: '50.0000', unit: 1 } } };
  assert.equal(buildPayload({ donations: donations(), fx: changed, status: {} }, config, NOW).totalTry, 62525);
  assert.equal(buildPayload({ donations: null, fx: changed }, config, NOW), null);
});

test('old data and source failures are explicitly marked stale', () => {
  const result = buildPayload({ donations: donations(), fx: rates(), status: { donations: { error: { code: 'source_blocked' } } } }, loadConfig({}), NOW + 7200000);
  assert.equal(result.stale, true);
  assert(result.warnings.some(row => row.code === 'source_blocked'));
  assert(result.warnings.some(row => row.code === 'stale_donations'));
  assert.equal(result.lastUpdated, new Date(NOW).toISOString());
});

test('corrupt stored totals, duplicate campaigns and source changes are rejected', () => {
  const config = loadConfig({});
  const valid = donations();
  for (const value of [
    { ...valid, directTeamRaisedCents: undefined },
    { ...valid, campaigns: [valid.campaigns[0], valid.campaigns[0]] },
    { ...valid, sourceUrl: SOURCE + '-other' },
    { ...valid, expectedMemberCount: 99 },
  ]) assert.throws(() => buildPayload({ donations: value, fx: rates(), status: {} }, config, NOW), { code: 'invalid_snapshot' });
});

test('collector preserves last successful sources independently after failure', async t => {
  const directory = await mkdtemp(join(tmpdir(), 'bursmetre-test-'));
  t.after(() => rm(directory, { recursive: true, force: true }));
  const store = new Store(directory);
  const config = loadConfig({ DATA_DIR: directory });
  await collect(config, store, { donationsFetcher: async () => donations(), fxFetcher: async () => rates(), log: () => {} });
  const oldDonations = await store.read('donations');
  await collect(config, store, { donationsFetcher: async () => { throw new SourceError('source_blocked', 'Blocked'); }, fxFetcher: async () => ({ ...rates(), fetchedAt: '2026-10-27T17:00:00Z' }), log: () => {} });
  assert.deepEqual(await store.read('donations'), oldDonations);
  assert.equal((await store.read('fx')).fetchedAt, '2026-10-27T17:00:00Z');
  assert.equal((await store.read('status')).donations.error.code, 'source_blocked');
  await writeFile(join(directory, 'collector.lock'), JSON.stringify({ pid: process.pid, at: Date.now() }));
  await assert.rejects(collect(config, store), { code: 'collector_busy' });
});

test('API reports missing data honestly, authenticates and serves a valid snapshot', async t => {
  const directory = await mkdtemp(join(tmpdir(), 'bursmetre-api-test-'));
  t.after(() => rm(directory, { recursive: true, force: true }));
  const config = loadConfig({ DATA_DIR: directory, API_TOKEN: 'test-token' });
  const store = new Store(directory);
  const server = createApi(config, store);
  t.after(() => new Promise(resolve => server.close(resolve)));
  server.listen(0, '127.0.0.1');
  await once(server, 'listening');
  const base = `http://127.0.0.1:${server.address().port}`;
  const headers = { Authorization: 'Bearer test-token' };
  assert.equal((await fetch(base + '/healthz')).status, 200);
  assert.equal((await fetch(base + '/api/yurtdisi')).status, 401);
  const missing = await fetch(base + '/api/yurtdisi', { headers });
  assert.equal(missing.status, 503);
  assert.equal((await missing.json()).error, 'no_complete_snapshot');
  await store.write('donations', donations());
  await store.write('fx', rates());
  const result = await fetch(base + '/api/yurtdisi', { headers });
  assert.equal(result.status, 200);
  assert.equal((await result.json()).totalUsd, 1250.5);
  assert.equal(result.headers.get('cache-control'), 'private, max-age=60');
  assert.equal((await fetch(base + '/api/yurtdisi', { method: 'POST', headers })).status, 405);
  assert.equal((await fetch(base + '/missing')).status, 404);
});
