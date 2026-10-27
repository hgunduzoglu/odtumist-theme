import { loadConfig } from './config.js';
import { Store } from './store.js';
import { createApi } from './api.js';

const config = loadConfig();
const server = createApi(config, new Store(config.dataDir));
server.listen(config.port, config.host, () => console.log(JSON.stringify({ event: 'api_listening', host: config.host, port: config.port })));
for (const signal of ['SIGTERM', 'SIGINT']) {
  process.once(signal, () => {
    server.close(() => process.exit(0));
    setTimeout(() => process.exit(1), 8000).unref();
  });
}
