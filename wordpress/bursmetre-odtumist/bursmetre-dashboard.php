<?php
/*
Plugin Name: Burs Metre Dashboard
Description: Google Sheets verisiyle dinamik bursmetre panosu (progress, grafikler, sıralamalar). Shortcode: [bursmetre-dashboard].
Version: 1.2.0
Author: Hüsamettin Gündüzoğlu
Requires at least: 6.0
Requires PHP: 7.4
Text Domain: bursmetre
*/

if (!defined('ABSPATH')) exit;

define('BURSMETRE_VERSION', '1.2.0');
define('BURSMETRE_FILE', __FILE__);
require_once plugin_dir_path(__FILE__) . 'includes/data-helpers.php';
require_once plugin_dir_path(__FILE__) . 'includes/yurtdisi-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/wordpress-integration.php';

// ---------- Ayarlar (Settings) ----------
function bursmetre_register_settings() {
    register_setting('bursmetre_settings_group', 'bursmetre_options', ['sanitize_callback' => 'bursmetre_sanitize_options']);
    add_settings_section('bursmetre_main_section', 'Google Sheets Bağlantı Ayarları', '__return_false', 'bursmetre_settings');

    add_settings_field('burs_sheet_id', 'Bursmetre Sheet ID', 'bursmetre_field_callback', 'bursmetre_settings', 'bursmetre_main_section', ['key'=>'burs_sheet_id']);
    add_settings_field('burs_gid', 'Bursmetre GID (varsayılan 0)', 'bursmetre_field_callback', 'bursmetre_settings', 'bursmetre_main_section', ['key'=>'burs_gid']);
    add_settings_field('kamp_sheet_id', 'Kampanyalar Sheet ID', 'bursmetre_field_callback', 'bursmetre_settings', 'bursmetre_main_section', ['key'=>'kamp_sheet_id']);
    add_settings_field('kamp_gid', 'Kampanyalar GID (varsayılan 0)', 'bursmetre_field_callback', 'bursmetre_settings', 'bursmetre_main_section', ['key'=>'kamp_gid']);
    add_settings_field('goal_amount', 'Genel Hedef Tutarı (₺)', 'bursmetre_field_callback', 'bursmetre_settings', 'bursmetre_main_section', ['key'=>'goal_amount']);
    add_settings_field('yurtdisi_sheet_id', 'Yurtdışı Kampanyalar Sheet ID', 'bursmetre_field_callback', 'bursmetre_settings', 'bursmetre_main_section', ['key'=>'yurtdisi_sheet_id']);
    add_settings_field('yurtdisi_gid', 'Yurtdışı GID (varsayılan 0)', 'bursmetre_field_callback', 'bursmetre_settings', 'bursmetre_main_section', ['key'=>'yurtdisi_gid']);
    add_settings_field('yurtdisi_goal_amount', 'Yurtdışı Yedek Hedef Tutarı (₺)', 'bursmetre_field_callback', 'bursmetre_settings', 'bursmetre_main_section', ['key'=>'yurtdisi_goal_amount']);
    add_settings_section('bursmetre_pages_section', 'Maraton Sayfası Entegrasyonu', '__return_false', 'bursmetre_settings');
    add_settings_field('marathon_page_id', 'Maraton Üst Sayfası', 'bursmetre_parent_field', 'bursmetre_settings', 'bursmetre_pages_section');
}
add_action('admin_init', 'bursmetre_register_settings');

function bursmetre_field_callback($args) {
    $options = get_option('bursmetre_options', []);
    $key = $args['key'];
    $defaults = ['yurtdisi_sheet_id' => bursmetre_yurtdisi_default_sheet_id(), 'yurtdisi_gid' => '0'];
    $val = !empty($options[$key]) ? $options[$key] : ($defaults[$key] ?? '');
    echo '<input type="text" class="regular-text" name="bursmetre_options[' . esc_attr($key) . ']" value="' . esc_attr($val) . '" />';
    if ($key === 'yurtdisi_sheet_id') {
        echo '<p class="description">Kampanya Adı, Toplanan, Hedef, Para Birimi, Link ve Resim URL sütunları okunur. Gönderdiğiniz yurtdışı tablosu varsayılan olarak tanımlıdır.</p>';
    } elseif ($key === 'yurtdisi_goal_amount') {
        echo '<p class="description">Öncelikle Sheet’teki kampanya hedeflerinin TL karşılığı toplanır. Hiç hedef girilmemişse bu tutar kullanılır. Dövizler son yayımlanan TCMB döviz alış kuruyla çevrilir.</p>';
    }
}

function bursmetre_sanitize_options($input) {
    $input = is_array($input) ? $input : [];
    $clean = [];
    foreach (['burs_sheet_id', 'kamp_sheet_id', 'goal_amount', 'yurtdisi_goal_amount'] as $key) {
        $clean[$key] = isset($input[$key]) && is_scalar($input[$key]) ? sanitize_text_field($input[$key]) : '';
    }
    $sheet_id = isset($input['yurtdisi_sheet_id']) && is_scalar($input['yurtdisi_sheet_id']) ? trim((string) $input['yurtdisi_sheet_id']) : '';
    if (preg_match('~/spreadsheets/d/([A-Za-z0-9_-]+)~', $sheet_id, $match)) {
        $sheet_id = $match[1];
    }
    $clean['yurtdisi_sheet_id'] = preg_match('/^[A-Za-z0-9_-]+$/D', $sheet_id) ? $sheet_id : bursmetre_yurtdisi_default_sheet_id();
    foreach (['burs_gid', 'kamp_gid', 'yurtdisi_gid'] as $key) {
        $clean[$key] = isset($input[$key]) && is_scalar($input[$key]) ? (string) absint($input[$key]) : '0';
    }
    $clean['marathon_page_id'] = isset($input['marathon_page_id']) ? absint($input['marathon_page_id']) : 0;
    return $clean;
}

function bursmetre_add_settings_page() {
    add_options_page('Burs Metre', 'Burs Metre', 'manage_options', 'bursmetre_settings', 'bursmetre_render_settings_page');
}
add_action('admin_menu', 'bursmetre_add_settings_page');

function bursmetre_render_settings_page() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
      <h1>Burs Metre Ayarları</h1>
      <?php if (isset($_GET['yurtdisi_refreshed'])) : $refreshed = bursmetre_yurtdisi_get_data(); ?>
        <div class="notice <?php echo empty($refreshed['error']) ? 'notice-success' : 'notice-warning'; ?>"><p><?php echo esc_html($refreshed['error'] ?: 'Yurtdışı kampanyaları ve TCMB kuru yenilendi.'); ?></p></div>
      <?php endif; ?>
      <p>Google Sheets tablolarınızı bağlantıya sahip kişilerin okuyabileceği şekilde paylaşın. Sheet ID ve GID alanları önceki sürümle aynı şekilde çalışır.</p>
      <?php settings_errors(); bursmetre_render_integration_status(); ?>
      <form method="post" action="options.php">
        <?php
          settings_fields('bursmetre_settings_group');
          do_settings_sections('bursmetre_settings');
          submit_button();
        ?>
        <p>Örnek Sheet ID’ler:<br>
        Bursmetre: <code>10SUOLDAKrCbGwui9MsuH6RrjljoYtAt-CRdqPAq1Zn0</code><br>
        Kampanyalar: <code>1X6zi1KzN8WG2zitt_IJNjsOt1qleUmZFlt4g0P_0Tlc</code></p>
      </form>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('bursmetre_setup_pages'); ?>
        <input type="hidden" name="action" value="bursmetre_setup_pages" />
        <?php submit_button('Bursmetre Sayfalarını Kur / Bağlantıları Güncelle', 'secondary'); ?>
        <p class="description">Seçili maraton sayfasının altında Bursmetre ve Yurtdışı sayfalarını oluşturur. Var olan sayfa içerikleri korunur. Üst sayfayı değiştirdiyseniz önce ayarları kaydedin.</p>
      </form>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('bursmetre_refresh_yurtdisi'); ?>
        <input type="hidden" name="action" value="bursmetre_refresh_yurtdisi" />
        <?php submit_button('Yurtdışı Verilerini ve Kuru Yenile', 'secondary'); ?>
        <p class="description">Sheet’i düzenledikten sonra önbelleği beklemeden günceller. Sheet ID/GID değiştirdiyseniz önce ayarları kaydedin.</p>
      </form>
    </div>
    <?php
}

// ---------- Asset'ler ----------
function bursmetre_enqueue_assets() {
    wp_enqueue_style('bursmetre-style', plugins_url('css/style.css', __FILE__), [], BURSMETRE_VERSION);
    if (!wp_script_is('bursmetre-chartjs', 'registered')) {
        wp_register_script('bursmetre-chartjs', plugins_url('js/vendor/chart.umd.js', __FILE__), [], BURSMETRE_VERSION, true);
        // Keep other plugins' Chart constructor and defaults intact.
        wp_add_inline_script('bursmetre-chartjs', 'window.bursmetrePreviousChart = window.Chart;', 'before');
        wp_add_inline_script('bursmetre-chartjs', 'window.BursmetreChart = window.Chart; window.Chart = window.bursmetrePreviousChart; delete window.bursmetrePreviousChart;', 'after');
    }
    wp_enqueue_script('bursmetre-chartjs');
    wp_enqueue_script('bursmetre-charts', plugins_url('js/charts.js', __FILE__), ['bursmetre-chartjs'], BURSMETRE_VERSION, true);
}
add_action('wp_enqueue_scripts', 'bursmetre_maybe_enqueue_assets');

// ---------- Kısa Kod ----------
function bursmetre_dashboard_shortcode($atts = []) {
    bursmetre_enqueue_assets();
    ob_start();
    include plugin_dir_path(__FILE__) . 'includes/fetch-data.php';
    return ob_get_clean();
}
add_shortcode('bursmetre-dashboard', 'bursmetre_dashboard_shortcode');

function bursmetre_yurtdisi_shortcode($atts = []) {
    bursmetre_enqueue_assets();
    return bursmetre_render_yurtdisi_dashboard();
}
add_shortcode('bursmetre-yurtdisi', 'bursmetre_yurtdisi_shortcode');

function bursmetre_plugin_action_links($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=bursmetre_settings')) . '">' . __('Ayarlar', 'bursmetre') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'bursmetre_plugin_action_links');

// ---------- Maraton sayfasına eklenebilen bağlantı ----------
function bursmetre_button_shortcode($atts = []) {
    $atts = shortcode_atts(['label' => "Bursmetre’yi Gör", 'admin_only' => '0'], $atts, 'bursmetre_button');
    if ($atts['admin_only'] === '1' && !current_user_can('manage_options')) {
        return '';
    }
    $page_id = bursmetre_get_dashboard_page_id();
    return $page_id ? '<a href="' . esc_url(get_permalink($page_id)) . '" class="btn btn-solid bursmetre-link">' . esc_html($atts['label']) . '</a>' : '';
}
add_shortcode('bursmetre_button', 'bursmetre_button_shortcode');

function bursmetre_register_cron_schedules($schedules) {
    if (!isset($schedules['bursmetre_15_minutes'])) {
        $schedules['bursmetre_15_minutes'] = [
            'interval' => 15 * MINUTE_IN_SECONDS,
            'display'  => __('Her 15 dakikada bir', 'bursmetre'),
        ];
    }
    return $schedules;
}
add_filter('cron_schedules', 'bursmetre_register_cron_schedules');

function bursmetre_schedule_yurtdisi_event() {
    if (wp_next_scheduled('bursmetre_refresh_yurtdisi_data') && wp_get_schedule('bursmetre_refresh_yurtdisi_data') !== 'bursmetre_15_minutes') {
        wp_clear_scheduled_hook('bursmetre_refresh_yurtdisi_data');
    }
    if (!wp_next_scheduled('bursmetre_refresh_yurtdisi_data')) {
        wp_schedule_event(time() + MINUTE_IN_SECONDS, 'bursmetre_15_minutes', 'bursmetre_refresh_yurtdisi_data');
    }
}
add_action('init', 'bursmetre_schedule_yurtdisi_event');

function bursmetre_refresh_yurtdisi_data_callback() {
    require_once plugin_dir_path(__FILE__) . 'includes/yurtdisi-dashboard.php';
    bursmetre_yurtdisi_get_data(true);
}
add_action('bursmetre_refresh_yurtdisi_data', 'bursmetre_refresh_yurtdisi_data_callback');

function bursmetre_refresh_yurtdisi_admin() {
    if (!current_user_can('manage_options')) {
        wp_die('Bu işlem için yetkiniz yok.');
    }
    check_admin_referer('bursmetre_refresh_yurtdisi');
    bursmetre_yurtdisi_get_data(true);
    wp_safe_redirect(admin_url('options-general.php?page=bursmetre_settings&yurtdisi_refreshed=1'));
    exit;
}
add_action('admin_post_bursmetre_refresh_yurtdisi', 'bursmetre_refresh_yurtdisi_admin');

function bursmetre_clear_yurtdisi_schedule() {
    $timestamp = wp_next_scheduled('bursmetre_refresh_yurtdisi_data');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'bursmetre_refresh_yurtdisi_data');
    }
    $refresh_timestamp = wp_next_scheduled('bursmetre_yurtdisi_remote_refresh');
    if ($refresh_timestamp) {
        wp_unschedule_event($refresh_timestamp, 'bursmetre_yurtdisi_remote_refresh');
    }
}
register_deactivation_hook(__FILE__, 'bursmetre_clear_yurtdisi_schedule');
