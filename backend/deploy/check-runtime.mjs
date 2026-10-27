// Initial host diagnostic. Does not write application data or solve access checks.
import { chromium } from '/opt/odtumist-runtime/node_modules/playwright/index.mjs';

let browser;
try {
  browser = await chromium.launch({ headless: true, chromiumSandbox: true });
  const page = await browser.newPage();
  await page.goto('about:blank');
  console.log(JSON.stringify({ browser: 'chromium', sandbox: 'enabled', status: 'ok' }));
  const response = await page.goto('https://secure.givelively.org/teams/bridge-to-turkiye/2026-btf-marathon-steps-that-shape-futures/odtumist', { waitUntil: 'domcontentloaded', timeout: 30000 });
  console.log(JSON.stringify({ source: 'givelively', status: response.status(), title: await page.title(), readable: response.ok() }));
  if (!response.ok()) process.exitCode = 2;
} catch (error) {
  console.error(JSON.stringify({ browser: 'chromium', status: 'failed', message: error.message }));
  process.exitCode = 1;
} finally { if (browser) await browser.close(); }
