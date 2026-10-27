import { XMLParser, XMLValidator } from 'fast-xml-parser';
import { SourceError } from './errors.js';

export function parseTcmbXml(xml, fetchedAt = new Date().toISOString()) {
  if (typeof xml !== 'string' || xml.length > 128000 || /<!DOCTYPE|<!ENTITY/i.test(xml) || XMLValidator.validate(xml) !== true) {
    throw new SourceError('invalid_tcmb_xml', 'TCMB returned invalid XML');
  }
  const parsed = new XMLParser({ ignoreAttributes: false, parseTagValue: false, processEntities: false }).parse(xml);
  const root = parsed.Tarih_Date;
  const match = /^(\d{2})\.(\d{2})\.(\d{4})$/.exec(root?.['@_Tarih'] || '');
  if (!match) throw new SourceError('invalid_tcmb_date', 'TCMB bulletin date is missing');
  const date = `${match[3]}-${match[2]}-${match[1]}`;
  const timestamp = Date.parse(`${date}T00:00:00Z`);
  if (!Number.isFinite(timestamp) || new Date(timestamp).toISOString().slice(0, 10) !== date || timestamp > Date.parse(fetchedAt) + 86400000) {
    throw new SourceError('invalid_tcmb_date', 'TCMB bulletin date is invalid');
  }
  const currencies = Array.isArray(root.Currency) ? root.Currency : [root.Currency];
  const usd = currencies.find(row => row?.['@_Kod'] === 'USD' || row?.['@_CurrencyCode'] === 'USD');
  const buying = String(usd?.ForexBuying || '').trim();
  const unit = Number(usd?.Unit);
  if (!/^\d+(?:\.\d{1,8})?$/.test(buying) || Number(buying) <= 0 || !Number.isSafeInteger(unit) || unit <= 0) {
    throw new SourceError('invalid_tcmb_rate', 'TCMB USD ForexBuying or Unit is missing');
  }
  return { date, fetchedAt, source: 'TCMB', type: 'ForexBuying', quotes: { USD: { buying, unit } } };
}

export async function fetchTcmb(config, fetchImpl = fetch) {
  const response = await fetchImpl(config.tcmbUrl, { signal: AbortSignal.timeout(20000), redirect: 'error', headers: { Accept: 'application/xml' } });
  if (!response.ok) throw new SourceError('tcmb_http_error', `TCMB HTTP ${response.status}`);
  const declared = Number(response.headers.get('content-length'));
  if (declared > 128000) throw new SourceError('invalid_tcmb_xml', 'TCMB response is too large');
  const reader = response.body.getReader();
  const chunks = [];
  let size = 0;
  try {
    while (true) {
      const { done, value } = await reader.read();
      if (done) break;
      size += value.byteLength;
      if (size > 128000) throw new SourceError('invalid_tcmb_xml', 'TCMB response is too large');
      chunks.push(value);
    }
  } finally {
    await reader.cancel().catch(() => {});
    reader.releaseLock();
  }
  return parseTcmbXml(Buffer.concat(chunks).toString('utf8'));
}
