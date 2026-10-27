import { loadConfig } from './config.js';
import { Store } from './store.js';
import { collect } from './collector.js';

try {
  const config = loadConfig();
  const status = await collect(config, new Store(config.dataDir));
  if (status.donations?.error || status.fx?.error) process.exitCode = 1;
} catch (error) {
  console.error(JSON.stringify({ event: 'collector_failed', code: error.code || 'unexpected_error', message: error.message }));
  process.exitCode = 1;
}
