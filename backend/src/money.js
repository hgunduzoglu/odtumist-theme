import { SourceError } from './errors.js';

export function parseUsdCents(input) {
  if (typeof input === 'number') {
    if (!Number.isFinite(input) || input < 0 || !Number.isSafeInteger(Math.round(input * 100)) || Math.abs(input * 100 - Math.round(input * 100)) > 0.000001) throw new SourceError('invalid_amount', 'Invalid monetary amount');
    return Math.round(input * 100);
  }
  if (typeof input !== 'string') throw new SourceError('invalid_amount', 'Missing monetary amount');
  const value = input.trim().replace(/^USD\s*/i, '').replace(/^\$\s*/, '').replace(/\s*USD$/i, '').trim();
  if (!/^(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d{1,2})?$/.test(value)) {
    throw new SourceError('invalid_amount', 'Expected an exact USD amount, without K/M abbreviations');
  }
  const [whole, fraction = ''] = value.replaceAll(',', '').split('.');
  const cents = BigInt(whole) * 100n + BigInt(fraction.padEnd(2, '0'));
  if (cents > BigInt(Number.MAX_SAFE_INTEGER)) throw new SourceError('invalid_amount', 'Amount exceeds safe range');
  return Number(cents);
}

export function convertCents(usdCents, quote) {
  if (!Number.isSafeInteger(usdCents) || usdCents < 0 || !quote || !/^\d+(?:\.\d{1,8})?$/.test(String(quote.buying)) || !Number.isSafeInteger(quote.unit) || quote.unit <= 0) {
    throw new SourceError('invalid_rate', 'Invalid TCMB exchange rate');
  }
  const [whole, fraction = ''] = String(quote.buying).split('.');
  const numerator = BigInt(usdCents) * BigInt(whole + fraction);
  const denominator = 10n ** BigInt(fraction.length) * BigInt(quote.unit);
  if (numerator < 0n || Number(quote.buying) <= 0) throw new SourceError('invalid_rate', 'Exchange rate must be positive');
  const converted = (numerator + denominator / 2n) / denominator;
  if (converted > BigInt(Number.MAX_SAFE_INTEGER)) throw new SourceError('invalid_amount', 'Converted amount exceeds safe range');
  return Number(converted);
}

export function formatAmount(cents) {
  return cents / 100;
}
