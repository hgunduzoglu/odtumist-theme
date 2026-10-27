<?php
if (!defined('ABSPATH')) {
    exit;
}

function bursmetre_get_marathon_page_id()
{
    $options = get_option('bursmetre_options', []);
    $selected = !empty($options['marathon_page_id']) ? get_post((int) $options['marathon_page_id']) : null;
    if ($selected) {
        return $selected->post_type === 'page' && $selected->post_status === 'publish' ? (int) $selected->ID : 0;
    }
    foreach (['dayanisma-spor-maraton', 'dayanisma/maraton', 'maraton'] as $path) {
        $page = get_page_by_path($path, OBJECT, 'page');
        if ($page && $page->post_status === 'publish') {
            return (int) $page->ID;
        }
    }
    return 0;
}

function bursmetre_get_dashboard_page_id($kind = 'dashboard')
{
    $pages = get_option('bursmetre_pages', []);
    $page = !empty($pages[$kind]) ? get_post((int) $pages[$kind]) : null;
    return $page && $page->post_type === 'page' && $page->post_status === 'publish' ? (int) $page->ID : 0;
}

function bursmetre_get_dashboard_url($kind = 'dashboard')
{
    $page_id = bursmetre_get_dashboard_page_id($kind);
    if ($page_id) {
        return get_permalink($page_id);
    }
    $parent_id = bursmetre_get_marathon_page_id();
    $base = $parent_id ? get_permalink($parent_id) : home_url('/dayanisma-spor-maraton/');
    return trailingslashit($base) . 'bursmetre/' . ($kind === 'yurtdisi' ? 'yurtdisi/' : '');
}

function bursmetre_setup_pages()
{
    $parent_id = bursmetre_get_marathon_page_id();
    if (!$parent_id) {
        return new WP_Error('bursmetre_parent_missing', 'Maraton üst sayfası bulunamadı. Burs Metre ayarlarından yayınlanmış maraton sayfasını seçip kaydedin.');
    }
    $pages = get_option('bursmetre_pages', []);
    // Validate both destinations before creating or moving either page.
    $existing = [];
    $parent_path = get_page_uri($parent_id);
    foreach (['dashboard' => $parent_path . '/bursmetre', 'yurtdisi' => $parent_path . '/bursmetre/yurtdisi'] as $kind => $path) {
        $page = !empty($pages[$kind]) ? get_post((int) $pages[$kind]) : null;
        if (!$page || $page->post_status === 'trash' || get_post_meta($page->ID, '_bursmetre_page', true) !== $kind) {
            $page = null;
        }
        $collision = get_page_by_path($path, OBJECT, 'page');
        if ($collision && (!$page || (int) $collision->ID !== (int) $page->ID)) {
            return new WP_Error('bursmetre_page_exists', 'Hedef adreste mevcut bir sayfa var; içeriği korunarak işlem durduruldu: ' . $path);
        }
        $existing[$kind] = $page;
    }
    if (in_array($parent_id, array_map('intval', $pages), true)) {
        return new WP_Error('bursmetre_invalid_parent', 'Bursmetre sayfalarını kendi üst sayfası olarak seçemezsiniz.');
    }
    $ancestors = get_post_ancestors($parent_id);
    if (array_intersect(array_map('intval', $pages), $ancestors)) {
        return new WP_Error('bursmetre_invalid_parent', 'Maraton üst sayfası, Bursmetre sayfalarından birinin altında olamaz.');
    }
    foreach (['dashboard', 'yurtdisi'] as $kind) {
        $page_parent = $kind === 'dashboard' ? $parent_id : (int) $pages['dashboard'];
        $page = $existing[$kind];
        if ($page) {
            $page_id = (int) $page->ID;
            if ((int) $page->post_parent !== $page_parent) {
                $result = wp_update_post(['ID' => $page_id, 'post_parent' => $page_parent], true);
                if (is_wp_error($result)) {
                    return $result;
                }
            }
        } else {
            $page_id = wp_insert_post([
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_parent' => $page_parent,
                'post_name' => $kind === 'dashboard' ? 'bursmetre' : 'yurtdisi',
                'post_title' => $kind === 'dashboard' ? 'Bursmetre' : 'Yurtdışı Burs Kampanyaları',
                'post_content' => $kind === 'dashboard' ? '[bursmetre-dashboard]' : '[bursmetre-yurtdisi]',
            ], true);
            if (is_wp_error($page_id)) {
                return $page_id;
            }
            update_post_meta($page_id, '_bursmetre_page', $kind);
        }
        $pages[$kind] = (int) $page_id;
        update_option('bursmetre_pages', $pages, false);
    }
    update_option('bursmetre_integration_version', BURSMETRE_VERSION, false);
    return $pages;
}

function bursmetre_activate()
{
    $result = bursmetre_setup_pages();
    if (is_wp_error($result)) {
        set_transient('bursmetre_setup_error', $result->get_error_message(), MINUTE_IN_SECONDS);
    }
}
register_activation_hook(BURSMETRE_FILE, 'bursmetre_activate');

function bursmetre_upgrade_integration()
{
    if (current_user_can('manage_options') && get_option('bursmetre_integration_version') !== BURSMETRE_VERSION) {
        bursmetre_setup_pages();
    }
}
add_action('admin_init', 'bursmetre_upgrade_integration');

function bursmetre_parent_field()
{
    wp_dropdown_pages([
        'name' => 'bursmetre_options[marathon_page_id]',
        'selected' => bursmetre_get_marathon_page_id(),
        'show_option_none' => 'Maraton sayfasını otomatik bul',
        'option_none_value' => '0',
        'post_status' => 'publish',
        'exclude_tree' => bursmetre_get_dashboard_page_id(),
    ]);
}

function bursmetre_render_integration_status()
{
    $error = get_transient('bursmetre_setup_error');
    if ($error) {
        echo '<div class="notice notice-warning inline"><p>' . esc_html($error) . '</p></div>';
        delete_transient('bursmetre_setup_error');
    }
    echo '<p>';
    foreach (['dashboard' => 'Bursmetre', 'yurtdisi' => 'Yurtdışı'] as $kind => $label) {
        if (bursmetre_get_dashboard_page_id($kind)) {
            echo '<a href="' . esc_url(bursmetre_get_dashboard_url($kind)) . '" target="_blank" rel="noopener noreferrer">' . esc_html($label) . ' sayfasını aç</a> &nbsp; ';
        }
    }
    echo '</p>';
}

function bursmetre_handle_setup_pages()
{
    if (!current_user_can('manage_options')) {
        wp_die('Bu işlem için yetkiniz yok.');
    }
    check_admin_referer('bursmetre_setup_pages');
    $result = bursmetre_setup_pages();
    if (is_wp_error($result)) {
        set_transient('bursmetre_setup_error', $result->get_error_message(), MINUTE_IN_SECONDS);
    }
    wp_safe_redirect(admin_url('options-general.php?page=bursmetre_settings'));
    exit;
}
add_action('admin_post_bursmetre_setup_pages', 'bursmetre_handle_setup_pages');

function bursmetre_maybe_enqueue_assets()
{
    if (!is_singular()) {
        return;
    }
    $post_id = (int) get_queried_object_id();
    $content = (string) get_post_field('post_content', $post_id);
    $elementor_data = (string) get_post_meta($post_id, '_elementor_data', true);
    if (get_post_meta($post_id, '_bursmetre_page', true)
        || has_shortcode($content, 'bursmetre-dashboard') || has_shortcode($content, 'bursmetre-yurtdisi')
        || strpos($elementor_data, '[bursmetre-dashboard') !== false || strpos($elementor_data, '[bursmetre-yurtdisi') !== false) {
        bursmetre_enqueue_assets();
    }
}

function bursmetre_page_template($template)
{
    $post_id = (int) get_queried_object_id();
    if (!is_page() || !get_post_meta($post_id, '_bursmetre_page', true)) {
        return $template;
    }
    // Respect a custom template or a layout subsequently built in Elementor.
    $custom = get_page_template_slug($post_id);
    $elementor = json_decode((string) get_post_meta($post_id, '_elementor_data', true), true);
    $content = (string) get_post_field('post_content', $post_id);
    if (($custom && $custom !== 'default') || !empty($elementor) || isset($_GET['elementor-preview'])
        || (!has_shortcode($content, 'bursmetre-dashboard') && !has_shortcode($content, 'bursmetre-yurtdisi'))) {
        return $template;
    }
    return plugin_dir_path(BURSMETRE_FILE) . 'includes/page-template.php';
}
add_filter('template_include', 'bursmetre_page_template', 20);

function bursmetre_rewrite_marathon_button($html)
{
    $parent_id = bursmetre_get_marathon_page_id();
    if (!$parent_id || (int) get_queried_object_id() !== $parent_id || !bursmetre_get_dashboard_page_id()) {
        return $html;
    }
    return preg_replace_callback('/<a\b([^>]*)>(.*?)<\/a>/is', function ($match) {
        if (!preg_match('/burs\s*metre/iu', wp_strip_all_tags($match[2]))) {
            return $match[0];
        }
        $attrs = preg_replace('/\s+href\s*=\s*(["\']).*?\1/is', '', $match[1]);
        $attrs = preg_replace('/\s+target\s*=\s*(["\']).*?\1/is', '', $attrs);
        return '<a' . $attrs . ' href="' . esc_url(bursmetre_get_dashboard_url()) . '">' . $match[2] . '</a>';
    }, $html);
}
add_filter('the_content', 'bursmetre_rewrite_marathon_button', 20);
add_filter('elementor/widget/render_content', 'bursmetre_rewrite_marathon_button', 20);

function bursmetre_redirect_legacy_urls()
{
    if (is_admin() || is_preview() || empty($_SERVER['REQUEST_URI'])) {
        return;
    }
    $path = trim((string) wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH), '/');
    $home_path = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');
    if ($home_path !== '') {
        if (strpos($path, $home_path . '/') !== 0) {
            return;
        }
        $path = substr($path, strlen($home_path) + 1);
    }
    $legacy = [
        'maraton/bursmetre' => 'dashboard',
        'maraton/bursmetre/yurtdisi' => 'yurtdisi',
        'dayanisma/maraton/bursmetre' => 'dashboard',
        'dayanisma/maraton/bursmetre/yurtdisi' => 'yurtdisi',
    ];
    if (isset($legacy[$path]) && bursmetre_get_dashboard_page_id($legacy[$path])) {
        $url = bursmetre_get_dashboard_url($legacy[$path]);
        if (trim((string) wp_parse_url($url, PHP_URL_PATH), '/') !== trim((string) wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH), '/')) {
            wp_safe_redirect($url, 301, 'Bursmetre');
            exit;
        }
    }
}
add_action('template_redirect', 'bursmetre_redirect_legacy_urls', 0);
