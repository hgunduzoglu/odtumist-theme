<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('wp_body_open')) {
    function wp_body_open()
    {
        do_action('wp_body_open');
    }
}

function odtumist_setup_theme()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_theme_support('elementor');
    add_theme_support('elementor-cpt-support', array('page', 'post', 'event', 'team'));
    add_editor_style('assets/css/theme.css');
    add_post_type_support('page', 'excerpt');

    register_nav_menus(array(
        'primary-menu'            => __('Primary Menu', 'odtumist'),
        'footer-menu'             => __('Footer Menu', 'odtumist'),
        'footer-corporate-menu'   => __('Footer Corporate Menu', 'odtumist'),
        'footer-info-menu'        => __('Footer Info Menu', 'odtumist'),
    ));
}
add_action('after_setup_theme', 'odtumist_setup_theme');

function odtumist_elementor_is_active()
{
    return class_exists('\\Elementor\\Plugin');
}

function odtumist_force_elementor_compat_defaults()
{
    // Geçmişte aktif edilmiş olabilecek template kilidini her zaman kapalı tut.
    if ((bool) get_option('odtumist_lock_templates', false)) {
        update_option('odtumist_lock_templates', false);
    }

    if (!odtumist_elementor_is_active()) {
        return;
    }

    $supported_types = array('page', 'post', 'event', 'team');

    // Elementor > Ayarlar > İçerik Tipleri seçiminin eksik kalması durumunu otomatik düzelt.
    $cpt_support = get_option('elementor_cpt_support', array());
    if (!is_array($cpt_support)) {
        $cpt_support = array();
    }

    $updated_cpt_support = array_values(array_unique(array_merge($cpt_support, $supported_types)));
    if ($updated_cpt_support !== $cpt_support) {
        update_option('elementor_cpt_support', $updated_cpt_support, false);
    }

    foreach ($supported_types as $post_type) {
        if (post_type_exists($post_type)) {
            add_post_type_support($post_type, 'elementor');
        }
    }
}
add_action('init', 'odtumist_force_elementor_compat_defaults', 5);

function odtumist_is_built_with_elementor($post_id = 0)
{
    $post_id = (int) ($post_id ?: get_the_ID());
    if ($post_id <= 0 || !odtumist_elementor_is_active()) {
        return false;
    }

    if (method_exists('\\Elementor\\Plugin', 'instance')) {
        $plugin_instance = \Elementor\Plugin::instance();
        if ($plugin_instance && isset($plugin_instance->documents) && method_exists($plugin_instance->documents, 'get')) {
            $document = $plugin_instance->documents->get($post_id);
            if ($document && method_exists($document, 'is_built_with_elementor')) {
                return (bool) $document->is_built_with_elementor();
            }
        }
    }

    return get_post_meta($post_id, '_elementor_edit_mode', true) === 'builder';
}

function odtumist_is_elementor_preview_request($post_id = 0)
{
    if (!odtumist_elementor_is_active()) {
        return false;
    }

    $post_id = (int) $post_id;
    $preview_id = isset($_GET['elementor-preview']) ? (int) $_GET['elementor-preview'] : 0;
    if ($preview_id > 0) {
        if ($post_id <= 0 || $preview_id === $post_id) {
            return true;
        }
    }

    if (method_exists('\\Elementor\\Plugin', 'instance')) {
        $plugin_instance = \Elementor\Plugin::instance();

        if ($plugin_instance && isset($plugin_instance->preview) && method_exists($plugin_instance->preview, 'is_preview_mode')) {
            if ((bool) $plugin_instance->preview->is_preview_mode()) {
                return true;
            }
        }

        if ($plugin_instance && isset($plugin_instance->editor) && method_exists($plugin_instance->editor, 'is_edit_mode')) {
            if ((bool) $plugin_instance->editor->is_edit_mode()) {
                return true;
            }
        }
    }

    return false;
}

function odtumist_should_render_with_elementor($post_id = 0)
{
    $post_id = (int) ($post_id ?: get_the_ID());
    if ($post_id <= 0) {
        return false;
    }

    // Elementor editor iframe isteğinde, custom layout fallback'i yerine
    // her zaman Elementor render hattını kullan.
    if (odtumist_is_elementor_preview_request($post_id)) {
        return true;
    }

    if (odtumist_templates_are_locked() && odtumist_is_locked_template_page($post_id)) {
        return false;
    }

    $template_slug = (string) get_page_template_slug($post_id);
    if (in_array($template_slug, array('elementor_canvas', 'elementor_header_footer'), true)) {
        return true;
    }

    if (!odtumist_is_built_with_elementor($post_id)) {
        return false;
    }

    // Elementor "builder" meta'si kalmis ama dokuman bos ise,
    // sayfayi custom tema layout'una dusur.
    return odtumist_has_meaningful_elementor_data($post_id);
}

function odtumist_has_meaningful_elementor_data($post_id = 0)
{
    $post_id = (int) ($post_id ?: get_the_ID());
    if ($post_id <= 0) {
        return false;
    }

    $raw = get_post_meta($post_id, '_elementor_data', true);
    if (!is_string($raw)) {
        return false;
    }

    $raw = trim($raw);
    if ($raw === '' || $raw === '[]') {
        return false;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        $decoded = json_decode(wp_unslash($raw), true);
    }

    return is_array($decoded) && !empty($decoded);
}

function odtumist_get_locked_template_page_slugs()
{
    return array(
        'anasayfa',
        'home',
        'hakkimizda',
        'about',
        'etkinlikler',
        'events',
        'uyelik',
        'membership',
        'dayanisma',
        'solidarity',
        'iletisim',
        'contact',
        // Hakkimizda alt sayfalari
        'neler-yapiyoruz',
        'calisma-gruplarimiz',
        'tarihce',
        'yonetim',
        // Uyelik alt sayfalari
        'neden-uye-olmaliyim',
        'bilgi-guncelleme',
        'aidat-odeme',
        'uyelik-avantajlari',
        'nasil-uye-olabilirsiniz',
        'yeni-mezunlar-icin-uyelik',
        'uyelik-sss',
        // Dayanisma alt sayfalari
        'sen-de-katil',
        'networking',
        'burs',
        'maraton',
        'mentorluk',
        'bursiyerler',
        'gonulluluk',
        'genclik-iletisim',
        'bagiscilar-paydaslar',
        'gonulluler',
        'bagiscilar',
        'paydaslar',
        // Kurumsal / sabit sayfalar
        'bagis-yapin',
        'yonetim-organlari',
        'profesyonel-ekip',
        'eski-baskanlar',
        'tuzuk',
        'yonetmelikler',
        'faaliyet-raporlari',
    );
}

function odtumist_templates_are_locked()
{
    // Bu projede varsayılan davranış her zaman Elementor düzenleme serbestliği.
    return (bool) apply_filters('odtumist_templates_are_locked', false);
}

function odtumist_get_cf7_form_choices()
{
    $choices = array(
        0 => __('Form Seçilmedi', 'odtumist'),
    );

    if (!post_type_exists('wpcf7_contact_form')) {
        return $choices;
    }

    $forms = get_posts(array(
        'post_type'      => 'wpcf7_contact_form',
        'post_status'    => array('publish', 'draft'),
        'posts_per_page' => 100,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ));

    foreach ($forms as $form) {
        $choices[(int) $form->ID] = get_the_title($form);
    }

    return $choices;
}

function odtumist_get_wpforms_form_choices()
{
    $choices = array(
        0 => __('Form Seçilmedi', 'odtumist'),
    );

    if (!post_type_exists('wpforms')) {
        return $choices;
    }

    $forms = get_posts(array(
        'post_type'      => 'wpforms',
        'post_status'    => array('publish', 'draft'),
        'posts_per_page' => 100,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ));

    foreach ($forms as $form) {
        $choices[(int) $form->ID] = get_the_title($form);
    }

    return $choices;
}

function odtumist_sanitize_contact_form_provider($value)
{
    $allowed = array('cf7', 'wpforms', 'shortcode');
    $value   = sanitize_key((string) $value);

    return in_array($value, $allowed, true) ? $value : 'cf7';
}

function odtumist_is_locked_template_page($post_id = 0)
{
    $post_id = (int) ($post_id ?: get_the_ID());
    if ($post_id <= 0) {
        return false;
    }

    if (get_post_type($post_id) !== 'page') {
        return false;
    }

    $slug = sanitize_title((string) get_post_field('post_name', $post_id));
    if ($slug === '') {
        return false;
    }

    return in_array($slug, odtumist_get_locked_template_page_slugs(), true);
}

function odtumist_filter_elementor_editability_for_locked_pages($can_edit, $post_id)
{
    if (!odtumist_elementor_is_active()) {
        return $can_edit;
    }

    $post_id = (int) $post_id;
    if ($post_id <= 0) {
        return $can_edit;
    }

    $post_type = get_post_type($post_id);
    if (in_array($post_type, array('page', 'post', 'event', 'team'), true)) {
        // Kullanıcı bu tipte bir içeriği düzenleyebiliyorsa Elementor butonu mutlaka aktif olsun.
        return true;
    }

    return $can_edit;
}
add_filter('elementor/can_edit_post', 'odtumist_filter_elementor_editability_for_locked_pages', 10, 2);

function odtumist_register_elementor_locations($elementor_theme_manager)
{
    if (!is_object($elementor_theme_manager) || !method_exists($elementor_theme_manager, 'register_all_core_location')) {
        return;
    }

    $elementor_theme_manager->register_all_core_location();
}
add_action('elementor/theme/register_locations', 'odtumist_register_elementor_locations');

function odtumist_enqueue_assets()
{
    wp_enqueue_style(
        'odtumist-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap',
        array(),
        null
    );

    $theme_css_path = get_template_directory() . '/assets/css/theme.css';
    $theme_css_url  = get_template_directory_uri() . '/assets/css/theme.css';
    $theme_css_ver  = file_exists($theme_css_path) ? (string) filemtime($theme_css_path) : '1.0.0';

    $theme_js_path = get_template_directory() . '/assets/js/theme.js';
    $theme_js_url  = get_template_directory_uri() . '/assets/js/theme.js';
    $theme_js_ver  = file_exists($theme_js_path) ? (string) filemtime($theme_js_path) : '1.0.0';

    wp_enqueue_style('odtumist-theme', $theme_css_url, array('odtumist-google-fonts'), $theme_css_ver);
    wp_enqueue_script('odtumist-theme', $theme_js_url, array(), $theme_js_ver, true);
}
add_action('wp_enqueue_scripts', 'odtumist_enqueue_assets');

function odtumist_fallback_register_cpts()
{
    if (!post_type_exists('event')) {
        register_post_type('event', array(
            'labels'       => array(
                'name'          => __('Etkinlikler', 'odtumist'),
                'singular_name' => __('Etkinlik', 'odtumist'),
            ),
            'public'       => true,
            'has_archive'  => false,
            'show_in_rest' => true,
            'supports'     => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
            'rewrite'      => array('slug' => 'etkinlik'),
            'menu_icon'    => 'dashicons-calendar-alt',
        ));
    }

    if (!taxonomy_exists('event-category')) {
        register_taxonomy('event-category', 'event', array(
            'labels'       => array('name' => __('Etkinlik Kategorileri', 'odtumist')),
            'public'       => true,
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite'      => array('slug' => 'etkinlik-kategori'),
        ));
    }

    if (!post_type_exists('team')) {
        register_post_type('team', array(
            'labels'       => array(
                'name'          => __('Çalışma Grupları', 'odtumist'),
                'singular_name' => __('Çalışma Grubu', 'odtumist'),
            ),
            'public'       => true,
            'has_archive'  => true,
            'show_in_rest' => true,
            'supports'     => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
            'rewrite'      => array('slug' => 'calisma-gruplari'),
            'menu_icon'    => 'dashicons-groups',
        ));
    }

    if (!taxonomy_exists('team-category')) {
        register_taxonomy('team-category', 'team', array(
            'labels'       => array('name' => __('Grup Kategorileri', 'odtumist')),
            'public'       => true,
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite'      => array('slug' => 'grup-kategori'),
        ));
    }
}
add_action('init', 'odtumist_fallback_register_cpts', 20);

function odtumist_register_bridge_shortcodes()
{
    add_shortcode('odtumist_featured_image', 'odtumist_shortcode_featured_image');
    add_shortcode('odtumist_team_featured_image', 'odtumist_shortcode_featured_image');
}
add_action('init', 'odtumist_register_bridge_shortcodes', 30);

function odtumist_shortcode_featured_image($atts = array(), $content = null, $shortcode_tag = 'odtumist_featured_image')
{
    $atts = shortcode_atts(array(
        'id' => 0,
        'size' => 'large',
        'class' => '',
        'width' => '100%',
        'height' => '',
        'fit' => 'cover',
        'position' => 'center center',
        'radius' => '',
        'loading' => 'eager',
        'decoding' => 'async',
        'link' => '0',
    ), $atts, $shortcode_tag);

    $post_id = (int) $atts['id'];
    if ($post_id <= 0) {
        $post_id = (int) get_the_ID();
    }
    if ($post_id <= 0) {
        $post_id = (int) get_queried_object_id();
    }
    if ($post_id <= 0) {
        return '';
    }

    $size = sanitize_key((string) $atts['size']);
    $allowed_sizes = array_map('strval', get_intermediate_image_sizes());
    $allowed_sizes[] = 'full';
    $allowed_sizes[] = 'post-thumbnail';
    $allowed_sizes = array_values(array_unique($allowed_sizes));
    if ($size === '' || !in_array($size, $allowed_sizes, true)) {
        $size = 'large';
    }

    $class_tokens = array('odt-shortcode-featured-image');
    if ($shortcode_tag === 'odtumist_team_featured_image') {
        $class_tokens[] = 'odt-shortcode-team-featured-image';
    }
    $raw_class = preg_split('/\s+/', trim((string) $atts['class']));
    if (is_array($raw_class)) {
        foreach ($raw_class as $class_name) {
            $sanitized = sanitize_html_class((string) $class_name);
            if ($sanitized !== '') {
                $class_tokens[] = $sanitized;
            }
        }
    }
    $class_tokens = array_values(array_unique($class_tokens));

    $styles = array();
    $fit = sanitize_key((string) $atts['fit']);
    if (in_array($fit, array('cover', 'contain', 'fill', 'none', 'scale-down'), true)) {
        $styles[] = 'object-fit:' . $fit;
    }

    $position = trim((string) $atts['position']);
    if ($position !== '' && preg_match('/^[a-zA-Z0-9.%\-\s]+$/', $position)) {
        $styles[] = 'object-position:' . preg_replace('/\s+/', ' ', $position);
    }

    $width = trim((string) $atts['width']);
    if ($width !== '' && preg_match('/^-?(?:\d+(?:\.\d+)?)(?:px|%|em|rem|vh|vw|vmin|vmax|ch)$/', $width)) {
        $styles[] = 'width:' . $width;
    }

    $height = trim((string) $atts['height']);
    if ($height !== '' && preg_match('/^-?(?:\d+(?:\.\d+)?)(?:px|%|em|rem|vh|vw|vmin|vmax|ch)$/', $height)) {
        $styles[] = 'height:' . $height;
    }

    $radius = trim((string) $atts['radius']);
    if ($radius !== '' && preg_match('/^-?(?:\d+(?:\.\d+)?)(?:px|%|em|rem|vh|vw|vmin|vmax|ch)$/', $radius)) {
        $styles[] = 'border-radius:' . $radius;
        $styles[] = 'overflow:hidden';
    }

    $img_attrs = array(
        'class' => implode(' ', $class_tokens),
        'loading' => ($atts['loading'] === 'lazy' ? 'lazy' : 'eager'),
        'decoding' => ($atts['decoding'] === 'sync' ? 'sync' : 'async'),
    );
    if (!empty($styles)) {
        $img_attrs['style'] = implode(';', $styles) . ';';
    }

    $thumb_id = get_post_thumbnail_id($post_id);
    if (!$thumb_id) {
        return '';
    }

    $html = wp_get_attachment_image((int) $thumb_id, $size, false, $img_attrs);
    if (!is_string($html) || trim($html) === '') {
        return '';
    }

    $link = isset($atts['link']) ? strtolower(trim((string) $atts['link'])) : '0';
    $should_link = in_array($link, array('1', 'true', 'yes'), true);
    if ($should_link) {
        $permalink = get_permalink($post_id);
        if (is_string($permalink) && $permalink !== '') {
            return '<a class="odt-shortcode-featured-image-link" href="' . esc_url($permalink) . '">' . $html . '</a>';
        }
    }

    return $html;
}

function odtumist_redirect_event_archive_to_page()
{
    if (!is_post_type_archive('event')) {
        return;
    }

    $events_page = odtumist_get_page_by_slug(array('etkinlikler', 'events'));
    if (!($events_page instanceof WP_Post)) {
        return;
    }

    $target = get_permalink($events_page);
    if (!is_string($target) || $target === '') {
        return;
    }

    wp_safe_redirect($target, 301);
    exit;
}
add_action('template_redirect', 'odtumist_redirect_event_archive_to_page', 1);

function odtumist_redirect_legacy_about_child_slugs()
{
    if (!is_page() || is_admin()) {
        return;
    }

    $post_id = (int) get_queried_object_id();
    if ($post_id <= 0) {
        return;
    }

    $slug = sanitize_title((string) get_post_field('post_name', $post_id));
    $anchor_map = array(
        'neler-yapiyoruz' => 'neler-yapiyoruz',
        'calisma-gruplari' => 'calisma-gruplarimiz',
        'calisma-gruplarimiz' => 'calisma-gruplarimiz',
        'tarihce' => 'tarihce',
        'yonetim' => 'yonetim',
    );
    if (empty($anchor_map[$slug])) {
        return;
    }

    $parent_id = (int) get_post_field('post_parent', $post_id);
    if ($parent_id <= 0) {
        $about_root = odtumist_get_page_by_slug(array('hakkimizda', 'about'));
        if (!($about_root instanceof WP_Post) || (int) $about_root->ID === $post_id) {
            return;
        }
        $parent_id = (int) $about_root->ID;
    } else {
        $parent_slug = sanitize_title((string) get_post_field('post_name', $parent_id));
        if (!in_array($parent_slug, array('hakkimizda', 'about'), true)) {
            return;
        }
    }

    $parent_url = get_permalink($parent_id);
    if (!is_string($parent_url) || $parent_url === '') {
        return;
    }

    $target_url = $parent_url . '#' . sanitize_title((string) $anchor_map[$slug]);
    wp_safe_redirect($target_url, 301);
    exit;
}
add_action('template_redirect', 'odtumist_redirect_legacy_about_child_slugs', 2);

function odtumist_get_page_by_slug($slugs)
{
    foreach ($slugs as $slug) {
        $page = get_page_by_path($slug);
        if ($page instanceof WP_Post) {
            return $page;
        }
    }

    return null;
}

function odtumist_get_runtime_page_id()
{
    $post_id = (int) get_queried_object_id();
    if ($post_id > 0) {
        return $post_id;
    }

    if (isset($_GET['elementor-preview'])) {
        $preview_id = (int) $_GET['elementor-preview'];
        if ($preview_id > 0) {
            return $preview_id;
        }
    }

    return 0;
}

function odtumist_is_solidarity_root_page($post_id = 0)
{
    $post_id = (int) $post_id;
    if ($post_id <= 0) {
        $post_id = odtumist_get_runtime_page_id();
    }
    if ($post_id <= 0) {
        return false;
    }

    $post = get_post($post_id);
    if (!($post instanceof WP_Post) || $post->post_type !== 'page') {
        return false;
    }

    $slug = sanitize_title((string) $post->post_name);
    return in_array($slug, array('dayanisma', 'solidarity'), true);
}

function odtumist_elementor_first_section_has_class($post_id, $class_name)
{
    $post_id = (int) $post_id;
    $class_name = sanitize_html_class((string) $class_name);
    if ($post_id <= 0 || $class_name === '') {
        return false;
    }

    $elementor_data = get_post_meta($post_id, '_elementor_data', true);
    if (!is_string($elementor_data) || trim($elementor_data) === '') {
        return false;
    }

    $elements = json_decode(wp_unslash($elementor_data), true);
    if (!is_array($elements)) {
        return false;
    }

    foreach ($elements as $element) {
        if (!is_array($element)) {
            continue;
        }

        $element_type = isset($element['elType']) ? (string) $element['elType'] : '';
        if (!in_array($element_type, array('section', 'container'), true)) {
            continue;
        }

        $classes = isset($element['settings']['_css_classes']) ? $element['settings']['_css_classes'] : '';
        if (is_array($classes)) {
            $classes = implode(' ', $classes);
        }
        $classes = preg_split('/\s+/', trim((string) $classes));

        return in_array($class_name, $classes, true);
    }

    return false;
}

function odtumist_add_context_body_classes($classes)
{
    if (!is_array($classes)) {
        $classes = array();
    }

    $runtime_page_id = odtumist_get_runtime_page_id();

    if (!is_admin() && odtumist_is_solidarity_root_page()) {
        $classes[] = 'odt-page-dayanisma';
    }

    if (!is_admin() && odtumist_elementor_first_section_has_class($runtime_page_id, 'odt-el-banner-section')) {
        $classes[] = 'odt-has-top-elementor-banner';
    }

    return array_values(array_unique($classes));
}
add_filter('body_class', 'odtumist_add_context_body_classes');

function odtumist_extract_content_sections($content)
{
    $content = (string) $content;
    if (trim($content) === '') {
        return array();
    }

    $matches = array();
    if (!preg_match_all('/<h2\b([^>]*)>(.*?)<\/h2>/is', $content, $matches, PREG_OFFSET_CAPTURE)) {
        return array();
    }

    $sections = array();
    $count    = count($matches[0]);

    for ($i = 0; $i < $count; $i++) {
        $full_heading = $matches[0][$i][0];
        $offset       = (int) $matches[0][$i][1];
        $attrs        = (string) $matches[1][$i][0];
        $title_html   = (string) $matches[2][$i][0];

        $id = '';
        if (preg_match('/\bid=(["\'])(.*?)\1/i', $attrs, $id_match)) {
            $id = sanitize_title((string) $id_match[2]);
        }

        $title = trim(wp_strip_all_tags($title_html));
        if ($id === '') {
            $id = sanitize_title($title);
        }

        $body_start = $offset + strlen($full_heading);
        $body_end   = ($i + 1 < $count) ? (int) $matches[0][$i + 1][1] : strlen($content);
        $body_html  = trim((string) substr($content, $body_start, $body_end - $body_start));

        if ($id === '') {
            continue;
        }

        $base_id = $id;
        $suffix  = 2;
        while (isset($sections[$id])) {
            $id = $base_id . '-' . $suffix;
            $suffix++;
        }

        $sections[$id] = array(
            'id'    => $id,
            'title' => $title,
            'body'  => $body_html,
        );
    }

    return $sections;
}

function odtumist_pick_content_section($sections, $candidate_ids)
{
    if (!is_array($sections) || empty($sections)) {
        return null;
    }

    foreach ($candidate_ids as $candidate_id) {
        $key = sanitize_title((string) $candidate_id);
        if (isset($sections[$key])) {
            return $sections[$key];
        }
    }

    return null;
}

function odtumist_get_primary_cta_links()
{
    return array(
        'donation'   => get_theme_mod('odtumist_cta_donation', 'https://fonzip.com/odtumist/bagis'),
        'membership' => get_theme_mod('odtumist_cta_membership', 'https://fonzip.com/odtumist/uyelik'),
    );
}

function odtumist_get_social_links()
{
    return array(
        'instagram' => get_theme_mod('odtumist_social_instagram', 'https://www.instagram.com/odtumist/'),
        'linkedin'  => get_theme_mod('odtumist_social_linkedin', 'https://www.linkedin.com/company/odtumist'),
        'x'         => get_theme_mod('odtumist_social_x', 'https://x.com/odtumist'),
        'facebook'  => get_theme_mod('odtumist_social_facebook', 'https://www.facebook.com/groups/23239228710/'),
        'youtube'   => get_theme_mod('odtumist_social_youtube', 'https://www.youtube.com/channel/UC0LCfHsf3vCAEBDgMV20YPA'),
    );
}

function odtumist_get_social_icon_svg($network)
{
    $icons = array(
        'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><circle cx="12" cy="12" r="4"></circle><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>',
        'linkedin'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4V9h4v2"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>',
        'x'         => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="currentColor"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932 6.064-6.932zm-1.292 19.494h2.039L6.486 3.24H4.298l13.311 17.407z"></path></svg>',
        'facebook'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
        'youtube'   => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-1.96C18.88 4 12 4 12 4s-6.88 0-8.6.46A2.78 2.78 0 0 0 1.46 6.42 29.94 29.94 0 0 0 1 12a29.94 29.94 0 0 0 .46 5.58 2.78 2.78 0 0 0 1.94 1.96C5.12 20 12 20 12 20s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-1.96A29.94 29.94 0 0 0 23 12a29.94 29.94 0 0 0-.46-5.58z"></path><path d="m9.75 15.02 5.5-3.18-5.5-3.18z"></path></svg>',
    );

    if (!isset($icons[$network])) {
        return '<span>' . esc_html(strtoupper(substr((string) $network, 0, 2))) . '</span>';
    }

    return $icons[$network];
}

function odtumist_is_external_url($url)
{
    if ($url === '' || strpos($url, '#') === 0) {
        return false;
    }

    $target_host = wp_parse_url($url, PHP_URL_HOST);
    if (!$target_host) {
        return false;
    }

    $home_host = wp_parse_url(home_url('/'), PHP_URL_HOST);
    if (!$home_host) {
        return false;
    }

    return strtolower((string) $target_host) !== strtolower((string) $home_host);
}

function odtumist_wp_date($format, $timestamp = null)
{
    if (function_exists('wp_date')) {
        return wp_date($format, $timestamp);
    }

    if ($timestamp === null) {
        $timestamp = current_time('timestamp');
    }

    return date_i18n($format, $timestamp);
}

function odtumist_get_event_datetime($post_id)
{
    $start = get_post_meta($post_id, 'solicitor_event_start_dt', true);
    if (!$start) {
        return get_the_date('d M Y', $post_id);
    }

    $timestamp = strtotime((string) $start);
    if (!$timestamp) {
        return (string) $start;
    }

    return odtumist_wp_date('d M Y - H:i', $timestamp);
}

function odtumist_get_event_location($post_id)
{
    $location = get_post_meta($post_id, 'solicitor_event_address', true);
    if (!empty($location)) {
        return (string) $location;
    }

    $location = get_post_meta($post_id, 'event_address', true);
    if (!empty($location)) {
        return (string) $location;
    }

    return __('Konum yakında eklenecek', 'odtumist');
}

function odtumist_get_featured_events($limit = 8)
{
    $query = new WP_Query(array(
        'post_type'      => 'event',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => array(
            'menu_order' => 'ASC',
            'modified'   => 'DESC',
            'ID'         => 'DESC',
        ),
    ));

    if (!$query->have_posts()) {
        $query = new WP_Query(array(
            'post_type'      => 'event',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
    }

    if (!$query->have_posts()) {
        $query = new WP_Query(array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
    }

    return $query;
}

function odtumist_get_working_groups($limit = 9)
{
    $query = new WP_Query(array(
        'post_type'      => 'team',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => array(
            'menu_order' => 'ASC',
            'modified'   => 'DESC',
            'ID'         => 'DESC',
        ),
    ));

    if (!$query->have_posts()) {
        $query = new WP_Query(array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ));
    }

    return $query;
}

function odtumist_get_home_hero_slides()
{
    $about_page      = odtumist_get_page_by_slug(array('hakkimizda', 'about'));
    $solidarity_page = odtumist_get_page_by_slug(array('dayanisma', 'solidarity'));
    $contact_page    = odtumist_get_page_by_slug(array('iletisim', 'contact'));
    $ctas            = odtumist_get_primary_cta_links();

    $defaults = array(
        array(
            'image'     => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=2000',
            'title'     => "İSTANBUL'DAKİ ODTÜ'LÜLERİN BULUŞMA NOKTASI",
            'desc'      => __('İstanbul\'da yaşayan ODTÜ mezunlarıyla güçlü bir dayanışma ağı kuruyoruz.', 'odtumist'),
            'primary'   => array(
                'label' => __('Tanışalım Hocam', 'odtumist'),
                'url'   => $about_page ? get_permalink($about_page) : home_url('/hakkimizda'),
            ),
            'secondary' => array('label' => '', 'url' => ''),
        ),
        array(
            'image'     => 'https://odtumist.org/wp-content/uploads/2021/01/ODTMST-Spr-Maraton-KV1-Banner-02.jpg',
            'title'     => 'BURS VER, YARINLARA NEFES OL',
            'desc'      => __('Burs gönüllüleri arasına katıl, maratonda koş ve öğrenciler için burs fonuna destek ol.', 'odtumist'),
            'primary'   => array(
                'label' => __('Bağış Yap', 'odtumist'),
                'url'   => $ctas['donation'],
            ),
            'secondary' => array(
                'label' => __('Gönüllü Ol', 'odtumist'),
                'url'   => $contact_page ? get_permalink($contact_page) : home_url('/iletisim'),
            ),
        ),
        array(
            'image'     => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&q=80&w=2000',
            'title'     => 'MENTOR OL, TECRÜBENİ PAYLAŞ',
            'desc'      => __('Genç mezunlara ve öğrencilere yol gösterecek bir dayanışma halkasına katıl.', 'odtumist'),
            'primary'   => array(
                'label' => __('Programları İncele', 'odtumist'),
                'url'   => $solidarity_page ? get_permalink($solidarity_page) : home_url('/dayanisma'),
            ),
            'secondary' => array('label' => '', 'url' => ''),
        ),
    );

    $slides = array();
    foreach ($defaults as $index => $default) {
        $slide_no = $index + 1;

        $image   = get_theme_mod("odtumist_hero_{$slide_no}_image", $default['image']);
        $title   = get_theme_mod("odtumist_hero_{$slide_no}_title", $default['title']);
        $desc    = get_theme_mod("odtumist_hero_{$slide_no}_desc", $default['desc']);
        $p_label = get_theme_mod("odtumist_hero_{$slide_no}_primary_label", $default['primary']['label']);
        $p_url   = get_theme_mod("odtumist_hero_{$slide_no}_primary_url", $default['primary']['url']);
        $s_label = get_theme_mod("odtumist_hero_{$slide_no}_secondary_label", $default['secondary']['label']);
        $s_url   = get_theme_mod("odtumist_hero_{$slide_no}_secondary_url", $default['secondary']['url']);

        $slides[] = array(
            'image'   => $image,
            'title'   => $title,
            'desc'    => $desc,
            'primary' => array(
                'label' => $p_label,
                'url'   => $p_url,
            ),
            'secondary' => (!empty($s_label) && !empty($s_url))
                ? array('label' => $s_label, 'url' => $s_url)
                : null,
        );
    }

    return $slides;
}

function odtumist_get_home_events_copy()
{
    return array(
        'kicker'      => get_theme_mod('odtumist_home_events_kicker', __('Etkinlik Takvimini Görüntüle', 'odtumist')),
        'title'       => get_theme_mod('odtumist_home_events_title', __('Etkinliklerimiz', 'odtumist')),
        'description' => get_theme_mod('odtumist_home_events_description', __('İlgi alanlarına göre filtreleyip dayanışmanın parçasına dönüşen buluşmalarımıza katıl.', 'odtumist')),
    );
}

function odtumist_get_home_cta_content()
{
    return array(
        'membership' => array(
            'title'        => get_theme_mod('odtumist_home_membership_title', __('Üyelerimizle Varız', 'odtumist')),
            'description'  => get_theme_mod('odtumist_home_membership_description', __('İstanbul\'da ODTÜ ruhunu yeniden keşfet, dayanışma ağının parçası ol ve öğrencilerin geleceğine dokun.', 'odtumist')),
            'button_label' => get_theme_mod('odtumist_home_membership_button', __('Üye Ol', 'odtumist')),
        ),
        'volunteer' => array(
            'title'        => get_theme_mod('odtumist_home_volunteer_title', __('Gönüllülerimizle Varız', 'odtumist')),
            'description'  => get_theme_mod('odtumist_home_volunteer_description', __('Etkinliklerden burs ve mentorluğa kadar birçok alanda emeğini ve deneyimini paylaşarak topluluğu büyüt.', 'odtumist')),
            'button_label' => get_theme_mod('odtumist_home_volunteer_button', __('Gönüllü Ol', 'odtumist')),
        ),
    );
}

function odtumist_get_home_groups_copy()
{
    return array(
        'kicker' => get_theme_mod('odtumist_home_groups_kicker', __('Birlikte Üretiyoruz', 'odtumist')),
        'title'  => get_theme_mod('odtumist_home_groups_title', __('Çalışma Gruplarımız', 'odtumist')),
    );
}

function odtumist_get_home_closing_content()
{
    return array(
        'image'       => get_theme_mod('odtumist_group_photo', 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=2000'),
        'title'       => get_theme_mod('odtumist_group_photo_title', __('Dayanışma Gücümüzdür', 'odtumist')),
        'description' => get_theme_mod('odtumist_group_photo_description', __('Nerede olursak olalım aynı değerler etrafında bir araya gelir, ODTÜ ruhunu İstanbul\'da birlikte yaşatırız.', 'odtumist')),
    );
}

function odtumist_get_brand_name()
{
    $brand_name = trim((string) get_theme_mod('odtumist_brand_name', ''));
    if ($brand_name !== '') {
        return $brand_name;
    }

    $site_name = trim((string) get_bloginfo('name'));
    if ($site_name === '') {
        return 'ODTÜMİST';
    }

    if (strcasecmp($site_name, 'ODTUMIST') === 0) {
        return 'ODTÜMİST';
    }

    return $site_name;
}

function odtumist_get_contact_content()
{
    return array(
        'address'   => get_theme_mod('odtumist_contact_address', __("Cumhuriyet Cad. Cumhuriyet Apt. No: 17\nKat: 2 D: 5 Taksim, Beyoğlu, İstanbul", 'odtumist')),
        'phone'     => get_theme_mod('odtumist_contact_phone', "0546 522 96 11\n0533 206 23 01\n0546 522 96 41"),
        'email'     => get_theme_mod('odtumist_contact_email', 'dernek@odtumist.org'),
        'map_url'   => get_theme_mod('odtumist_contact_map_url', 'https://www.google.com/maps?q=Cumhuriyet+Cad.+Cumhuriyet+Apt.+No:+17,+Beyoglu,+Istanbul&output=embed'),
        'hero_image' => get_theme_mod('odtumist_contact_hero_image', 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=2000'),
        'hero_text'  => get_theme_mod('odtumist_contact_hero_text', __('İstanbul ODTÜ Mezunları Derneği ile iletişimde kalmak için bize yazabilir, arayabilir veya dernek merkezimizi ziyaret edebilirsin.', 'odtumist')),
    );
}

function odtumist_get_contact_phone_lines($raw_phone)
{
    $raw_phone = is_string($raw_phone) ? $raw_phone : '';
    $parts = preg_split('/[\r\n,;]+/', $raw_phone);
    if (!is_array($parts)) {
        $parts = array();
    }

    $phones = array();
    foreach ($parts as $part) {
        $phone = trim((string) $part);
        if ($phone !== '') {
            $phones[] = $phone;
        }
    }

    if (empty($phones) && trim($raw_phone) !== '') {
        $phones[] = trim($raw_phone);
    }

    return $phones;
}

function odtumist_get_footer_logo_image_url()
{
    $preferred_sizes = array('thumbnail', 'medium', 'full');

    $custom_url = trim((string) get_theme_mod('odtumist_footer_logo_image', ''));
    if ($custom_url !== '') {
        $custom_id = odtumist_resolve_attachment_id_from_url($custom_url);
        if ($custom_id > 0) {
            foreach ($preferred_sizes as $size_name) {
                $sized_url = wp_get_attachment_image_url($custom_id, $size_name);
                if (is_string($sized_url) && trim($sized_url) !== '') {
                    return esc_url_raw($sized_url);
                }
            }
        }

        return esc_url_raw($custom_url);
    }

    $custom_logo_id = (int) get_theme_mod('custom_logo');
    if ($custom_logo_id > 0) {
        foreach ($preferred_sizes as $size_name) {
            $logo_url = wp_get_attachment_image_url($custom_logo_id, $size_name);
            if (is_string($logo_url) && trim($logo_url) !== '') {
                return esc_url_raw($logo_url);
            }
        }
    }

    $site_icon_id = (int) get_option('site_icon');
    if ($site_icon_id > 0) {
        foreach ($preferred_sizes as $size_name) {
            $icon_url = wp_get_attachment_image_url($site_icon_id, $size_name);
            if (is_string($icon_url) && trim($icon_url) !== '') {
                return esc_url_raw($icon_url);
            }
        }
    }

    $site_icon_url = get_site_icon_url(96, '');
    if (is_string($site_icon_url) && trim($site_icon_url) !== '') {
        return esc_url_raw($site_icon_url);
    }

    return '';
}

function odtumist_resolve_attachment_id_from_url($url)
{
    $url = trim((string) $url);
    if ($url === '') {
        return 0;
    }

    $attachment_id = (int) attachment_url_to_postid($url);
    if ($attachment_id > 0) {
        return $attachment_id;
    }

    // Boyut suffix'li URL gelirse (or. image-150x150.jpg) orijinali de dene.
    $normalized_url = preg_replace('/-\d+x\d+(?=\.[a-zA-Z0-9]+(?:\?.*)?$)/', '', $url);
    if (!is_string($normalized_url) || $normalized_url === $url) {
        return 0;
    }

    return (int) attachment_url_to_postid($normalized_url);
}

function odtumist_get_footer_content()
{
    return array(
        'logo_text'    => get_theme_mod('odtumist_footer_logo_text', 'O'),
        'logo_image'   => odtumist_get_footer_logo_image_url(),
        'subtitle'     => get_theme_mod('odtumist_org_name', __('İstanbul ODTÜ Mezunları Derneği', 'odtumist')),
        'description' => get_theme_mod('odtumist_footer_description', __('ODTÜMİST; İstanbul\'daki ODTÜ mezunlarını dayanışma, burs, mentorluk ve ortak projelerde bir araya getiren mezunlar topluluğudur.', 'odtumist')),
        'quick_title'  => get_theme_mod('odtumist_footer_quick_title', __('Hızlı Erişim', 'odtumist')),
        'corp_title'   => get_theme_mod('odtumist_footer_corporate_title', __('Kurumsal', 'odtumist')),
        'info_title'   => get_theme_mod('odtumist_footer_info_title', __('Bilgi Merkezi', 'odtumist')),
        'contact_title' => get_theme_mod('odtumist_footer_contact_title', __('İletişim', 'odtumist')),
        'copyright'    => get_theme_mod('odtumist_footer_copyright_text', __('Tüm hakları saklıdır.', 'odtumist')),
    );
}

function odtumist_get_membership_menu_children($membership_url = '')
{
    $membership_url = trim((string) $membership_url);
    if ($membership_url === '') {
        $membership_page = odtumist_get_page_by_slug(array('uyelik', 'membership'));
        $membership_url = $membership_page ? get_permalink($membership_page) : home_url('/uyelik/');
    }

    $children = array();
    foreach (odtumist_get_membership_tab_defaults() as $tab) {
        $tab_id = isset($tab['id']) ? sanitize_title((string) $tab['id']) : '';
        if ($tab_id === '') {
            continue;
        }

        $children[] = array(
            'id'    => $tab_id,
            'title' => isset($tab['label']) ? (string) $tab['label'] : $tab_id,
            'url'   => $membership_url . '#' . $tab_id,
        );
    }

    return $children;
}

function odtumist_get_url_fragment_slug($url)
{
    $fragment = wp_parse_url((string) $url, PHP_URL_FRAGMENT);
    if (!is_string($fragment) || $fragment === '') {
        return '';
    }

    return sanitize_title($fragment);
}

function odtumist_menu_item_matches_membership_parent($item, $membership_url)
{
    if (!is_object($item)) {
        return false;
    }

    $title_slug = sanitize_title(wp_strip_all_tags((string) $item->title));
    if (in_array($title_slug, array('uyelik', 'membership'), true)) {
        return true;
    }

    $item_path = trim((string) wp_parse_url((string) $item->url, PHP_URL_PATH), '/');
    $membership_path = trim((string) wp_parse_url((string) $membership_url, PHP_URL_PATH), '/');

    return $membership_path !== '' && $item_path === $membership_path;
}

function odtumist_sync_primary_membership_menu_items($items)
{
    if (!is_array($items) || empty($items)) {
        return $items;
    }

    $membership_page = odtumist_get_page_by_slug(array('uyelik', 'membership'));
    $membership_url = $membership_page ? get_permalink($membership_page) : home_url('/uyelik/');
    $membership_children = odtumist_get_membership_menu_children($membership_url);
    if (empty($membership_children)) {
        return $items;
    }

    $tab_map = array();
    foreach ($membership_children as $index => $child) {
        $tab_map[$child['id']] = array(
            'order' => $index,
            'title' => $child['title'],
            'url'   => $child['url'],
        );
    }

    $membership_parent_id = 0;
    foreach ($items as $item) {
        if (!is_object($item) || (int) $item->menu_item_parent !== 0) {
            continue;
        }

        if (odtumist_menu_item_matches_membership_parent($item, $membership_url)) {
            $membership_parent_id = (int) $item->ID;
            break;
        }
    }

    if ($membership_parent_id <= 0) {
        return $items;
    }

    $membership_child_items = array();
    $original_positions = array();
    foreach ($items as $index => $item) {
        if (!is_object($item)) {
            continue;
        }

        $original_positions[(int) $item->ID] = $index;
        if ((int) $item->menu_item_parent !== $membership_parent_id) {
            continue;
        }

        $fragment = odtumist_get_url_fragment_slug($item->url);
        if (isset($tab_map[$fragment])) {
            $item->title = $tab_map[$fragment]['title'];
            $item->url = $tab_map[$fragment]['url'];
        }

        $membership_child_items[] = $item;
    }

    if (empty($membership_child_items)) {
        return $items;
    }

    usort($membership_child_items, function ($a, $b) use ($tab_map, $original_positions) {
        $fragment_a = odtumist_get_url_fragment_slug($a->url);
        $fragment_b = odtumist_get_url_fragment_slug($b->url);
        $order_a = isset($tab_map[$fragment_a]) ? $tab_map[$fragment_a]['order'] : 1000 + ($original_positions[(int) $a->ID] ?? 0);
        $order_b = isset($tab_map[$fragment_b]) ? $tab_map[$fragment_b]['order'] : 1000 + ($original_positions[(int) $b->ID] ?? 0);

        if ($order_a === $order_b) {
            return ($original_positions[(int) $a->ID] ?? 0) <=> ($original_positions[(int) $b->ID] ?? 0);
        }

        return $order_a <=> $order_b;
    });

    $synced_items = array();
    foreach ($items as $item) {
        if (!is_object($item)) {
            $synced_items[] = $item;
            continue;
        }

        if ((int) $item->menu_item_parent === $membership_parent_id) {
            continue;
        }

        $synced_items[] = $item;
        if ((int) $item->ID === $membership_parent_id) {
            foreach ($membership_child_items as $child_item) {
                $synced_items[] = $child_item;
            }
        }
    }

    return $synced_items;
}

function odtumist_filter_empty_menu_items($items, $args)
{
    if (empty($args->theme_location) || !in_array($args->theme_location, array('primary-menu', 'footer-menu', 'footer-corporate-menu', 'footer-info-menu'), true)) {
        return $items;
    }

    if ('primary-menu' === (string) $args->theme_location) {
        $items = odtumist_sync_primary_membership_menu_items($items);
    }

    $filtered = array();
    foreach ($items as $item) {
        if (trim(wp_strip_all_tags((string) $item->title)) === '') {
            continue;
        }
        $filtered[] = $item;
    }

    return $filtered;
}
add_filter('wp_nav_menu_objects', 'odtumist_filter_empty_menu_items', 10, 2);

function odtumist_render_fallback_menu($args)
{
    $menu_class = 'menu';
    $theme_location = '';
    if (is_array($args) && isset($args['menu_class'])) {
        $menu_class = (string) $args['menu_class'];
    }
    if (is_array($args) && isset($args['theme_location'])) {
        $theme_location = (string) $args['theme_location'];
    }

    $about_page      = odtumist_get_page_by_slug(array('hakkimizda', 'about'));
    $events_page     = odtumist_get_page_by_slug(array('etkinlikler', 'events'));
    $membership_page = odtumist_get_page_by_slug(array('uyelik', 'membership'));
    $solidarity_page = odtumist_get_page_by_slug(array('dayanisma', 'solidarity'));
    $contact_page    = odtumist_get_page_by_slug(array('iletisim', 'contact'));

    $about_url      = $about_page ? get_permalink($about_page) : home_url('/hakkimizda/');
    $events_url     = $events_page ? get_permalink($events_page) : home_url('/etkinlikler/');
    $membership_url = $membership_page ? get_permalink($membership_page) : home_url('/uyelik/');
    $solidarity_url = $solidarity_page ? get_permalink($solidarity_page) : home_url('/dayanisma/');
    $contact_url    = $contact_page ? get_permalink($contact_page) : home_url('/iletisim/');
    $resolve_child_url = static function ($slug, $fallback_url) {
        $child_page = get_page_by_path((string) $slug);
        if ($child_page instanceof WP_Post) {
            return get_permalink($child_page);
        }

        return $fallback_url;
    };

    if ('primary-menu' === $theme_location) {
        $items = array(
            array(
                'title' => 'HAKKIMIZDA',
                'url'   => $about_url,
                'children' => array(
                    array('title' => 'Neler Yapıyoruz?', 'url' => $about_url . '#neler-yapiyoruz'),
                    array('title' => 'Çalışma Gruplarımız', 'url' => $about_url . '#calisma-gruplarimiz'),
                    array('title' => 'Tarihçe', 'url' => $about_url . '#tarihce'),
                    array('title' => 'Yönetim', 'url' => $about_url . '#yonetim'),
                    array('title' => 'Ekip', 'url' => $resolve_child_url('profesyonel-ekip', home_url('/profesyonel-ekip/'))),
                ),
            ),
            array(
                'title' => 'ETKİNLİKLER',
                'url'   => $events_url,
                'children' => array(),
            ),
            array(
                'title' => 'ÜYELİK',
                'url'   => $membership_url,
                'children' => odtumist_get_membership_menu_children($membership_url),
            ),
            array(
                'title' => 'DAYANIŞMA',
                'url'   => $solidarity_url,
                'children' => array(
                    array('title' => 'Burs', 'url' => $solidarity_url . '#burs'),
                    array('title' => 'Spor & Maraton', 'url' => $solidarity_url . '#maraton'),
                    array('title' => 'Mentorluk', 'url' => $solidarity_url . '#mentorluk'),
                    array('title' => 'Gönüllüler', 'url' => $solidarity_url . '#gonulluluk'),
                    array('title' => 'Gençlik & İletişim', 'url' => $solidarity_url . '#genclik-iletisim'),
                    array('title' => 'Bağışçılar / Paydaşlar', 'url' => $solidarity_url . '#bagiscilar-paydaslar'),
                    array('title' => 'Bursiyerler', 'url' => $solidarity_url . '#bursiyerler'),
                    array('title' => 'Networking', 'url' => $solidarity_url . '#networking'),
                ),
            ),
            array(
                'title' => 'İLETİŞİM',
                'url'   => $contact_url,
                'children' => array(),
            ),
        );

        echo '<ul class="' . esc_attr($menu_class) . '">';
        foreach ($items as $item) {
            $has_children = !empty($item['children']);
            echo '<li class="menu-item' . ($has_children ? ' menu-item-has-children' : '') . '">';
            echo '<a href="' . esc_url($item['url']) . '">' . esc_html($item['title']) . '</a>';

            if ($has_children) {
                echo '<ul class="sub-menu">';
                foreach ($item['children'] as $child) {
                    echo '<li class="menu-item"><a href="' . esc_url($child['url']) . '">' . esc_html($child['title']) . '</a></li>';
                }
                echo '</ul>';
            }

            echo '</li>';
        }
        echo '</ul>';
        return;
    }

    if ('footer-menu' === $theme_location) {
        $items = array(
            array('title' => 'Hakkımızda', 'url' => $about_url),
            array('title' => 'Etkinlikler', 'url' => $events_url),
            array('title' => 'Üyelik', 'url' => $membership_url),
            array('title' => 'Dayanışma', 'url' => $solidarity_url),
            array('title' => 'İletişim', 'url' => $contact_url),
        );

        echo '<ul class="' . esc_attr($menu_class) . '">';
        foreach ($items as $item) {
            echo '<li class="menu-item"><a href="' . esc_url($item['url']) . '">' . esc_html($item['title']) . '</a></li>';
        }
        echo '</ul>';
        return;
    }

    if ('footer-corporate-menu' === $theme_location) {
        $items = array(
            array('title' => 'Bir Bakışta ODTÜMİST', 'url' => $about_url . '#neler-yapiyoruz'),
            array('title' => 'Yönetim Kurulu', 'url' => $resolve_child_url('yonetim-organlari', home_url('/yonetim-organlari/'))),
            array('title' => 'Profesyonel Ekip', 'url' => $resolve_child_url('profesyonel-ekip', home_url('/profesyonel-ekip/'))),
        );

        echo '<ul class="' . esc_attr($menu_class) . '">';
        foreach ($items as $item) {
            echo '<li class="menu-item"><a href="' . esc_url($item['url']) . '">' . esc_html($item['title']) . '</a></li>';
        }
        echo '</ul>';
        return;
    }

    if ('footer-info-menu' === $theme_location) {
        $items = array(
            array('title' => 'Yönetim', 'url' => $resolve_child_url('yonetim-organlari', home_url('/yonetim-organlari/'))),
            array('title' => 'Tüzük', 'url' => $resolve_child_url('tuzuk', home_url('/tuzuk/'))),
            array('title' => 'Yönetmelikler', 'url' => $resolve_child_url('yonetmelikler', home_url('/yonetmelikler/'))),
            array('title' => 'Faaliyet Raporları', 'url' => $resolve_child_url('faaliyet-raporlari', home_url('/faaliyet-raporlari/'))),
        );

        echo '<ul class="' . esc_attr($menu_class) . '">';
        foreach ($items as $item) {
            echo '<li class="menu-item"><a href="' . esc_url($item['url']) . '">' . esc_html($item['title']) . '</a></li>';
        }
        echo '</ul>';
        return;
    }

    echo '<ul class="' . esc_attr($menu_class) . '">';
    wp_list_pages(array(
        'title_li'    => '',
        'depth'       => 2,
        'sort_column' => 'menu_order,post_title',
    ));
    echo '</ul>';
}

function odtumist_get_page_layout_slug($slug)
{
    $map = array(
        'hakkimizda' => 'about-layout',
        'about'      => 'about-layout',
        'neler-yapiyoruz' => 'about-layout',
        'calisma-gruplarimiz' => 'about-layout',
        'tarihce' => 'about-layout',
        'yonetim' => 'about-layout',
        'etkinlikler' => 'events-layout',
        'events'      => 'events-layout',
        'uyelik'      => 'membership-layout',
        'membership'  => 'membership-layout',
        'neden-uye-olmaliyim' => 'membership-layout',
        'bilgi-guncelleme' => 'membership-layout',
        'aidat-odeme' => 'membership-layout',
        'uyelik-avantajlari' => 'membership-layout',
        'nasil-uye-olabilirsiniz' => 'membership-layout',
        'yeni-mezunlar-icin-uyelik' => 'membership-layout',
        'uyelik-sss' => 'membership-layout',
        'dayanisma'   => 'solidarity-layout',
        'solidarity'  => 'solidarity-layout',
        'sen-de-katil' => 'solidarity-layout',
        'networking'  => 'solidarity-layout',
        'burs'        => 'solidarity-layout',
        'maraton'     => 'solidarity-layout',
        'mentorluk'   => 'solidarity-layout',
        'bursiyerler' => 'solidarity-layout',
        'gonulluluk'  => 'solidarity-layout',
        'genclik-iletisim' => 'solidarity-layout',
        'bagiscilar-paydaslar' => 'solidarity-layout',
        'gonulluler'  => 'solidarity-layout',
        'bagiscilar'  => 'solidarity-layout',
        'paydaslar'   => 'solidarity-layout',
        'iletisim'    => 'contact-layout',
        'contact'     => 'contact-layout',
    );

    return isset($map[$slug]) ? $map[$slug] : 'default-layout';
}

function odtumist_customize_register($wp_customize)
{
    $wp_customize->add_section('odtumist_branding_section', array(
        'title'    => __('ODTÜMİST Kimlik (Header/Footer)', 'odtumist'),
        'priority' => 39,
    ));

    $branding_fields = array(
        'odtumist_brand_name' => array(
            'label' => 'Marka Adı (Header/Genel)',
            'default' => 'ODTÜMİST',
            'type' => 'text',
            'sanitize' => 'sanitize_text_field',
        ),
        'odtumist_org_name' => array(
            'label' => 'Dernek Adı (Footer Alt Başlık)',
            'default' => 'İstanbul ODTÜ Mezunları Derneği',
            'type' => 'text',
            'sanitize' => 'sanitize_text_field',
        ),
        'odtumist_footer_logo_text' => array(
            'label' => 'Footer Logo Harfi',
            'default' => 'O',
            'type' => 'text',
            'sanitize' => 'sanitize_text_field',
        ),
        'odtumist_footer_quick_title' => array(
            'label' => 'Footer Kolon 1 Başlık',
            'default' => 'Hızlı Erişim',
            'type' => 'text',
            'sanitize' => 'sanitize_text_field',
        ),
        'odtumist_footer_corporate_title' => array(
            'label' => 'Footer Kolon 2 Başlık',
            'default' => 'Kurumsal',
            'type' => 'text',
            'sanitize' => 'sanitize_text_field',
        ),
        'odtumist_footer_info_title' => array(
            'label' => 'Footer Kolon 3 Başlık',
            'default' => 'Bilgi Merkezi',
            'type' => 'text',
            'sanitize' => 'sanitize_text_field',
        ),
        'odtumist_footer_contact_title' => array(
            'label' => 'Footer İletişim Başlığı',
            'default' => 'İletişim',
            'type' => 'text',
            'sanitize' => 'sanitize_text_field',
        ),
        'odtumist_footer_copyright_text' => array(
            'label' => 'Footer Copyright Metni',
            'default' => 'Tüm hakları saklıdır.',
            'type' => 'text',
            'sanitize' => 'sanitize_text_field',
        ),
    );

    foreach ($branding_fields as $setting => $field) {
        $wp_customize->add_setting($setting, array(
            'default'           => $field['default'],
            'sanitize_callback' => $field['sanitize'],
        ));

        $wp_customize->add_control($setting, array(
            'label'   => __($field['label'], 'odtumist'),
            'section' => 'odtumist_branding_section',
            'type'    => $field['type'],
        ));
    }

    $wp_customize->add_setting('odtumist_footer_logo_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'odtumist_footer_logo_image', array(
        'label'    => __('Footer Logo Görseli', 'odtumist'),
        'section'  => 'odtumist_branding_section',
        'settings' => 'odtumist_footer_logo_image',
    )));

    $wp_customize->add_section('odtumist_social_section', array(
        'title'    => __('ODTÜMİST Sosyal ve Buton Linkleri', 'odtumist'),
        'priority' => 40,
    ));

    $url_fields = array(
        'odtumist_social_instagram' => array('label' => 'Instagram URL', 'default' => 'https://www.instagram.com/odtumist/'),
        'odtumist_social_linkedin'  => array('label' => 'LinkedIn URL', 'default' => 'https://www.linkedin.com/company/odtumist'),
        'odtumist_social_x'         => array('label' => 'X URL', 'default' => 'https://x.com/odtumist'),
        'odtumist_social_facebook'  => array('label' => 'Facebook URL', 'default' => 'https://www.facebook.com/groups/23239228710/'),
        'odtumist_social_youtube'   => array('label' => 'YouTube URL', 'default' => 'https://www.youtube.com/channel/UC0LCfHsf3vCAEBDgMV20YPA'),
        'odtumist_cta_membership'   => array('label' => 'Üyelik Başvuru URL', 'default' => 'https://fonzip.com/odtumist/uyelik'),
        'odtumist_cta_donation'     => array('label' => 'Bağış URL', 'default' => 'https://fonzip.com/odtumist/bagis'),
    );

    foreach ($url_fields as $setting => $field) {
        $wp_customize->add_setting($setting, array(
            'default'           => $field['default'],
            'sanitize_callback' => 'esc_url_raw',
        ));

        $wp_customize->add_control($setting, array(
            'label'   => __($field['label'], 'odtumist'),
            'section' => 'odtumist_social_section',
            'type'    => 'url',
        ));
    }

    $wp_customize->add_setting('odtumist_header_donation_label', array(
        'default'           => __('Bağış Yap', 'odtumist'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('odtumist_header_donation_label', array(
        'label'   => __('Header Bağış Buton Metni', 'odtumist'),
        'section' => 'odtumist_social_section',
        'type'    => 'text',
    ));

    $wp_customize->add_setting('odtumist_header_membership_label', array(
        'default'           => __('Üye Ol', 'odtumist'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('odtumist_header_membership_label', array(
        'label'   => __('Header Üyelik Buton Metni', 'odtumist'),
        'section' => 'odtumist_social_section',
        'type'    => 'text',
    ));

    $wp_customize->add_section('odtumist_home_hero_section', array(
        'title'    => __('Anasayfa Hero Slider', 'odtumist'),
        'priority' => 41,
    ));

    $hero_defaults = odtumist_get_home_hero_slides();
    foreach ($hero_defaults as $index => $slide_default) {
        $slide_no = $index + 1;

        $wp_customize->add_setting("odtumist_hero_{$slide_no}_image", array(
            'default'           => $slide_default['image'],
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, "odtumist_hero_{$slide_no}_image", array(
            'label'   => sprintf(__('Hero %d Görseli', 'odtumist'), $slide_no),
            'section' => 'odtumist_home_hero_section',
        )));

        $wp_customize->add_setting("odtumist_hero_{$slide_no}_title", array(
            'default'           => $slide_default['title'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_hero_{$slide_no}_title", array(
            'label'   => sprintf(__('Hero %d Başlık', 'odtumist'), $slide_no),
            'section' => 'odtumist_home_hero_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting("odtumist_hero_{$slide_no}_desc", array(
            'default'           => $slide_default['desc'],
            'sanitize_callback' => 'sanitize_textarea_field',
        ));
        $wp_customize->add_control("odtumist_hero_{$slide_no}_desc", array(
            'label'   => sprintf(__('Hero %d Açıklama', 'odtumist'), $slide_no),
            'section' => 'odtumist_home_hero_section',
            'type'    => 'textarea',
        ));

        $wp_customize->add_setting("odtumist_hero_{$slide_no}_primary_label", array(
            'default'           => $slide_default['primary']['label'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_hero_{$slide_no}_primary_label", array(
            'label'   => sprintf(__('Hero %d Birincil Buton Metni', 'odtumist'), $slide_no),
            'section' => 'odtumist_home_hero_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting("odtumist_hero_{$slide_no}_primary_url", array(
            'default'           => $slide_default['primary']['url'],
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control("odtumist_hero_{$slide_no}_primary_url", array(
            'label'   => sprintf(__('Hero %d Birincil Buton Linki', 'odtumist'), $slide_no),
            'section' => 'odtumist_home_hero_section',
            'type'    => 'url',
        ));

        $wp_customize->add_setting("odtumist_hero_{$slide_no}_secondary_label", array(
            'default'           => isset($slide_default['secondary']['label']) ? $slide_default['secondary']['label'] : '',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_hero_{$slide_no}_secondary_label", array(
            'label'   => sprintf(__('Hero %d İkincil Buton Metni', 'odtumist'), $slide_no),
            'section' => 'odtumist_home_hero_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting("odtumist_hero_{$slide_no}_secondary_url", array(
            'default'           => isset($slide_default['secondary']['url']) ? $slide_default['secondary']['url'] : '',
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control("odtumist_hero_{$slide_no}_secondary_url", array(
            'label'   => sprintf(__('Hero %d İkincil Buton Linki', 'odtumist'), $slide_no),
            'section' => 'odtumist_home_hero_section',
            'type'    => 'url',
        ));
    }

    $wp_customize->add_section('odtumist_home_sections', array(
        'title'    => __('Anasayfa Metinleri', 'odtumist'),
        'priority' => 42,
    ));

    $text_fields = array(
        'odtumist_home_events_kicker'        => array('label' => 'Etkinlikler Üst Link', 'default' => __('Etkinlik Takvimini Görüntüle', 'odtumist'), 'type' => 'text'),
        'odtumist_home_events_title'         => array('label' => 'Etkinlikler Başlık', 'default' => __('Etkinliklerimiz', 'odtumist'), 'type' => 'text'),
        'odtumist_home_events_description'   => array('label' => 'Etkinlikler Açıklama', 'default' => __('İlgi alanlarına göre filtreleyip dayanışmanın parçasına dönüşen buluşmalarımıza katıl.', 'odtumist'), 'type' => 'textarea'),
        'odtumist_home_membership_title'     => array('label' => 'Üyelik Blok Başlık', 'default' => __('Üyelerimizle Varız', 'odtumist'), 'type' => 'text'),
        'odtumist_home_membership_description' => array('label' => 'Üyelik Blok Açıklama', 'default' => __('İstanbul\'da ODTÜ ruhunu yeniden keşfet, dayanışma ağının parçası ol ve öğrencilerin geleceğine dokun.', 'odtumist'), 'type' => 'textarea'),
        'odtumist_home_membership_button'    => array('label' => 'Üyelik Blok Buton Metni', 'default' => __('Üye Ol', 'odtumist'), 'type' => 'text'),
        'odtumist_home_volunteer_title'      => array('label' => 'Gönüllü Blok Başlık', 'default' => __('Gönüllülerimizle Varız', 'odtumist'), 'type' => 'text'),
        'odtumist_home_volunteer_description' => array('label' => 'Gönüllü Blok Açıklama', 'default' => __('Etkinliklerden burs ve mentorluğa kadar birçok alanda emeğini ve deneyimini paylaşarak topluluğu büyüt.', 'odtumist'), 'type' => 'textarea'),
        'odtumist_home_volunteer_button'     => array('label' => 'Gönüllü Blok Buton Metni', 'default' => __('Gönüllü Ol', 'odtumist'), 'type' => 'text'),
        'odtumist_home_groups_kicker'        => array('label' => 'Çalışma Grupları Üst Başlık', 'default' => __('Birlikte Üretiyoruz', 'odtumist'), 'type' => 'text'),
        'odtumist_home_groups_title'         => array('label' => 'Çalışma Grupları Başlık', 'default' => __('Çalışma Gruplarımız', 'odtumist'), 'type' => 'text'),
        'odtumist_group_photo_title'         => array('label' => 'Kapanış Başlık', 'default' => __('Dayanışma Gücümüzdür', 'odtumist'), 'type' => 'text'),
        'odtumist_group_photo_description'   => array('label' => 'Kapanış Açıklama', 'default' => __('Nerede olursak olalım aynı değerler etrafında bir araya gelir, ODTÜ ruhunu İstanbul\'da birlikte yaşatırız.', 'odtumist'), 'type' => 'textarea'),
    );

    foreach ($text_fields as $setting => $field) {
        $sanitize_callback = $field['type'] === 'textarea' ? 'sanitize_textarea_field' : 'sanitize_text_field';

        $wp_customize->add_setting($setting, array(
            'default'           => $field['default'],
            'sanitize_callback' => $sanitize_callback,
        ));

        $wp_customize->add_control($setting, array(
            'label'   => __($field['label'], 'odtumist'),
            'section' => 'odtumist_home_sections',
            'type'    => $field['type'],
        ));
    }

    $wp_customize->add_setting('odtumist_group_photo', array(
        'default'           => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=2000',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'odtumist_group_photo', array(
        'label'   => __('Kapanış Arkaplan Görseli', 'odtumist'),
        'section' => 'odtumist_home_sections',
    )));

    $wp_customize->add_section('odtumist_contact_footer_section', array(
        'title'    => __('İletişim ve Footer Bilgileri', 'odtumist'),
        'priority' => 43,
    ));

    $contact_fields = array(
        'odtumist_contact_address' => array('label' => 'Adres', 'default' => __("Cumhuriyet Cad. Cumhuriyet Apt. No: 17\nKat: 2 D: 5 Taksim, Beyoğlu, İstanbul", 'odtumist'), 'type' => 'textarea', 'sanitize' => 'sanitize_textarea_field'),
        'odtumist_contact_phone'   => array('label' => 'Telefon', 'default' => "0546 522 96 11\n0533 206 23 01\n0546 522 96 41", 'type' => 'textarea', 'sanitize' => 'sanitize_textarea_field'),
        'odtumist_contact_email'   => array('label' => 'E-posta', 'default' => 'dernek@odtumist.org', 'type' => 'email', 'sanitize' => 'sanitize_email'),
        'odtumist_contact_map_url' => array('label' => 'Google Maps Embed URL', 'default' => 'https://www.google.com/maps?q=Cumhuriyet+Cad.+Cumhuriyet+Apt.+No:+17,+Beyoglu,+Istanbul&output=embed', 'type' => 'url', 'sanitize' => 'esc_url_raw'),
        'odtumist_contact_hero_text' => array('label' => 'İletişim Hero Açıklama', 'default' => __('İstanbul ODTÜ Mezunları Derneği ile iletişimde kalmak için bize yazabilir, arayabilir veya dernek merkezimizi ziyaret edebilirsin.', 'odtumist'), 'type' => 'textarea', 'sanitize' => 'sanitize_textarea_field'),
        'odtumist_contact_form_title' => array('label' => 'İletişim Form Alanı Başlığı', 'default' => __('Sizi Dinlemeye Hazırız', 'odtumist'), 'type' => 'text', 'sanitize' => 'sanitize_text_field'),
        'odtumist_contact_form_desc'  => array('label' => 'İletişim Form Alanı Açıklaması', 'default' => __('Bize her konuda yazabilirsiniz Hocam.', 'odtumist'), 'type' => 'text', 'sanitize' => 'sanitize_text_field'),
        'odtumist_contact_form_shortcode' => array('label' => 'Gelişmiş: Manuel Form Shortcode', 'default' => '[contact-form-7 id="123" title="İletişim Formu"]', 'type' => 'text', 'sanitize' => 'sanitize_text_field'),
        'odtumist_footer_description' => array('label' => 'Footer Açıklama', 'default' => __('ODTÜMİST; İstanbul\'daki ODTÜ mezunlarını dayanışma, burs, mentorluk ve ortak projelerde bir araya getiren mezunlar topluluğudur.', 'odtumist'), 'type' => 'textarea', 'sanitize' => 'sanitize_textarea_field'),
    );

    foreach ($contact_fields as $setting => $field) {
        $wp_customize->add_setting($setting, array(
            'default'           => $field['default'],
            'sanitize_callback' => $field['sanitize'],
        ));

        $wp_customize->add_control($setting, array(
            'label'   => __($field['label'], 'odtumist'),
            'section' => 'odtumist_contact_footer_section',
            'type'    => $field['type'],
        ));
    }

    $wp_customize->add_setting('odtumist_contact_hero_image', array(
        'default'           => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=2000',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'odtumist_contact_hero_image', array(
        'label'   => __('İletişim Hero Görseli', 'odtumist'),
        'section' => 'odtumist_contact_footer_section',
    )));

    $wp_customize->add_setting('odtumist_contact_form_provider', array(
        'default'           => 'cf7',
        'sanitize_callback' => 'odtumist_sanitize_contact_form_provider',
    ));
    $wp_customize->add_control('odtumist_contact_form_provider', array(
        'label'   => __('Form Sağlayıcı', 'odtumist'),
        'section' => 'odtumist_contact_footer_section',
        'type'    => 'select',
        'choices' => array(
            'cf7'       => __('Contact Form 7', 'odtumist'),
            'wpforms'   => __('WPForms Lite/Pro', 'odtumist'),
            'shortcode' => __('Manuel Shortcode', 'odtumist'),
        ),
    ));

    $wp_customize->add_setting('odtumist_contact_cf7_form_id', array(
        'default'           => 0,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('odtumist_contact_cf7_form_id', array(
        'label'   => __('Contact Form 7 Formu', 'odtumist'),
        'section' => 'odtumist_contact_footer_section',
        'type'    => 'select',
        'choices' => odtumist_get_cf7_form_choices(),
    ));

    $wp_customize->add_setting('odtumist_contact_wpforms_form_id', array(
        'default'           => 0,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('odtumist_contact_wpforms_form_id', array(
        'label'   => __('WPForms Formu', 'odtumist'),
        'section' => 'odtumist_contact_footer_section',
        'type'    => 'select',
        'choices' => odtumist_get_wpforms_form_choices(),
    ));
}
add_action('customize_register', 'odtumist_customize_register');

/* ──────────────────────────────────────────────
   Stats Section (Ana Sayfa)
   ────────────────────────────────────────────── */
function odtumist_get_home_stats()
{
    $stats = array();
    for ($i = 1; $i <= 4; $i++) {
        $defaults = array(
            1 => array('value' => '5,000+', 'label' => 'Aktif Üye'),
            2 => array('value' => '12,000+', 'label' => 'Verilen Burs'),
            3 => array('value' => '150+', 'label' => 'Yıllık Etkinlik'),
            4 => array('value' => '1991', 'label' => 'Kuruluş'),
        );
        $stats[] = array(
            'value' => get_theme_mod("odtumist_stat_{$i}_value", $defaults[$i]['value']),
            'label' => get_theme_mod("odtumist_stat_{$i}_label", $defaults[$i]['label']),
        );
    }
    return $stats;
}

/* ──────────────────────────────────────────────
   Contact Departments
   ────────────────────────────────────────────── */
function odtumist_get_contact_departments()
{
    $departments = array();
    $defaults = array(
        1 => array('title' => 'Yönetim Kurulu & Üyelik', 'subtitle' => 'Üyelik süreçleri ve genel iletişim', 'email' => 'dernek@odtumist.org', 'accent' => 'red'),
        2 => array('title' => 'Dernek Koordinatörü', 'subtitle' => 'Buket Akpınar', 'email' => 'buket.akpinar@odtumist.org', 'accent' => 'blue'),
        3 => array('title' => 'Burs Sorumlusu', 'subtitle' => 'Delal Filizay', 'email' => 'delal.filizay@odtumist.org', 'accent' => 'red'),
    );
    for ($i = 1; $i <= 3; $i++) {
        $departments[] = array(
            'title'    => get_theme_mod("odtumist_dept_{$i}_title", $defaults[$i]['title']),
            'subtitle' => get_theme_mod("odtumist_dept_{$i}_subtitle", $defaults[$i]['subtitle']),
            'email'    => get_theme_mod("odtumist_dept_{$i}_email", $defaults[$i]['email']),
            'accent'   => $defaults[$i]['accent'],
        );
    }
    return $departments;
}

/* ──────────────────────────────────────────────
   Solidarity Sections (hardcoded structure, content from page)
   ────────────────────────────────────────────── */
function odtumist_get_solidarity_section_defaults()
{
    $defaults = array(
        array('id' => 'burs',         'title' => 'Burs',                   'icon' => '&#127891;', 'tone' => 'tone-light',  'btn' => 'Keşfet'),
        array('id' => 'maraton',      'title' => 'Spor &amp; Maraton',     'icon' => '&#127942;', 'tone' => 'tone-orange', 'btn' => 'Destekle'),
        array('id' => 'mentorluk',    'title' => 'Mentorluk',              'icon' => '&#9749;',   'tone' => 'tone-red',    'btn' => 'Katıl'),
        array('id' => 'gonulluluk',   'title' => 'Gönüllüler',             'icon' => '&#10084;',  'tone' => 'tone-dark',   'btn' => 'Harekete Geç'),
        array('id' => 'genclik-iletisim', 'title' => 'Gençlik &amp; İletişim', 'icon' => '&#128227;', 'tone' => 'tone-cream',  'btn' => 'Katıl'),
        array('id' => 'bagiscilar-paydaslar', 'title' => 'Bağışçılar / Paydaşlar', 'icon' => '&#129309;', 'tone' => '', 'btn' => 'İncele'),
        array('id' => 'bursiyerler',  'title' => 'Bursiyerler',            'icon' => '&#128101;', 'tone' => 'tone-light',  'btn' => 'Mezun-Öğrenci Dayanışması'),
        array('id' => 'networking',   'title' => 'Networking',             'icon' => '&#127760;', 'tone' => 'tone-blue',   'btn' => 'Ağa Katıl'),
    );
    foreach ($defaults as $i => &$section) {
        $n = $i + 1;
        $section['title'] = get_theme_mod("odtumist_sol_{$n}_title", $section['title']);
        $section['btn']   = get_theme_mod("odtumist_sol_{$n}_btn", $section['btn']);
    }
    unset($section);
    return $defaults;
}

/* ──────────────────────────────────────────────
   Membership Tab Defaults
   ────────────────────────────────────────────── */
function odtumist_get_membership_tab_defaults()
{
    $defaults = array(
        array('id' => 'neden-uye-olmaliyim', 'label' => 'Neden Üye Olmalıyım?'),
        array('id' => 'uyelik-avantajlari',  'label' => 'Üyelik Avantajları'),
        array('id' => 'bilgi-guncelleme',    'label' => 'Bilgi Güncelleme'),
        array('id' => 'aidat-odeme',         'label' => 'Aidat Ödeme'),
        array('id' => 'nasil-uye-olabilirsiniz', 'label' => 'Nasıl Üye Olabilirsiniz?'),
        array('id' => 'yeni-mezunlar-icin-uyelik', 'label' => 'Yeni Mezunlar İçin Üyelik'),
        array('id' => 'uyelik-sss',          'label' => 'Üyelik SSS'),
    );
    foreach ($defaults as $i => &$tab) {
        $n = $i + 1;
        $tab['label'] = get_theme_mod("odtumist_memtab_{$n}_label", $tab['label']);
    }
    unset($tab);
    return $defaults;
}

/* ──────────────────────────────────────────────
   Events Page Gallery
   ────────────────────────────────────────────── */
function odtumist_get_events_gallery()
{
    $gallery_items = odtumist_get_events_gallery_items();
    $gallery = array();
    foreach ($gallery_items as $item) {
        if (!empty($item['image'])) {
            $gallery[] = (string) $item['image'];
        }
    }
    return $gallery;
}

function odtumist_get_events_gallery_items()
{
    $items = array();
    $defaults = array(
        array(
            'image' => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&q=80&w=800',
            'title' => 'Mezunlar Günü',
            'desc'  => 'Geleneksel buluşmadan bir kare',
        ),
        array(
            'image' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=800',
            'title' => 'Bahar Şenliği',
            'desc'  => 'Sosyal buluşma ve dayanışma',
        ),
        array(
            'image' => 'https://images.unsplash.com/photo-1475721027187-4024733923f7?auto=format&fit=crop&q=80&w=800',
            'title' => 'Panel / Seminer',
            'desc'  => 'Konuşmacı buluşmalarından kesitler',
        ),
        array(
            'image' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&q=80&w=800',
            'title' => 'Kültür Etkinliği',
            'desc'  => 'Gezi ve sosyal etkinlik anları',
        ),
        array(
            'image' => 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&q=80&w=800',
            'title' => 'Genç Mezun Etkinliği',
            'desc'  => 'Yeni mezunlarla ağ kurma buluşması',
        ),
        array(
            'image' => 'https://images.unsplash.com/photo-1540575861501-7cf05a4b125a?auto=format&fit=crop&q=80&w=800',
            'title' => 'ODTÜMİST Topluluğu',
            'desc'  => 'Etkinliklerden kareler',
        ),
    );
    for ($i = 0; $i < 6; $i++) {
        $n = $i + 1;
        $url = get_theme_mod("odtumist_gallery_{$n}", $defaults[$i]['image']);
        $title = get_theme_mod("odtumist_gallery_{$n}_title", $defaults[$i]['title']);
        $desc = get_theme_mod("odtumist_gallery_{$n}_desc", $defaults[$i]['desc']);
        if (!empty($url)) {
            $items[] = array(
                'image' => $url,
                'title' => $title,
                'desc'  => $desc,
            );
        }
    }
    return $items;
}

/* ──────────────────────────────────────────────
   Additional Customizer Settings
   ────────────────────────────────────────────── */
function odtumist_customize_register_extra($wp_customize)
{
    /* --- Stats Section --- */
    $wp_customize->add_section('odtumist_stats_section', array(
        'title'    => __('Anasayfa İstatistikler', 'odtumist'),
        'priority' => 44,
    ));

    for ($i = 1; $i <= 4; $i++) {
        $defaults = array(
            1 => array('value' => '5,000+', 'label' => 'Aktif Üye'),
            2 => array('value' => '12,000+', 'label' => 'Verilen Burs'),
            3 => array('value' => '150+', 'label' => 'Yıllık Etkinlik'),
            4 => array('value' => '1991', 'label' => 'Kuruluş'),
        );

        $wp_customize->add_setting("odtumist_stat_{$i}_value", array(
            'default'           => $defaults[$i]['value'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_stat_{$i}_value", array(
            'label'   => sprintf(__('İstatistik %d Değer', 'odtumist'), $i),
            'section' => 'odtumist_stats_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting("odtumist_stat_{$i}_label", array(
            'default'           => $defaults[$i]['label'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_stat_{$i}_label", array(
            'label'   => sprintf(__('İstatistik %d Etiket', 'odtumist'), $i),
            'section' => 'odtumist_stats_section',
            'type'    => 'text',
        ));
    }

    /* --- Contact Departments --- */
    $wp_customize->add_section('odtumist_departments_section', array(
        'title'    => __('İletişim Departmanları', 'odtumist'),
        'priority' => 45,
    ));

    $dept_defaults = array(
        1 => array('title' => 'Yönetim Kurulu & Üyelik', 'subtitle' => 'Üyelik süreçleri ve genel iletişim', 'email' => 'dernek@odtumist.org'),
        2 => array('title' => 'Dernek Koordinatörü', 'subtitle' => 'Buket Akpınar', 'email' => 'buket.akpinar@odtumist.org'),
        3 => array('title' => 'Burs Sorumlusu', 'subtitle' => 'Delal Filizay', 'email' => 'delal.filizay@odtumist.org'),
    );

    for ($i = 1; $i <= 3; $i++) {
        $wp_customize->add_setting("odtumist_dept_{$i}_title", array(
            'default'           => $dept_defaults[$i]['title'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_dept_{$i}_title", array(
            'label'   => sprintf(__('Departman %d Başlık', 'odtumist'), $i),
            'section' => 'odtumist_departments_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting("odtumist_dept_{$i}_subtitle", array(
            'default'           => $dept_defaults[$i]['subtitle'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_dept_{$i}_subtitle", array(
            'label'   => sprintf(__('Departman %d Alt Başlık', 'odtumist'), $i),
            'section' => 'odtumist_departments_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting("odtumist_dept_{$i}_email", array(
            'default'           => $dept_defaults[$i]['email'],
            'sanitize_callback' => 'sanitize_email',
        ));
        $wp_customize->add_control("odtumist_dept_{$i}_email", array(
            'label'   => sprintf(__('Departman %d E-posta', 'odtumist'), $i),
            'section' => 'odtumist_departments_section',
            'type'    => 'email',
        ));
    }

    /* --- Solidarity Sections --- */
    $wp_customize->add_section('odtumist_solidarity_section', array(
        'title'    => __('Dayanışma Bölüm Başlıkları', 'odtumist'),
        'priority' => 46,
    ));

    $sol_defaults = array(
        1 => array('title' => 'Burs',                    'btn' => 'Keşfet'),
        2 => array('title' => 'Spor &amp; Maraton',      'btn' => 'Destekle'),
        3 => array('title' => 'Mentorluk',               'btn' => 'Katıl'),
        4 => array('title' => 'Gönüllüler',              'btn' => 'Harekete Geç'),
        5 => array('title' => 'Gençlik &amp; İletişim',  'btn' => 'Katıl'),
        6 => array('title' => 'Bağışçılar / Paydaşlar',  'btn' => 'İncele'),
        7 => array('title' => 'Bursiyerler',             'btn' => 'Mezun-Öğrenci Dayanışması'),
        8 => array('title' => 'Networking',              'btn' => 'Ağa Katıl'),
    );

    for ($i = 1; $i <= 8; $i++) {
        $wp_customize->add_setting("odtumist_sol_{$i}_title", array(
            'default'           => $sol_defaults[$i]['title'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_sol_{$i}_title", array(
            'label'   => sprintf(__('Bölüm %d Başlık', 'odtumist'), $i),
            'section' => 'odtumist_solidarity_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting("odtumist_sol_{$i}_btn", array(
            'default'           => $sol_defaults[$i]['btn'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_sol_{$i}_btn", array(
            'label'   => sprintf(__('Bölüm %d Buton Metni', 'odtumist'), $i),
            'section' => 'odtumist_solidarity_section',
            'type'    => 'text',
        ));
    }

    /* --- Membership Tabs --- */
    $wp_customize->add_section('odtumist_membership_section', array(
        'title'    => __('Üyelik Sayfa Ayarları', 'odtumist'),
        'priority' => 47,
    ));

    $memtab_defaults = array(
        1 => 'Neden Üye Olmalıyım?',
        2 => 'Üyelik Avantajları',
        3 => 'Bilgi Güncelleme',
        4 => 'Aidat Ödeme',
        5 => 'Nasıl Üye Olabilirsiniz?',
        6 => 'Yeni Mezunlar İçin Üyelik',
        7 => 'Üyelik SSS',
    );

    for ($i = 1; $i <= 7; $i++) {
        $wp_customize->add_setting("odtumist_memtab_{$i}_label", array(
            'default'           => $memtab_defaults[$i],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_memtab_{$i}_label", array(
            'label'   => sprintf(__('Tab %d Etiket', 'odtumist'), $i),
            'section' => 'odtumist_membership_section',
            'type'    => 'text',
        ));
    }

    $step_defaults = array(
        1 => 'Dayanışma',
        2 => 'Derneğin Varlığını Sürdürmesi',
        3 => 'Mezunların Öğrencilere Fayda Sağlaması',
        4 => 'Yeni Mezunların Aramıza Katılması',
        5 => 'Camiamız Genişledikçe Dayanışmamız Büyür',
    );

    for ($i = 1; $i <= 5; $i++) {
        $wp_customize->add_setting("odtumist_memstep_{$i}_title", array(
            'default'           => $step_defaults[$i],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_memstep_{$i}_title", array(
            'label'   => sprintf(__('Adım %d Başlık', 'odtumist'), $i),
            'section' => 'odtumist_membership_section',
            'type'    => 'text',
        ));
    }

    /* --- About Page Settings --- */
    $wp_customize->add_section('odtumist_about_section', array(
        'title'    => __('Hakkımızda Sayfa Ayarları', 'odtumist'),
        'priority' => 48,
    ));

    $about_tab_defaults = array(
        1 => 'Neler Yapıyoruz?',
        2 => 'Çalışma Gruplarımız',
        3 => 'Tarihçe',
        4 => 'Yönetim',
    );

    for ($i = 1; $i <= 4; $i++) {
        $wp_customize->add_setting("odtumist_abouttab_{$i}_label", array(
            'default'           => $about_tab_defaults[$i],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_abouttab_{$i}_label", array(
            'label'   => sprintf(__('Tab %d Etiket', 'odtumist'), $i),
            'section' => 'odtumist_about_section',
            'type'    => 'text',
        ));
    }

    $history_defaults = array(
        1 => array('title' => 'Efsanevi "Et Arabası"', 'desc' => '1970 öncesi kampüste servis aracı olarak kullanılan ve öğrencilerin "Et Arabası" dediği meşhur kırmızı otobüslerden sonuncusu, hurdaya gitmek üzereyken Mersin\'de bulunup İstanbul\'a getirildi.', 'image' => 'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&q=80&w=800'),
        2 => array('title' => 'Bilim Ağacı\'nın Taşınma Hikayesi', 'desc' => 'Hazırlık okulunun oradaki Bilim Ağacı heykelinin görünürlüğü azaldığında, derneğin ısrarlı çabalarıyla 1991 yılında bugünkü yerine taşındı.', 'image' => 'https://images.unsplash.com/photo-1549490349-8643362247b5?auto=format&fit=crop&q=80&w=800'),
        3 => array('title' => 'Beyaz Masa\'nın Doğuşu', 'desc' => '1994-95 yıllarında derneğin öncülük ettiği çevre platformu çalışmaları, İstanbul Büyükşehir Belediyesi\'ndeki Beyaz Masa modelinin kurulmasına katkı sundu.', 'image' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&q=80&w=800'),
        4 => array('title' => '"Bi\' Dünya ODTÜ\'lü"', 'desc' => 'Pandemi döneminde fiziksel buluşmalar iptal olunca, dernek 28 farklı oturumla küresel bir dijital mezun buluşması organize etti.', 'image' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&q=80&w=800'),
    );

    for ($i = 1; $i <= 4; $i++) {
        $wp_customize->add_setting("odtumist_history_{$i}_title", array(
            'default'           => $history_defaults[$i]['title'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_history_{$i}_title", array(
            'label'   => sprintf(__('Tarihçe %d Başlık', 'odtumist'), $i),
            'section' => 'odtumist_about_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting("odtumist_history_{$i}_desc", array(
            'default'           => $history_defaults[$i]['desc'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_history_{$i}_desc", array(
            'label'   => sprintf(__('Tarihçe %d Açıklama', 'odtumist'), $i),
            'section' => 'odtumist_about_section',
            'type'    => 'textarea',
        ));

        $wp_customize->add_setting("odtumist_history_{$i}_image", array(
            'default'           => $history_defaults[$i]['image'],
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, "odtumist_history_{$i}_image", array(
            'label'   => sprintf(__('Tarihçe %d Görsel', 'odtumist'), $i),
            'section' => 'odtumist_about_section',
        )));
    }

    $mgmt_defaults = array(
        1 => array('title' => 'Dernek Yönetim Organları', 'desc' => 'Yönetim Kurulu, Denetleme Kurulu, Disiplin Kurulu ve Danışma Kurulu üyelerimizin biyografileri.'),
        2 => array('title' => 'Çalışma Gruplarımız', 'desc' => 'Derneğimizi yaşatan çalışma gruplarımızın katkılarıyla büyümeye devam ediyoruz.'),
        3 => array('title' => 'Dernek Tüzüğü ve Yönetmelikler', 'desc' => 'Şeffaf yönetişim ilkelerimiz, tüzüğümüz ve çalışma yönetmeliklerimiz.'),
        4 => array('title' => 'Faaliyet Raporları', 'desc' => 'Yıllık çalışma raporlarımız, mali tablolarımız ve kurumsal başarı hikayelerimiz.'),
        5 => array('title' => 'Eski Başkanlar', 'desc' => '1986\'dan bugüne derneğimize emek vermiş tüm kurullarımız ve yöneticilerimiz.'),
    );

    for ($i = 1; $i <= 5; $i++) {
        $wp_customize->add_setting("odtumist_mgmt_{$i}_title", array(
            'default'           => $mgmt_defaults[$i]['title'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_mgmt_{$i}_title", array(
            'label'   => sprintf(__('Yönetim Kart %d Başlık', 'odtumist'), $i),
            'section' => 'odtumist_about_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting("odtumist_mgmt_{$i}_desc", array(
            'default'           => $mgmt_defaults[$i]['desc'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_mgmt_{$i}_desc", array(
            'label'   => sprintf(__('Yönetim Kart %d Açıklama', 'odtumist'), $i),
            'section' => 'odtumist_about_section',
            'type'    => 'textarea',
        ));
    }

    /* --- Events Gallery --- */
    $wp_customize->add_section('odtumist_gallery_section', array(
        'title'    => __('Etkinlik Galeri Görselleri', 'odtumist'),
        'priority' => 46,
    ));

    $gallery_defaults = array(
        array('image' => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&q=80&w=800', 'title' => 'Mezunlar Günü', 'desc' => 'Geleneksel buluşmadan bir kare'),
        array('image' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=800', 'title' => 'Bahar Şenliği', 'desc' => 'Sosyal buluşma ve dayanışma'),
        array('image' => 'https://images.unsplash.com/photo-1475721027187-4024733923f7?auto=format&fit=crop&q=80&w=800', 'title' => 'Panel / Seminer', 'desc' => 'Konuşmacı buluşmalarından kesitler'),
        array('image' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&q=80&w=800', 'title' => 'Kültür Etkinliği', 'desc' => 'Gezi ve sosyal etkinlik anları'),
        array('image' => 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&q=80&w=800', 'title' => 'Genç Mezun Etkinliği', 'desc' => 'Yeni mezunlarla ağ kurma buluşması'),
        array('image' => 'https://images.unsplash.com/photo-1540575861501-7cf05a4b125a?auto=format&fit=crop&q=80&w=800', 'title' => 'ODTÜMİST Topluluğu', 'desc' => 'Etkinliklerden kareler'),
    );

    for ($i = 1; $i <= 6; $i++) {
        $wp_customize->add_setting("odtumist_gallery_{$i}", array(
            'default'           => $gallery_defaults[$i - 1]['image'],
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, "odtumist_gallery_{$i}", array(
            'label'   => sprintf(__('Galeri Görseli %d', 'odtumist'), $i),
            'section' => 'odtumist_gallery_section',
        )));

        $wp_customize->add_setting("odtumist_gallery_{$i}_title", array(
            'default'           => $gallery_defaults[$i - 1]['title'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_gallery_{$i}_title", array(
            'label'   => sprintf(__('Galeri %d Etkinlik Adı', 'odtumist'), $i),
            'section' => 'odtumist_gallery_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting("odtumist_gallery_{$i}_desc", array(
            'default'           => $gallery_defaults[$i - 1]['desc'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("odtumist_gallery_{$i}_desc", array(
            'label'   => sprintf(__('Galeri %d Açıklama', 'odtumist'), $i),
            'section' => 'odtumist_gallery_section',
            'type'    => 'text',
        ));
    }
}
add_action('customize_register', 'odtumist_customize_register_extra');
