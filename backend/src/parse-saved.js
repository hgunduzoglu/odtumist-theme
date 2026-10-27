import { readFile } from 'node:fs/promises';
import { loadConfig } from './config.js';
import { parseGiveLivelyHtml, validateCompleteDonations } from './givelively-parser.js';

// Offline markup verification only. Never writes a production snapshot.
try {
  if (!process.argv[2]) throw new Error('Usage: npm run parse:saved -- /path/to/rendered-page.html');
  const html = await readFile(process.argv[2], 'utf8');
  const parsed = validateCompleteDonations(parseGiveLivelyHtml(html, loadConfig().sourceUrl));
  console.log(JSON.stringify(parsed, null, 2));
} catch (error) {
  console.error(JSON.stringify({ code: error.code || 'parse_error', message: error.message }));
  process.exitCode = 1;
}
