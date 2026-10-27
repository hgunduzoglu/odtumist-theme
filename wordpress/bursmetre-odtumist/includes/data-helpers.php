<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pulls Google Sheet content as CSV and caches for ten minutes.
 */
function bursmetre_fetch_csv($sheet_id, $gid = '0')
{
    $url = "https://docs.google.com/spreadsheets/d/{$sheet_id}/gviz/tq?tqx=out:csv&gid={$gid}";
    $cache_key = 'bursmetre_cache_' . md5($url);
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $response = wp_remote_get($url, ['timeout' => 15]);
    if (is_wp_error($response)) {
        return '';
    }

    $csv = wp_remote_retrieve_body($response);
    set_transient($cache_key, $csv, 10 * MINUTE_IN_SECONDS);
    return $csv;
}

/**
 * Converts CSV string into header / rows tuple.
 */
function bursmetre_csv_to_array($csv)
{
    if (trim($csv) === '') {
        return [[], []];
    }

    $lines = preg_split("/\r\n|\n|\r/", trim($csv));
    if (!$lines) {
        return [[], []];
    }

    $headers = str_getcsv(array_shift($lines));
    $rows = [];
    foreach ($lines as $line) {
        if (trim($line) === '') {
            continue;
        }
        $rows[] = str_getcsv($line);
    }

    return [$headers, $rows];
}

/**
 * Normalises values for fuzzy matching.
 */
function bursmetre_slug($value)
{
    if ($value === null) {
        return '';
    }

    $value = (string) $value;
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $map = [
        'Ç' => 'C', 'ç' => 'c',
        'Ğ' => 'G', 'ğ' => 'g',
        'İ' => 'I', 'I' => 'I', 'ı' => 'i',
        'Ö' => 'O', 'ö' => 'o',
        'Ş' => 'S', 'ş' => 's',
        'Ü' => 'U', 'ü' => 'u',
        'Â' => 'A', 'â' => 'a',
        'Ê' => 'E', 'ê' => 'e',
        'Ô' => 'O', 'ô' => 'o'
    ];
    $value = strtr($value, $map);

    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        if ($converted !== false) {
            $value = $converted;
        }
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '', $value);
    return $value ?: '';
}

/**
 * Attempts to find a column header by keyword family.
 */
function bursmetre_find_column($headers, array $needles)
{
    if (!$headers) {
        return null;
    }

    $needle_slugs = array_filter(array_map('bursmetre_slug', $needles));

    foreach ($headers as $header) {
        $header_slug = bursmetre_slug($header);
        if ($header_slug === '') {
            continue;
        }
        foreach ($needle_slugs as $needle) {
            if ($needle !== '' && strpos($header_slug, $needle) !== false) {
                return $header;
            }
        }
    }

    return null;
}

/**
 * Safely pulls a cell from row by header.
 */
function bursmetre_get_value($row, $headers, $header)
{
    if ($header === null) {
        return '';
    }
    $index = array_search($header, $headers, true);
    if ($index === false || !isset($row[$index])) {
        return '';
    }
    return trim($row[$index]);
}

/**
 * Parses Turkish formatted amounts.
 */
function bursmetre_parse_amount($value)
{
    if (is_int($value) || is_float($value)) {
        return (float) $value;
    }

    $value = (string) $value;
    $value = str_replace(["\u{00A0}", ' '], '', $value);
    $value = preg_replace('/[^\d,.\-]/', '', $value);

    $comma_count = substr_count($value, ',');
    $dot_count = substr_count($value, '.');
    $has_comma = $comma_count > 0;
    $has_dot = $dot_count > 0;

    if ($has_comma && $has_dot) {
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
    } elseif ($has_comma) {
        $value = str_replace(',', '.', $value);
    } elseif ($has_dot && preg_match('/^-?\d{1,3}(?:\.\d{3})+$/', $value)) {
        $value = str_replace('.', '', $value);
    }

    $value = trim($value);
    if ($value === '' || $value === '.') {
        return 0.0;
    }

    return (float) $value;
}

/**
 * Parses integer-like values.
 */
function bursmetre_parse_int($value)
{
    if (is_numeric($value)) {
        return (int) $value;
    }
    $value = preg_replace('/[^\d\-]/', '', (string) $value);
    return $value === '' ? 0 : (int) $value;
}

/**
 * Currency formatting helper.
 */
function bursmetre_format_currency($amount)
{
    return '₺' . number_format((float) $amount, 0, ',', '.');
}

