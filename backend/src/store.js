import { mkdir, readFile, open, rename, unlink } from 'node:fs/promises';
import { join } from 'node:path';
import { randomUUID } from 'node:crypto';

const FILES = new Set(['donations', 'fx', 'status']);

export class Store {
  constructor(directory) { this.directory = directory; }

  async read(name) {
    if (!FILES.has(name)) throw new Error('Invalid store key');
    try {
      const content = await readFile(join(this.directory, `${name}.json`), 'utf8');
      if (content.length > 5000000) throw new Error('Stored snapshot is too large');
      return JSON.parse(content);
    } catch (error) {
      if (error.code === 'ENOENT') return null;
      throw error;
    }
  }

  async write(name, value) {
    if (!FILES.has(name)) throw new Error('Invalid store key');
    await mkdir(this.directory, { recursive: true, mode: 0o750 });
    const temporary = join(this.directory, `.${name}-${randomUUID()}.tmp`);
    const file = await open(temporary, 'wx', 0o640);
    try {
      await file.writeFile(JSON.stringify(value, null, 2) + '\n');
      await file.sync();
    } finally { await file.close(); }
    try { await rename(temporary, join(this.directory, `${name}.json`)); }
    finally { await unlink(temporary).catch(() => {}); }
  }
}
