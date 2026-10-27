import { open, mkdir, readFile, unlink } from 'node:fs/promises';
import { join } from 'node:path';
import { fetchTcmb } from './tcmb.js';
import { errorSummary, SourceError } from './errors.js';

async function lock(directory) {
  await mkdir(directory, { recursive: true, mode: 0o750 });
  const path = join(directory, 'collector.lock');
  async function create() {
    const handle = await open(path, 'wx', 0o640);
    await handle.writeFile(JSON.stringify({ pid: process.pid, at: Date.now() }));
    return async () => { await handle.close(); await unlink(path).catch(() => {}); };
  }
  try { return await create(); }
  catch (error) {
    if (error.code !== 'EEXIST') throw error;
    let previous;
    try { previous = JSON.parse(await readFile(path, 'utf8')); }
    catch { throw new SourceError('collector_busy', 'Collector lock cannot be verified'); }
    let alive = true;
    try { process.kill(previous.pid, 0); }
    catch (checkError) { if (checkError.code === 'ESRCH') alive = false; }
    if (alive) throw new SourceError('collector_busy', 'Another collector is running');
    await unlink(path);
    return create();
  }
}

export async function collect(config, store, { donationsFetcher, fxFetcher = fetchTcmb, log = console.log } = {}) {
  const release = await lock(config.dataDir);
  try {
    const status = await store.read('status') || {};
    for (const [key, loader] of [
      ['fx', () => fxFetcher(config)],
      ['donations', async () => {
        const fetcher = donationsFetcher || (await import('./givelively.js')).fetchGiveLively;
        return fetcher(config);
      }],
    ]) {
      const attemptAt = new Date().toISOString();
      try {
        const result = await loader();
        // Data is persisted only after a source reader validates the complete result.
        await store.write(key, result);
        status[key] = { lastAttemptAt: attemptAt, lastSuccessAt: result.fetchedAt, error: null };
        log(JSON.stringify({ event: 'collection_success', source: key, at: result.fetchedAt }));
      } catch (error) {
        status[key] = { ...status[key], lastAttemptAt: attemptAt, error: errorSummary(error) };
        log(JSON.stringify({ event: 'collection_failed', source: key, code: error.code || 'unexpected_error', message: error.message }));
      }
      await store.write('status', status);
    }
    return status;
  } finally { await release(); }
}
