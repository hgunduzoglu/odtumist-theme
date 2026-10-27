import { loadConfig } from './config.js';
import { fetchGiveLively } from './givelively.js';
import { fetchTcmb } from './tcmb.js';

const config = loadConfig();
let failed = false;
for (const [source, reader] of [['tcmb', fetchTcmb], ['givelively', fetchGiveLively]]) {
  try {
    const result = await reader(config);
    console.log(JSON.stringify(source === 'tcmb'
      ? { source, status: 'ok', date: result.date, usd: result.quotes.USD }
      : { source, status: 'ok', totalUsd: result.teamRaisedCents / 100, campaignCount: result.campaigns.length, expectedMemberCount: result.expectedMemberCount, verification: result.verification }));
  } catch (error) {
    console.error(JSON.stringify({ source, status: 'failed', code: error.code || 'unexpected_error', message: error.message }));
    failed = true;
  }
}
if (failed) process.exitCode = 1;
