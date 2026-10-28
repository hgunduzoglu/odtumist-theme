<?php
if (!defined('ABSPATH')) {
    exit;
}

function bursmetre_yurtdisi_default_sheet_id()
{
    return '1kj26qQyLsYr2gdaRdx7jf2g7FiGNQITULjPoP6_atcU';
}

function bursmetre_yurtdisi_endpoint()
{
    $options = get_option('bursmetre_options', []);
    $id = !empty($options['yurtdisi_sheet_id']) ? $options['yurtdisi_sheet_id'] : bursmetre_yurtdisi_default_sheet_id();
    $gid = isset($options['yurtdisi_gid']) ? absint($options['yurtdisi_gid']) : 0;
    return 'https://docs.google.com/spreadsheets/d/' . rawurlencode($id) . '/gviz/tq?tqx=out:csv&headers=1&gid=' . $gid;
}

function bursmetre_yurtdisi_cache_key($kind)
{
    // A new source/schema must never reuse the old API's fixed-rate snapshots.
    return 'bursmetre_yd_v2_' . $kind . '_' . md5(bursmetre_yurtdisi_endpoint());
}

function bursmetre_yurtdisi_empty_payload($error = '')
{
    return [
        'campaigns' => [], 'total_try' => 0.0, 'goal_try' => 0.0,
        'currency_breakdown' => [], 'last_updated' => '', 'fx' => [],
        'chart_payload' => ['labels' => [], 'values' => [], 'colors' => [], 'originals' => []],
        'max_amount' => 0.0, 'error' => $error, 'fetched_at' => time(),
    ];
}

/** Parse both Turkish and US currency formatting; invalid values are not zero. */
function bursmetre_yurtdisi_parse_amount($value)
{
    if (is_int($value) || is_float($value)) {
        return is_finite((float) $value) && $value >= 0 ? round((float) $value, 2) : null;
    }
    $value = preg_replace('/[\s\x{00A0}\x{202F}$€£₺]/u', '', trim((string) $value));
    if ($value === '' || !preg_match('/^\d[\d.,]*$/D', $value)) {
        return null;
    }
    $comma = strrpos($value, ',');
    $dot = strrpos($value, '.');
    if ($comma !== false && $dot !== false) {
        $decimal = $comma > $dot ? ',' : '.';
        $group = $decimal === ',' ? '.' : ',';
        $parts = explode($decimal, $value);
        if (count($parts) !== 2 || !preg_match('/^\d{1,3}(?:' . preg_quote($group, '/') . '\d{3})+$/D', $parts[0]) || !preg_match('/^\d{1,2}$/D', $parts[1])) {
            return null;
        }
        $value = str_replace($group, '', $parts[0]) . '.' . $parts[1];
    } elseif ($comma !== false || $dot !== false) {
        $separator = $comma !== false ? ',' : '.';
        if (preg_match('/^\d{1,3}(?:' . preg_quote($separator, '/') . '\d{3})+$/D', $value)) {
            $value = str_replace($separator, '', $value);
        } elseif (preg_match('/^\d+' . preg_quote($separator, '/') . '\d{1,2}$/D', $value)) {
            $value = str_replace($separator, '.', $value);
        } else {
            return null;
        }
    }
    $amount = (float) $value;
    return is_finite($amount) && $amount <= 1000000000000 ? round($amount, 2) : null;
}

function bursmetre_yurtdisi_normalize_currency($value)
{
    $value = strtoupper(trim((string) $value));
    $aliases = ['' => 'USD', '$' => 'USD', 'US$' => 'USD', '€' => 'EUR', '£' => 'GBP', '₺' => 'TRY', 'TL' => 'TRY'];
    return isset($aliases[$value]) ? $aliases[$value] : (preg_match('/^[A-Z]{3}$/D', $value) ? $value : '');
}

function bursmetre_yurtdisi_find_column($headers, array $needles)
{
    // Exact matching first keeps "Resim URL" from matching the donation link.
    foreach ($needles as $needle) {
        foreach ($headers as $index => $header) {
            if (bursmetre_slug($header) === bursmetre_slug($needle)) {
                return $index;
            }
        }
    }
    return null;
}

function bursmetre_yurtdisi_parse_sheet($csv)
{
    if (strlen($csv) > 2 * 1024 * 1024 || preg_match('/^\s*<(?:!doctype|html)/i', $csv)) {
        return new WP_Error('bursmetre_sheet_format', 'Yurtdışı tablosu CSV olarak okunamadı. Google Sheets paylaşım iznini kontrol edin.');
    }
    $stream = fopen('php://temp', 'r+');
    fwrite($stream, preg_replace('/^\xEF\xBB\xBF/', '', $csv));
    rewind($stream);
    $headers = fgetcsv($stream, 0, ',', '"', '');
    $headers = is_array($headers) ? array_map('trim', $headers) : [];
    $columns = [
        'ad' => bursmetre_yurtdisi_find_column($headers, ['Kampanya Adı', 'Kampanya Açan', 'Ad Soyad', 'Campaign', 'Name']),
        'raised' => bursmetre_yurtdisi_find_column($headers, ['Toplanan', 'Toplanan Bağış', 'Toplam Bağış', 'Raised']),
        'goal' => bursmetre_yurtdisi_find_column($headers, ['Hedef', 'Hedef Tutarı', 'Goal']),
        'currency' => bursmetre_yurtdisi_find_column($headers, ['Para Birimi', 'Currency']),
        'url' => bursmetre_yurtdisi_find_column($headers, ['Link', 'Kampanya Linki', 'URL']),
        'image' => bursmetre_yurtdisi_find_column($headers, ['Resim URL', 'Resim', 'Profil', 'Image URL', 'Image']),
    ];
    if ($columns['ad'] === null || $columns['raised'] === null) {
        fclose($stream);
        return new WP_Error('bursmetre_sheet_headers', 'Yurtdışı tablosunda Kampanya Adı ve Toplanan sütunları bulunamadı.');
    }
    $campaigns = [];
    $urls = [];
    $line = 1;
    while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) {
        $line++;
        if (implode('', array_map(function ($value) { return trim((string) $value); }, $row)) === '') {
            continue;
        }
        $entry = [];
        foreach ($columns as $key => $index) {
            $entry[$key] = $index !== null && isset($row[$index]) ? trim($row[$index]) : '';
        }
        if (implode('', $entry) === '') {
            continue;
        }
        $name = trim(wp_strip_all_tags($entry['ad']));
        if (in_array(bursmetre_slug($name), ['toplam', 'geneltoplam', 'total', 'grandtotal'], true)) {
            continue;
        }
        $amount = bursmetre_yurtdisi_parse_amount($entry['raised']);
        $goal = $entry['goal'] === '' ? 0.0 : bursmetre_yurtdisi_parse_amount($entry['goal']);
        $currency = bursmetre_yurtdisi_normalize_currency($entry['currency']);
        if ($name === '' || $amount === null || $goal === null || $currency === '') {
            fclose($stream);
            return new WP_Error('bursmetre_sheet_row', sprintf('Yurtdışı tablosunun %d. satırında kampanya adı, tutar, hedef veya para birimi geçersiz. Tutarlar negatif olamaz; boş Toplanan hücresi yerine 0 yazın.', $line));
        }
        $url = esc_url_raw($entry['url'], ['https', 'http']);
        if ($url !== '' && isset($urls[$url])) {
            fclose($stream);
            return new WP_Error('bursmetre_sheet_duplicate', sprintf('Yurtdışı tablosunun %d. satırındaki kampanya bağlantısı birden fazla kez girilmiş.', $line));
        }
        if ($url !== '') {
            $urls[$url] = true;
        }
        $campaigns[] = ['ad' => $name, 'raised' => $amount, 'goal' => $goal, 'currency' => $currency, 'url' => $url, 'image' => esc_url_raw($entry['image'], ['https', 'http'])];
    }
    fclose($stream);
    return ['campaigns' => $campaigns, 'lastUpdated' => gmdate('c'), 'fetched_at' => time(), 'warning' => ''];
}

function bursmetre_yurtdisi_source_failure($error, $cache_key, $label)
{
    $last = get_option($cache_key . '_last');
    if (is_array($last)) {
        $last['warning'] = $label . ' okunamadı; son başarılı kayıt kullanılıyor. ' . $error->get_error_message();
        set_transient($cache_key, $last, MINUTE_IN_SECONDS);
        return $last;
    }
    set_transient($cache_key, $error, MINUTE_IN_SECONDS);
    return $error;
}

function bursmetre_yurtdisi_fetch_remote($force_refresh = false)
{
    $key = bursmetre_yurtdisi_cache_key('sheet');
    if (!$force_refresh && ($cached = get_transient($key)) !== false) {
        return $cached;
    }
    $response = wp_safe_remote_get(bursmetre_yurtdisi_endpoint(), ['timeout' => 15, 'limit_response_size' => 2 * 1024 * 1024 + 1, 'headers' => ['Accept' => 'text/csv']]);
    if (is_wp_error($response)) {
        $result = new WP_Error('bursmetre_sheet_request', 'Google Sheets bağlantısı kurulamadı.');
    } elseif (wp_remote_retrieve_response_code($response) !== 200) {
        $result = new WP_Error('bursmetre_sheet_http', 'Yurtdışı Google Sheets tablosu okunamadı. Bağlantıya sahip kişilere görüntüleme izni verin.');
    } else {
        $result = bursmetre_yurtdisi_parse_sheet(wp_remote_retrieve_body($response));
    }
    if (is_wp_error($result)) {
        return bursmetre_yurtdisi_source_failure($result, $key, 'Yurtdışı tablosu');
    }
    set_transient($key, $result, 10 * MINUTE_IN_SECONDS);
    update_option($key . '_last', $result, false);
    return $result;
}

function bursmetre_yurtdisi_parse_tcmb($body)
{
    if (!function_exists('simplexml_load_string')) {
        return new WP_Error('bursmetre_xml_extension', 'TCMB kuru için sunucuda PHP SimpleXML uzantısının etkin olması gerekiyor.');
    }
    if (strlen($body) > 128 * 1024 || preg_match('/<!DOCTYPE|<!ENTITY/i', $body)) {
        return new WP_Error('bursmetre_fx_xml', 'TCMB kur verisi geçersiz.');
    }
    $previous = libxml_use_internal_errors(true);
    $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    if ($xml === false || $xml->getName() !== 'Tarih_Date') {
        return new WP_Error('bursmetre_fx_xml', 'TCMB kur verisi okunamadı.');
    }
    $date_text = (string) $xml['Tarih'];
    $date = DateTimeImmutable::createFromFormat('!d.m.Y', $date_text, new DateTimeZone('Europe/Istanbul'));
    if (!$date || $date->format('d.m.Y') !== $date_text || $date->getTimestamp() > time() + DAY_IN_SECONDS) {
        return new WP_Error('bursmetre_fx_date', 'TCMB bülten tarihi geçersiz.');
    }
    $rates = ['TRY' => 1.0];
    foreach ($xml->Currency as $currency) {
        $code = (string) $currency['Kod'];
        $unit = (string) $currency->Unit;
        $buying = trim((string) $currency->ForexBuying);
        if (preg_match('/^[A-Z]{3}$/D', $code) && ctype_digit($unit) && (int) $unit > 0 && preg_match('/^\d+(?:\.\d+)?$/D', $buying) && (float) $buying > 0) {
            $rates[$code] = (float) $buying / (int) $unit;
        }
    }
    if (!isset($rates['USD']) || !is_finite($rates['USD'])) {
        return new WP_Error('bursmetre_fx_missing', 'TCMB USD döviz alış kuru bulunamadı.');
    }
    return ['rates' => $rates, 'date' => $date->format('Y-m-d'), 'fetched_at' => time(), 'warning' => ''];
}

function bursmetre_yurtdisi_fetch_tcmb($force_refresh = false)
{
    $key = 'bursmetre_yd_tcmb_v1';
    if (!$force_refresh && ($cached = get_transient($key)) !== false) {
        return $cached;
    }
    $response = wp_safe_remote_get('https://www.tcmb.gov.tr/kurlar/today.xml', ['timeout' => 10, 'limit_response_size' => 128 * 1024 + 1]);
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        $result = new WP_Error('bursmetre_fx_request', 'TCMB döviz alış kurlarına ulaşılamadı.');
    } else {
        $result = bursmetre_yurtdisi_parse_tcmb(wp_remote_retrieve_body($response));
    }
    if (is_wp_error($result)) {
        return bursmetre_yurtdisi_source_failure($result, $key, 'TCMB kuru');
    }
    set_transient($key, $result, HOUR_IN_SECONDS);
    update_option($key . '_last', $result, false);
    return $result;
}

function bursmetre_yurtdisi_convert_to_try($amount, $currency, $rates)
{
    return round($amount * $rates[$currency], 2);
}

function bursmetre_yurtdisi_format_try($amount)
{
    return '₺' . number_format((float) $amount, 0, ',', '.');
}

function bursmetre_yurtdisi_format_original($amount, $currency)
{
    $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'TRY' => '₺'];
    $decimals = abs($amount - round($amount)) > 0.00001 ? 2 : 0;
    return (isset($symbols[$currency]) ? $symbols[$currency] : '') . number_format($amount, $decimals, ',', '.') . ' ' . $currency;
}

function bursmetre_yurtdisi_prepare_chart($campaigns)
{
    $palette = ['#ffcd56', '#ff6384', '#36a2eb', '#4bc0c0', '#9966ff', '#ff9f40', '#c9cbcf', '#ff6f91', '#845ef7', '#51cf66'];
    $chart = ['labels' => [], 'values' => [], 'colors' => [], 'originals' => []];
    foreach (array_slice($campaigns, 0, 10) as $index => $row) {
        $chart['labels'][] = $row['name'];
        $chart['values'][] = $row['amount_try'];
        $chart['colors'][] = $palette[$index % count($palette)];
        $chart['originals'][] = $row['amount_original_formatted'];
    }
    return $chart;
}

function bursmetre_yurtdisi_build_payload($force_refresh = false)
{
    $sheet = bursmetre_yurtdisi_fetch_remote($force_refresh);
    if (is_wp_error($sheet)) {
        return $sheet;
    }
    $fx = bursmetre_yurtdisi_fetch_tcmb($force_refresh);
    if (is_wp_error($fx)) {
        return $fx;
    }
    $campaigns = [];
    $totals = [];
    $goals = [];
    foreach ($sheet['campaigns'] as $row) {
        $currency = $row['currency'];
        if (!isset($fx['rates'][$currency]) || !is_finite($fx['rates'][$currency]) || $fx['rates'][$currency] <= 0) {
            return new WP_Error('bursmetre_currency_missing', sprintf('TCMB %s döviz alış kuru bulunamadı. Sheet’teki para birimini kontrol edin.', $currency));
        }
        $totals[$currency] = ($totals[$currency] ?? 0) + (int) round($row['raised'] * 100);
        $goals[$currency] = ($goals[$currency] ?? 0) + (int) round($row['goal'] * 100);
        $campaigns[] = [
            'name' => $row['ad'], 'url' => $row['url'], 'image' => $row['image'], 'currency' => $currency,
            'amount_original' => $row['raised'], 'amount_try' => bursmetre_yurtdisi_convert_to_try($row['raised'], $currency, $fx['rates']),
            'goal_original' => $row['goal'], 'goal_try' => bursmetre_yurtdisi_convert_to_try($row['goal'], $currency, $fx['rates']),
            'amount_original_formatted' => bursmetre_yurtdisi_format_original($row['raised'], $currency),
            'goal_original_formatted' => bursmetre_yurtdisi_format_original($row['goal'], $currency),
        ];
    }
    usort($campaigns, function ($a, $b) { return $b['amount_try'] <=> $a['amount_try']; });
    $breakdown = [];
    $total_try = 0.0;
    $goal_try = 0.0;
    foreach ($totals as $currency => $cents) {
        $amount = $cents / 100;
        $try = bursmetre_yurtdisi_convert_to_try($amount, $currency, $fx['rates']);
        $breakdown[] = ['currency' => $currency, 'amount' => $amount, 'amount_try' => $try, 'formatted' => bursmetre_yurtdisi_format_original($amount, $currency)];
        $total_try += $try;
        $goal_try += bursmetre_yurtdisi_convert_to_try($goals[$currency] / 100, $currency, $fx['rates']);
    }
    $warnings = array_filter([$sheet['warning'], $fx['warning']]);
    if (strtotime($fx['date'] . ' UTC') < time() - 7 * DAY_IN_SECONDS) {
        $warnings[] = 'Kullanılan TCMB bülteni 7 günden eski. Kur tarihi aşağıda gösterilmiştir.';
    }
    return [
        'campaigns' => $campaigns, 'total_try' => round($total_try, 2), 'goal_try' => round($goal_try, 2),
        'currency_breakdown' => $breakdown, 'last_updated' => $sheet['lastUpdated'], 'fx' => $fx,
        'chart_payload' => bursmetre_yurtdisi_prepare_chart($campaigns),
        'max_amount' => $campaigns ? max(array_column($campaigns, 'amount_try')) : 0.0,
        'error' => implode(' ', $warnings), 'fetched_at' => time(),
    ];
}

function bursmetre_yurtdisi_get_data($force_refresh = false)
{
    $key = bursmetre_yurtdisi_cache_key('payload');
    if (!$force_refresh && ($cached = get_transient($key)) !== false) {
        return $cached;
    }
    $payload = bursmetre_yurtdisi_build_payload($force_refresh);
    if (is_wp_error($payload)) {
        $fallback = get_option($key . '_last');
        $message = $payload->get_error_message();
        if (is_array($fallback)) {
            $fallback['error'] = 'Son başarılı veriler gösteriliyor. ' . $message;
        } else {
            $fallback = bursmetre_yurtdisi_empty_payload($message);
        }
        set_transient($key, $fallback, MINUTE_IN_SECONDS);
        return $fallback;
    }
    set_transient($key, $payload, 10 * MINUTE_IN_SECONDS);
    update_option($key . '_last', $payload, false);
    return $payload;
}
