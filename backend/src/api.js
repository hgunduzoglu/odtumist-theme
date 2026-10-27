import { createServer } from 'node:http';
import { timingSafeEqual } from 'node:crypto';
import { buildPayload } from './payload.js';

function authenticated(request, token) {
  if (!token) return true;
  const supplied = Buffer.from(request.headers.authorization || '');
  const expected = Buffer.from(`Bearer ${token}`);
  return supplied.length === expected.length && timingSafeEqual(supplied, expected);
}

function send(response, statusCode, body, extraHeaders = {}) {
  response.writeHead(statusCode, { 'Content-Type': 'application/json; charset=utf-8', 'X-Content-Type-Options': 'nosniff', 'Cache-Control': 'no-store', ...extraHeaders });
  response.end(JSON.stringify(body) + '\n');
}

export function createApi(config, store) {
  const server = createServer(async (request, response) => {
    if (request.method !== 'GET' && request.method !== 'HEAD') return send(response, 405, { error: 'method_not_allowed' }, { Allow: 'GET, HEAD' });
    let path;
    try { path = new URL(request.url, 'http://localhost').pathname.replace(/\/$/, '') || '/'; }
    catch { return send(response, 400, { error: 'invalid_url' }); }
    if (path === '/healthz') return send(response, 200, { status: 'ok', service: 'odtumist-bursmetre', version: '0.1.0' });
    if (!['/api/yurtdisi', '/api/v1/yurtdisi', '/readyz'].includes(path)) return send(response, 404, { error: 'not_found' });
    if (!authenticated(request, config.apiToken)) return send(response, 401, { error: 'unauthorized' });
    try {
      const [donations, fx, status] = await Promise.all(['donations', 'fx', 'status'].map(key => store.read(key)));
      const payload = buildPayload({ donations, fx, status }, config);
      if (!payload) {
        return send(response, 503, { status: 'waiting_for_data', error: 'no_complete_snapshot', missing: [!donations && 'givelively', !fx && 'tcmb'].filter(Boolean), sources: status || {} }, { 'Retry-After': '900' });
      }
      if (path === '/readyz') return send(response, payload.stale ? 503 : 200, { status: payload.stale ? 'stale' : 'ready', lastUpdated: payload.lastUpdated, rateDate: payload.fx.date });
      return send(response, 200, payload, { 'Cache-Control': `${config.apiToken ? 'private' : 'public'}, max-age=60`, 'X-Data-Stale': String(payload.stale) });
    } catch (error) {
      console.error(JSON.stringify({ event: 'api_snapshot_error', message: error.message }));
      return send(response, 503, { error: 'snapshot_unavailable' }, { 'Retry-After': '900' });
    }
  });
  server.requestTimeout = 10000;
  server.headersTimeout = 5000;
  server.keepAliveTimeout = 5000;
  server.maxHeadersCount = 40;
  return server;
}
