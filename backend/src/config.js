import { resolve } from 'node:path';

function positiveInteger(value, fallback, name) {
  const parsed = value === undefined ? fallback : Number(value);
  if (!Number.isSafeInteger(parsed) || parsed <= 0) throw new Error(`Invalid ${name}`);
  return parsed;
}

export function loadConfig(env = process.env) {
  const sourceUrl = new URL(env.SOURCE_URL || 'https://secure.givelively.org/teams/bridge-to-turkiye/2026-btf-marathon-steps-that-shape-futures/odtumist');
  if (sourceUrl.protocol !== 'https:' || sourceUrl.hostname !== 'secure.givelively.org' || !/^\/teams\/[^/]+\/[^/]+\/[^/]+\/?$/.test(sourceUrl.pathname) || sourceUrl.username || sourceUrl.password || sourceUrl.port) {
    throw new Error('SOURCE_URL must be an HTTPS Give Lively team page');
  }
  sourceUrl.search = '';
  sourceUrl.hash = '';
  const tcmbUrl = new URL(env.TCMB_URL || 'https://www.tcmb.gov.tr/kurlar/today.xml');
  if (tcmbUrl.protocol !== 'https:' || tcmbUrl.hostname !== 'www.tcmb.gov.tr' || tcmbUrl.pathname !== '/kurlar/today.xml' || tcmbUrl.port || tcmbUrl.username || tcmbUrl.password) {
    throw new Error('TCMB_URL must be the official latest bulletin');
  }
  const port = positiveInteger(env.PORT, 3100, 'PORT');
  if (port > 65535) throw new Error('Invalid PORT');
  return {
    host: env.HOST || '127.0.0.1',
    port,
    dataDir: resolve(env.DATA_DIR || './data'),
    sourceUrl: sourceUrl.href,
    tcmbUrl: tcmbUrl.href,
    sourceTimeoutMs: positiveInteger(env.SOURCE_TIMEOUT_MS, 60000, 'SOURCE_TIMEOUT_MS'),
    sourceStaleMs: positiveInteger(env.SOURCE_STALE_MINUTES, 90, 'SOURCE_STALE_MINUTES') * 60000,
    fxStaleMs: positiveInteger(env.FX_STALE_HOURS, 96, 'FX_STALE_HOURS') * 3600000,
    annualScholarshipTry: positiveInteger(env.ANNUAL_SCHOLARSHIP_TRY, 49500, 'ANNUAL_SCHOLARSHIP_TRY'),
    apiToken: env.API_TOKEN || '',
  };
}
