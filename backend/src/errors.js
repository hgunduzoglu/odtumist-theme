export class SourceError extends Error {
  constructor(code, message, options = {}) {
    super(message, options);
    this.name = 'SourceError';
    this.code = code;
  }
}

export function errorSummary(error) {
  return { code: error.code || 'unexpected_error', message: error.message, at: new Date().toISOString() };
}
