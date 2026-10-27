import { convertCents, formatAmount } from './money.js';
import { SourceError } from './errors.js';

export function buildPayload({ donations, fx, status }, config, now = Date.now()) {
  if (!donations || !fx) return null;
  if (donations.sourceUrl !== config.sourceUrl || donations.currency !== 'USD' || !Array.isArray(donations.campaigns)
      || !Number.isSafeInteger(donations.teamRaisedCents) || donations.teamRaisedCents < 0
      || !Number.isFinite(Date.parse(donations.fetchedAt)) || !Number.isFinite(Date.parse(fx.fetchedAt))) {
    throw new SourceError('invalid_snapshot', 'Stored snapshot does not match the configured source');
  }
  const quote = fx.quotes?.USD;
  const ids = new Set();
  let membersCents = 0;
  for (const row of donations.campaigns) {
    let url;
    try { url = new URL(row.url); } catch { throw new SourceError('invalid_snapshot', 'Invalid stored campaign URL'); }
    if (!row.ad || url.origin !== new URL(config.sourceUrl).origin || !/^\/fundraisers?\//i.test(url.pathname)
        || ids.has(row.url) || !Number.isSafeInteger(row.raisedCents) || row.raisedCents < 0) {
      throw new SourceError('invalid_snapshot', 'Stored campaigns are invalid or duplicated');
    }
    ids.add(row.url);
    membersCents += row.raisedCents;
  }
  if (!Number.isSafeInteger(membersCents) || membersCents > donations.teamRaisedCents
      || donations.directTeamRaisedCents !== donations.teamRaisedCents - membersCents
      || (donations.expectedMemberCount !== null && donations.expectedMemberCount !== donations.campaigns.length)
      || (donations.campaigns.length === 0 && donations.expectedMemberCount !== 0)) {
    throw new SourceError('invalid_snapshot', 'Stored totals or campaign completeness are invalid');
  }
  const totalTryCents = convertCents(donations.teamRaisedCents, quote);
  const campaigns = donations.campaigns.map(row => ({
    id: row.id,
    ad: row.ad,
    url: row.url,
    currency: 'USD',
    raised: formatAmount(row.raisedCents),
    raisedCents: row.raisedCents,
    raisedTry: formatAmount(convertCents(row.raisedCents, quote)),
  })).sort((a, b) => b.raisedCents - a.raisedCents || a.url.localeCompare(b.url));
  const donationAge = now - Date.parse(donations.fetchedAt);
  const rateAge = now - Date.parse(`${fx.date}T00:00:00Z`);
  if (!Number.isFinite(rateAge) || donationAge < -60000 || rateAge < -86400000) throw new SourceError('invalid_snapshot', 'Stored timestamps are invalid');
  const warnings = [];
  if (status?.donations?.error) warnings.push({ source: 'givelively', code: status.donations.error.code, message: 'Bağış kaynağına son erişim başarısız; son başarılı veri gösteriliyor.' });
  if (donationAge > config.sourceStaleMs) warnings.push({ source: 'givelively', code: 'stale_donations', message: 'Bağış verisi güncelliğini yitirmiş olabilir.' });
  if (status?.fx?.error) warnings.push({ source: 'tcmb', code: status.fx.error.code, message: 'TCMB kaynağına son erişim başarısız; son başarılı kur kullanılıyor.' });
  if (rateAge > config.fxStaleMs) warnings.push({ source: 'tcmb', code: 'old_rate', message: 'Kur tarihi eski; yayımlanma tarihini kontrol edin.' });
  return {
    schemaVersion: 1,
    source: { provider: 'Give Lively', url: config.sourceUrl, verification: donations.verification },
    currency: 'USD',
    totalUsd: formatAmount(donations.teamRaisedCents),
    totalTry: formatAmount(totalTryCents),
    directTeamUsd: formatAmount(donations.directTeamRaisedCents),
    campaignCount: campaigns.length,
    expectedMemberCount: donations.expectedMemberCount,
    campaigns,
    fx: { provider: 'TCMB', url: config.tcmbUrl, type: 'ForexBuying', date: fx.date, fetchedAt: fx.fetchedAt, usdTryRate: Number(quote.buying) / quote.unit },
    scholarship: { annualTry: config.annualScholarshipTry, studentsCovered: Math.floor(totalTryCents / (config.annualScholarshipTry * 100)) },
    lastUpdated: donations.fetchedAt,
    calculatedAt: new Date(now).toISOString(),
    stale: warnings.length > 0,
    warnings,
  };
}
