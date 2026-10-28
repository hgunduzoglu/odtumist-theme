<?php
// PHP CLI regression checks, without changing a WordPress installation.
define('ABSPATH', __DIR__);
define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', 3600);
define('DAY_IN_SECONDS', 86400);
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
class WP_Error {
    private $message;
    public function __construct($code, $message) { $this->message = $message; }
    public function get_error_message() { return $this->message; }
}
$options = [];
$transients = [];
$requests = [];
$sheet_body = '';
$fx_body = '';
$offline = false;
$tcmb_offline = false;
$fx_code = 200;
function is_wp_error($value) { return $value instanceof WP_Error; }
function get_option($key, $default = false) { global $options; return $options[$key] ?? $default; }
function update_option($key, $value, $autoload = null) { global $options; $options[$key] = $value; }
function get_transient($key) { global $transients; return $transients[$key] ?? false; }
function set_transient($key, $value, $ttl) { global $transients; $transients[$key] = $value; }
function absint($value) { return abs((int) $value); }
function wp_strip_all_tags($value) { return strip_tags($value); }
function esc_url_raw($value, $protocols = []) { return preg_match('~^https?://~i', $value) ? $value : ''; }
function wp_safe_remote_get($url, $args) {
    global $requests, $sheet_body, $fx_body, $offline, $tcmb_offline, $fx_code;
    $requests[] = $url;
    $is_tcmb = strpos($url, 'tcmb.gov.tr') !== false;
    if ($offline || ($is_tcmb && $tcmb_offline)) return new WP_Error('offline', 'offline');
    return ['body' => $is_tcmb ? $fx_body : $sheet_body, 'response' => ['code' => $is_tcmb ? $fx_code : 200]];
}
function wp_remote_retrieve_body($response) { return $response['body']; }
function wp_remote_retrieve_response_code($response) { return $response['response']['code']; }
function add_action($hook, $callback) {}
function apply_filters($hook, $value) { return $value; }
function bursmetre_get_dashboard_url() { return 'https://example.org/bursmetre/'; }
function wp_json_encode($value, $flags = 0) { return json_encode($value, $flags); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return esc_html($value); }
function esc_url($value) { return esc_attr(esc_url_raw($value)); }
function __($value, $domain = '') { return $value; }
function esc_html__($value, $domain = '') { return esc_html($value); }
function esc_html_e($value, $domain = '') { echo esc_html($value); }
function number_format_i18n($value, $decimals = 0) { return number_format($value, $decimals, ',', '.'); }
function wp_date($format, $timestamp) { return date($format, $timestamp); }
function date_i18n($format, $timestamp) { return date($format, $timestamp); }
function wp_unique_id($prefix) { static $id = 0; return $prefix . ++$id; }

require __DIR__ . '/../bursmetre-odtumist/includes/data-helpers.php';
require __DIR__ . '/../bursmetre-odtumist/includes/yurtdisi-dashboard.php';

$checks = 0;
function check($condition, $message) {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}
function near($actual, $expected) { return abs($actual - $expected) < 0.001; }
function sheet_csv($rows) {
    $stream = fopen('php://temp', 'r+');
    fputcsv($stream, ['', 'Kampanya Adı', 'Toplanan', 'Hedef', 'Para Birimi', 'Link', 'Resim URL'], ',', '"', '');
    foreach ($rows as $row) fputcsv($stream, $row, ',', '"', '');
    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);
    return $csv;
}
function tcmb_xml() {
    return '<Tarih_Date Tarih="' . date('d.m.Y') . '"><Currency Kod="USD"><Unit>1</Unit><ForexBuying>49.6997</ForexBuying><ForexSelling>99</ForexSelling><BanknoteBuying>88</BanknoteBuying></Currency><Currency Kod="JPY"><Unit>100</Unit><ForexBuying>31.4275</ForexBuying></Currency></Tarih_Date>';
}

foreach (['1.250,50' => 1250.50, '1,250.50' => 1250.50, '1250,50' => 1250.50, '1250.50' => 1250.50, '10.000' => 10000, '10,000' => 10000, '0' => 0] as $input => $expected) {
    check(near(bursmetre_yurtdisi_parse_amount($input), $expected), 'Incorrect amount: ' . $input);
}
foreach (['', '-1', 'abc', '1,2,3', '1e3', '12.34.56'] as $input) {
    check(bursmetre_yurtdisi_parse_amount($input) === null, 'Invalid amount accepted: ' . $input);
}
$sheet_body = sheet_csv([
    [1, "A\nCampaign", '4126', '10000', 'USD', 'https://example.org/a', 'https://example.org/a.png'],
    [2, 'B', '0', '', 'USD', '', ''],
    [3, 'C', '100', '500', 'JPY', 'https://example.org/c', ''],
    ['', 'Genel Toplam', '999999', '', 'USD', '', ''],
]);
$fx_body = tcmb_xml();
$parsed = bursmetre_yurtdisi_parse_sheet($sheet_body);
check(!is_wp_error($parsed) && count($parsed['campaigns']) === 3, 'Multiline CSV / total row / zero fundraiser');
check($parsed['campaigns'][0]['image'] === 'https://example.org/a.png', 'Image URL confused with donation link');
check($parsed['campaigns'][0]['url'] === 'https://example.org/a', 'Donation URL incorrect');
$fx = bursmetre_yurtdisi_parse_tcmb($fx_body);
check(near($fx['rates']['USD'], 49.6997), 'Wrong TCMB rate type');
check(near($fx['rates']['JPY'], 0.314275), 'TCMB Unit ignored');
check(is_wp_error(bursmetre_yurtdisi_parse_tcmb('<!DOCTYPE x [<!ENTITY xx SYSTEM "file:///etc/passwd">]><x/>')), 'XML entities accepted');
check(is_wp_error(bursmetre_yurtdisi_parse_tcmb('<html>broken</html>')), 'Malformed XML accepted');
$payload = bursmetre_yurtdisi_get_data(true);
check(near($payload['total_try'], 205092.39), 'Aggregate currency conversion incorrect');
check(near($payload['goal_try'], 497154.14), 'Campaign goals not converted and summed');
check(count($payload['campaigns']) === 3 && $payload['campaigns'][2]['amount_original'] === 0.0, 'Zero campaign missing / incorrect ranking');
$request_count = count($requests);
bursmetre_yurtdisi_get_data();
check(count($requests) === $request_count, 'Cache performed an extra request');
$html = bursmetre_render_yurtdisi_dashboard();
check(strpos($html, 'data-progress="41.3"') !== false, 'Progress not based on campaign goal');
check(strpos($html, '<img src="https://example.org/a.png"') !== false, 'Campaign image not rendered');
check(strpos($html, 'Hedef: $10.000 USD') !== false, 'Campaign goal not rendered');
check(strpos($html, '1 USD = 49,6997 TL') !== false, 'Applied conversion rate missing');
check(stripos($html, 'Son Güncelleme') === false && strpos($html, date('d.m.Y')) === false, 'Update/date information still displayed');
$offline = true;
$stale = bursmetre_yurtdisi_get_data(true);
check(near($stale['total_try'], $payload['total_try']) && $stale['error'] !== '', 'Failed sources lost last good totals / no warning');
$options['bursmetre_options'] = ['yurtdisi_sheet_id' => 'another-sheet'];
$new_source = bursmetre_yurtdisi_get_data(true);
check($new_source['campaigns'] === [] && $new_source['error'] !== '', 'Snapshot leaked across different sheets');
$options = [];
$transients = [];
$offline = false;
$sheet_body = sheet_csv([[1, 'A', '10', '100', 'XYZ', '', '']]);
$unknown = bursmetre_yurtdisi_get_data(true);
check($unknown['total_try'] === 0.0 && $unknown['error'] !== '', 'Unknown currency treated as TRY');
$bad = bursmetre_yurtdisi_parse_sheet(sheet_csv([[1, 'A', 'bad', '100', 'USD', '', '']]));
check(is_wp_error($bad), 'Invalid row silently reduced the total');
$duplicate = bursmetre_yurtdisi_parse_sheet(sheet_csv([[1, 'A', '10', '100', 'USD', 'https://example.org/a', ''], [2, 'B', '10', '100', 'USD', 'https://example.org/a', '']]));
check(is_wp_error($duplicate), 'Duplicate fundraiser double-counted');
check(is_wp_error(bursmetre_yurtdisi_parse_sheet('<html>Sign in</html>')), 'Google sign-in page accepted');
check(!is_wp_error(bursmetre_yurtdisi_parse_sheet(sheet_csv([]))), 'A valid empty sheet should be supported');

// TCMB failure must not prevent fresh Sheet data from appearing on first use.
$options = [];
$transients = [];
$sheet_body = sheet_csv([[1, 'USD campaign', '100', '1000', 'USD', 'https://example.org/usd', '']]);
$tcmb_offline = true;
$old_payload_key = 'bursmetre_yd_v2_payload_' . md5(bursmetre_yurtdisi_endpoint());
$transients[$old_payload_key] = bursmetre_yurtdisi_empty_payload('TCMB döviz alış kurlarına ulaşılamadı.');
$transients['bursmetre_yd_tcmb_v1'] = new WP_Error('old_error', 'old error');
$fixed = bursmetre_yurtdisi_get_data();
check(count($fixed['campaigns']) === 1 && near($fixed['total_try'], 4969.97), 'Cold start with TCMB offline hid Sheet data');
check(near($fixed['goal_try'], 49699.70), 'Configured USD rate not applied to goals');
check($fixed['error'] === '' && $fixed['fx']['date'] === '', 'Rate outage exposed warning / fabricated bulletin date');
$fixed_html = bursmetre_render_yurtdisi_dashboard();
check(strpos($fixed_html, 'burs-alert') === false && stripos($fixed_html, 'Son Güncelleme') === false, 'Outage notice / update label remains');
$sheet_body = sheet_csv([[1, 'USD campaign', '200', '1000', 'USD', 'https://example.org/usd', '']]);
$updated = bursmetre_yurtdisi_get_data(true);
check(near($updated['total_try'], 9939.94), 'TCMB outage prevented newer Sheet amounts');
$tcmb_offline = false;
$fx_code = 503;
check(bursmetre_yurtdisi_get_data(true)['error'] === '', 'TCMB HTTP failure prevented USD conversion');
$fx_code = 200;
$fx_body = '<html>unavailable</html>';
check(bursmetre_yurtdisi_get_data(true)['error'] === '', 'Invalid TCMB XML prevented USD conversion');
$fx_body = str_replace('49.6997', '50.0000', tcmb_xml());
$recovered = bursmetre_yurtdisi_get_data(true);
check(near($recovered['total_try'], 10000), 'Fresh TCMB rate was not restored after recovery');
$options = [];
$transients = [];
$tcmb_offline = true;
$sheet_body = sheet_csv([[1, 'JPY campaign', '100', '1000', 'JPY', '', '']]);
$unsupported = bursmetre_yurtdisi_get_data(true);
check($unsupported['campaigns'] === [] && $unsupported['error'] !== '', 'USD-only configured rate applied to another currency');
$tcmb_offline = false;

// Optional real provider snapshots downloaded separately, never bundled in ZIP.
if (!empty($argv[1])) {
    $options = [];
    $transients = [];
    $offline = false;
    $sheet_body = file_get_contents($argv[1] . '/campaigns.csv');
    $fx_body = file_get_contents($argv[1] . '/tcmb.xml');
    $live = bursmetre_yurtdisi_get_data(true);
    check($live['error'] === '', 'Real source parse failed: ' . $live['error']);
    check(count($live['campaigns']) === 15, 'Real source campaign count');
    check(near($live['currency_breakdown'][0]['amount'], 15281), 'Real source USD total');
    check(near($live['total_try'], 759461.12), 'Real source TRY total');
    check(near($live['goal_try'], 3142263.53), 'Real source goal total');
    file_put_contents($argv[1] . '/rendered.html', bursmetre_render_yurtdisi_dashboard());
    echo json_encode(['campaigns' => count($live['campaigns']), 'total_try' => $live['total_try'], 'goal_try' => $live['goal_try'], 'fx_date' => $live['fx']['date']], JSON_UNESCAPED_UNICODE) . PHP_EOL;
    $options = [];
    $transients = [];
    $tcmb_offline = true;
    $outage_live = bursmetre_yurtdisi_get_data(true);
    check(count($outage_live['campaigns']) === 15 && near($outage_live['total_try'], 759461.12), 'Real Sheet campaigns unavailable during TCMB outage');
    check($outage_live['error'] === '', 'Real Sheet outage notice not suppressed');
    file_put_contents($argv[1] . '/rendered-fallback.html', bursmetre_render_yurtdisi_dashboard());
}
echo $checks . ' checks passed.' . PHP_EOL;
