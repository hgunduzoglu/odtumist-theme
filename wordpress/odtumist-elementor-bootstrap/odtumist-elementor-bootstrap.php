<?php
/**
 * Plugin Name: ODTÜMİST Elementor Bootstrap
 * Description: Elementor Pro odaklı ODTÜMİST kurulumu için sayfa, menü, CPT ve temel içerikleri tek tıkla oluşturur/günceller.
 * Version: 1.2.7
 * Author: Hüsamettin Gündüzoğlu
 */

if (!defined('ABSPATH')) {
    exit;
}

final class ODTUMIST_Elementor_Bootstrap
{
    const REPORT_TRANSIENT = 'odtumist_eb_report';
    const SOCIAL_FEED_OPTION = 'odtumist_social_feed_shortcode';
    const SOCIAL_FEED_DEFAULT = '[instagram-feed feed="1"]';
    const CARD_SOURCE_SNAPSHOT_META = '_odtumist_card_source_snapshot';
    private static $is_bootstrap_running = false;
    private static $is_card_source_sync_running = false;
    private static $pre_update_card_snapshots = array();

    public static function init()
    {
        add_action('init', array(__CLASS__, 'ensure_elementor_edit_compatibility'), 5);
        add_action('init', array(__CLASS__, 'register_cpts'), 20);
        add_action('init', array(__CLASS__, 'register_shortcodes'), 21);
        add_action('pre_post_update', array(__CLASS__, 'capture_page_card_snapshot_before_save'), 10, 2);
        add_action('save_post_event', array(__CLASS__, 'handle_event_post_saved'), 25, 3);
        add_action('save_post_team', array(__CLASS__, 'handle_team_post_saved'), 25, 3);
        add_action('set_object_terms', array(__CLASS__, 'handle_team_term_relationships_updated'), 25, 6);
        add_action('created_team-category', array(__CLASS__, 'handle_team_category_terms_changed'), 25, 2);
        add_action('edited_team-category', array(__CLASS__, 'handle_team_category_terms_changed'), 25, 2);
        add_action('delete_team-category', array(__CLASS__, 'handle_team_category_terms_deleted'), 25, 4);
        add_action('save_post_page', array(__CLASS__, 'handle_key_page_saved'), 25, 3);
        add_action('transition_post_status', array(__CLASS__, 'handle_content_type_status_transition'), 25, 3);
        add_action('deleted_post', array(__CLASS__, 'handle_content_type_post_deleted'), 25, 2);
        add_action('admin_menu', array(__CLASS__, 'register_admin_page'));
        add_action('admin_post_odtumist_eb_run', array(__CLASS__, 'handle_run'));

        if (defined('WP_CLI') && WP_CLI && class_exists('WP_CLI')) {
            \WP_CLI::add_command('odtumist bootstrap', array(__CLASS__, 'cli_run'));
        }
    }

    public static function register_cpts()
    {
        if (!post_type_exists('event')) {
            register_post_type('event', array(
                'labels' => array(
                    'name'          => __('Etkinlikler', 'odtumist-eb'),
                    'singular_name' => __('Etkinlik', 'odtumist-eb'),
                ),
                'public'       => true,
                'show_in_rest' => true,
                // /etkinlikler/ slug'i Elementor sayfasi icin ayrilsin.
                'has_archive'  => false,
                'rewrite'      => array('slug' => 'etkinlik'),
                'supports'     => array('title', 'editor', 'excerpt', 'thumbnail'),
                'menu_icon'    => 'dashicons-calendar-alt',
            ));
        }

        if (!taxonomy_exists('event-category')) {
            register_taxonomy('event-category', 'event', array(
                'labels'       => array('name' => __('Etkinlik Kategorileri', 'odtumist-eb')),
                'public'       => true,
                'hierarchical' => true,
                'show_in_rest' => true,
                'rewrite'      => array('slug' => 'etkinlik-kategori'),
            ));
        }

        if (!post_type_exists('team')) {
            register_post_type('team', array(
                'labels' => array(
                    'name'          => __('Çalışma Grupları', 'odtumist-eb'),
                    'singular_name' => __('Çalışma Grubu', 'odtumist-eb'),
                ),
                'public'       => true,
                'show_in_rest' => true,
                'has_archive'  => true,
                'rewrite'      => array('slug' => 'calisma-gruplari'),
                'supports'     => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
                'menu_icon'    => 'dashicons-groups',
            ));
        }

        if (!taxonomy_exists('team-category')) {
            register_taxonomy('team-category', 'team', array(
                'labels'       => array('name' => __('Çalışma Grubu Kategorileri', 'odtumist-eb')),
                'public'       => true,
                'hierarchical' => true,
                'show_in_rest' => true,
                'rewrite'      => array('slug' => 'calisma-grubu-kategori'),
            ));
        }
    }

    public static function register_shortcodes()
    {
        add_shortcode('odtumist_frontpage', array(__CLASS__, 'shortcode_frontpage_sections'));
        add_shortcode('odtumist_about_layout', array(__CLASS__, 'shortcode_about_layout'));
        add_shortcode('odtumist_events_layout', array(__CLASS__, 'shortcode_events_layout'));
        add_shortcode('odtumist_membership_layout', array(__CLASS__, 'shortcode_membership_layout'));
        add_shortcode('odtumist_solidarity_layout', array(__CLASS__, 'shortcode_solidarity_layout'));
        add_shortcode('odtumist_contact_layout', array(__CLASS__, 'shortcode_contact_layout'));

        add_shortcode('odtumist_events_grid', array(__CLASS__, 'shortcode_events_grid'));
        add_shortcode('odtumist_events_gallery', array(__CLASS__, 'shortcode_events_gallery'));
        add_shortcode('odtumist_working_groups_grid', array(__CLASS__, 'shortcode_working_groups_grid'));
        add_shortcode('odtumist_contact_departments', array(__CLASS__, 'shortcode_contact_departments'));
        add_shortcode('odtumist_contact_map', array(__CLASS__, 'shortcode_contact_map'));
        add_shortcode('odtumist_contact_form', array(__CLASS__, 'shortcode_contact_form'));
        add_shortcode('odtumist_social_feed', array(__CLASS__, 'shortcode_social_feed'));

        // Kisa takma isimler
        add_shortcode('odtumist-home', array(__CLASS__, 'shortcode_frontpage_sections'));
        add_shortcode('odtumist-about', array(__CLASS__, 'shortcode_about_layout'));
        add_shortcode('odtumist-events-layout', array(__CLASS__, 'shortcode_events_layout'));
        add_shortcode('odtumist-membership', array(__CLASS__, 'shortcode_membership_layout'));
        add_shortcode('odtumist-solidarity', array(__CLASS__, 'shortcode_solidarity_layout'));
        add_shortcode('odtumist-contact', array(__CLASS__, 'shortcode_contact_layout'));
        add_shortcode('odtumist-events', array(__CLASS__, 'shortcode_events_grid'));
        add_shortcode('odtumist-events-gallery', array(__CLASS__, 'shortcode_events_gallery'));
        add_shortcode('odtumist-groups', array(__CLASS__, 'shortcode_working_groups_grid'));
        add_shortcode('odtumist-social-feed', array(__CLASS__, 'shortcode_social_feed'));
    }

    public static function register_admin_page()
    {
        add_management_page(
            __('ODTÜMİST Elementor Bootstrap', 'odtumist-eb'),
            __('ODTÜMİST Bootstrap', 'odtumist-eb'),
            'manage_options',
            'odtumist-elementor-bootstrap',
            array(__CLASS__, 'render_admin_page')
        );
    }

    public static function handle_run()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Bu isleme yetkiniz yok.', 'odtumist-eb'));
        }

        check_admin_referer('odtumist_eb_run_nonce');

        $options = array(
            'force_reseed'   => !empty($_POST['force_reseed']),
            'apply_elementor' => !empty($_POST['apply_elementor']),
            'elementor_full_mode' => !empty($_POST['elementor_full_mode']),
            'sync_group_cards' => !empty($_POST['sync_group_cards']),
            'sync_event_cards' => !empty($_POST['sync_event_cards']),
            'rebuild_menus'  => !empty($_POST['rebuild_menus']),
            'cleanup_defaults' => !empty($_POST['cleanup_defaults']),
            'social_feed_shortcode' => isset($_POST['social_feed_shortcode']) ? wp_unslash((string) $_POST['social_feed_shortcode']) : '',
        );

        if ($options['force_reseed']) {
            $options['rebuild_menus'] = true;
        }

        self::update_social_feed_shortcode_option($options['social_feed_shortcode']);
        $report = self::run($options);
        set_transient(self::REPORT_TRANSIENT, $report, 180);

        wp_safe_redirect(admin_url('tools.php?page=odtumist-elementor-bootstrap'));
        exit;
    }

    /**
     * WP-CLI: wp odtumist bootstrap [--force=1] [--elementor=1] [--full=1] [--sync-groups=1] [--sync-events=1] [--menus=1] [--cleanup=1] [--social-feed='[instagram-feed feed="1"]']
     */
    public static function cli_run($args, $assoc_args)
    {
        $options = array(
            'force_reseed'    => !empty($assoc_args['force']),
            'apply_elementor' => !isset($assoc_args['elementor']) || (bool) $assoc_args['elementor'],
            'elementor_full_mode' => !isset($assoc_args['full']) || (bool) $assoc_args['full'],
            'sync_group_cards' => !empty($assoc_args['sync-groups']),
            'sync_event_cards' => !empty($assoc_args['sync-events']),
            'rebuild_menus'   => !empty($assoc_args['menus']),
            'cleanup_defaults' => !isset($assoc_args['cleanup']) || (bool) $assoc_args['cleanup'],
            'social_feed_shortcode' => isset($assoc_args['social-feed']) ? (string) $assoc_args['social-feed'] : '',
        );

        if ($options['force_reseed']) {
            $options['rebuild_menus'] = true;
        }

        if ($options['social_feed_shortcode'] !== '') {
            self::update_social_feed_shortcode_option($options['social_feed_shortcode']);
        }

        $report = self::run($options);

        if (!empty($report['errors'])) {
            foreach ($report['errors'] as $line) {
                \WP_CLI::warning($line);
            }
        }
        if (!empty($report['warnings'])) {
            foreach ($report['warnings'] as $line) {
                \WP_CLI::warning($line);
            }
        }
        if (!empty($report['messages'])) {
            foreach ($report['messages'] as $line) {
                \WP_CLI::log($line);
            }
        }

        if (!empty($report['errors'])) {
            \WP_CLI::error('ODTÜMİST bootstrap tamamlansa da hata kayitlari var.');
        }

        \WP_CLI::success('ODTÜMİST bootstrap tamamlandi.');
    }

    private static function run($options)
    {
        self::$is_bootstrap_running = true;
        try {
            $defaults = array(
                'force_reseed'    => false,
                'apply_elementor' => true,
                'elementor_full_mode' => true,
                'sync_group_cards' => false,
                'sync_event_cards' => false,
                'rebuild_menus'   => false,
                'cleanup_defaults' => true,
            );
            $options  = wp_parse_args($options, $defaults);

            $report = array(
                'messages' => array(),
                'warnings' => array(),
                'errors'   => array(),
                'options'  => $options,
            );

            if ($options['apply_elementor']) {
                $report['messages'][] = $options['elementor_full_mode']
                    ? 'Tam Elementor modu secildi: layoutlar shortcode yerine duzenlenebilir widget/section olarak yazilacak.'
                    : 'Legacy Elementor modu secildi: layoutlar shortcode bazli kalacak.';
            } elseif ($options['sync_group_cards'] || $options['sync_event_cards']) {
                $report['warnings'][] = 'Kart senkronu icin Elementor kurulumu secenegi acik olmalidir.';
            }

            if ($options['sync_group_cards'] && !$options['elementor_full_mode']) {
                $report['warnings'][] = 'Calisma grubu kart senkronu yalnizca Tam Elementor Modu ile uyumludur.';
            }
            if ($options['sync_event_cards'] && !$options['elementor_full_mode']) {
                $report['warnings'][] = 'Etkinlik kart senkronu yalnizca Tam Elementor Modu ile uyumludur.';
            }

            self::ensure_elementor_edit_compatibility();
            self::ensure_permalink_structure($report);
            $page_ids = self::upsert_pages($report, (bool) $options['force_reseed']);
            self::upsert_teams($report, (bool) $options['force_reseed']);
            self::upsert_events($report, (bool) $options['force_reseed']);
            self::build_menus($page_ids, $report, (bool) $options['rebuild_menus']);
            self::apply_reading_settings($page_ids, $report);
            self::ensure_home_slider_theme_mods($page_ids, $report, (bool) $options['force_reseed']);
            self::ensure_contact_theme_mods($report, (bool) $options['force_reseed']);

            if ($options['apply_elementor']) {
                update_option('odtumist_lock_templates', false);
                $report['messages'][] = 'Tema template kilidi kapatildi (Elementor duzenleme serbest).';
                self::apply_elementor_defaults($report);
                self::seed_elementor_pages(
                    $page_ids,
                    $report,
                    (bool) $options['force_reseed'],
                    (bool) $options['elementor_full_mode']
                );

                // Kart satirlari her zaman içerik tiplerinden canlı senkronlansın.
                if ($options['elementor_full_mode']) {
                    self::sync_group_cards_into_elementor_pages($page_ids, $report);
                    self::sync_event_cards_into_elementor_pages($page_ids, $report);
                    self::refresh_card_source_snapshots($page_ids);
                    $report['messages'][] = 'Etkinlik/Calisma Grubu kartlari dinamik kaynaklardan zorunlu senkronlandi.';
                }

                self::migrate_html_widgets_to_native_widgets($page_ids, $report);
                self::clear_elementor_runtime_cache($report);
                self::audit_seeded_pages_elementor_readiness($page_ids, $report);
            }

            if ($options['cleanup_defaults']) {
                self::cleanup_defaults($report);
            }

            return $report;
        } finally {
            self::$is_bootstrap_running = false;
        }
    }

    public static function handle_event_post_saved($post_id, $post, $update)
    {
        self::handle_content_type_post_saved($post_id, $post, 'event');
    }

    public static function handle_team_post_saved($post_id, $post, $update)
    {
        self::handle_content_type_post_saved($post_id, $post, 'team');
    }

    public static function handle_team_term_relationships_updated($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids)
    {
        if ((string) $taxonomy !== 'team-category') {
            return;
        }

        $object_id = (int) $object_id;
        if ($object_id <= 0) {
            return;
        }

        $post = get_post($object_id);
        if (!($post instanceof WP_Post) || $post->post_type !== 'team') {
            return;
        }

        if (!self::can_run_auto_card_sync($object_id, $post)) {
            return;
        }

        self::auto_sync_card_sections('team');
    }

    public static function handle_team_category_terms_changed($term_id, $tt_id)
    {
        self::trigger_team_filter_and_cards_resync();
    }

    public static function handle_team_category_terms_deleted($term, $tt_id, $deleted_term, $object_ids)
    {
        self::trigger_team_filter_and_cards_resync();
    }

    private static function trigger_team_filter_and_cards_resync()
    {
        if (self::$is_bootstrap_running || self::$is_card_source_sync_running) {
            return;
        }

        if (!class_exists('Elementor\\Plugin')) {
            return;
        }

        self::auto_sync_card_sections('team');
    }

    public static function capture_page_card_snapshot_before_save($post_id, $data)
    {
        if (self::$is_bootstrap_running || self::$is_card_source_sync_running) {
            return;
        }

        $post_id = (int) $post_id;
        if ($post_id <= 0) {
            return;
        }

        $post = get_post($post_id);
        if (!($post instanceof WP_Post) || $post->post_type !== 'page') {
            return;
        }

        $slug = sanitize_title((string) $post->post_name);
        if (!self::is_card_sync_page_slug($slug)) {
            return;
        }

        self::$pre_update_card_snapshots[$post_id] = self::collect_dynamic_card_source_ids_for_page($post_id);
    }

    public static function handle_key_page_saved($post_id, $post, $update)
    {
        if (!self::can_run_auto_card_sync($post_id, $post)) {
            return;
        }

        if (!($post instanceof WP_Post) || $post->post_type !== 'page') {
            return;
        }

        $slug = sanitize_title((string) $post->post_name);
        if (!self::is_card_sync_page_slug($slug)) {
            return;
        }

        self::sync_removed_cards_to_source_posts($post_id, $slug);
        self::auto_sync_card_sections('both');
    }

    private static function handle_content_type_post_saved($post_id, $post, $expected_post_type)
    {
        if (!self::can_run_auto_card_sync($post_id, $post)) {
            return;
        }

        if (!($post instanceof WP_Post) || $post->post_type !== $expected_post_type) {
            return;
        }

        self::auto_sync_card_sections($expected_post_type);
    }

    public static function handle_content_type_status_transition($new_status, $old_status, $post)
    {
        if (!($post instanceof WP_Post)) {
            return;
        }

        if (!in_array($post->post_type, array('event', 'team'), true)) {
            return;
        }

        if ($new_status === $old_status) {
            return;
        }

        if (!self::can_run_auto_card_sync((int) $post->ID, $post)) {
            return;
        }

        self::auto_sync_card_sections((string) $post->post_type);
    }

    public static function handle_content_type_post_deleted($post_id, $post = null)
    {
        $post_id = (int) $post_id;
        if ($post_id <= 0 || self::$is_bootstrap_running || self::$is_card_source_sync_running) {
            return;
        }

        if (!($post instanceof WP_Post)) {
            $post = get_post($post_id);
        }
        if (!($post instanceof WP_Post)) {
            return;
        }
        if (!in_array($post->post_type, array('event', 'team'), true)) {
            return;
        }

        self::auto_sync_card_sections((string) $post->post_type);
    }

    private static function can_run_auto_card_sync($post_id, $post = null)
    {
        if (self::$is_bootstrap_running || self::$is_card_source_sync_running) {
            return false;
        }
        if (!class_exists('Elementor\\Plugin')) {
            return false;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return false;
        }

        if (!($post instanceof WP_Post)) {
            $post = get_post($post_id);
            if (!($post instanceof WP_Post)) {
                return false;
            }
        }

        // Elementor editör kaydı REST üstünden de geldiği için bunu da güvenli şekilde kabul et.
        if (!is_admin() && !defined('WP_CLI')) {
            if (!(defined('REST_REQUEST') && REST_REQUEST)) {
                return false;
            }
            if (!is_user_logged_in() || !current_user_can('edit_post', (int) $post_id)) {
                return false;
            }
        }

        return true;
    }

    private static function is_card_sync_page_slug($slug)
    {
        $slug = sanitize_title((string) $slug);
        if ($slug === '') {
            return false;
        }

        return in_array($slug, self::get_card_sync_page_slugs(), true);
    }

    private static function get_card_sync_page_slugs()
    {
        return array(
            'anasayfa',
            'hakkimizda',
            'etkinlikler',
            // Hakkımızda child/alias slug'larında kart alanı manipüle edilirse
            // dinamik satırı tekrar kur.
            'neler-yapiyoruz',
            'calisma-gruplarimiz',
            'calisma-gruplari',
            'tarihce',
            'yonetim',
        );
    }

    private static function expected_card_post_types_for_page_slug($slug)
    {
        $slug = sanitize_title((string) $slug);
        if ($slug === 'anasayfa') {
            return array('event', 'team');
        }
        if ($slug === 'etkinlikler') {
            return array('event');
        }
        if (in_array($slug, array('hakkimizda', 'neler-yapiyoruz', 'calisma-gruplarimiz', 'calisma-gruplari', 'tarihce', 'yonetim'), true)) {
            return array('team');
        }

        return array();
    }

    private static function sync_removed_cards_to_source_posts($page_id, $page_slug)
    {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            return;
        }

        $before = null;
        if (isset(self::$pre_update_card_snapshots[$page_id]) && is_array(self::$pre_update_card_snapshots[$page_id])) {
            $before = self::$pre_update_card_snapshots[$page_id];
        } else {
            $meta_snapshot = get_post_meta($page_id, self::CARD_SOURCE_SNAPSHOT_META, true);
            if (is_array($meta_snapshot)) {
                $before = $meta_snapshot;
            }
        }
        unset(self::$pre_update_card_snapshots[$page_id]);

        $expected_types = self::expected_card_post_types_for_page_slug($page_slug);
        if (empty($expected_types)) {
            return;
        }

        $after = self::collect_dynamic_card_source_ids_for_page($page_id);
        update_post_meta($page_id, self::CARD_SOURCE_SNAPSHOT_META, $after);

        if (!is_array($before)) {
            return;
        }

        $to_trash = array();

        foreach ($expected_types as $post_type) {
            $before_ids = !empty($before[$post_type]) && is_array($before[$post_type])
                ? array_values(array_unique(array_map('intval', $before[$post_type])))
                : array();
            $after_ids = !empty($after[$post_type]) && is_array($after[$post_type])
                ? array_values(array_unique(array_map('intval', $after[$post_type])))
                : array();

            if (empty($before_ids)) {
                continue;
            }

            $removed_ids = array_values(array_diff($before_ids, $after_ids));
            if (empty($removed_ids)) {
                continue;
            }

            // Kasıtsız toplu silmelerde güvenlik freni.
            if (count($removed_ids) > 5) {
                continue;
            }

            foreach ($removed_ids as $removed_id) {
                $removed_id = (int) $removed_id;
                if ($removed_id <= 0) {
                    continue;
                }

                $source_post = get_post($removed_id);
                if (!($source_post instanceof WP_Post) || $source_post->post_type !== $post_type) {
                    continue;
                }
                if ($source_post->post_status === 'trash' || $source_post->post_status === 'auto-draft') {
                    continue;
                }

                $to_trash[$removed_id] = $post_type;
            }
        }

        if (empty($to_trash)) {
            return;
        }

        self::$is_card_source_sync_running = true;
        try {
            foreach (array_keys($to_trash) as $post_id_to_trash) {
                wp_trash_post((int) $post_id_to_trash);
            }
        } finally {
            self::$is_card_source_sync_running = false;
        }
    }

    private static function collect_dynamic_card_source_ids_for_page($page_id)
    {
        $page_id = (int) $page_id;
        $ids = array(
            'event' => array(),
            'team' => array(),
        );

        if ($page_id <= 0) {
            return $ids;
        }

        $document = self::get_elementor_document($page_id);
        if (empty($document) || !is_array($document)) {
            return $ids;
        }

        foreach ($document as $node) {
            if (!is_array($node) || empty($node['elType']) || $node['elType'] !== 'section') {
                continue;
            }

            $post_type = '';
            if (self::section_has_css_class($node, 'odt-el-event-card-row')) {
                $post_type = 'event';
            } elseif (self::section_has_css_class($node, 'odt-el-group-card-row')) {
                $post_type = 'team';
            } else {
                continue;
            }

            $urls = array();
            self::collect_image_box_links_from_elementor_node($node, $urls);
            if (empty($urls)) {
                continue;
            }

            foreach ($urls as $url) {
                $source_post = self::resolve_source_post_from_card_url($url, $post_type);
                if ($source_post instanceof WP_Post) {
                    $ids[$post_type][] = (int) $source_post->ID;
                }
            }
        }

        $ids['event'] = array_values(array_unique(array_filter(array_map('intval', $ids['event']))));
        $ids['team']  = array_values(array_unique(array_filter(array_map('intval', $ids['team']))));

        return $ids;
    }

    private static function collect_image_box_links_from_elementor_node($node, &$urls)
    {
        if (!is_array($node)) {
            return;
        }

        $el_type = isset($node['elType']) ? (string) $node['elType'] : '';
        if ($el_type === 'widget' && (isset($node['widgetType']) ? (string) $node['widgetType'] : '') === 'image-box') {
            $settings = !empty($node['settings']) && is_array($node['settings']) ? $node['settings'] : array();
            $link = !empty($settings['link']) && is_array($settings['link']) ? $settings['link'] : array();
            $url = isset($link['url']) ? trim((string) $link['url']) : '';
            if ($url !== '') {
                $urls[] = $url;
            }
        }

        if (empty($node['elements']) || !is_array($node['elements'])) {
            return;
        }

        foreach ($node['elements'] as $child_node) {
            self::collect_image_box_links_from_elementor_node($child_node, $urls);
        }
    }

    private static function resolve_source_post_from_card_url($url, $expected_post_type = '')
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $url = preg_replace('/#.*/', '', $url);
        $post_id = url_to_postid($url);

        if ($post_id <= 0) {
            $path = trim((string) wp_parse_url($url, PHP_URL_PATH), '/');
            if ($path !== '') {
                $candidate_types = $expected_post_type !== ''
                    ? array($expected_post_type)
                    : array('event', 'team');

                $candidate_post = get_page_by_path($path, OBJECT, $candidate_types);
                if ($candidate_post instanceof WP_Post) {
                    $post_id = (int) $candidate_post->ID;
                }
            }
        }

        if ($post_id <= 0) {
            return null;
        }

        $post = get_post($post_id);
        if (!($post instanceof WP_Post)) {
            return null;
        }
        if (!in_array($post->post_type, array('event', 'team'), true)) {
            return null;
        }
        if ($expected_post_type !== '' && $post->post_type !== $expected_post_type) {
            return null;
        }

        return $post;
    }

    private static function auto_sync_card_sections($scope = 'both')
    {
        $scope = sanitize_key((string) $scope);
        $valid_scopes = array('event', 'team', 'both');
        if (!in_array($scope, $valid_scopes, true)) {
            $scope = 'both';
        }

        $page_ids = self::resolve_core_page_ids_for_card_sync();
        if (empty($page_ids)) {
            return;
        }

        $report = array(
            'messages' => array(),
            'warnings' => array(),
            'errors' => array(),
        );

        if ($scope === 'event' || $scope === 'both') {
            self::sync_event_cards_into_elementor_pages($page_ids, $report);
        }
        if ($scope === 'team' || $scope === 'both') {
            self::sync_group_cards_into_elementor_pages($page_ids, $report);
        }

        self::refresh_card_source_snapshots($page_ids);
        self::clear_elementor_runtime_cache($report);
    }

    private static function resolve_core_page_ids_for_card_sync()
    {
        $slugs = array('anasayfa', 'hakkimizda', 'etkinlikler');
        $page_ids = array();

        foreach ($slugs as $slug) {
            $page = get_page_by_path($slug, OBJECT, 'page');
            if (!($page instanceof WP_Post)) {
                continue;
            }
            $page_ids[$slug] = (int) $page->ID;
        }

        return $page_ids;
    }

    private static function refresh_card_source_snapshots($page_ids)
    {
        $target_ids = array();

        if (is_array($page_ids)) {
            foreach ($page_ids as $page_id) {
                $page_id = (int) $page_id;
                if ($page_id > 0) {
                    $target_ids[] = $page_id;
                }
            }
        }

        foreach (self::get_card_sync_page_slugs() as $slug) {
            $page = get_page_by_path($slug, OBJECT, 'page');
            if ($page instanceof WP_Post) {
                $target_ids[] = (int) $page->ID;
            }
        }

        foreach (array('hakkimizda/calisma-gruplari', 'calisma-gruplari') as $alias_path) {
            $alias_page = get_page_by_path($alias_path, OBJECT, 'page');
            if ($alias_page instanceof WP_Post) {
                $target_ids[] = (int) $alias_page->ID;
            }
        }

        $target_ids = array_values(array_unique(array_filter(array_map('intval', $target_ids))));
        if (empty($target_ids)) {
            return;
        }

        foreach ($target_ids as $page_id) {
            $page_id = (int) $page_id;
            if ($page_id <= 0) {
                continue;
            }

            $snapshot = self::collect_dynamic_card_source_ids_for_page($page_id);
            update_post_meta($page_id, self::CARD_SOURCE_SNAPSHOT_META, $snapshot);
        }
    }

    public static function ensure_elementor_edit_compatibility()
    {
        // Geçmişten kalan kilit açık kalsa bile Elementor düzenlemeyi her zaman serbest bırak.
        if ((bool) get_option('odtumist_lock_templates', false)) {
            update_option('odtumist_lock_templates', false);
        }

        $supported_types = array('page', 'post', 'event', 'team');

        $cpt_support = get_option('elementor_cpt_support', array());
        if (!is_array($cpt_support)) {
            $cpt_support = array();
        }
        $updated = array_values(array_unique(array_merge($cpt_support, $supported_types)));
        if ($updated !== $cpt_support) {
            update_option('elementor_cpt_support', $updated, false);
        }

        foreach ($supported_types as $post_type) {
            if (post_type_exists($post_type)) {
                add_post_type_support($post_type, 'elementor');
            }
        }
    }

    private static function ensure_permalink_structure(&$report)
    {
        $current = (string) get_option('permalink_structure', '');
        $did_flush = false;

        if ($current !== '/%postname%/') {
            update_option('permalink_structure', '/%postname%/');
            flush_rewrite_rules(false);
            $did_flush = true;
            $report['messages'][] = __('Kalici baglanti yapisi "Yazi ismi" olarak ayarlandi.', 'odtumist-eb');
        }

        // CPT rewrite değişiklikleri sonrasında bir kere rewrite yenile.
        $rewrite_version = (int) get_option('odtumist_eb_rewrite_version', 0);
        if ($rewrite_version < 2 && !$did_flush) {
            flush_rewrite_rules(false);
            $report['messages'][] = 'Rewrite kurallari guncellendi (CPT URL cakismalari temizlendi).';
        }
        if ($rewrite_version < 2) {
            update_option('odtumist_eb_rewrite_version', 2, false);
        }
    }

    private static function page_seed_data()
    {
        return array(
            'anasayfa' => array(
                'title'   => 'Anasayfa',
                'excerpt' => '',
                'content' => '',
            ),
            'hakkimizda' => array(
                'title'   => 'Hakkımızda',
                'excerpt' => "İstanbul'un dinamizminde ODTÜ ruhunu, dayanışmasını ve kültürünü yaşatan topluluğumuza hoş geldin.",
                'content' => "<h2 id=\"neler-yapiyoruz\">İstanbul’daki ODTÜ'lüleri Birleştiren Çatı</h2>\n<p>ODTÜ topluluğunu İstanbul’da bir arada tutuyoruz. Üyelerimiz, gönüllülerimiz, bursiyerlerimiz ve tüm destekçilerimizle kocaman bir aileyiz!</p>\n<p><strong>Misyonumuz:</strong></p>\n<p>“ODTÜ mezunları arasındaki yaşam boyu dayanışmayı güçlendirerek ‘ODTÜ ruhunu’ geleceğe aktarmak”</p>\n<p><strong>Neler Yapıyoruz?</strong></p>\n<p>ODTÜ ile bağımızı canlı tutuyor, ODTÜ ruhunu geleceğe taşıyoruz.<br>→ Üye Ol</p>\n<p>İstanbul’daki mezunların buluşma noktasıyız. Sosyal ve kültürel etkinliklerle topluluğu canlı tutuyoruz.<br>→ Etkinliklerimizi İncele</p>\n<p>Öğrenci dayanışmasını büyütüyoruz: burs, maraton ve destek çalışmaları.<br>→ Gönüllü Ol → ODTÜ'lüler İçin Burs Topla!</p>\n<p>Mentorlukla mezun–öğrenci bağını güçlendiriyor; genç ODTÜ'lüleri geleceğe hazırlıyoruz.<br>→ Mentor Ol</p>\n<p>Bir fikrin mi var? Yeni bir etkinlik, işbirliği ya da proje önerisi…<br>→ Fikir Getir / İletişime Geç</p>\n<p>Çalışma Gruplarımızla üretiyor, tüm faydayı gönüllülerimizin emeğiyle yaratıyoruz.<br>→ Gönüllü Ol / Çalışma Grubuna Katıl</p>\n<p>ODTÜPARK’ta bir araya geliyor, bağlarımızı yüz yüze güçlendiriyoruz.<br>→ ODTÜPARK’ı Keşfet</p>\n<h2 id=\"calisma-gruplarimiz\">Çalışma Gruplarımız</h2>\n<p>Uzmanlık alanlarına veya ilgi alanlarına göre ayrılmış gruplarımızda birlikte üretiyoruz.</p>\n<h2 id=\"tarihce\">Tarihçe</h2>\n<p>ODTÜMİST, uzun yıllara dayanan bir mezun dayanışma yapısıdır.</p>\n<h2 id=\"yonetim\">Yönetim</h2>\n<p>Yönetim ve kurul bilgileri düzenli olarak güncellenir.</p>",
            ),
            'neler-yapiyoruz' => array(
                'title'   => 'Neler Yapıyoruz?',
                'excerpt' => '',
                'content' => "<p><strong>İstanbul’daki ODTÜ'lüleri Birleştiren Çatı</strong></p><p>ODTÜ topluluğunu İstanbul’da bir arada tutuyoruz. Üyelerimiz, gönüllülerimiz, bursiyerlerimiz ve tüm destekçilerimizle kocaman bir aileyiz!</p><p><strong>Misyonumuz:</strong> “ODTÜ mezunları arasındaki yaşam boyu dayanışmayı güçlendirerek ‘ODTÜ ruhunu’ geleceğe aktarmak”</p>",
                'parent'  => 'hakkimizda',
            ),
            'calisma-gruplarimiz' => array(
                'title'   => 'Çalışma Gruplarımız',
                'excerpt' => '',
                'content' => '<p>Uzmanlık alanlarına veya ilgi alanlarına göre ayrılmış gruplarımızda birlikte üretiyoruz.</p>',
                'parent'  => 'hakkimizda',
            ),
            'sen-de-katil' => array(
                'title'   => 'Sen de Katıl Hocam!',
                'excerpt' => '',
                'content' => '<p>Dayanışmanın parçası olmak için üyelik, gönüllülük, mentorluk ve burs çalışmalarına katılabilirsin.</p>',
                'parent'  => 'dayanisma',
            ),
            'tarihce' => array(
                'title'   => 'Tarihçe',
                'excerpt' => '',
                'content' => '<p>ODTÜMİST uzun yıllara dayanan bir mezun dayanışma yapısıdır. Geçmişten bugüne biriken bilgi birikimimizi geleceğe taşıyoruz.</p>',
                'parent'  => 'hakkimizda',
            ),
            'yonetim' => array(
                'title'   => 'Yönetim',
                'excerpt' => '',
                'content' => '<p>Yönetim kurulumuz ve çalışma birimlerimiz ile ilgili güncel bilgilere bu sayfadan ulaşabilirsiniz.</p>',
                'parent'  => 'hakkimizda',
            ),
            'yonetim-organlari' => array(
                'title'   => 'Yönetim Organları',
                'excerpt' => '',
                'content' => "<h2>Yönetim Kurulu</h2><ul><li><strong>F. Şebnem Karagöl</strong> — Elektrik Elektronik Mühendisliği '79 – İTÜ EE '82 Ms (Başkan)</li><li><strong>Yusuf Maz</strong> — Makine Mühendisliği '20 (2. Başkan)</li><li><strong>Bilge Taner</strong> — İktisat-İstatistik '78 – İktisat '81 (Sayman)</li><li><strong>Oya Tığlı</strong> — Sosyoloji '83 (Üye)</li><li><strong>Ayşe Savran</strong> — Elektrik Elektronik Mühendisliği '82 (Üye)</li><li><strong>Canan Malkoç</strong> — Elektrik Elektronik Mühendisliği '89 (Üye)</li><li><strong>Dilara Sönmez</strong> — Uluslararası İlişkiler '10 (Üye)</li></ul><h2>Denetleme Kurulu</h2><ul><li><strong>Betül Selcen Özer</strong> — Sosyoloji '98</li><li><strong>Emel Yavuz</strong> — İktisat '14</li><li><strong>Adnan Can Çakır</strong> — Siyaset Bilimi ve Kamu Yönetimi '17</li></ul><h2>Disiplin Kurulu</h2><ul><li><strong>Feyzan Aliefendioğlu</strong> — Kimya '78</li><li><strong>Ahmet Asena</strong> — Endüstri Mühendisliği '78</li><li><strong>Uğur Ayken</strong> — Makine Mühendisliği '76</li></ul><h2>Yedek Kurullar</h2><p><strong>Yönetim Kurulu - Yedek:</strong> Taylan Gedikoğlu, Meltem Haykır, Ömer Benan Ekici, Ş. Özüm Özkan Gündoğan, Kenan Aybars Atılgan, Ezgi Özkök Sefer</p><p><strong>Denetleme Kurulu - Yedek:</strong> Fethiye Güray, Yeliz İsmi, Esra Kayaalp</p><p><strong>Disiplin Kurulu - Yedek:</strong> İsmail Işık, Ahmet Ergül, Ali Torun</p>",
            ),
            'profesyonel-ekip' => array(
                'title'   => 'Profesyonel Ekip',
                'excerpt' => '',
                'content' => "<h2>İdari Ekip</h2><ul><li><strong>Buket Akpınar</strong> — Genel Koordinatör — <a href=\"mailto:buket.akpinar@odtumist.org\">buket.akpinar@odtumist.org</a></li><li><strong>Delal Filizay</strong> — Kaynak Geliştirme Uzman Yardımcısı — <a href=\"mailto:delal.filizay@odtumist.org\">delal.filizay@odtumist.org</a></li><li><strong>Selami Kara</strong> — İdari Destek Personeli</li></ul>",
            ),
            'tuzuk' => array(
                'title'   => 'Tüzük',
                'excerpt' => '',
                'content' => '<p>Dernek tüzüğünün güncel haline aşağıdaki bağlantıdan ulaşabilirsiniz.</p><p><a href="https://odtumist.org/wp-content/uploads/2021/01/TUZUK-2022-2022.pdf" target="_blank" rel="noopener noreferrer">Dernek Tüzüğü (PDF)</a></p>',
            ),
            'yonetmelikler' => array(
                'title'   => 'Yönetmelikler',
                'excerpt' => '',
                'content' => '<p>Yönetmelikler ve yönergeler:</p><ul><li><a href="https://odtumist.org/wp-content/uploads/2021/01/YÖNETMELİK-ÇG-YENİ.pdf" target="_blank" rel="noopener noreferrer">Çalışma Grupları Yönetmeliği (PDF)</a></li><li><a href="https://odtumist.org/wp-content/uploads/2021/01/YÖNETMELİK-MALİ-İŞLER-YENİ.pdf" target="_blank" rel="noopener noreferrer">Mali İşler Yönetmeliği (PDF)</a></li><li><a href="https://odtumist.org/wp-content/uploads/2021/01/YÖNETMELİK-DENETLEME-YENİ.pdf" target="_blank" rel="noopener noreferrer">Denetleme Kurulu Yönetmeliği (PDF)</a></li><li><a href="https://odtumist.org/wp-content/uploads/2021/01/YÖNETMELİK-PERSONEL-YENİ.pdf" target="_blank" rel="noopener noreferrer">Personel Yönetmeliği (PDF)</a></li><li><a href="https://odtumist.org/wp-content/uploads/2021/01/YÖNETMELİK-DİSİPLİN-YENİ.pdf" target="_blank" rel="noopener noreferrer">Disiplin Kurulu Yönetmeliği (PDF)</a></li><li><a href="https://odtumist.org/wp-content/uploads/2021/01/Burs-Yonergesi-Nisan-2025.pdf" target="_blank" rel="noopener noreferrer">Burs Yönergesi (PDF)</a></li></ul>',
            ),
            'faaliyet-raporlari' => array(
                'title'   => 'Faaliyet Raporları',
                'excerpt' => '',
                'content' => '<p>Faaliyet raporları:</p><ul><li><a href="https://odtumist.org/wp-content/uploads/2026/04/ODTUMIST-Faaliyet-Raporu-2025-v4.pdf" target="_blank" rel="noopener noreferrer">Faaliyet Raporu 2025</a></li><li><a href="https://odtumist.org/wp-content/uploads/2021/01/ODTUMIST-2024-FAALIYET-RAPORU.pdf" target="_blank" rel="noopener noreferrer">Faaliyet Raporu 2024</a></li><li><a href="https://odtumist.org/wp-content/uploads/2021/01/ODTUMIST-2023-FAALIYET-RAPORU-24022024.pdf" target="_blank" rel="noopener noreferrer">Faaliyet Raporu 2023</a></li><li><a href="https://odtumist.org/wp-content/uploads/2021/01/ODTÜMİST-2021-FAALİYET-RAPORU-25022022-SON-HALİ-1.pdf" target="_blank" rel="noopener noreferrer">Faaliyet Raporu 2021</a></li><li><a href="https://odtumist.org/wp-content/uploads/2017/03/ODTÜMİST-Faaliyet-Raporu-2019-2020-2021.pdf" target="_blank" rel="noopener noreferrer">Faaliyet Raporu 2019-2020-2021</a></li><li><a href="https://odtumist.org/wp-content/uploads/2017/03/ODTÜMİST-Faaliyet-Raporu-2018.pdf" target="_blank" rel="noopener noreferrer">Faaliyet Raporu 2018</a></li><li><a href="https://odtumist.org/wp-content/uploads/2017/03/ODTÜMİST-Faaliyet-Raporu-2017.pdf" target="_blank" rel="noopener noreferrer">Faaliyet Raporu 2017</a></li></ul>',
            ),
            'eski-baskanlar' => array(
                'title'   => 'Eski Başkanlar',
                'excerpt' => '',
                'content' => "<h2>Eski Başkanlar</h2><ul><li><strong>Fatma Şebnem Karagöl</strong> — 2021-2023</li><li><strong>Yener Aydın</strong> — 2016-2021</li><li><strong>Mehmet Ali Acartürk</strong> — 2014-2016</li><li><strong>Mehmet Rasgelener</strong> — 2012/10-2014</li><li><strong>Feyzan Ali Aliefendioğlu</strong> — 2012-2012/10</li><li><strong>Ahmet Savaş Deringöl</strong> — 2011/05-2012</li><li><strong>Burcu Küçükbabacık</strong> — 2010-2011/05</li><li><strong>Feyzan Ali Aliefendioğlu</strong> — 2004-2010</li><li><strong>Orhan Kurmuş</strong> — 2003-2004</li><li><strong>Uğur Ayken</strong> — 1997-1999</li><li><strong>İlhan Çetinkaya</strong> — 1995-1997</li><li><strong>Vasfiye İpekçi</strong> — 1990-1995</li><li><strong>Altan Lostar</strong> — 1986-1990</li></ul>",
            ),
            'etkinlikler' => array(
                'title'   => 'Etkinlikler',
                'excerpt' => 'Takvimdeki etkinlikleri inceleyebilir, detay sayfalarından kayıt ve katılım bilgilerine ulaşabilirsin.',
                'content' => '<p>Etkinlik kartları "Etkinlikler" içerik tipinden otomatik çekilir.</p>',
            ),
            'uyelik' => array(
                'title'   => 'Üyelik',
                'excerpt' => 'ODTÜMİST üyeliği; dayanışma, aidiyet ve öğrencilere uzanan etkiyi büyüten güçlü bir topluluk çatısıdır.',
                'content' => "<h2 id=\"neden-uye-olmaliyim\">Neden Üye Olmalıyım?</h2>\n<p>Dayanışma ağımıza katılmak için üyelik başvurusu yapabilirsin.</p>\n<h2 id=\"bilgi-guncelleme\">Bilgi Güncelleme</h2>\n<p>Mezun bilgi alanlarını güncel tutman iletişimi güçlendirir.</p>\n<h2 id=\"aidat-odeme\">Aidat Ödeme</h2>\n<p>Aidat işlemleri dijital olarak takip edilir.</p>\n<h2 id=\"uyelik-avantajlari\">Üyelik Avantajları</h2>\n<p>Etkinlik, mentorluk ve güçlü mezun ağı imkânları sunulur.</p>\n<h2 id=\"nasil-uye-olabilirsiniz\">Nasıl Üye Olabilirsiniz?</h2>\n<p>Başvuru adımları, üyelik kriterleri ve süreç akışını bu bölümden inceleyebilirsiniz.</p>\n<h2 id=\"yeni-mezunlar-icin-uyelik\">Yeni Mezunlar İçin Üyelik</h2>\n<p>Yeni mezunlara özel üyelik kolaylıkları ve topluluğa hızlı adaptasyon desteğini bu bölümde bulabilirsiniz.</p>\n<h2 id=\"uyelik-sss\">Üyelik SSS</h2>\n<p>Üyelikle ilgili sık sorulan sorulara ve güncel yanıtlara bu bölümden ulaşabilirsiniz.</p>",
            ),
            'neden-uye-olmaliyim' => array(
                'title'   => 'Neden Üye Olmalıyım?',
                'excerpt' => '',
                'content' => '<p>Dayanışma ağımıza katılarak mezunlar, öğrenciler ve üniversitemiz ile bağınızı güçlendirebilirsiniz.</p>',
                'parent'  => 'uyelik',
            ),
            'bilgi-guncelleme' => array(
                'title'   => 'Bilgi Güncelleme',
                'excerpt' => '',
                'content' => '<p>İletişim bilgilerinizin güncel olması, mezun ağımızın sürekliliği ve etkili iletişim için kritik önemdedir.</p>',
                'parent'  => 'uyelik',
            ),
            'aidat-odeme' => array(
                'title'   => 'Aidat Ödeme',
                'excerpt' => '',
                'content' => '<p>Aidat işlemlerinizi dijital ödeme altyapısı üzerinden güvenli şekilde yönetebilirsiniz.</p>',
                'parent'  => 'uyelik',
            ),
            'uyelik-avantajlari' => array(
                'title'   => 'Üyelik Avantajları',
                'excerpt' => '',
                'content' => '<p>Etkinlikler, mentorluk, networking ve dayanışma projelerinde aktif rol alarak mezun ağımızın parçası olabilirsiniz.</p>',
                'parent'  => 'uyelik',
            ),
            'nasil-uye-olabilirsiniz' => array(
                'title'   => 'Nasıl Üye Olabilirsiniz?',
                'excerpt' => '',
                'content' => '<p>Başvuru formu, onay süreci ve üyelik adımlarını bu sayfadan takip edebilirsiniz.</p>',
                'parent'  => 'uyelik',
            ),
            'yeni-mezunlar-icin-uyelik' => array(
                'title'   => 'Yeni Mezunlar İçin Üyelik',
                'excerpt' => '',
                'content' => '<p>Yeni mezunlar için özel üyelik akışı, etkinlikler ve gelişim fırsatları bu sayfada yayınlanacaktır.</p>',
                'parent'  => 'uyelik',
            ),
            'uyelik-sss' => array(
                'title'   => 'Üyelik SSS',
                'excerpt' => '',
                'content' => '<p>Üyelik aidatı, başvuru, belge ve süreçlerle ilgili sık sorulan soruların yanıtlarını bu sayfadan bulabilirsiniz.</p>',
                'parent'  => 'uyelik',
            ),
            'dayanisma' => array(
                'title'   => 'Dayanışma',
                'excerpt' => 'ODTÜ mezunu olmanın getirdiği bağ, ODTÜMİST çatısı altında ortak bir etki alanına dönüşüyor.',
                'content' => "<h2 id=\"sen-de-katil\">Sen de Katıl Hocam!</h2>\n<p>Dayanışmanın parçası olmak için burs, mentorluk, maraton ve gönüllülük alanlarında birlikte üretelim.</p>\n<h2 id=\"burs\">Burs</h2>\n<p>ODTÜ öğrencilerinin eğitimlerini kesintisiz sürdürebilmeleri için burs dayanışmasını büyütüyoruz.</p>\n<h2 id=\"maraton\">Spor & Maraton</h2>\n<p>İyilik için koşuyor, kampanyalarla burs fonunu güçlendiriyoruz.</p>\n<h2 id=\"mentorluk\">Mentorluk</h2>\n<p>Farklı kuşaklardan ODTÜ’lüleri yapılandırılmış mentorluk programlarında bir araya getiriyoruz.</p>\n<h2 id=\"gonulluluk\">Gönüllüler</h2>\n<p>Çalışma gruplarında, etkinliklerde ve dayanışma projelerinde gönüllü katkısıyla etkimizi artırıyoruz.</p>\n<h2 id=\"genclik-iletisim\">Gençlik & İletişim</h2>\n<p>Genç mezunlar ve öğrenciler için iletişim, aidiyet ve gelişim alanları oluşturuyoruz.</p>\n<h2 id=\"bagiscilar-paydaslar\">Bağışçılar / Paydaşlar</h2>\n<p>Bağışçılarımız ve paydaş kurumlarımızla ortak değer üreterek toplumsal etki alanımızı büyütüyoruz.</p>\n<h2 id=\"bursiyerler\">Bursiyerler</h2>\n<p>Bursiyer öğrencilerimizle mezunlarımız arasında sürekli bir gelişim ve dayanışma köprüsü kuruyoruz.</p>\n<h2 id=\"networking\">Networking</h2>\n<p>Mezunlar arası profesyonel ve sosyal bağları güçlendirerek güçlü bir mezun ağı oluşturuyoruz.</p>",
            ),
            'networking' => array(
                'title'   => 'Networking',
                'excerpt' => '',
                'content' => '<p>Mezunlar arası profesyonel ve sosyal bağları güçlendirerek ortak bir etki alanı oluşturuyoruz.</p>',
                'parent'  => 'dayanisma',
            ),
            'burs' => array(
                'title'   => 'Burs',
                'excerpt' => '',
                'content' => '<p>ODTÜMİST, ODTÜ öğrencilerinin eğitimini sürdürebilmesi için burs desteği sunar. 2024/2025 döneminde 800 öğrenciye burs verildi; 2025/2026 döneminde de 600+ bursiyer ile dayanışma sürüyor.</p><p><a href="https://odtumist.org/burs/" target="_blank" rel="noopener noreferrer">Burs sayfasının mevcut sürümünü incele</a></p>',
                'parent'  => 'dayanisma',
            ),
            'maraton' => array(
                'title'   => 'Spor & Maraton',
                'excerpt' => '',
                'content' => '<p>İstanbul Maratonu ve yıl içi spor etkinlikleriyle burs fonunu büyütüyoruz. “Yarınlara Nefes Ol” kampanyası ile bireysel ve kurumsal destekleri öğrencilerimize ulaştırıyoruz.</p><p><a href="https://odtumist.org/maraton/" target="_blank" rel="noopener noreferrer">Maraton sayfasının mevcut sürümünü incele</a></p>',
                'parent'  => 'dayanisma',
            ),
            'mentorluk' => array(
                'title'   => 'Mentorluk',
                'excerpt' => '',
                'content' => '<p>ODTÜMİST Mentorluk Programları ile mezunlar ve öğrenciler arasında deneyim aktarımı sağlıyoruz. Akran mentorlugu, kariyer mentorlugu ve iş/yaşam mentorlugu gibi programlarla öğrenme ortaklıkları kuruyoruz.</p><p><a href="https://odtumist.org/mentorluk/" target="_blank" rel="noopener noreferrer">Mentorluk sayfasının mevcut sürümünü incele</a></p>',
                'parent'  => 'dayanisma',
            ),
            'bursiyerler' => array(
                'title'   => 'Bursiyerler',
                'excerpt' => '',
                'content' => '<p>Bursiyer öğrencilerimizle sürekli iletişim ve gelişim odaklı dayanışmanın bir parçası oluyoruz.</p>',
                'parent'  => 'dayanisma',
            ),
            'gonulluluk' => array(
                'title'   => 'Gönüllüler',
                'excerpt' => '',
                'content' => '<p>Gönüllü ağımızı büyüterek etkinlik, burs ve mentorluk projelerinin sürdürülebilirliğini birlikte güçlendiriyoruz.</p><p>Başa gönüllülük tanıtımı ve gönüllülük başvuru formu ekleyebilirsiniz.</p>',
                'parent'  => 'dayanisma',
            ),
            'genclik-iletisim' => array(
                'title'   => 'Gençlik & İletişim',
                'excerpt' => '',
                'content' => '<p>Genç mezunlar ve öğrenciler için iletişim, mentorluk ve topluluk katılım kanallarını bu sayfadan yönetiyoruz.</p>',
                'parent'  => 'dayanisma',
            ),
            'bagiscilar-paydaslar' => array(
                'title'   => 'Bağışçılar / Paydaşlar',
                'excerpt' => '',
                'content' => '<p>Bağışçılarımız ve paydaş kurumlarımızla birlikte geliştirdiğimiz iş birlikleri bu sayfada yayınlanacaktır.</p>',
                'parent'  => 'dayanisma',
            ),
            'bagis-yapin' => array(
                'title'   => 'Bağış Yapın',
                'excerpt' => '',
                'content' => '<p>Bağış süreçleri ve kampanya detayları bu sayfadan yönetilecektir.</p>',
            ),
            'iletisim' => array(
                'title'   => 'İletişim',
                'excerpt' => "Cumhuriyet Cad. Cumhuriyet Apt. No: 17 Kat: 2 D: 5 Taksim, Beyoğlu, İstanbul adresindeki dernek merkezimizde sizleri bekliyoruz.",
                'content' => '<p>Bu sayfada form alanı, iletişim kartları ve harita bölümü bulunur.</p>',
            ),
            'haberler' => array(
                'title'   => 'Haberler',
                'excerpt' => '',
                'content' => '<p>Güncel duyuru ve haber içeriklerinizi bu sayfadan yayınlayabilirsiniz.</p>',
            ),
        );
    }

    private static function team_seed_data()
    {
        return array(
            array('slug' => 'edebiyat', 'title' => 'Edebiyat', 'excerpt' => 'Edebiyat okumaları, yazar buluşmaları ve üretim atölyeleri.', 'content' => "Edebiyat çalışma grubumuz, farklı türlerde okumalar ve tartışma oturumları düzenler.\n\nAylık buluşma takvimini buradan duyurabilir, yeni kitap önerilerini paylaşabilirsiniz.", 'category' => 'kultur'),
            array('slug' => 'felsefe', 'title' => 'Felsefe', 'excerpt' => 'Felsefi tartışmalar, metin okumaları ve düşünce atölyeleri.', 'content' => "Felsefe grubumuz eleştirel düşünmeyi güçlendiren açık oturumlar yürütür.\n\nEtkinlik notları, konuşma başlıkları ve yeni dönem programlarını bu alandan yönetebilirsiniz.", 'category' => 'kultur'),
            array('slug' => 'fotograf', 'title' => 'Fotoğraf', 'excerpt' => 'Fotoğraf üretimi, şehir gezileri ve sergi hazırlıkları.', 'content' => "Fotoğraf grubumuz saha buluşmaları, portfolyo geri bildirimleri ve atölyeler organize eder.\n\nYeni gezi duyuruları ve sergi içeriklerini bu sayfadan güncelleyebilirsiniz.", 'category' => 'sanat'),
            array('slug' => 'sosyal-komite', 'title' => 'Sosyal Komite', 'excerpt' => 'Gezi, etkinlik, buluşma ve sosyal organizasyonlar.', 'content' => "Sosyal komite, mezun ağını bir arada tutan buluşmaları planlar.\n\nProgram duyuruları, gönüllü çağrıları ve etkinlik özetlerini buradan paylaşabilirsiniz.", 'category' => 'topluluk'),
            array('slug' => 'burs', 'title' => 'Burs', 'excerpt' => 'Burs fonu, bursiyer desteği ve etki odaklı dayanışma programları.', 'content' => "Burs grubumuz öğrencilerimize sürdürülebilir destek modelleri geliştirir.\n\nBaşvuru süreçleri, burs güncellemeleri ve fon kampanyalarını bu sayfadan yönetebilirsiniz.", 'category' => 'dayanisma'),
            array('slug' => 'ik-uye-gelistirme', 'title' => 'İK & Üye Geliştirme', 'excerpt' => 'Üyelik ve mezun ağı gelişim çalışmaları.', 'content' => "İK ve Üye Geliştirme grubumuz, dernek içi katılımı artıran süreçler tasarlar.\n\nAçık pozisyonlar, gönüllü çağrıları ve gelişim içeriklerini bu sayfadan güncelleyebilirsiniz.", 'category' => 'gelisim'),
            array('slug' => 'spor-maraton', 'title' => 'Spor & Maraton', 'excerpt' => 'Spor etkinlikleri, takım koşuları ve maraton hazırlıkları.', 'content' => "Spor & Maraton grubumuz, dayanışmayı hareketle büyüten etkinlikler düzenler.\n\nKoşu takvimi, kampanya bağlantıları ve katılım bilgilerini bu alandan yönetebilirsiniz.", 'category' => 'spor'),
        );
    }

    private static function event_seed_data()
    {
        return array(
            array('slug' => 'geleneksel-visnelik-bulusmasi', 'title' => 'Geleneksel Vişnelik Buluşması', 'excerpt' => 'Mezunlar buluşması ve sosyal paylaşım etkinliği.', 'content' => 'Etkinlik detaylarını bu sayfadan güncelleyebilirsiniz.', 'category' => 'sosyal', 'location' => 'ODTÜPARK Ulus', 'start_dt' => '2026-06-15 19:00:00'),
            array('slug' => 'istanbul-maratonu-hazirlik-kosusu', 'title' => 'İstanbul Maratonu Hazırlık Koşusu', 'excerpt' => 'Maraton öncesi hazırlık koşusu.', 'content' => 'Etkinlik detaylarını bu sayfadan güncelleyebilirsiniz.', 'category' => 'spor', 'location' => 'Caddebostan Sahili', 'start_dt' => '2026-07-06 09:00:00'),
            array('slug' => 'yapay-zeka-ve-sanat-soylesisi', 'title' => 'Yapay Zekâ ve Sanat Söyleşisi', 'excerpt' => 'Teknoloji ve sanat üzerine mezun söyleşisi.', 'content' => 'Etkinlik detaylarını bu sayfadan güncelleyebilirsiniz.', 'category' => 'soylesi', 'location' => 'Online', 'start_dt' => '2026-08-22 20:30:00'),
            array('slug' => 'siyah-beyaz-istanbul-fotograf-atolyesi', 'title' => 'Siyah Beyaz İstanbul Fotoğraf Atölyesi', 'excerpt' => 'Fotoğraf grubundan uygulamalı atölye.', 'content' => 'Etkinlik detaylarını bu sayfadan güncelleyebilirsiniz.', 'category' => 'atolye', 'location' => 'Beyoğlu', 'start_dt' => '2026-09-12 14:00:00'),
        );
    }

    private static function upsert_pages(&$report, $force_reseed)
    {
        $seed_data = self::page_seed_data();
        $root_seed = array();
        $child_seed = array();

        foreach ($seed_data as $slug => $page) {
            if (empty($page['parent'])) {
                $root_seed[$slug] = $page;
                continue;
            }
            $child_seed[$slug] = $page;
        }

        // Her zaman önce üst seviye sayfaları, ardından child sayfaları işleyelim.
        // Bu sayede child sayfalarda parent/path bazlı canonical eşleşme güvenilir olur.
        $ordered_seed_data = array_merge($root_seed, $child_seed);

        $ids = array();
        foreach ($ordered_seed_data as $slug => $page) {
            $status  = 'skipped';
            $path_hint = '';
            if (!empty($page['parent'])) {
                $path_hint = sanitize_title((string) $page['parent']) . '/' . sanitize_title((string) $slug);
            }

            $post_id = self::upsert_post('page', $slug, $page, $force_reseed, $status, $path_hint);

            if (is_wp_error($post_id)) {
                $report['errors'][] = sprintf('Sayfa olusturulamadi: %s (%s)', $page['title'], $post_id->get_error_message());
                continue;
            }

            $ids[$slug] = (int) $post_id;
            update_post_meta($post_id, '_odtumist_eb_seed_key', sanitize_title($slug));

            if ($status === 'created') {
                $report['messages'][] = sprintf('Sayfa olusturuldu: %s', $page['title']);
            } elseif ($status === 'updated') {
                $report['messages'][] = sprintf('Sayfa guncellendi: %s', $page['title']);
            } else {
                $report['messages'][] = sprintf('Sayfa korundu (ezilmedi): %s', $page['title']);
            }
        }

        // Alt menudeki sayfalari hiyerarsik olarak ana sayfalarina bagla.
        foreach ($seed_data as $slug => $page) {
            if (empty($page['parent'])) {
                continue;
            }

            $child_id  = !empty($ids[$slug]) ? (int) $ids[$slug] : 0;
            $parent_id = !empty($ids[$page['parent']]) ? (int) $ids[$page['parent']] : 0;
            if ($child_id <= 0 || $parent_id <= 0 || $child_id === $parent_id) {
                continue;
            }

            $current_parent = (int) get_post_field('post_parent', $child_id);
            if ($current_parent === $parent_id) {
                continue;
            }

            $parent_update = wp_update_post(array(
                'ID'          => $child_id,
                'post_parent' => $parent_id,
            ), true);

            if (is_wp_error($parent_update)) {
                $report['warnings'][] = sprintf('Alt sayfa baglanamadi: %s', $page['title']);
                continue;
            }

            $parent_title = !empty($seed_data[$page['parent']]['title']) ? $seed_data[$page['parent']]['title'] : 'Ana Sayfa';
            $report['messages'][] = sprintf('Alt sayfa baglandi: %s -> %s', $parent_title, $page['title']);
        }

        self::reconcile_legacy_duplicate_pages($ids, $report);
        self::deduplicate_seed_pages($seed_data, $ids, $report);

        return $ids;
    }

    private static function reconcile_legacy_duplicate_pages(&$page_ids, &$report)
    {
        // Eski sürümlerde kullanılan slug'lar yeni canonical slug setiyle çakışabiliyor.
        $legacy_map = array(
            'gonulluler' => 'gonulluluk',
            'bagiscilar' => 'bagiscilar-paydaslar',
            'paydaslar' => 'bagiscilar-paydaslar',
            'baskanlar' => 'eski-baskanlar',
        );

        foreach ($legacy_map as $legacy_slug => $canonical_slug) {
            $legacy_page = get_page_by_path($legacy_slug, OBJECT, 'page');
            if (!($legacy_page instanceof WP_Post)) {
                continue;
            }

            $legacy_id = (int) $legacy_page->ID;
            $canonical_id = !empty($page_ids[$canonical_slug]) ? (int) $page_ids[$canonical_slug] : 0;

            // Canonical henüz yoksa legacy kaydı canonical slug'a taşı.
            if ($canonical_id <= 0) {
                $update = wp_update_post(array(
                    'ID'        => $legacy_id,
                    'post_name' => sanitize_title($canonical_slug),
                ), true);
                if (!is_wp_error($update)) {
                    $page_ids[$canonical_slug] = $legacy_id;
                    update_post_meta($legacy_id, '_odtumist_eb_seed_key', sanitize_title($canonical_slug));
                    $report['messages'][] = sprintf(
                        'Legacy sayfa canonical slug ile birlestirildi: %s -> %s',
                        $legacy_slug,
                        $canonical_slug
                    );
                } else {
                    $report['warnings'][] = sprintf(
                        'Legacy sayfa birlestirilemedi: %s -> %s',
                        $legacy_slug,
                        $canonical_slug
                    );
                }
                continue;
            }

            if ($canonical_id === $legacy_id) {
                continue;
            }

            $canonical_page = get_post($canonical_id);
            if (!($canonical_page instanceof WP_Post)) {
                continue;
            }

            $canonical_content = trim((string) $canonical_page->post_content);
            $legacy_content = trim((string) $legacy_page->post_content);
            $canonical_excerpt = trim((string) $canonical_page->post_excerpt);
            $legacy_excerpt = trim((string) $legacy_page->post_excerpt);

            // Canonical sayfa boşsa legacy içeriği kaybetmeden aktar.
            $copy_payload = array('ID' => $canonical_id);
            $should_copy = false;
            if ($canonical_content === '' && $legacy_content !== '') {
                $copy_payload['post_content'] = (string) $legacy_page->post_content;
                $should_copy = true;
            }
            if ($canonical_excerpt === '' && $legacy_excerpt !== '') {
                $copy_payload['post_excerpt'] = (string) $legacy_page->post_excerpt;
                $should_copy = true;
            }
            if ($should_copy) {
                wp_update_post($copy_payload, true);
            }

            // Legacy sayfayı silmeden pasife al, duplicate görünümünü kır.
            $legacy_new_slug = sanitize_title($legacy_slug . '-legacy-' . $legacy_id);
            wp_update_post(array(
                'ID'          => $legacy_id,
                'post_status' => 'draft',
                'post_name'   => $legacy_new_slug,
            ), true);
            update_post_meta($legacy_id, '_odtumist_legacy_of', $canonical_id);

            $report['warnings'][] = sprintf(
                'Duplicate legacy sayfa pasife alindi: %s (ID %d) -> canonical %s (ID %d)',
                $legacy_slug,
                $legacy_id,
                $canonical_slug,
                $canonical_id
            );
        }
    }

    private static function deduplicate_seed_pages($seed_data, &$page_ids, &$report)
    {
        if (!is_array($seed_data) || empty($seed_data)) {
            return;
        }

        foreach ($seed_data as $slug => $page) {
            $canonical_slug = sanitize_title((string) $slug);
            if ($canonical_slug === '') {
                continue;
            }

            $matches = get_posts(array(
                'post_type'      => 'page',
                'name'           => $canonical_slug,
                'post_status'    => array('publish', 'draft', 'pending', 'future', 'private'),
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'orderby'        => 'ID',
                'order'          => 'ASC',
            ));

            if (!is_array($matches) || count($matches) <= 1) {
                continue;
            }

            $canonical_id = !empty($page_ids[$slug]) ? (int) $page_ids[$slug] : 0;
            if ($canonical_id <= 0 || !in_array($canonical_id, $matches, true)) {
                $canonical_id = (int) $matches[0];
                $page_ids[$slug] = $canonical_id;
            }

            $canonical_page = get_post($canonical_id);
            if (!($canonical_page instanceof WP_Post)) {
                continue;
            }

            foreach ($matches as $duplicate_id) {
                $duplicate_id = (int) $duplicate_id;
                if ($duplicate_id <= 0 || $duplicate_id === $canonical_id) {
                    continue;
                }

                $duplicate_page = get_post($duplicate_id);
                if (!($duplicate_page instanceof WP_Post)) {
                    continue;
                }

                // Canonical sayfa boşsa, içerik kaybı olmaması için duplicate içeriğini taşı.
                $canonical_content = trim((string) $canonical_page->post_content);
                $canonical_excerpt = trim((string) $canonical_page->post_excerpt);
                $duplicate_content = trim((string) $duplicate_page->post_content);
                $duplicate_excerpt = trim((string) $duplicate_page->post_excerpt);

                $copy_payload = array('ID' => $canonical_id);
                $should_copy = false;
                if ($canonical_content === '' && $duplicate_content !== '') {
                    $copy_payload['post_content'] = (string) $duplicate_page->post_content;
                    $should_copy = true;
                }
                if ($canonical_excerpt === '' && $duplicate_excerpt !== '') {
                    $copy_payload['post_excerpt'] = (string) $duplicate_page->post_excerpt;
                    $should_copy = true;
                }
                if ($should_copy) {
                    wp_update_post($copy_payload, true);
                    $canonical_page = get_post($canonical_id);
                }

                $new_slug = sanitize_title($canonical_slug . '-duplicate-' . $duplicate_id);
                wp_update_post(array(
                    'ID'          => $duplicate_id,
                    'post_status' => 'draft',
                    'post_name'   => $new_slug,
                ), true);
                update_post_meta($duplicate_id, '_odtumist_duplicate_of', $canonical_id);

                $report['warnings'][] = sprintf(
                    'Ayni slug icin duplicate sayfa pasife alindi: %s (ID %d) -> canonical ID %d',
                    $canonical_slug,
                    $duplicate_id,
                    $canonical_id
                );
            }
        }
    }

    private static function upsert_teams(&$report, $force_reseed)
    {
        foreach (self::team_seed_data() as $item) {
            $status  = 'skipped';
            $post_id = self::upsert_post('team', $item['slug'], $item, $force_reseed, $status);

            if (is_wp_error($post_id)) {
                $report['warnings'][] = sprintf('Calisma grubu olusturulamadi: %s', $item['title']);
                continue;
            }

            update_post_meta($post_id, '_odtumist_eb_seed_key', sanitize_title($item['slug']));
            if (!empty($item['category'])) {
                wp_set_object_terms($post_id, $item['category'], 'team-category', false);
            }

            if ($status === 'created') {
                $report['messages'][] = sprintf('Calisma grubu olusturuldu: %s', $item['title']);
            } elseif ($status === 'updated') {
                $report['messages'][] = sprintf('Calisma grubu guncellendi: %s', $item['title']);
            }
        }
    }

    private static function upsert_events(&$report, $force_reseed)
    {
        foreach (self::event_seed_data() as $item) {
            $status  = 'skipped';
            $post_id = self::upsert_post('event', $item['slug'], $item, $force_reseed, $status);

            if (is_wp_error($post_id)) {
                $report['warnings'][] = sprintf('Etkinlik olusturulamadi: %s', $item['title']);
                continue;
            }

            update_post_meta($post_id, '_odtumist_eb_seed_key', sanitize_title($item['slug']));
            if (!empty($item['category'])) {
                wp_set_object_terms($post_id, $item['category'], 'event-category', false);
            }
            if (!empty($item['location'])) {
                update_post_meta($post_id, 'event_address', $item['location']);
                update_post_meta($post_id, 'solicitor_event_address', $item['location']);
            }
            if (!empty($item['start_dt'])) {
                update_post_meta($post_id, 'event_start_dt', $item['start_dt']);
                update_post_meta($post_id, 'solicitor_event_start_dt', $item['start_dt']);
            }

            if ($status === 'created') {
                $report['messages'][] = sprintf('Etkinlik olusturuldu: %s', $item['title']);
            } elseif ($status === 'updated') {
                $report['messages'][] = sprintf('Etkinlik guncellendi: %s', $item['title']);
            }
        }
    }

    private static function upsert_post($post_type, $slug, $payload, $force_update, &$result_status, $path_hint = '')
    {
        $result_status = 'skipped';
        $slug          = sanitize_title($slug);
        $seed_key      = $slug;
        $existing      = get_posts(array(
            'post_type'      => $post_type,
            'post_status'    => array('publish', 'draft', 'pending', 'future', 'private'),
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_odtumist_eb_seed_key',
            'meta_value'     => $seed_key,
        ));

        if (empty($existing) && $post_type === 'page') {
            $path_hint = sanitize_text_field((string) $path_hint);
            if ($path_hint !== '') {
                $found_by_path = get_page_by_path($path_hint, OBJECT, 'page');
                if ($found_by_path instanceof WP_Post) {
                    $existing = array((int) $found_by_path->ID);
                }
            }
        }

        if (empty($existing) && $post_type === 'page') {
            $found_by_slug = get_page_by_path($slug, OBJECT, 'page');
            if ($found_by_slug instanceof WP_Post) {
                $existing = array((int) $found_by_slug->ID);
            }
        }

        if (empty($existing)) {
            $existing = get_posts(array(
                'post_type'      => $post_type,
                'name'           => $slug,
                'post_status'    => array('publish', 'draft', 'pending', 'future', 'private'),
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ));
        }

        $postarr = array(
            'post_type'    => $post_type,
            'post_status'  => 'publish',
            'post_name'    => $slug,
            'post_title'   => wp_strip_all_tags((string) $payload['title']),
            'post_excerpt' => isset($payload['excerpt']) ? (string) $payload['excerpt'] : '',
            'post_content' => isset($payload['content']) ? (string) $payload['content'] : '',
        );

        if (!empty($existing)) {
            $existing_id = (int) $existing[0];
            if (!$force_update) {
                $existing_post = get_post($existing_id);
                if ($existing_post instanceof WP_Post) {
                    $partial_update = array('ID' => $existing_id);
                    $should_update = false;

                    $current_title = trim(wp_strip_all_tags((string) $existing_post->post_title));
                    if ($current_title === '' && trim((string) $postarr['post_title']) !== '') {
                        $partial_update['post_title'] = (string) $postarr['post_title'];
                        $should_update = true;
                    }

                    $current_excerpt = trim((string) $existing_post->post_excerpt);
                    if ($current_excerpt === '' && trim((string) $postarr['post_excerpt']) !== '') {
                        $partial_update['post_excerpt'] = (string) $postarr['post_excerpt'];
                        $should_update = true;
                    }

                    $current_content = trim((string) $existing_post->post_content);
                    if ($current_content === '' && trim((string) $postarr['post_content']) !== '') {
                        $partial_update['post_content'] = (string) $postarr['post_content'];
                        $should_update = true;
                    }

                    if ($should_update) {
                        $result = wp_update_post($partial_update, true);
                        if (!is_wp_error($result)) {
                            $result_status = 'updated';
                        }
                    }
                }

                return $existing_id;
            }

            $postarr['ID'] = $existing_id;
            $result        = wp_update_post($postarr, true);
            if (!is_wp_error($result)) {
                $result_status = 'updated';
            }

            return $result;
        }

        $result = wp_insert_post($postarr, true);
        if (!is_wp_error($result)) {
            $result_status = 'created';
        }

        return $result;
    }

    private static function build_menus($page_ids, &$report, $rebuild_menus)
    {
        if (empty($page_ids['hakkimizda']) || empty($page_ids['etkinlikler']) || empty($page_ids['uyelik']) || empty($page_ids['dayanisma']) || empty($page_ids['iletisim'])) {
            $report['warnings'][] = __('Menu kurulumu icin gerekli sayfalar bulunamadi.', 'odtumist-eb');
            return;
        }

        $main_menu_id             = self::get_or_create_menu('ODTÜMİST Main Menu');
        $footer_menu_id           = self::get_or_create_menu('ODTÜMİST Footer Menu');
        $footer_corporate_menu_id = self::get_or_create_menu('ODTÜMİST Footer Corporate Menu');
        $footer_info_menu_id      = self::get_or_create_menu('ODTÜMİST Footer Info Menu');

        if ($main_menu_id < 1 || $footer_menu_id < 1 || $footer_corporate_menu_id < 1 || $footer_info_menu_id < 1) {
            $report['warnings'][] = __('Menu olusturma adimi atlandi.', 'odtumist-eb');
            return;
        }

        $should_rebuild_main             = $rebuild_menus
            || !self::menu_has_items($main_menu_id)
            || self::menu_has_missing_titles($main_menu_id, array(
                'HAKKIMIZDA',
                'ETKİNLİKLER',
                'ÜYELİK',
                'DAYANIŞMA',
                'İLETİŞİM',
                'Ekip',
                'Spor & Maraton',
                'Bağışçılar / Paydaşlar',
            ));
        $should_rebuild_footer           = $rebuild_menus
            || !self::menu_has_items($footer_menu_id)
            || self::menu_has_missing_titles($footer_menu_id, array('Hakkımızda', 'Etkinlikler', 'Üyelik', 'Dayanışma', 'İletişim'));
        $should_rebuild_footer_corporate = $rebuild_menus
            || !self::menu_has_items($footer_corporate_menu_id)
            || self::menu_has_missing_titles($footer_corporate_menu_id, array('Bir Bakışta ODTÜMİST', 'Yönetim Kurulu', 'Profesyonel Ekip'));
        $should_rebuild_footer_info      = $rebuild_menus
            || !self::menu_has_items($footer_info_menu_id)
            || self::menu_has_missing_titles($footer_info_menu_id, array('Yönetim', 'Tüzük', 'Yönetmelikler', 'Faaliyet Raporları'));

        if ($should_rebuild_main) {
            self::clear_menu_items($main_menu_id);
            self::fill_main_menu($main_menu_id, $page_ids);
            $report['messages'][] = __('Ana menu guncellendi.', 'odtumist-eb');
        } else {
            $report['messages'][] = __('Ana menu korunarak birakildi (ezilmedi).', 'odtumist-eb');
        }

        // Geçmişte child sayfaya bağlanmış menüleri hash tabanlı URL'e normalize et.
        self::normalize_main_menu_anchor_children($main_menu_id, $page_ids, $report);

        if ($should_rebuild_footer) {
            self::clear_menu_items($footer_menu_id);
            self::fill_footer_menu($footer_menu_id, $page_ids);
            $report['messages'][] = __('Footer menu guncellendi.', 'odtumist-eb');
        } else {
            $report['messages'][] = __('Footer menu korunarak birakildi (ezilmedi).', 'odtumist-eb');
        }

        if ($should_rebuild_footer_corporate) {
            self::clear_menu_items($footer_corporate_menu_id);
            self::fill_footer_corporate_menu($footer_corporate_menu_id, $page_ids);
            $report['messages'][] = __('Kurumsal footer menu guncellendi.', 'odtumist-eb');
        } else {
            $report['messages'][] = __('Kurumsal footer menu korunarak birakildi (ezilmedi).', 'odtumist-eb');
        }

        if ($should_rebuild_footer_info) {
            self::clear_menu_items($footer_info_menu_id);
            self::fill_footer_info_menu($footer_info_menu_id, $page_ids);
            $report['messages'][] = __('Bilgi Merkezi footer menu guncellendi.', 'odtumist-eb');
        } else {
            $report['messages'][] = __('Bilgi Merkezi footer menu korunarak birakildi (ezilmedi).', 'odtumist-eb');
        }

        self::apply_menu_locations($main_menu_id, $footer_menu_id, $footer_corporate_menu_id, $footer_info_menu_id);
    }

    private static function fill_main_menu($menu_id, $page_ids)
    {
        $about_url      = get_permalink($page_ids['hakkimizda']);
        $events_url     = get_permalink($page_ids['etkinlikler']);
        $membership_url = get_permalink($page_ids['uyelik']);
        $solidarity_url = get_permalink($page_ids['dayanisma']);

        $main_items = array(
            array(
                'title'  => 'HAKKIMIZDA',
                'object' => 'page',
                'id'     => $page_ids['hakkimizda'],
                'children' => array(
                    array('title' => 'Neler Yapıyoruz?', 'slug' => 'neler-yapiyoruz', 'url' => $about_url . '#neler-yapiyoruz'),
                    array('title' => 'Çalışma Gruplarımız', 'slug' => 'calisma-gruplarimiz', 'url' => $about_url . '#calisma-gruplarimiz'),
                    array('title' => 'Tarihçe', 'slug' => 'tarihce', 'url' => $about_url . '#tarihce'),
                    array('title' => 'Yönetim', 'slug' => 'yonetim', 'url' => $about_url . '#yonetim'),
                    array('title' => 'Ekip', 'slug' => 'profesyonel-ekip', 'url' => home_url('/profesyonel-ekip/')),
                ),
            ),
            array('title' => 'ETKİNLİKLER', 'object' => 'page', 'id' => $page_ids['etkinlikler'], 'children' => array()),
            array(
                'title'  => 'ÜYELİK',
                'object' => 'page',
                'id'     => $page_ids['uyelik'],
                'children' => array(
                    array('title' => 'Neden Üye Olmalıyım?', 'slug' => 'neden-uye-olmaliyim', 'url' => $membership_url . '#neden-uye-olmaliyim'),
                    array('title' => 'Bilgi Güncelleme', 'slug' => 'bilgi-guncelleme', 'url' => $membership_url . '#bilgi-guncelleme'),
                    array('title' => 'Aidat Ödeme', 'slug' => 'aidat-odeme', 'url' => $membership_url . '#aidat-odeme'),
                    array('title' => 'Üyelik Avantajları', 'slug' => 'uyelik-avantajlari', 'url' => $membership_url . '#uyelik-avantajlari'),
                    array('title' => 'Nasıl Üye Olabilirsiniz?', 'slug' => 'nasil-uye-olabilirsiniz', 'url' => $membership_url . '#nasil-uye-olabilirsiniz'),
                    array('title' => 'Yeni Mezunlar İçin Üyelik', 'slug' => 'yeni-mezunlar-icin-uyelik', 'url' => $membership_url . '#yeni-mezunlar-icin-uyelik'),
                    array('title' => 'Üyelik SSS', 'slug' => 'uyelik-sss', 'url' => $membership_url . '#uyelik-sss'),
                ),
            ),
            array(
                'title'  => 'DAYANIŞMA',
                'object' => 'page',
                'id'     => $page_ids['dayanisma'],
                'children' => array(
                    array('title' => 'Burs', 'slug' => 'burs', 'url' => $solidarity_url . '#burs'),
                    array('title' => 'Spor & Maraton', 'slug' => 'maraton', 'url' => $solidarity_url . '#maraton'),
                    array('title' => 'Mentorluk', 'slug' => 'mentorluk', 'url' => $solidarity_url . '#mentorluk'),
                    array('title' => 'Gönüllüler', 'slug' => 'gonulluluk', 'url' => $solidarity_url . '#gonulluluk'),
                    array('title' => 'Gençlik & İletişim', 'slug' => 'genclik-iletisim', 'url' => $solidarity_url . '#genclik-iletisim'),
                    array('title' => 'Bağışçılar / Paydaşlar', 'slug' => 'bagiscilar-paydaslar', 'url' => $solidarity_url . '#bagiscilar-paydaslar'),
                    array('title' => 'Bursiyerler', 'slug' => 'bursiyerler', 'url' => $solidarity_url . '#bursiyerler'),
                    array('title' => 'Networking', 'slug' => 'networking', 'url' => $solidarity_url . '#networking'),
                ),
            ),
            array('title' => 'İLETİŞİM', 'object' => 'page', 'id' => $page_ids['iletisim'], 'children' => array()),
        );

        foreach ($main_items as $item) {
            $parent_id = wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title'     => $item['title'],
                'menu-item-object'    => 'page',
                'menu-item-object-id' => (int) $item['id'],
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ));

            if (is_wp_error($parent_id)) {
                continue;
            }

            foreach ($item['children'] as $child) {
                $child_slug = !empty($child['slug']) ? sanitize_title((string) $child['slug']) : '';
                $child_id   = ($child_slug !== '' && !empty($page_ids[$child_slug])) ? (int) $page_ids[$child_slug] : 0;
                $child_url  = !empty($child['url']) ? (string) $child['url'] : '';
                $is_anchor_child = $child_url !== '' && strpos($child_url, '#') !== false;

                // Tab yapısının tek kaynağı parent sayfa olsun.
                if ($is_anchor_child) {
                    wp_update_nav_menu_item($menu_id, 0, array(
                        'menu-item-title'     => $child['title'],
                        'menu-item-url'       => $child_url,
                        'menu-item-type'      => 'custom',
                        'menu-item-parent-id' => (int) $parent_id,
                        'menu-item-status'    => 'publish',
                    ));
                    continue;
                }

                if ($child_id > 0) {
                    wp_update_nav_menu_item($menu_id, 0, array(
                        'menu-item-title'     => $child['title'],
                        'menu-item-object'    => 'page',
                        'menu-item-object-id' => $child_id,
                        'menu-item-type'      => 'post_type',
                        'menu-item-parent-id' => (int) $parent_id,
                        'menu-item-status'    => 'publish',
                    ));
                    continue;
                }

                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title'     => $child['title'],
                    'menu-item-url'       => $child['url'],
                    'menu-item-type'      => 'custom',
                    'menu-item-parent-id' => (int) $parent_id,
                    'menu-item-status'    => 'publish',
                ));
            }
        }
    }

    private static function fill_footer_menu($menu_id, $page_ids)
    {
        $footer_items = array(
            array('title' => 'Hakkımızda', 'id' => $page_ids['hakkimizda']),
            array('title' => 'Etkinlikler', 'id' => $page_ids['etkinlikler']),
            array('title' => 'Üyelik', 'id' => $page_ids['uyelik']),
            array('title' => 'Dayanışma', 'id' => $page_ids['dayanisma']),
            array('title' => 'İletişim', 'id' => $page_ids['iletisim']),
        );

        foreach ($footer_items as $item) {
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title'     => $item['title'],
                'menu-item-object'    => 'page',
                'menu-item-object-id' => (int) $item['id'],
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ));
        }
    }

    private static function fill_footer_corporate_menu($menu_id, $page_ids)
    {
        $about_url = !empty($page_ids['hakkimizda']) ? get_permalink((int) $page_ids['hakkimizda']) : home_url('/hakkimizda/');
        $items = array(
            array(
                'title' => 'Bir Bakışta ODTÜMİST',
                'url' => $about_url . '#neler-yapiyoruz',
            ),
            array(
                'title' => 'Yönetim Kurulu',
                'url' => !empty($page_ids['yonetim-organlari']) ? get_permalink((int) $page_ids['yonetim-organlari']) : home_url('/yonetim-organlari/'),
            ),
            array(
                'title' => 'Profesyonel Ekip',
                'url' => !empty($page_ids['profesyonel-ekip']) ? get_permalink((int) $page_ids['profesyonel-ekip']) : home_url('/profesyonel-ekip/'),
            ),
        );

        foreach ($items as $item) {
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title'  => $item['title'],
                'menu-item-url'    => $item['url'],
                'menu-item-type'   => 'custom',
                'menu-item-status' => 'publish',
            ));
        }
    }

    private static function fill_footer_info_menu($menu_id, $page_ids)
    {
        $items = array(
            array('title' => 'Yönetim', 'url' => !empty($page_ids['yonetim-organlari']) ? get_permalink((int) $page_ids['yonetim-organlari']) : home_url('/yonetim-organlari/')),
            array('title' => 'Tüzük', 'url' => !empty($page_ids['tuzuk']) ? get_permalink((int) $page_ids['tuzuk']) : home_url('/tuzuk/')),
            array('title' => 'Yönetmelikler', 'url' => !empty($page_ids['yonetmelikler']) ? get_permalink((int) $page_ids['yonetmelikler']) : home_url('/yonetmelikler/')),
            array('title' => 'Faaliyet Raporları', 'url' => !empty($page_ids['faaliyet-raporlari']) ? get_permalink((int) $page_ids['faaliyet-raporlari']) : home_url('/faaliyet-raporlari/')),
        );

        foreach ($items as $item) {
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title'  => $item['title'],
                'menu-item-url'    => $item['url'],
                'menu-item-type'   => 'custom',
                'menu-item-status' => 'publish',
            ));
        }
    }

    private static function normalize_main_menu_anchor_children($menu_id, $page_ids, &$report)
    {
        $menu_id = (int) $menu_id;
        if ($menu_id <= 0) {
            return;
        }

        $about_url      = !empty($page_ids['hakkimizda']) ? get_permalink((int) $page_ids['hakkimizda']) : home_url('/hakkimizda/');
        $membership_url = !empty($page_ids['uyelik']) ? get_permalink((int) $page_ids['uyelik']) : home_url('/uyelik/');
        $solidarity_url = !empty($page_ids['dayanisma']) ? get_permalink((int) $page_ids['dayanisma']) : home_url('/dayanisma/');

        $anchor_targets = array(
            'Neler Yapıyoruz?' => $about_url . '#neler-yapiyoruz',
            'Çalışma Gruplarımız' => $about_url . '#calisma-gruplarimiz',
            'Tarihçe' => $about_url . '#tarihce',
            'Yönetim' => $about_url . '#yonetim',
            'Neden Üye Olmalıyım?' => $membership_url . '#neden-uye-olmaliyim',
            'Bilgi Güncelleme' => $membership_url . '#bilgi-guncelleme',
            'Aidat Ödeme' => $membership_url . '#aidat-odeme',
            'Üyelik Avantajları' => $membership_url . '#uyelik-avantajlari',
            'Nasıl Üye Olabilirsiniz?' => $membership_url . '#nasil-uye-olabilirsiniz',
            'Yeni Mezunlar İçin Üyelik' => $membership_url . '#yeni-mezunlar-icin-uyelik',
            'Üyelik SSS' => $membership_url . '#uyelik-sss',
            'Burs' => $solidarity_url . '#burs',
            'Spor & Maraton' => $solidarity_url . '#maraton',
            'Mentorluk' => $solidarity_url . '#mentorluk',
            'Gönüllüler' => $solidarity_url . '#gonulluluk',
            'Gençlik & İletişim' => $solidarity_url . '#genclik-iletisim',
            'Bağışçılar / Paydaşlar' => $solidarity_url . '#bagiscilar-paydaslar',
            'Bursiyerler' => $solidarity_url . '#bursiyerler',
            'Networking' => $solidarity_url . '#networking',
        );

        $items = wp_get_nav_menu_items($menu_id, array('post_status' => 'any'));
        if (!is_array($items) || empty($items)) {
            return;
        }

        $updated = 0;
        foreach ($items as $item) {
            if (!($item instanceof WP_Post)) {
                continue;
            }

            $parent_item_id = (int) $item->menu_item_parent;
            if ($parent_item_id <= 0) {
                continue;
            }

            $title = trim(wp_strip_all_tags((string) $item->title));
            if ($title === '' || empty($anchor_targets[$title])) {
                continue;
            }

            $target_url = (string) $anchor_targets[$title];
            $current_url = trim((string) $item->url);
            $is_custom = ((string) $item->type === 'custom');

            if ($is_custom && $current_url === $target_url) {
                continue;
            }

            $result = wp_update_nav_menu_item($menu_id, (int) $item->ID, array(
                'menu-item-title'     => $title,
                'menu-item-url'       => $target_url,
                'menu-item-type'      => 'custom',
                'menu-item-parent-id' => $parent_item_id,
                'menu-item-status'    => 'publish',
            ));

            if (!is_wp_error($result)) {
                $updated++;
            }
        }

        if ($updated > 0) {
            $report['messages'][] = sprintf(
                '%d adet alt menu baglantisi parent sayfa hash yapisina normalize edildi.',
                $updated
            );
        }
    }

    private static function apply_menu_locations($main_menu_id, $footer_menu_id, $footer_corporate_menu_id, $footer_info_menu_id)
    {
        $registered_locations = get_registered_nav_menus();
        $locations            = get_theme_mod('nav_menu_locations', array());

        if (isset($registered_locations['menu-1'])) {
            $locations['menu-1'] = $main_menu_id;
        }
        if (isset($registered_locations['primary-menu'])) {
            $locations['primary-menu'] = $main_menu_id;
        }
        if (isset($registered_locations['footer-menu'])) {
            $locations['footer-menu'] = $footer_menu_id;
        }
        if (isset($registered_locations['footer-corporate-menu'])) {
            $locations['footer-corporate-menu'] = $footer_corporate_menu_id;
        }
        if (isset($registered_locations['footer-info-menu'])) {
            $locations['footer-info-menu'] = $footer_info_menu_id;
        }
        if (isset($registered_locations['menu-2'])) {
            $locations['menu-2'] = $footer_menu_id;
        }

        set_theme_mod('nav_menu_locations', $locations);
    }

    private static function menu_has_items($menu_id)
    {
        $items = wp_get_nav_menu_items($menu_id, array('post_status' => 'any'));
        return is_array($items) && !empty($items);
    }

    private static function menu_has_missing_titles($menu_id, $required_titles)
    {
        $required_titles = is_array($required_titles) ? $required_titles : array();
        if (empty($required_titles)) {
            return false;
        }

        $items = wp_get_nav_menu_items($menu_id, array('post_status' => 'any'));
        if (!is_array($items) || empty($items)) {
            return true;
        }

        $seen = array();
        foreach ($items as $item) {
            if (!is_object($item) || empty($item->title)) {
                continue;
            }
            $slug = sanitize_title((string) wp_strip_all_tags((string) $item->title));
            if ($slug !== '') {
                $seen[$slug] = true;
            }
        }

        foreach ($required_titles as $required_title) {
            $required_slug = sanitize_title((string) $required_title);
            if ($required_slug === '') {
                continue;
            }
            if (empty($seen[$required_slug])) {
                return true;
            }
        }

        return false;
    }

    private static function get_or_create_menu($name)
    {
        $menu = wp_get_nav_menu_object($name);
        if ($menu) {
            return (int) $menu->term_id;
        }
        $created = wp_create_nav_menu($name);
        return is_wp_error($created) ? 0 : (int) $created;
    }

    private static function clear_menu_items($menu_id)
    {
        $items = wp_get_nav_menu_items($menu_id, array('post_status' => 'any'));
        if (empty($items)) {
            return;
        }
        foreach ($items as $item) {
            wp_delete_post((int) $item->ID, true);
        }
    }

    private static function apply_reading_settings($page_ids, &$report)
    {
        if (!empty($page_ids['anasayfa'])) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', (int) $page_ids['anasayfa']);
        }
        if (!empty($page_ids['haberler'])) {
            update_option('page_for_posts', (int) $page_ids['haberler']);
        }
        $report['messages'][] = __('Okuma ayarlari guncellendi (statik anasayfa + haberler).', 'odtumist-eb');
    }

    private static function ensure_home_slider_theme_mods($page_ids, &$report, $force_reseed)
    {
        $defaults = self::get_default_home_slider_slides($page_ids);
        $updated  = false;

        foreach ($defaults as $index => $slide) {
            $slide_no = $index + 1;
            $map = array(
                "odtumist_hero_{$slide_no}_image" => isset($slide['image']) ? (string) $slide['image'] : '',
                "odtumist_hero_{$slide_no}_title" => isset($slide['title']) ? (string) $slide['title'] : '',
                "odtumist_hero_{$slide_no}_desc" => isset($slide['desc']) ? (string) $slide['desc'] : '',
                "odtumist_hero_{$slide_no}_primary_label" => isset($slide['primary']['label']) ? (string) $slide['primary']['label'] : '',
                "odtumist_hero_{$slide_no}_primary_url" => isset($slide['primary']['url']) ? (string) $slide['primary']['url'] : '',
                "odtumist_hero_{$slide_no}_secondary_label" => (!empty($slide['secondary']) && isset($slide['secondary']['label'])) ? (string) $slide['secondary']['label'] : '',
                "odtumist_hero_{$slide_no}_secondary_url" => (!empty($slide['secondary']) && isset($slide['secondary']['url'])) ? (string) $slide['secondary']['url'] : '',
            );

            foreach ($map as $key => $value) {
                $current_value = (string) get_theme_mod($key, '');
                if (!$force_reseed && trim($current_value) !== '') {
                    continue;
                }
                if ($force_reseed || $current_value !== $value) {
                    set_theme_mod($key, $value);
                    $updated = true;
                }
            }
        }

        if ($updated) {
            $report['messages'][] = $force_reseed
                ? 'Anasayfa slider ayarlari sifirdan kuruldu (3 slayt).'
                : 'Anasayfa slider icin eksik tema ayarlari tamamlandi.';
        }
    }

    private static function ensure_contact_theme_mods(&$report, $force_reseed)
    {
        $defaults = array(
            'odtumist_brand_name' => 'ODTÜMİST',
            'odtumist_org_name' => 'İstanbul ODTÜ Mezunları Derneği',
            'odtumist_contact_address' => "Cumhuriyet Cad. Cumhuriyet Apt. No: 17\nKat: 2 D: 5 Taksim, Beyoğlu, İstanbul",
            'odtumist_contact_phone' => "0546 522 96 11\n0533 206 23 01\n0546 522 96 41",
            'odtumist_contact_email' => 'dernek@odtumist.org',
            'odtumist_contact_map_url' => 'https://www.google.com/maps?q=Cumhuriyet+Cad.+Cumhuriyet+Apt.+No:+17,+Beyoglu,+Istanbul&output=embed',
            'odtumist_contact_hero_text' => "İstanbul ODTÜ Mezunları Derneği ile iletişimde kalmak için bize yazabilir, arayabilir veya dernek merkezimizi ziyaret edebilirsin.",
        );

        $legacy_markers = array(
            'odtumist_brand_name' => array('odtumist'),
            'odtumist_org_name' => array('odtü mezunlar derneği'),
            'odtumist_contact_address' => array('odtüpark', 'odtupark', 'levazım', 'levazim'),
            'odtumist_contact_phone' => array('281 40 47', '2814047'),
            'odtumist_contact_map_url' => array('odtupark', 'levaz', 'ulus'),
            'odtumist_contact_hero_text' => array('odtüpark', 'odtupark', 'ulus'),
        );

        $updated = false;
        foreach ($defaults as $key => $value) {
            $current = get_theme_mod($key, '');
            $current = is_string($current) ? $current : '';
            $normalized = function_exists('mb_strtolower')
                ? mb_strtolower($current, 'UTF-8')
                : strtolower($current);

            $has_legacy = false;
            if (!empty($legacy_markers[$key])) {
                foreach ($legacy_markers[$key] as $marker) {
                    if ($marker !== '' && strpos($normalized, $marker) !== false) {
                        $has_legacy = true;
                        break;
                    }
                }
            }

            // Resmi iletisim alanlarini eksik/yanlis anahtarlar acisindan da denetle.
            $missing_required_marker = false;
            if ($key === 'odtumist_brand_name') {
                if (trim($current) === '' || strcasecmp(trim($current), 'ODTUMIST') === 0) {
                    $missing_required_marker = true;
                }
            } elseif ($key === 'odtumist_org_name') {
                if (strpos($normalized, 'mezunlar') !== false && strpos($normalized, 'mezunları') === false) {
                    $missing_required_marker = true;
                }
            } elseif ($key === 'odtumist_contact_address') {
                if (strpos($normalized, 'cumhuriyet') === false || (strpos($normalized, 'beyo') === false && strpos($normalized, 'taksim') === false)) {
                    $missing_required_marker = true;
                }
            } elseif ($key === 'odtumist_contact_phone') {
                if (strpos($normalized, '0546 522 96 11') === false && strpos($normalized, '05465229611') === false) {
                    $missing_required_marker = true;
                }
            } elseif ($key === 'odtumist_contact_email') {
                if (strpos($normalized, 'dernek@odtumist.org') === false) {
                    $missing_required_marker = true;
                }
            }

            if (!$force_reseed && !$has_legacy && !$missing_required_marker && trim($current) !== '') {
                continue;
            }

            set_theme_mod($key, $value);
            $updated = true;
        }

        if ($updated) {
            $report['messages'][] = $force_reseed
                ? 'Resmi dernek iletisim bilgileri yeniden yazildi.'
                : 'Eksik/legacy iletisim bilgileri resmi dernek bilgileriyle guncellendi.';
        }
    }

    private static function cleanup_defaults(&$report)
    {
        $sample_page = get_page_by_path('sample-page', OBJECT, 'page');
        if ($sample_page instanceof WP_Post) {
            wp_delete_post($sample_page->ID, true);
        }

        $hello_post = get_page_by_path('hello-world', OBJECT, 'post');
        if ($hello_post instanceof WP_Post) {
            wp_delete_post($hello_post->ID, true);
        }

        $report['messages'][] = __('Varsayilan ornek icerikler temizlendi.', 'odtumist-eb');
    }

    private static function apply_elementor_defaults(&$report)
    {
        if (!class_exists('Elementor\\Plugin')) {
            $report['warnings'][] = __('Elementor aktif olmadigi icin Elementor ayarlari atlandi.', 'odtumist-eb');
            return;
        }

        update_option('elementor_disable_color_schemes', 'yes');
        update_option('elementor_disable_typography_schemes', 'yes');
        update_option('elementor_css_print_method', 'external');
        update_option('elementor_container_width', 1200);

        $cpt_support = get_option('elementor_cpt_support', array('page', 'post'));
        if (!is_array($cpt_support)) {
            $cpt_support = array('page', 'post');
        }
        $cpt_support = array_values(array_unique(array_merge($cpt_support, array('page', 'post', 'team', 'event'))));
        update_option('elementor_cpt_support', $cpt_support);

        $report['messages'][] = __('Elementor temel ayarlari uygulandi.', 'odtumist-eb');
    }

    private static function clear_elementor_runtime_cache(&$report)
    {
        if (!class_exists('Elementor\\Plugin')) {
            return;
        }

        try {
            $plugin = \Elementor\Plugin::instance();

            if (is_object($plugin) && isset($plugin->files_manager) && method_exists($plugin->files_manager, 'clear_cache')) {
                $plugin->files_manager->clear_cache();
            }
            if (is_object($plugin) && isset($plugin->experiments) && method_exists($plugin->experiments, 'clear_cache')) {
                $plugin->experiments->clear_cache();
            }

            $report['messages'][] = 'Elementor cache temizlendi.';
        } catch (\Throwable $e) {
            $report['warnings'][] = 'Elementor cache temizlenirken hata alindi: ' . $e->getMessage();
        }
    }

    private static function seed_elementor_pages($page_ids, &$report, $force_reseed, $full_mode)
    {
        if (!class_exists('Elementor\\Plugin')) {
            return;
        }

        $blueprints = self::page_elementor_blueprints($page_ids, (bool) $full_mode);
        foreach ((array) $page_ids as $slug => $page_id_raw) {
            $page_id = (int) $page_id_raw;
            if ($page_id <= 0) {
                continue;
            }

            $payload = !empty($blueprints[$slug]) && is_array($blueprints[$slug])
                ? $blueprints[$slug]
                : self::build_default_elementor_payload_for_page($page_id);

            if (!$force_reseed && self::is_elementor_data_present($page_id)) {
                $report['messages'][] = sprintf('Elementor duzeni korundu (ezilmedi): %s', $payload['title']);
                continue;
            }

            $document = array();
            if (!empty($payload['document']) && is_array($payload['document'])) {
                $document = $payload['document'];
            } else {
                $widgets  = isset($payload['widgets']) && is_array($payload['widgets']) ? $payload['widgets'] : array();
                $document = self::build_elementor_document($widgets);
            }

            $data_json = wp_json_encode($document);
            if (!is_string($data_json) || $data_json === '') {
                $report['warnings'][] = sprintf('Elementor verisi olusturulamadi: %s', $payload['title']);
                continue;
            }

            update_post_meta($page_id, '_elementor_edit_mode', 'builder');
            update_post_meta($page_id, '_elementor_template_type', 'wp-page');
            update_post_meta($page_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '3.0.0');
            update_post_meta($page_id, '_elementor_page_settings', array('hide_title' => 'yes'));
            update_post_meta($page_id, '_elementor_data', wp_slash($data_json));
            $report['messages'][] = sprintf('Elementor duzeni olusturuldu: %s', $payload['title']);
        }
    }

    private static function build_default_elementor_payload_for_page($page_id)
    {
        $page_id = (int) $page_id;
        $title = $page_id > 0 ? get_the_title($page_id) : 'Sayfa';
        $excerpt = $page_id > 0 ? (string) get_post_field('post_excerpt', $page_id) : '';
        $content = $page_id > 0 ? (string) get_post_field('post_content', $page_id) : '';

        $widgets = array(
            self::build_widget('heading', array(
                'title' => (string) $title,
                'size' => 'xxl',
                'align' => 'left',
                '_css_classes' => 'odt-el-title',
            )),
        );

        if (trim($excerpt) !== '') {
            $widgets[] = self::build_widget('text-editor', array(
                'editor' => '<p>' . esc_html($excerpt) . '</p>',
                '_css_classes' => 'odt-el-subtitle',
            ));
        }

        $body = trim($content);
        if ($body === '') {
            $body = '<p>Bu sayfayı Elementor ile düzenleyebilirsiniz.</p>';
        }

        $widgets[] = self::build_widget('text-editor', array(
            'editor' => wp_kses_post(wpautop($body)),
            '_css_classes' => 'odt-el-richtext',
        ));

        $document = array(
            self::build_section_with_columns(
                array(
                    array(
                        'size' => 100,
                        'widgets' => $widgets,
                    ),
                ),
                array('_css_classes' => 'odt-el odt-el-section odt-el-generic-page')
            ),
        );

        return array(
            'title' => (string) $title,
            'document' => $document,
        );
    }

    private static function audit_seeded_pages_elementor_readiness($page_ids, &$report)
    {
        if (!is_array($page_ids) || empty($page_ids)) {
            return;
        }

        $not_ready = array();
        $ready_count = 0;

        foreach ($page_ids as $slug => $page_id_raw) {
            $page_id = (int) $page_id_raw;
            if ($page_id <= 0) {
                continue;
            }

            $reasons = array();
            if (get_post_type($page_id) !== 'page') {
                $reasons[] = 'post_type_page_degil';
            }

            $edit_mode = (string) get_post_meta($page_id, '_elementor_edit_mode', true);
            if ($edit_mode !== 'builder') {
                $reasons[] = 'edit_mode_builder_degil';
            }

            if (!self::is_elementor_data_present($page_id)) {
                $reasons[] = 'elementor_data_bos_veya_gecersiz';
            }

            if (!empty($reasons)) {
                $not_ready[] = sprintf('%s (ID %d): %s', sanitize_title((string) $slug), $page_id, implode(', ', $reasons));
                continue;
            }

            $ready_count++;
        }

        $report['messages'][] = sprintf(
            'Elementor hazirlik ozeti: %d/%d seeded sayfa duzenlenebilir durumda.',
            $ready_count,
            count($page_ids)
        );

        if (!empty($not_ready)) {
            foreach ($not_ready as $line) {
                $report['warnings'][] = 'Elementor hazir degil -> ' . $line;
            }
        }
    }

    private static function sync_group_cards_into_elementor_pages($page_ids, &$report)
    {
        if (!class_exists('Elementor\\Plugin')) {
            $report['warnings'][] = 'Elementor aktif degil, calisma grubu kart senkronu atlandi.';
            return;
        }

        $team_count = (int) wp_count_posts('team')->publish;
        if ($team_count <= 0) {
            $report['warnings'][] = 'Yayinlanmis calisma grubu bulunamadigi icin kart senkronu atlandi.';
            return;
        }

        $fallback = 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&q=80&w=800';

        if (!empty($page_ids['anasayfa'])) {
            // Anasayfada grup sayisini sabitleme; yayinlanan tum calisma gruplarini cek.
            $home_sections = self::build_card_sections_for_post_type('team', -1, $fallback, false, true, 'odt-el-home-groups-row');
            if (!empty($home_sections)) {
                $home_synced = self::sync_elementor_section_rows(
                    (int) $page_ids['anasayfa'],
                    array('odt-el-home-groups-row'),
                    'odt-el-home-groups-intro',
                    $home_sections
                );
                if ($home_synced) {
                    $report['messages'][] = 'Anasayfa calisma grubu kartlari Elementor icinde guncellendi.';
                }
            }
        }

        // Hakkimizda + alt sayfa sluglarinda grup kart satiri ayni dinamik kaynaktan gelsin.
        $about_targets = array();
        foreach (array('hakkimizda', 'neler-yapiyoruz', 'calisma-gruplarimiz', 'calisma-gruplari', 'tarihce', 'yonetim') as $about_slug) {
            if (!empty($page_ids[$about_slug])) {
                $about_targets[] = (int) $page_ids[$about_slug];
            }
        }

        // Seed disinda manuel olusturulmus alias child sayfalari da yakala.
        foreach (array('hakkimizda/calisma-gruplari', 'calisma-gruplari') as $about_alias_path) {
            $about_alias_page = get_page_by_path($about_alias_path, OBJECT, 'page');
            if ($about_alias_page instanceof WP_Post) {
                $about_targets[] = (int) $about_alias_page->ID;
            }
        }
        $about_targets = array_values(array_unique(array_filter($about_targets)));

        if (!empty($about_targets)) {
            $about_sections = self::build_card_sections_for_post_type('team', -1, $fallback, false, false, 'odt-el-about-groups-row');
            $about_filter_section = self::build_team_filter_section_for_elementor();
            if (is_array($about_filter_section) && !empty($about_filter_section)) {
                array_unshift($about_sections, $about_filter_section);
            }
            if (!empty($about_sections)) {
                $synced_count = 0;
                $copy_migrated_count = 0;
                foreach ($about_targets as $about_page_id) {
                    $about_synced = self::sync_elementor_section_rows(
                        (int) $about_page_id,
                        array('odt-el-about-groups-row', 'odt-el-about-groups-dynamic', 'odt-el-about-groups-intro', 'odt-el-about-groups-filter'),
                        'odt-el-about-panel-calisma-gruplarimiz',
                        $about_sections
                    );
                    if ($about_synced) {
                        $synced_count++;
                    }
                    if (self::migrate_about_groups_panel_legacy_copy((int) $about_page_id)) {
                        $copy_migrated_count++;
                    }
                }
                if ($synced_count > 0) {
                    $report['messages'][] = sprintf(
                        'Hakkimizda calisma grubu kartlari %d sayfada dinamik satir olarak guncellendi.',
                        $synced_count
                    );
                }
                if ($copy_migrated_count > 0) {
                    $report['messages'][] = sprintf(
                        'Hakkimizda calisma grubu baloncuk metni %d sayfada eski metinden guncel metne tasindi.',
                        $copy_migrated_count
                    );
                }
            }
        }
    }

    private static function sync_event_cards_into_elementor_pages($page_ids, &$report)
    {
        if (!class_exists('Elementor\\Plugin')) {
            $report['warnings'][] = 'Elementor aktif degil, etkinlik kart senkronu atlandi.';
            return;
        }

        $event_count = (int) wp_count_posts('event')->publish;
        if ($event_count <= 0) {
            $report['warnings'][] = 'Yayinlanmis etkinlik bulunamadigi icin kart senkronu atlandi.';
            return;
        }

        $fallback = 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=900';

        if (!empty($page_ids['anasayfa'])) {
            // Anasayfada daha kompakt bir liste: 6 etkinlik.
            $home_sections = self::build_card_sections_for_post_type('event', 6, $fallback, true, true, 'odt-el-home-events-row');
            if (!empty($home_sections)) {
                $home_synced = self::sync_elementor_section_rows(
                    (int) $page_ids['anasayfa'],
                    array('odt-el-home-events-row'),
                    'odt-el-home-events-intro',
                    $home_sections
                );
                if ($home_synced) {
                    $report['messages'][] = 'Anasayfa etkinlik kartlari Elementor icinde guncellendi.';
                }
            }
        }

        if (!empty($page_ids['etkinlikler'])) {
            // Etkinlikler sayfasi: daha genis liste.
            $events_sections = self::build_card_sections_for_post_type('event', 12, $fallback, true, false, 'odt-el-events-page-row');
            if (!empty($events_sections)) {
                $events_synced = self::sync_elementor_section_rows(
                    (int) $page_ids['etkinlikler'],
                    array('odt-el-events-page-row'),
                    'odt-el-events-hero',
                    $events_sections
                );
                if ($events_synced) {
                    $report['messages'][] = 'Etkinlikler sayfasi kartlari Elementor icinde guncellendi.';
                }
            }
        }
    }

    private static function sync_elementor_section_rows($page_id, $remove_classes, $insert_after_class, $new_sections)
    {
        $document = self::get_elementor_document($page_id);
        if (empty($document) || !is_array($document)) {
            return false;
        }
        if (empty($new_sections) || !is_array($new_sections)) {
            return false;
        }

        $new_doc = array();
        $inserted = false;
        $removed = false;

        foreach ($document as $node) {
            if (self::section_has_any_css_class($node, $remove_classes)) {
                $removed = true;
                continue;
            }

            $new_doc[] = $node;

            if (!$inserted && self::section_has_css_class($node, $insert_after_class)) {
                foreach ($new_sections as $section) {
                    $new_doc[] = $section;
                }
                $inserted = true;
            }
        }

        if (!$inserted) {
            foreach ($new_sections as $section) {
                $new_doc[] = $section;
            }
            $inserted = true;
        }

        if ($new_doc === $document) {
            return false;
        }

        return self::save_elementor_document($page_id, $new_doc);
    }

    private static function migrate_about_groups_panel_legacy_copy($page_id)
    {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            return false;
        }

        $document = self::get_elementor_document($page_id);
        if (empty($document) || !is_array($document)) {
            return false;
        }

        $changed = false;

        foreach ($document as &$node) {
            if (!self::section_has_css_class($node, 'odt-el-about-panel-calisma-gruplarimiz')) {
                continue;
            }

            if (self::replace_about_groups_panel_text_if_legacy($node)) {
                $changed = true;
            }
        }
        unset($node);

        if (!$changed) {
            return false;
        }

        return self::save_elementor_document($page_id, $document);
    }

    private static function replace_about_groups_panel_text_if_legacy(&$node)
    {
        if (!is_array($node)) {
            return false;
        }

        if (!empty($node['elType']) && $node['elType'] === 'widget' && !empty($node['widgetType']) && $node['widgetType'] === 'text-editor') {
            if (!self::widget_has_css_class($node, 'odt-el-richtext')) {
                return false;
            }

            $settings = !empty($node['settings']) && is_array($node['settings']) ? $node['settings'] : array();
            $current_editor = isset($settings['editor']) ? (string) $settings['editor'] : '';
            if (!self::is_legacy_about_groups_panel_copy($current_editor)) {
                return false;
            }

            $node['settings']['editor'] = '<p>Uzmanlık alanlarına veya ilgi alanlarına göre ayrılmış gruplarımızda birlikte üretiyoruz.</p>';
            return true;
        }

        if (empty($node['elements']) || !is_array($node['elements'])) {
            return false;
        }

        foreach ($node['elements'] as &$child) {
            if (self::replace_about_groups_panel_text_if_legacy($child)) {
                return true;
            }
        }
        unset($child);

        return false;
    }

    private static function is_legacy_about_groups_panel_copy($editor_html)
    {
        $current_text = self::normalize_panel_copy_for_match(wp_strip_all_tags((string) $editor_html));
        if ($current_text === '') {
            return false;
        }

        $target_text = self::normalize_panel_copy_for_match('Uzmanlık alanlarına veya ilgi alanlarına göre ayrılmış gruplarımızda birlikte üretiyoruz.');
        if ($current_text === $target_text) {
            return false;
        }

        $legacy_markers = array(
            'edebiyat, felsefe, fotoğraf, sosyal komite, burs, ik & üye geliştirme, spor & maraton dahil farklı alanlarda üretiyoruz.',
            'uzmanlık alanlarına göre ayrılan çalışma gruplarımızla birlikte üretiyor, etkinlik ve projeler geliştiriyoruz.',
            'uzmanlık alanlarına göre ayrılan çalışma gruplarımızla birlikte üretiyor',
            'dahil farklı alanlarda üretiyoruz.',
        );

        foreach ($legacy_markers as $legacy_marker) {
            $legacy_marker = self::normalize_panel_copy_for_match($legacy_marker);
            if ($legacy_marker !== '' && strpos($current_text, $legacy_marker) !== false) {
                return true;
            }
        }

        return false;
    }

    private static function normalize_panel_copy_for_match($text)
    {
        $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(array('’', '“', '”', '–', '—'), array("'", '"', '"', '-', '-'), $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            $text = mb_strtolower($text, 'UTF-8');
        } else {
            $text = strtolower($text);
        }

        return trim($text);
    }

    private static function get_elementor_document($page_id)
    {
        $raw = get_post_meta((int) $page_id, '_elementor_data', true);
        if (!is_string($raw) || trim($raw) === '') {
            return array();
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $decoded = json_decode(wp_unslash($raw), true);
        }

        return is_array($decoded) ? $decoded : array();
    }

    private static function save_elementor_document($page_id, $document)
    {
        $data_json = wp_json_encode($document);
        if (!is_string($data_json) || $data_json === '') {
            return false;
        }

        update_post_meta((int) $page_id, '_elementor_data', wp_slash($data_json));
        update_post_meta((int) $page_id, '_elementor_edit_mode', 'builder');
        update_post_meta((int) $page_id, '_elementor_template_type', 'wp-page');
        update_post_meta((int) $page_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '3.0.0');

        return true;
    }

    private static function migrate_html_widgets_to_native_widgets($page_ids, &$report)
    {
        if (!is_array($page_ids) || empty($page_ids)) {
            return;
        }

        $target_slugs = array('etkinlikler', 'uyelik', 'iletisim');
        $migrated_pages = 0;

        foreach ($target_slugs as $slug) {
            $page_id = !empty($page_ids[$slug]) ? (int) $page_ids[$slug] : 0;
            if ($page_id <= 0) {
                $page = get_page_by_path($slug, OBJECT, 'page');
                if ($page instanceof WP_Post) {
                    $page_id = (int) $page->ID;
                }
            }
            if ($page_id <= 0) {
                continue;
            }

            if (self::migrate_html_widgets_for_page($page_id)) {
                $migrated_pages++;
            }
        }

        if ($migrated_pages > 0) {
            $report['messages'][] = sprintf(
                '%d sayfada HTML widget kullanimlari native Elementor widget yapisina cevrildi.',
                $migrated_pages
            );
        }
    }

    private static function migrate_html_widgets_for_page($page_id)
    {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            return false;
        }

        $document = self::get_elementor_document($page_id);
        if (empty($document) || !is_array($document)) {
            return false;
        }

        $changed = false;
        $document = self::replace_html_widgets_in_nodes($document, $changed);
        if (!$changed) {
            return false;
        }

        return self::save_elementor_document($page_id, $document);
    }

    private static function replace_html_widgets_in_nodes($nodes, &$changed)
    {
        if (!is_array($nodes)) {
            return $nodes;
        }

        $updated_nodes = array();
        foreach ($nodes as $node) {
            if (!is_array($node)) {
                $updated_nodes[] = $node;
                continue;
            }

            if (!empty($node['elType']) && $node['elType'] === 'widget') {
                $replacement = self::get_native_widget_replacement_for_html_widget($node);
                if (is_array($replacement) && !empty($replacement)) {
                    foreach ($replacement as $replacement_widget) {
                        $updated_nodes[] = $replacement_widget;
                    }
                    $changed = true;
                    continue;
                }
            }

            if (!empty($node['elements']) && is_array($node['elements'])) {
                $node['elements'] = self::replace_html_widgets_in_nodes($node['elements'], $changed);
            }

            $updated_nodes[] = $node;
        }

        return $updated_nodes;
    }

    private static function get_native_widget_replacement_for_html_widget($node)
    {
        if (!is_array($node)) {
            return null;
        }
        if (empty($node['elType']) || $node['elType'] !== 'widget') {
            return null;
        }
        if (empty($node['widgetType']) || $node['widgetType'] !== 'html') {
            return null;
        }

        if (self::widget_has_css_class($node, 'odt-el-events-flip-html')) {
            return self::build_events_flip_editable_widgets();
        }

        if (self::widget_has_css_class($node, 'odt-el-membership-benefits-html')) {
            return self::build_membership_benefits_editable_widgets();
        }

        if (self::widget_has_css_class($node, 'odt-el-events-gallery-shortcode')) {
            return self::build_events_gallery_editable_widgets();
        }

        if (self::widget_has_css_class($node, 'odt-el-map')) {
            return array(
                self::build_widget('shortcode', array(
                    'shortcode' => '[odtumist_contact_map]',
                    '_css_classes' => 'odt-el-map-shortcode',
                )),
            );
        }

        return null;
    }

    private static function widget_has_css_class($widget, $class_name)
    {
        $class_name = sanitize_html_class((string) $class_name);
        if ($class_name === '' || !is_array($widget)) {
            return false;
        }

        $settings = !empty($widget['settings']) && is_array($widget['settings']) ? $widget['settings'] : array();
        $class_string = trim(
            (isset($settings['css_classes']) ? (string) $settings['css_classes'] : '') . ' ' .
            (isset($settings['_css_classes']) ? (string) $settings['_css_classes'] : '')
        );

        if ($class_string === '') {
            return false;
        }

        $tokens = preg_split('/\s+/', $class_string);
        if (!is_array($tokens)) {
            return false;
        }

        foreach ($tokens as $token) {
            if (sanitize_html_class((string) $token) === $class_name) {
                return true;
            }
        }

        return false;
    }

    private static function section_has_any_css_class($node, $classes)
    {
        foreach ((array) $classes as $class_name) {
            if (self::section_has_css_class($node, (string) $class_name)) {
                return true;
            }
        }

        return false;
    }

    private static function section_has_css_class($node, $class_name)
    {
        $class_name = sanitize_html_class((string) $class_name);
        if ($class_name === '' || !is_array($node) || empty($node['elType']) || $node['elType'] !== 'section') {
            return false;
        }

        $settings = !empty($node['settings']) && is_array($node['settings']) ? $node['settings'] : array();
        $class_string = trim(
            (isset($settings['css_classes']) ? (string) $settings['css_classes'] : '') . ' ' .
            (isset($settings['_css_classes']) ? (string) $settings['_css_classes'] : '')
        );

        if ($class_string === '') {
            return false;
        }

        $tokens = preg_split('/\s+/', $class_string);
        if (!is_array($tokens)) {
            return false;
        }

        foreach ($tokens as $token) {
            if (sanitize_html_class((string) $token) === $class_name) {
                return true;
            }
        }

        return false;
    }

    private static function is_elementor_data_present($post_id)
    {
        $data = get_post_meta($post_id, '_elementor_data', true);
        if (!is_string($data)) {
            return false;
        }

        $raw = trim($data);
        if ($raw === '' || $raw === '[]') {
            return false;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $decoded = json_decode(wp_unslash($raw), true);
        }

        return is_array($decoded) && !empty($decoded);
    }

    private static function page_elementor_blueprints($page_ids, $full_mode = true)
    {
        if ($full_mode) {
            return self::page_elementor_blueprints_full($page_ids);
        }

        return self::page_elementor_blueprints_legacy($page_ids);
    }

    private static function page_elementor_blueprints_legacy($page_ids)
    {
        return array(
            'anasayfa' => array(
                'title' => 'Anasayfa',
                'widgets' => array(
                    self::build_widget('shortcode', array('shortcode' => '[odtumist_frontpage]')),
                ),
            ),
            'hakkimizda' => array(
                'title' => 'Hakkımızda',
                'widgets' => array(
                    self::build_widget('shortcode', array('shortcode' => '[odtumist_about_layout]')),
                ),
            ),
            'etkinlikler' => array(
                'title' => 'Etkinlikler',
                'widgets' => array(
                    self::build_widget('shortcode', array('shortcode' => '[odtumist_events_layout]')),
                ),
            ),
            'uyelik' => array(
                'title' => 'Üyelik',
                'widgets' => array(
                    self::build_widget('shortcode', array('shortcode' => '[odtumist_membership_layout]')),
                ),
            ),
            'dayanisma' => array(
                'title' => 'Dayanışma',
                'widgets' => array(
                    self::build_widget('shortcode', array('shortcode' => '[odtumist_solidarity_layout]')),
                ),
            ),
            'iletisim' => array(
                'title' => 'İletişim',
                'widgets' => array(
                    self::build_widget('shortcode', array('shortcode' => '[odtumist_contact_layout]')),
                ),
            ),
            'haberler' => array(
                'title' => 'Haberler',
                'widgets' => array(
                    self::build_widget('heading', array('title' => 'Haberler', 'size' => 'xxl', 'align' => 'left')),
                    self::build_widget('text-editor', array('editor' => '<p>Bu sayfa WordPress yazilarini listeler. Haberleri Yazilar menusu altindan yonetebilirsin.</p>')),
                    self::build_widget('shortcode', array('shortcode' => '[odtumist_events_grid limit="6"]')),
                ),
            ),
        );
    }

    private static function page_elementor_blueprints_full($page_ids)
    {
        $links = self::get_primary_links_from_site($page_ids);
        $home_closing = array(
            'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=2000',
            'title' => 'DAYANIŞMA GÜCÜMÜZDÜR',
            'description' => "Nerede olursak olalım aynı değerler etrafında bir araya gelir, ODTÜ ruhunu İstanbul'da birlikte yaşatırız.",
        );

        if (function_exists('odtumist_get_home_closing_content')) {
            $closing = odtumist_get_home_closing_content();
            if (is_array($closing)) {
                $home_closing['image'] = !empty($closing['image']) ? (string) $closing['image'] : $home_closing['image'];
                $home_closing['title'] = !empty($closing['title']) ? (string) $closing['title'] : $home_closing['title'];
                $home_closing['description'] = !empty($closing['description']) ? (string) $closing['description'] : $home_closing['description'];
            }
        }

        $home_doc = array();
        $home_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_home_slider_widget(),
                    ),
                ),
            ),
            array(
                'layout' => 'full_width',
                'content_width' => 'full_width',
                'padding' => array('unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false),
                '_css_classes' => 'odt-el odt-el-home-hero',
            )
        );

        $home_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('heading', array('title' => 'Etkinliklerimiz', 'size' => 'xxl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => '<p>Takvimdeki etkinlikleri inceleyebilir, detay sayfalarindan kayit ve katilim bilgilerine ulasabilirsin.</p>', '_css_classes' => 'odt-el-subtitle')),
                        self::build_widget('button', array(
                            'text' => 'ETKİNLİK TAKVİMİ',
                            'link' => array('url' => $links['events']),
                            'align' => 'left',
                            'size' => 'md',
                            '_css_classes' => 'odt-el-btn odt-el-btn-primary',
                        )),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-home-events-intro')
        );

        $home_doc = array_merge($home_doc, self::build_card_sections_for_post_type(
            'event',
            6,
            'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=900',
            true,
            true,
            'odt-el-home-events-row'
        ));

        $home_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 50,
                    'settings' => array('_css_classes' => 'odt-el-cta-col odt-el-cta-col-blue'),
                    'widgets' => array(
                        self::build_widget('heading', array('title' => 'Üyelerimizle Varız', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-cta-title')),
                        self::build_widget('text-editor', array('editor' => '<p>İstanbul\'da ODTÜ ruhunu yeniden keşfet, dayanışma ağının parçası ol.</p>', '_css_classes' => 'odt-el-cta-text')),
                        self::build_widget('button', array(
                            'text' => 'ÜYE OL',
                            'link' => array('url' => $links['membership_ext']),
                            'align' => 'left',
                            '_css_classes' => 'odt-el-btn odt-el-btn-ghost',
                        )),
                    ),
                ),
                array(
                    'size' => 50,
                    'settings' => array('_css_classes' => 'odt-el-cta-col odt-el-cta-col-red'),
                    'widgets' => array(
                        self::build_widget('heading', array('title' => 'Gönüllülerimizle Varız', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-cta-title')),
                        self::build_widget('text-editor', array('editor' => '<p>Etkinliklerden burs ve mentorluğa kadar birçok alanda katkı verebilirsin.</p>', '_css_classes' => 'odt-el-cta-text')),
                        self::build_widget('button', array(
                            'text' => 'GÖNÜLLÜ OL',
                            'link' => array('url' => $links['contact']),
                            'align' => 'left',
                            '_css_classes' => 'odt-el-btn odt-el-btn-ghost',
                        )),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-home-ctas')
        );

        $home_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('heading', array('title' => 'Çalışma Gruplarımız', 'size' => 'xxl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => '<p>İlgi alanına göre gruplara katılabilir, birlikte üretime devam edebilirsin.</p>', '_css_classes' => 'odt-el-subtitle')),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-home-groups-intro')
        );

        $home_doc = array_merge($home_doc, self::build_card_sections_for_post_type(
            'team',
            -1,
            'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&q=80&w=800',
            false,
            true,
            'odt-el-home-groups-row'
        ));

        $home_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 45,
                    'widgets' => array(
                        self::build_widget('heading', array('title' => 'E-bültene Kaydol', 'size' => 'xxl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => '<p>İletişimde kalın: etkinlikler, burs, mentorluk ve dayanışma haberlerini kaçırmayın.</p>', '_css_classes' => 'odt-el-subtitle')),
                    ),
                ),
                array(
                    'size' => 55,
                    'widgets' => array(
                        self::build_widget('shortcode', array(
                            'shortcode' => '[odtumist_contact_form provider="wpforms"]',
                            '_css_classes' => 'odt-el-home-newsletter-form',
                        )),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-home-newsletter')
        );

        $home_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('heading', array('title' => (string) $home_closing['title'], 'size' => 'xxl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => '<p>' . esc_html((string) $home_closing['description']) . '</p>', '_css_classes' => 'odt-el-subtitle')),
                    ),
                ),
            ),
            array(
                '_css_classes' => 'odt-el odt-el-section odt-el-home-closing',
                'background_background' => 'classic',
                'background_image' => array('url' => esc_url_raw((string) $home_closing['image']), 'id' => ''),
                'background_position' => 'center center',
                'background_repeat' => 'no-repeat',
                'background_size' => 'cover',
                'background_overlay_background' => 'classic',
                'background_overlay_color' => 'rgba(7, 12, 32, 0.56)',
                'padding' => array('unit' => 'px', 'top' => '120', 'right' => '0', 'bottom' => '120', 'left' => '0', 'isLinked' => false),
            )
        );

        $about_id       = !empty($page_ids['hakkimizda']) ? (int) $page_ids['hakkimizda'] : 0;
        $about_banner_image_url = 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&q=80&w=2200';
        $about_sections = self::extract_sections_from_page($about_id);
        $news_url       = !empty($page_ids['haberler']) ? get_permalink((int) $page_ids['haberler']) : home_url('/haberler/');
        $management_org_url = !empty($page_ids['yonetim-organlari']) ? get_permalink((int) $page_ids['yonetim-organlari']) : home_url('/yonetim-organlari/');
        $former_presidents_url = !empty($page_ids['eski-baskanlar']) ? get_permalink((int) $page_ids['eski-baskanlar']) : home_url('/eski-baskanlar/');
        $bylaw_url = !empty($page_ids['tuzuk']) ? get_permalink((int) $page_ids['tuzuk']) : home_url('/tuzuk/');
        $regulations_url = !empty($page_ids['yonetmelikler']) ? get_permalink((int) $page_ids['yonetmelikler']) : home_url('/yonetmelikler/');
        $reports_url = !empty($page_ids['faaliyet-raporlari']) ? get_permalink((int) $page_ids['faaliyet-raporlari']) : home_url('/faaliyet-raporlari/');

        $about_sections_map = array();
        foreach ((array) $about_sections as $section_item) {
            if (!is_array($section_item)) {
                continue;
            }

            $section_key = sanitize_title(isset($section_item['id']) ? (string) $section_item['id'] : '');
            if ($section_key === '') {
                continue;
            }

            $about_sections_map[$section_key] = array(
                'id' => $section_key,
                'title' => isset($section_item['title']) ? (string) $section_item['title'] : '',
                'body' => isset($section_item['body']) ? (string) $section_item['body'] : '',
            );
        }

        $get_about_section = static function ($candidate_ids, $fallback_title, $fallback_body = '') use ($about_sections_map) {
            $candidate_ids = (array) $candidate_ids;
            foreach ($candidate_ids as $candidate_id) {
                $candidate_key = sanitize_title((string) $candidate_id);
                if ($candidate_key !== '' && isset($about_sections_map[$candidate_key])) {
                    return $about_sections_map[$candidate_key];
                }
            }

            $primary_id = sanitize_title((string) reset($candidate_ids));
            if ($primary_id === '') {
                $primary_id = 'icerik';
            }

            return array(
                'id' => $primary_id,
                'title' => (string) $fallback_title,
                'body' => (string) $fallback_body,
            );
        };

        $doing_section = $get_about_section(
            array('neler-yapiyoruz'),
            'Neler Yapıyoruz?',
            '<p>Üyelerimizi, gönüllülerimizi, bursiyerlerimizi ve tüm destekçilerimizi aynı dayanışma ağında buluşturarak, İstanbul\'daki ODTÜ topluluğunu bir arada tutuyoruz.</p>'
        );
        $groups_section = $get_about_section(
            array('calisma-gruplarimiz'),
            'Çalışma Gruplarımız',
            '<p>Uzmanlık alanlarına veya ilgi alanlarına göre ayrılmış gruplarımızda birlikte üretiyoruz.</p>'
        );
        $history_section = $get_about_section(
            array('tarihce'),
            'Tarihçe',
            '<p>ODTÜMİST 1986 yılında İstanbul\'daki ODTÜ\'lülerin emekleriyle şube olarak yolculuğuna başladı ve 2001 yılında bağımsız bir dernek oldu.</p>'
        );
        $management_section = $get_about_section(
            array('yonetim'),
            'Yönetim',
            '<p>Mevcut ve geçmiş yönetimler, çalışma gruplarımız, tüzük ve faaliyet raporlarımızı görüntüleyebilirsiniz.</p>'
        );

        $about_doc   = array();
        $about_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('image', array(
                            'image' => array(
                                'id' => '',
                                'url' => $about_banner_image_url,
                            ),
                            'image_size' => 'full',
                            'align' => 'center',
                            '_css_classes' => 'odt-el-banner-image odt-el-about-hero-image',
                        )),
                    ),
                ),
            ),
            array(
                '_css_classes' => 'odt-el odt-el-section odt-el-about-hero odt-el-banner-section',
                'content_width' => 'full_width',
                'padding' => array('unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false),
            )
        );

        $about_nav_links = array(
            array('label' => 'Neler Yapıyoruz?', 'anchor' => '#neler-yapiyoruz'),
            array('label' => 'Çalışma Gruplarımız', 'anchor' => '#calisma-gruplarimiz'),
            array('label' => 'Tarihçe', 'anchor' => '#tarihce'),
            array('label' => 'Yönetim', 'anchor' => '#yonetim'),
        );
        $about_nav_columns = array();
        $about_nav_size = max(20, (int) floor(100 / max(1, count($about_nav_links))));
        foreach ($about_nav_links as $nav_item) {
            $about_nav_columns[] = array(
                'size' => $about_nav_size,
                'widgets' => array(
                    self::build_widget('button', array(
                        'text' => $nav_item['label'],
                        'link' => array('url' => $nav_item['anchor']),
                        'size' => 'sm',
                        'align' => 'center',
                        '_css_classes' => 'odt-el-btn odt-el-btn-tab',
                    )),
                ),
            );
        }
        $about_doc[] = self::build_section_with_columns($about_nav_columns, array(
            '_css_classes' => 'odt-el odt-el-section odt-el-about-nav',
            'padding' => array('unit' => 'px', 'top' => '20', 'right' => '0', 'bottom' => '20', 'left' => '0', 'isLinked' => false),
        ));

        $about_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('menu-anchor', array('anchor' => 'neler-yapiyoruz')),
                        self::build_widget('heading', array('title' => 'Etkileşimi Güçlendiren Bir Köprü', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => (string) $doing_section['body'], '_css_classes' => 'odt-el-richtext')),
                        self::build_widget('text-editor', array('editor' => '<p>"Bütün bu faydayı, Çalışma Gruplarımızın gönüllü katkıları ile devam ettiriyoruz."</p>', '_css_classes' => 'odt-el-about-quote')),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-about-panel odt-el-about-panel-neler-yapiyoruz')
        );
        $about_doc[] = self::build_about_tab_pagination_section(
            null,
            array('anchor' => 'calisma-gruplarimiz', 'label' => 'Çalışma Gruplarımız'),
            'doing'
        );

        $about_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('menu-anchor', array('anchor' => 'calisma-gruplarimiz')),
                        self::build_widget('heading', array('title' => 'Çalışma Gruplarımız', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => (string) $groups_section['body'], '_css_classes' => 'odt-el-richtext')),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-about-panel odt-el-about-panel-calisma-gruplarimiz')
        );

        $about_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('shortcode', array(
                            'shortcode' => '[odtumist_working_groups_grid limit="all"]',
                            '_css_classes' => 'odt-el-about-groups-shortcode',
                        )),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-about-groups-row odt-el-about-groups-dynamic')
        );
        $about_doc[] = self::build_about_tab_pagination_section(
            array('anchor' => 'neler-yapiyoruz', 'label' => 'Neler Yapıyoruz?'),
            array('anchor' => 'tarihce', 'label' => 'Tarihçe'),
            'groups'
        );

        $about_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('menu-anchor', array('anchor' => 'tarihce')),
                        self::build_widget('heading', array('title' => 'Bir Meşalenin İstanbul Yolculuğu', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => (string) $history_section['body'], '_css_classes' => 'odt-el-richtext')),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-about-panel odt-el-about-panel-tarihce')
        );

        $about_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('heading', array('title' => 'Biliyor Muydunuz?', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => '<p>ODTÜMİST tarihinden öne çıkan bazı anılar:</p>', '_css_classes' => 'odt-el-subtitle')),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-about-history-intro')
        );

        $history_facts = array(
            array(
                'title' => 'Efsanevi "Et Arabası"',
                'desc' => '1970 öncesi kampüste kullanılan meşhur kırmızı otobüslerden sonuncusu hurdaya gitmek üzereyken bulunup İstanbul\'a getirildi.',
                'image' => 'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&q=80&w=800',
                'accent' => 'red',
            ),
            array(
                'title' => 'Bilim Ağacı\'nın Taşınma Hikayesi',
                'desc' => 'Bilim Ağacı heykeli, derneğin girişimleriyle 1991 yılında bugünkü görünür konumuna taşındı.',
                'image' => 'https://images.unsplash.com/photo-1549490349-8643362247b5?auto=format&fit=crop&q=80&w=800',
                'accent' => 'blue',
            ),
            array(
                'title' => 'Beyaz Masa\'nın Doğuşu',
                'desc' => '1994-95 yıllarında derneğin çevre platformu çalışmaları, Beyaz Masa modelinin kurulmasına katkı sundu.',
                'image' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&q=80&w=800',
                'accent' => 'dark',
            ),
            array(
                'title' => '"Bi\' Dünya ODTÜ\'lü"',
                'desc' => 'Pandemi döneminde 28 oturumluk küresel dijital buluşma ile dünyadaki ODTÜ\'lüler bir araya getirildi.',
                'image' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&q=80&w=800',
                'accent' => 'orange',
            ),
        );

        foreach (array_chunk($history_facts, 2) as $history_row) {
            $history_columns = array();
            foreach ($history_row as $fact) {
                $history_columns[] = array(
                    'size' => 50,
                    'widgets' => array(
                        self::build_widget('image-box', array(
                            'image' => array('url' => $fact['image'], 'id' => ''),
                            'title_text' => $fact['title'],
                            'description_text' => $fact['desc'],
                            'position' => 'top',
                            'align' => 'left',
                            '_css_classes' => 'odt-el-card odt-el-about-history-card odt-el-about-history-card-' . sanitize_html_class($fact['accent']),
                        )),
                    ),
                );
            }

            $about_doc[] = self::build_section_with_columns(
                $history_columns,
                array(
                    '_css_classes' => 'odt-el odt-el-section odt-el-about-history-row',
                    'padding' => array('unit' => 'px', 'top' => '14', 'right' => '0', 'bottom' => '14', 'left' => '0', 'isLinked' => false),
                )
            );
        }
        $about_doc[] = self::build_about_tab_pagination_section(
            array('anchor' => 'calisma-gruplarimiz', 'label' => 'Çalışma Gruplarımız'),
            array('anchor' => 'yonetim', 'label' => 'Yönetim'),
            'history'
        );

        $about_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('menu-anchor', array('anchor' => 'yonetim')),
                        self::build_widget('heading', array('title' => 'Yönetim', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => (string) $management_section['body'], '_css_classes' => 'odt-el-richtext')),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-about-panel odt-el-about-panel-yonetim')
        );

        $management_cards = array(
            array(
                'title' => 'Dernek Yönetim Organları',
                'desc' => 'Yönetim Kurulu, Denetleme Kurulu, Disiplin Kurulu ve Danışma Kurulu üyelerimizin biyografileri.',
                'icon' => '&#128737;',
                'url' => $management_org_url,
                'accent' => 'blue',
            ),
            array(
                'title' => 'Çalışma Gruplarımız',
                'desc' => 'Derneğimizi yaşatan çalışma gruplarımızın katkılarıyla büyümeye devam ediyoruz.',
                'icon' => '&#127919;',
                'url' => $links['about'] . '#calisma-gruplarimiz',
                'accent' => 'red',
            ),
            array(
                'title' => 'Dernek Tüzüğü ve Yönetmelikler',
                'desc' => 'Şeffaf yönetişim ilkelerimiz, tüzüğümüz ve çalışma yönetmeliklerimiz.',
                'icon' => '&#128196;',
                'url' => $bylaw_url,
                'accent' => 'blue',
            ),
            array(
                'title' => 'Faaliyet Raporları',
                'desc' => 'Yıllık çalışma raporlarımız, mali tablolarımız ve kurumsal başarı hikayelerimiz.',
                'icon' => '&#128188;',
                'url' => $reports_url,
                'accent' => 'red',
            ),
            array(
                'title' => 'Eski Başkanlar',
                'desc' => '1986\'dan bugüne derneğimize emek vermiş tüm kurullarımız ve yöneticilerimiz.',
                'icon' => '&#128336;',
                'url' => $former_presidents_url,
                'accent' => 'dark',
            ),
        );

        foreach ($management_cards as $card) {
            $about_doc[] = self::build_section_with_columns(
                array(
                    array(
                        'size' => 15,
                        'settings' => array('_css_classes' => 'odt-el-about-management-icon-col'),
                        'widgets' => array(
                            self::build_widget('heading', array('title' => $card['icon'], 'size' => 'xl', 'align' => 'center', '_css_classes' => 'odt-el-about-management-icon')),
                        ),
                    ),
                    array(
                        'size' => 60,
                        'settings' => array('_css_classes' => 'odt-el-about-management-content-col'),
                        'widgets' => array(
                            self::build_widget('heading', array('title' => $card['title'], 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-about-management-title')),
                            self::build_widget('text-editor', array('editor' => '<p>' . esc_html($card['desc']) . '</p>', '_css_classes' => 'odt-el-about-management-desc')),
                        ),
                    ),
                    array(
                        'size' => 25,
                        'settings' => array('_css_classes' => 'odt-el-about-management-action-col'),
                        'widgets' => array(
                            self::build_widget('button', array('text' => 'DETAYI AÇ', 'link' => array('url' => $card['url']), 'align' => 'right', 'size' => 'sm', '_css_classes' => 'odt-el-btn odt-el-btn-secondary odt-el-about-management-btn')),
                        ),
                    ),
                ),
                array(
                    '_css_classes' => 'odt-el odt-el-section odt-el-about-management-row odt-el-about-management-row-' . sanitize_html_class($card['accent']),
                    'padding' => array('unit' => 'px', 'top' => '10', 'right' => '0', 'bottom' => '10', 'left' => '0', 'isLinked' => false),
                )
            );
        }
        $about_doc[] = self::build_about_tab_pagination_section(
            array('anchor' => 'tarihce', 'label' => 'Tarihçe'),
            null,
            'management'
        );

        $events_id      = !empty($page_ids['etkinlikler']) ? (int) $page_ids['etkinlikler'] : 0;
        $events_excerpt = self::get_page_excerpt_or_default($events_id, 'Takvimdeki etkinlikleri inceleyebilir, detay sayfalarından kayıt ve katılım bilgilerine ulaşabilirsin.');
        $events_doc     = array(
            self::build_section_with_columns(
                array(
                    array(
                        'size' => 100,
                        'widgets' => array(
                            self::build_widget('heading', array('title' => 'Etkinlikler', 'size' => 'xxl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                            self::build_widget('text-editor', array('editor' => '<p>' . esc_html($events_excerpt) . '</p>', '_css_classes' => 'odt-el-subtitle')),
                        ),
                    ),
                ),
                array('_css_classes' => 'odt-el odt-el-section odt-el-page-hero odt-el-events-hero')
            ),
        );
        $events_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array_merge(
                        array(
                            self::build_widget('heading', array('title' => 'Blok #1: Etkinlik Kategorileri', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                            self::build_widget('text-editor', array('editor' => '<p>Dört ana etkinlik kategorimizi düzenlenebilir kart metinleriyle inceleyin.</p>', '_css_classes' => 'odt-el-subtitle')),
                        ),
                        self::build_events_flip_editable_widgets()
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-events-flip-section')
        );
        $events_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('heading', array('title' => 'Blok #2: Yaklaşan Etkinlikler', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => '<p>Etkinliklerin gösterimi anasayfadaki kart yapısıyla senkron çalışır.</p>', '_css_classes' => 'odt-el-subtitle')),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-events-upcoming-intro')
        );
        $events_doc = array_merge($events_doc, self::build_card_sections_for_post_type(
            'event',
            12,
            'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=900',
            true,
            false,
            'odt-el-events-page-row'
        ));
        $events_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array_merge(
                        array(
                            self::build_widget('heading', array('title' => 'Blok #3: Etkinliklerden Kareler', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                            self::build_widget('text-editor', array('editor' => '<p>Foto galeride görsellere açıklama ve etkinlik adı eklenebilir.</p>', '_css_classes' => 'odt-el-subtitle')),
                        ),
                        self::build_events_gallery_editable_widgets()
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-events-gallery-section')
        );
        $events_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('heading', array('title' => 'Social Media Feed', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => '<p>Instagram akışımızdan son paylaşımlarımızı takip edebilirsin.</p>', '_css_classes' => 'odt-el-subtitle')),
                        self::build_widget('shortcode', array('shortcode' => '[odtumist_social_feed]', '_css_classes' => 'odt-el-events-social-feed')),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-events-social-feed-section')
        );

        $membership_id      = !empty($page_ids['uyelik']) ? (int) $page_ids['uyelik'] : 0;
        $membership_banner_image_url = 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&q=80&w=2200';
        $membership_doc     = array(
            self::build_section_with_columns(
                array(
                    array(
                        'size' => 100,
                        'widgets' => array(
                            self::build_widget('image', array(
                                'image' => array(
                                    'id' => '',
                                    'url' => $membership_banner_image_url,
                                ),
                                'image_size' => 'full',
                                'align' => 'center',
                                '_css_classes' => 'odt-el-banner-image odt-el-membership-hero-image',
                            )),
                        ),
                    ),
                ),
                array(
                    '_css_classes' => 'odt-el odt-el-section odt-el-page-hero odt-el-membership-hero odt-el-banner-section',
                    'content_width' => 'full_width',
                    'padding' => array('unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false),
                )
            ),
        );
        $membership_sections = self::extract_sections_from_page($membership_id);
        $membership_panel_ctas = array(
            'neden-uye-olmaliyim' => array(
                'text'  => 'ŞİMDİ ÜYE OL',
                'url'   => $links['membership_ext'],
                'class' => 'odt-el-btn odt-el-btn-primary',
            ),
            'uyelik-avantajlari' => array(
                'text'  => 'ÜYE OL',
                'url'   => $links['membership_ext'],
                'class' => 'odt-el-btn odt-el-btn-secondary',
            ),
            'bilgi-guncelleme' => array(
                'text'  => 'İLETİŞİME GEÇ',
                'url'   => $links['contact'],
                'class' => 'odt-el-btn odt-el-btn-secondary',
            ),
            'aidat-odeme' => array(
                'text'  => 'AİDAT ÖDE',
                'url'   => 'https://fonzip.com/odtumist/odeme',
                'class' => 'odt-el-btn odt-el-btn-primary',
            ),
            'nasil-uye-olabilirsiniz' => array(
                'text'  => 'BAŞVURU ADIMLARI',
                'url'   => $links['membership_ext'],
                'class' => 'odt-el-btn odt-el-btn-secondary',
            ),
            'yeni-mezunlar-icin-uyelik' => array(
                'text'  => 'YENİ MEZUN BAŞVURUSU',
                'url'   => $links['membership_ext'],
                'class' => 'odt-el-btn odt-el-btn-secondary',
            ),
            'uyelik-sss' => array(
                'text'  => 'İLETİŞİME GEÇ',
                'url'   => $links['contact'],
                'class' => 'odt-el-btn odt-el-btn-secondary',
            ),
        );
        $membership_panel_emojis = array(
            'neden-uye-olmaliyim' => '🤝',
            'uyelik-avantajlari'  => '🎁',
            'bilgi-guncelleme'    => '📝',
            'aidat-odeme'         => '💳',
            'nasil-uye-olabilirsiniz' => '🧭',
            'yeni-mezunlar-icin-uyelik' => '🎓',
            'uyelik-sss' => '❓',
        );
        foreach ($membership_sections as $membership_index => $section) {
            $section_css_id = sanitize_html_class((string) $section['id']);
            if ($section_css_id === '') {
                $section_css_id = 'icerik';
            }

            $section_id = isset($section['id']) ? sanitize_title((string) $section['id']) : '';
            $panel_widgets = array(
                self::build_widget('menu-anchor', array('anchor' => $section['id'])),
                self::build_widget('heading', array('title' => $section['title'], 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                self::build_widget('text-editor', array('editor' => $section['body'], '_css_classes' => 'odt-el-richtext')),
            );
            if ($section_id === 'bilgi-guncelleme') {
                $panel_widgets[] = self::build_widget('shortcode', array(
                    'shortcode' => '[odtumist_contact_form provider="wpforms"]',
                    '_css_classes' => 'odt-el-membership-form',
                ));
            }
            if ($section_id === 'uyelik-avantajlari') {
                $panel_widgets = array_merge($panel_widgets, self::build_membership_benefits_editable_widgets());
            }

            if ($section_id !== '' && !empty($membership_panel_ctas[$section_id])) {
                $cta = $membership_panel_ctas[$section_id];
                $panel_widgets[] = self::build_widget('button', array(
                    'text' => (string) $cta['text'],
                    'link' => array('url' => (string) $cta['url']),
                    'align' => 'left',
                    '_css_classes' => (string) $cta['class'],
                ));
            }

            $has_aside = ($section_id !== '' && !empty($membership_panel_emojis[$section_id]));
            $panel_columns = array(
                array(
                    'size' => $has_aside ? 72 : 100,
                    'widgets' => $panel_widgets,
                ),
            );

            if ($has_aside) {
                $panel_columns[] = array(
                    'size' => 28,
                    'settings' => array('_css_classes' => 'odt-el-membership-panel-aside'),
                    'widgets' => array(
                        self::build_widget('heading', array(
                            'title' => (string) $membership_panel_emojis[$section_id],
                            'size' => 'xxl',
                            'align' => 'center',
                            '_css_classes' => 'odt-el-membership-emoji',
                        )),
                    ),
                );
            }

            $membership_doc[] = self::build_section_with_columns(
                $panel_columns,
                array('_css_classes' => 'odt-el odt-el-section odt-el-membership-panel odt-el-membership-panel-' . ((int) $membership_index + 1) . ' odt-el-membership-' . $section_css_id . ($has_aside ? ' odt-el-membership-panel-has-aside' : ''))
            );
        }
        $membership_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 50,
                    'widgets' => array(
                        self::build_widget('button', array(
                            'text' => 'ÜYE OL',
                            'link' => array('url' => $links['membership_ext']),
                            'align' => 'left',
                            '_css_classes' => 'odt-el-btn odt-el-btn-primary',
                        )),
                    ),
                ),
                array(
                    'size' => 50,
                    'widgets' => array(
                        self::build_widget('button', array(
                            'text' => 'AİDAT ÖDEME',
                            'link' => array('url' => 'https://fonzip.com/odtumist/odeme'),
                            'align' => 'right',
                            '_css_classes' => 'odt-el-btn odt-el-btn-secondary',
                        )),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-membership-actions')
        );

        $solidarity_id      = !empty($page_ids['dayanisma']) ? (int) $page_ids['dayanisma'] : 0;
        $solidarity_banner_image_url = 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&q=80&w=2200';
        $solidarity_doc     = array(
            self::build_section_with_columns(
                array(
                    array(
                        'size' => 100,
                        'widgets' => array(
                            self::build_widget('image', array(
                                'image' => array(
                                    'id' => '',
                                    'url' => $solidarity_banner_image_url,
                                ),
                                'image_size' => 'full',
                                'align' => 'center',
                                '_css_classes' => 'odt-el-banner-image odt-el-solidarity-hero-image',
                            )),
                        ),
                    ),
                ),
                array(
                    '_css_classes' => 'odt-el odt-el-section odt-el-page-hero odt-el-solidarity-hero odt-el-banner-section',
                    'content_width' => 'full_width',
                    'padding' => array('unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false),
                )
            ),
        );
        $solidarity_sections = self::extract_sections_from_page($solidarity_id);
        $solidarity_icons = array(
            'sen-de-katil' => '💬',
            'burs' => '🎓',
            'maraton' => '🏃',
            'mentorluk' => '☕',
            'gonulluluk' => '❤️',
            'genclik-iletisim' => '📣',
            'bagiscilar-paydaslar' => '🤝',
            'bursiyerler' => '👥',
            'networking' => '🌐',
        );
        $solidarity_ctas = array(
            'sen-de-katil' => array('text' => 'DAYANIŞMAYA KATIL', 'url' => $links['contact'], 'class' => 'odt-el-btn odt-el-btn-primary'),
            'burs' => array('text' => 'KEŞFET', 'url' => $links['donation_ext'], 'class' => 'odt-el-btn odt-el-btn-primary'),
            'maraton' => array('text' => 'DESTEKLE', 'url' => $links['donation_ext'], 'class' => 'odt-el-btn odt-el-btn-primary'),
            'mentorluk' => array('text' => 'KATIL', 'url' => $links['contact'], 'class' => 'odt-el-btn odt-el-btn-secondary'),
            'gonulluluk' => array('text' => 'HAREKETE GEÇ', 'url' => $links['contact'], 'class' => 'odt-el-btn odt-el-btn-primary'),
            'genclik-iletisim' => array('text' => 'KATIL', 'url' => $links['contact'], 'class' => 'odt-el-btn odt-el-btn-secondary'),
            'bagiscilar-paydaslar' => array('text' => 'İNCELE', 'url' => $links['donation_ext'], 'class' => 'odt-el-btn odt-el-btn-secondary'),
            'bursiyerler' => array('text' => 'MEZUN-ÖĞRENCİ DAYANIŞMASI', 'url' => $links['contact'], 'class' => 'odt-el-btn odt-el-btn-secondary'),
            'networking' => array('text' => 'AĞA KATIL', 'url' => $links['contact'], 'class' => 'odt-el-btn odt-el-btn-secondary'),
        );
        foreach ($solidarity_sections as $solidarity_index => $section) {
            $section_css_id = sanitize_html_class((string) $section['id']);
            if ($section_css_id === '') {
                $section_css_id = 'icerik';
            }

            $section_id = isset($section['id']) ? sanitize_title((string) $section['id']) : '';
            $cta = ($section_id !== '' && !empty($solidarity_ctas[$section_id])) ? $solidarity_ctas[$section_id] : array(
                'text' => 'KEŞFET',
                'url' => $links['contact'],
                'class' => 'odt-el-btn odt-el-btn-secondary',
            );

            $content_widgets = array(
                self::build_widget('menu-anchor', array('anchor' => $section['id'])),
                self::build_widget('heading', array('title' => $section['title'], 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                self::build_widget('text-editor', array('editor' => $section['body'], '_css_classes' => 'odt-el-richtext')),
                self::build_widget('button', array(
                    'text' => (string) $cta['text'],
                    'link' => array('url' => (string) $cta['url']),
                    'align' => 'left',
                    '_css_classes' => (string) $cta['class'],
                )),
            );

            $solidarity_doc[] = self::build_section_with_columns(
                array(
                    array(
                        'size' => 22,
                        'settings' => array('_css_classes' => 'odt-el-solidarity-icon-col'),
                        'widgets' => array(
                            self::build_widget('heading', array(
                                'title' => !empty($solidarity_icons[$section_id]) ? (string) $solidarity_icons[$section_id] : '✨',
                                'size' => 'xxl',
                                'align' => 'center',
                                '_css_classes' => 'odt-el-solidarity-emoji',
                            )),
                        ),
                    ),
                    array(
                        'size' => 78,
                        'widgets' => $content_widgets,
                    ),
                ),
                array('_css_classes' => 'odt-el odt-el-section odt-el-solidarity-panel odt-el-solidarity-panel-' . ((int) $solidarity_index + 1) . ' odt-el-solidarity-' . $section_css_id . ' odt-el-solidarity-panel-layout')
            );
        }
        $solidarity_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('heading', array('title' => 'ODTÜ Ruhunu Şimdi Yaşatın', 'size' => 'xxl', 'align' => 'center', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => '<p style="text-align:center">Siz hangi alanda dayanışmaya katılmak istersiniz Hocam?</p>', '_css_classes' => 'odt-el-subtitle')),
                        self::build_widget('button', array(
                            'text' => 'İLETİŞİME GEÇİN',
                            'link' => array('url' => $links['contact']),
                            'align' => 'center',
                            '_css_classes' => 'odt-el-btn odt-el-btn-secondary',
                        )),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-solidarity-final-cta')
        );

        $contact_id      = !empty($page_ids['iletisim']) ? (int) $page_ids['iletisim'] : 0;
        $contact_excerpt = self::get_page_excerpt_or_default($contact_id, 'Cumhuriyet Cad. Cumhuriyet Apt. No: 17 Kat: 2 D: 5 Taksim, Beyoğlu, İstanbul adresindeki dernek merkezimizde sizleri bekliyoruz.');
        $contact         = self::get_contact_fields();
        $contact_phones_html = self::build_contact_phones_html($contact['phone']);
        $contact_hero_image = $contact_id > 0 ? get_the_post_thumbnail_url($contact_id, 'full') : '';
        if (!$contact_hero_image) {
            $contact_hero_image = 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=2000';
        }
        $contact_doc     = array(
            self::build_section_with_columns(
                array(
                    array(
                        'size' => 100,
                        'widgets' => array(
                            self::build_widget('heading', array('title' => 'İletişim', 'size' => 'xxl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                            self::build_widget('text-editor', array('editor' => '<p>' . esc_html($contact_excerpt) . '</p>', '_css_classes' => 'odt-el-subtitle')),
                        ),
                    ),
                ),
                array(
                    '_css_classes' => 'odt-el odt-el-section odt-el-page-hero odt-el-contact-hero',
                    'background_background' => 'classic',
                    'background_image' => array('url' => esc_url_raw($contact_hero_image), 'id' => ''),
                    'background_position' => 'center center',
                    'background_repeat' => 'no-repeat',
                    'background_size' => 'cover',
                    'background_overlay_background' => 'classic',
                    'background_overlay_color' => 'rgba(7, 12, 32, 0.52)',
                    'padding' => array('unit' => 'px', 'top' => '140', 'right' => '0', 'bottom' => '140', 'left' => '0', 'isLinked' => false),
                )
            ),
            self::build_section_with_columns(
                array(
                    array(
                        'size' => 50,
                        'widgets' => array(
                            self::build_widget('heading', array('title' => 'İletişim Bilgileri', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                            self::build_widget('text-editor', array(
                                'editor' => '<p><strong>Adres:</strong><br>' . nl2br(esc_html($contact['address'])) . '</p>'
                                    . '<p><strong>Telefon:</strong><br>' . $contact_phones_html . '</p>'
                                    . '<p><strong>E-posta:</strong> <a href="mailto:' . esc_attr($contact['email']) . '">' . esc_html($contact['email']) . '</a></p>',
                                '_css_classes' => 'odt-el-richtext',
                            )),
                            self::build_widget('shortcode', array('shortcode' => '[odtumist_contact_departments]', '_css_classes' => 'odt-el-contact-depts')),
                        ),
                    ),
                    array(
                        'size' => 50,
                        'widgets' => array(
                            self::build_widget('shortcode', array('shortcode' => '[odtumist_contact_map]', '_css_classes' => 'odt-el-map-shortcode')),
                        ),
                    ),
                ),
                array('_css_classes' => 'odt-el odt-el-section odt-el-contact-main')
            ),
            self::build_section_with_columns(
                array(
                    array(
                        'size' => 100,
                        'widgets' => array(
                            self::build_widget('heading', array('title' => 'Mesaj Formu', 'size' => 'xl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                            self::build_widget('shortcode', array('shortcode' => '[odtumist_contact_form provider="auto"]', '_css_classes' => 'odt-el-contact-form')),
                        ),
                    ),
                ),
                array('_css_classes' => 'odt-el odt-el-section odt-el-contact-form-section')
            ),
        );

        $news_doc = array(
            self::build_section_with_columns(
                array(
                    array(
                        'size' => 100,
                        'widgets' => array(
                            self::build_widget('heading', array('title' => 'Haberler', 'size' => 'xxl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                            self::build_widget('text-editor', array('editor' => '<p>Bu sayfada WordPress yazilarini yonetebilirsin. Istedigin widgeti ekleyip kaldirabilirsin.</p>', '_css_classes' => 'odt-el-subtitle')),
                        ),
                    ),
                ),
                array('_css_classes' => 'odt-el odt-el-section odt-el-page-hero')
            ),
        );

        $blueprints = array(
            'anasayfa' => array('title' => 'Anasayfa', 'document' => $home_doc),
            'hakkimizda' => array('title' => 'Hakkımızda', 'document' => $about_doc),
            'etkinlikler' => array('title' => 'Etkinlikler', 'document' => $events_doc),
            'uyelik' => array('title' => 'Üyelik', 'document' => $membership_doc),
            'dayanisma' => array('title' => 'Dayanışma', 'document' => $solidarity_doc),
            'iletisim' => array('title' => 'İletişim', 'document' => $contact_doc),
            'haberler' => array('title' => 'Haberler', 'document' => $news_doc),
        );

        // Child sayfaları generic fallback ile bırakmak yerine ilgili parent blueprint'ini
        // devraltıyoruz. Böylece /hakkimizda/... ve /dayanisma/... URL'leri, parent
        // sayfadaki referans deneyimle tutarlı kalır.
        $blueprints = self::inherit_parent_blueprints_for_child_pages($blueprints, $page_ids);

        return $blueprints;
    }

    private static function inherit_parent_blueprints_for_child_pages($blueprints, $page_ids)
    {
        if (!is_array($blueprints) || empty($blueprints)) {
            return is_array($blueprints) ? $blueprints : array();
        }

        $child_map = array(
            'hakkimizda' => array(
                'neler-yapiyoruz',
                'calisma-gruplarimiz',
                'calisma-gruplari',
                'tarihce',
                'yonetim',
            ),
            'uyelik' => array(
                'neden-uye-olmaliyim',
                'bilgi-guncelleme',
                'aidat-odeme',
                'uyelik-avantajlari',
                'nasil-uye-olabilirsiniz',
                'yeni-mezunlar-icin-uyelik',
                'uyelik-sss',
            ),
            'dayanisma' => array(
                'sen-de-katil',
                'networking',
                'burs',
                'maraton',
                'mentorluk',
                'bursiyerler',
                'gonulluluk',
                'genclik-iletisim',
                'bagiscilar-paydaslar',
                // Legacy alias slug'ları da aynı parent deneyimini alsın.
                'gonulluler',
                'bagiscilar',
                'paydaslar',
            ),
        );

        foreach ($child_map as $parent_slug => $child_slugs) {
            if (empty($blueprints[$parent_slug]['document']) || !is_array($blueprints[$parent_slug]['document'])) {
                continue;
            }

            $parent_document = $blueprints[$parent_slug]['document'];

            foreach ((array) $child_slugs as $child_slug) {
                $child_slug = sanitize_title((string) $child_slug);
                if ($child_slug === '') {
                    continue;
                }

                if (!empty($blueprints[$child_slug])) {
                    // Child için explicit blueprint varsa onu koru.
                    continue;
                }

                $child_title = ucfirst(str_replace('-', ' ', $child_slug));
                if (!empty($page_ids[$child_slug])) {
                    $resolved_title = get_the_title((int) $page_ids[$child_slug]);
                    if (is_string($resolved_title) && trim($resolved_title) !== '') {
                        $child_title = $resolved_title;
                    }
                }

                $blueprints[$child_slug] = array(
                    'title' => (string) $child_title,
                    'document' => $parent_document,
                );
            }
        }

        return $blueprints;
    }

    private static function get_primary_links_from_site($page_ids)
    {
        $links = array(
            'home' => !empty($page_ids['anasayfa']) ? get_permalink((int) $page_ids['anasayfa']) : home_url('/'),
            'about' => !empty($page_ids['hakkimizda']) ? get_permalink((int) $page_ids['hakkimizda']) : home_url('/hakkimizda/'),
            'events' => !empty($page_ids['etkinlikler']) ? get_permalink((int) $page_ids['etkinlikler']) : home_url('/etkinlikler/'),
            'membership' => !empty($page_ids['uyelik']) ? get_permalink((int) $page_ids['uyelik']) : home_url('/uyelik/'),
            'solidarity' => !empty($page_ids['dayanisma']) ? get_permalink((int) $page_ids['dayanisma']) : home_url('/dayanisma/'),
            'contact' => !empty($page_ids['iletisim']) ? get_permalink((int) $page_ids['iletisim']) : home_url('/iletisim/'),
            'membership_ext' => 'https://fonzip.com/odtumist/uyelik',
            'donation_ext' => 'https://fonzip.com/odtumist/bagis',
        );

        if (function_exists('odtumist_get_primary_cta_links')) {
            $ctas = odtumist_get_primary_cta_links();
            if (is_array($ctas)) {
                if (!empty($ctas['membership'])) {
                    $links['membership_ext'] = $ctas['membership'];
                }
                if (!empty($ctas['donation'])) {
                    $links['donation_ext'] = $ctas['donation'];
                }
            }
        }

        return $links;
    }

    private static function get_page_excerpt_or_default($page_id, $fallback)
    {
        if ($page_id > 0) {
            $excerpt = (string) get_post_field('post_excerpt', $page_id);
            if (trim($excerpt) !== '') {
                return $excerpt;
            }
        }

        return (string) $fallback;
    }

    private static function extract_sections_from_page($page_id)
    {
        if ($page_id <= 0) {
            return array();
        }

        $content = (string) get_post_field('post_content', $page_id);
        if (trim($content) === '') {
            return array();
        }

        if (function_exists('odtumist_extract_content_sections')) {
            $sections = odtumist_extract_content_sections($content);
            if (is_array($sections) && !empty($sections)) {
                return array_values($sections);
            }
        }

        $fallback = array();
        $fallback[] = array(
            'id' => 'icerik',
            'title' => 'Icerik',
            'body' => wpautop(wp_kses_post($content)),
        );

        return $fallback;
    }

    private static function get_contact_fields()
    {
        if (function_exists('odtumist_get_contact_content')) {
            $contact = odtumist_get_contact_content();
            if (is_array($contact)) {
                return array(
                    'address' => isset($contact['address']) ? (string) $contact['address'] : '',
                    'phone'   => isset($contact['phone']) ? (string) $contact['phone'] : '',
                    'email'   => isset($contact['email']) ? (string) $contact['email'] : '',
                    'map_url' => isset($contact['map_url']) ? (string) $contact['map_url'] : '',
                );
            }
        }

        return array(
            'address' => "Cumhuriyet Cad. Cumhuriyet Apt. No: 17\nKat: 2 D: 5 Taksim, Beyoğlu, İstanbul",
            'phone' => "0546 522 96 11\n0533 206 23 01\n0546 522 96 41",
            'email' => 'dernek@odtumist.org',
            'map_url' => 'https://www.google.com/maps?q=Cumhuriyet+Cad.+Cumhuriyet+Apt.+No:+17,+Beyoglu,+Istanbul&output=embed',
        );
    }

    private static function build_contact_phones_html($raw)
    {
        $raw = is_string($raw) ? $raw : '';
        $parts = preg_split('/[\r\n,;]+/', $raw);
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
        if (empty($phones) && trim($raw) !== '') {
            $phones[] = trim($raw);
        }

        if (empty($phones)) {
            return '';
        }

        $chunks = array();
        foreach ($phones as $phone) {
            $tel = preg_replace('/[^0-9+]/', '', $phone);
            if ($tel === '') {
                $chunks[] = esc_html($phone);
            } else {
                $chunks[] = '<a href="tel:' . esc_attr($tel) . '">' . esc_html($phone) . '</a>';
            }
        }

        return implode('<br>', $chunks);
    }

    private static function build_home_slider_widget()
    {
        $slides = array();
        if (function_exists('odtumist_get_home_hero_slides')) {
            $slides = odtumist_get_home_hero_slides();
        }
        $slides = self::normalize_home_slider_slides($slides);

        if (defined('ELEMENTOR_PRO_VERSION')) {
            $slides_settings = array();
            foreach ($slides as $slide) {
                $slides_settings[] = array(
                    'background_image' => array('url' => (string) $slide['image'], 'id' => ''),
                    'heading' => isset($slide['title']) ? (string) $slide['title'] : '',
                    'description' => isset($slide['desc']) ? (string) $slide['desc'] : '',
                    'button_text' => isset($slide['primary']['label']) ? (string) $slide['primary']['label'] : '',
                    'button_link' => array('url' => isset($slide['primary']['url']) ? (string) $slide['primary']['url'] : ''),
                    'button_text2' => (!empty($slide['secondary']) && !empty($slide['secondary']['label'])) ? (string) $slide['secondary']['label'] : '',
                    'button_link2' => array('url' => (!empty($slide['secondary']) && !empty($slide['secondary']['url'])) ? (string) $slide['secondary']['url'] : ''),
                    'background_overlay_color' => 'rgba(7, 12, 32, 0.52)',
                );
            }

            return self::build_widget('slides', array(
                'slides' => $slides_settings,
                'show_arrows' => 'yes',
                'show_dots' => 'yes',
                'autoplay' => 'yes',
                'autoplay_speed' => 5500,
                'infinite' => 'yes',
                'transition' => 'slide',
                'height' => array('unit' => 'vh', 'size' => 64, 'sizes' => array()),
                '_css_classes' => 'odt-el-home-slider',
            ));
        }

        $carousel = array();
        foreach ($slides as $slide) {
            $carousel[] = array(
                'id' => '',
                'url' => (string) $slide['image'],
            );
        }

        return self::build_widget('image-carousel', array(
            'carousel' => $carousel,
            'slides_to_show' => '1',
            'slides_to_scroll' => '1',
            'navigation' => 'both',
            'autoplay' => 'yes',
            'infinite' => 'yes',
            '_css_classes' => 'odt-el-home-slider',
        ));
    }

    private static function get_default_home_slider_slides($page_ids = array())
    {
        $about_url      = !empty($page_ids['hakkimizda']) ? get_permalink((int) $page_ids['hakkimizda']) : home_url('/hakkimizda/');
        $solidarity_url = !empty($page_ids['dayanisma']) ? get_permalink((int) $page_ids['dayanisma']) : home_url('/dayanisma/');
        $contact_url    = !empty($page_ids['iletisim']) ? get_permalink((int) $page_ids['iletisim']) : home_url('/iletisim/');
        $ctas           = array('donation' => 'https://fonzip.com/odtumist/bagis');

        if (function_exists('odtumist_get_primary_cta_links')) {
            $site_ctas = odtumist_get_primary_cta_links();
            if (is_array($site_ctas) && !empty($site_ctas['donation'])) {
                $ctas['donation'] = (string) $site_ctas['donation'];
            }
        }

        return array(
            array(
                'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=2000',
                'title' => "İSTANBUL'DAKİ ODTÜ'LÜLERİN BULUŞMA NOKTASI",
                'desc' => "İstanbul'da yaşayan ODTÜ mezunlarıyla güçlü bir dayanışma ağı kuruyoruz.",
                'primary' => array('label' => 'TANIŞALIM HOCAM!', 'url' => $about_url),
                'secondary' => null,
            ),
            array(
                'image' => 'https://odtumist.org/wp-content/uploads/2021/01/ODTMST-Spr-Maraton-KV1-Banner-02.jpg',
                'title' => 'BURS VER, YARINLARA NEFES OL',
                'desc' => "Burs gönüllüleri arasına katılın, burs verin, maratonda koşun ve ODTÜ öğrencileri için burs toplayın.",
                'primary' => array('label' => 'BAĞIŞ YAP', 'url' => $ctas['donation']),
                'secondary' => array('label' => 'GÖNÜLLÜ OL', 'url' => $contact_url),
            ),
            array(
                'image' => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&q=80&w=2000',
                'title' => 'MENTOR OL, TECRÜBENİ PAYLAŞ',
                'desc' => 'Genç mezunlara ve öğrencilere yol göster, kariyer yolculuklarında onlara ışık tut.',
                'primary' => array('label' => 'PROGRAMLARI İNCELE', 'url' => $solidarity_url),
                'secondary' => null,
            ),
        );
    }

    private static function normalize_home_slider_slides($slides)
    {
        $defaults = self::get_default_home_slider_slides();
        $source   = is_array($slides) ? array_values($slides) : array();
        $normalized = array();

        for ($i = 0; $i < 3; $i++) {
            $default = $defaults[$i];
            $item = isset($source[$i]) && is_array($source[$i]) ? $source[$i] : array();

            $image = isset($item['image']) ? trim((string) $item['image']) : '';
            $title = isset($item['title']) ? trim((string) $item['title']) : '';
            $desc  = isset($item['desc']) ? trim((string) $item['desc']) : '';

            $primary = isset($item['primary']) && is_array($item['primary']) ? $item['primary'] : array();
            $p_label = isset($primary['label']) ? trim((string) $primary['label']) : '';
            $p_url   = isset($primary['url']) ? trim((string) $primary['url']) : '';

            $secondary = isset($item['secondary']) && is_array($item['secondary']) ? $item['secondary'] : array();
            $s_label = isset($secondary['label']) ? trim((string) $secondary['label']) : '';
            $s_url   = isset($secondary['url']) ? trim((string) $secondary['url']) : '';

            $normalized[] = array(
                'image' => $image !== '' ? $image : (string) $default['image'],
                'title' => $title !== '' ? $title : (string) $default['title'],
                'desc' => $desc !== '' ? $desc : (string) $default['desc'],
                'primary' => array(
                    'label' => $p_label !== '' ? $p_label : (string) $default['primary']['label'],
                    'url' => $p_url !== '' ? $p_url : (string) $default['primary']['url'],
                ),
                'secondary' => ($s_label !== '' && $s_url !== '')
                    ? array('label' => $s_label, 'url' => $s_url)
                    : (!empty($default['secondary']) ? $default['secondary'] : null),
            );
        }

        return $normalized;
    }

    private static function build_card_sections_for_post_type($post_type, $limit, $fallback_image, $is_event = false, $single_row = false, $row_extra_class = '')
    {
        $sections = array();
        $cards    = array();

        $query_orderby = ($post_type === 'team')
            ? array(
                'menu_order' => 'ASC',
                'modified'   => 'DESC',
                'ID'         => 'DESC',
            )
            : 'date';

        $posts_per_page = (int) $limit;
        if ($posts_per_page === 0) {
            $posts_per_page = 1;
        }
        if ($posts_per_page < -1) {
            $posts_per_page = -1;
        }
        if ($posts_per_page > 100) {
            $posts_per_page = 100;
        }

        $query_args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'orderby' => $query_orderby,
        );
        if ($post_type !== 'team') {
            $query_args['order'] = 'DESC';
        }

        $query = new WP_Query($query_args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $image   = get_the_post_thumbnail_url($post_id, 'full');
                if (!$image) {
                    $image = $fallback_image;
                }

                $description = get_the_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 20);
                if ($is_event) {
                    $description = self::get_event_datetime($post_id) . ' | ' . self::get_event_location($post_id) . ' | ' . $description;
                }

                $category_slugs = array();
                if ($post_type === 'team') {
                    $team_terms = get_the_terms($post_id, 'team-category');
                    if (is_array($team_terms) && !is_wp_error($team_terms)) {
                        foreach ($team_terms as $team_term) {
                            if (!($team_term instanceof WP_Term)) {
                                continue;
                            }
                            $slug = sanitize_title((string) $team_term->slug);
                            if ($slug !== '') {
                                $category_slugs[] = $slug;
                            }
                        }
                    }
                    $category_slugs = array_values(array_unique($category_slugs));
                }

                $cards[] = array(
                    'title' => get_the_title(),
                    'description' => $description,
                    'image' => $image,
                    'url' => get_permalink($post_id),
                    'category_slugs' => $category_slugs,
                );
            }
            wp_reset_postdata();
        }

        if (empty($cards)) {
            return $sections;
        }

        $cards_per_row = 3;
        $rows = $single_row ? array($cards) : array_chunk($cards, $cards_per_row);
        $card_type_class = $is_event ? 'odt-el-event-card' : 'odt-el-group-card';
        foreach ($rows as $row) {
            $columns = array();
            $col_count = count($row);
            $col_size = $col_count > 0 ? (int) floor(100 / $col_count) : 100;
            if (!$single_row) {
                // Son satırda kart sayısı azalsa da kart genişliği önceki satırlarla aynı kalsın.
                $col_size = (int) floor(100 / $cards_per_row);
            }

            foreach ($row as $card) {
                $card_css_classes = array('odt-el-card', $card_type_class);
                $column_css_classes = array('odt-el-card-col', $card_type_class . '-col');

                if ($post_type === 'team' && !empty($card['category_slugs']) && is_array($card['category_slugs'])) {
                    foreach ($card['category_slugs'] as $term_slug) {
                        $term_slug = sanitize_title((string) $term_slug);
                        if ($term_slug === '') {
                            continue;
                        }
                        $term_class = 'odt-team-cat-' . sanitize_html_class($term_slug);
                        $card_css_classes[] = $term_class;
                        $column_css_classes[] = $term_class;
                    }
                }

                $card_css_classes = implode(' ', array_values(array_unique($card_css_classes)));
                $column_css_classes = implode(' ', array_values(array_unique($column_css_classes)));
                $columns[] = array(
                    'size' => $col_size,
                    'settings' => array(
                        '_css_classes' => $column_css_classes,
                    ),
                    'widgets' => array(
                        self::build_widget('image-box', array(
                            'image' => array('url' => (string) $card['image'], 'id' => ''),
                            'title_text' => (string) $card['title'],
                            'description_text' => (string) $card['description'],
                            'link' => array('url' => (string) $card['url']),
                            'position' => 'top',
                            'align' => 'left',
                            '_css_classes' => $card_css_classes,
                        )),
                    ),
                );
            }

            $row_class_tokens = array('odt-el', 'odt-el-card-row', $card_type_class . '-row');
            if (is_string($row_extra_class) && trim($row_extra_class) !== '') {
                foreach (preg_split('/\s+/', trim($row_extra_class)) as $candidate_class) {
                    $candidate_class = sanitize_html_class((string) $candidate_class);
                    if ($candidate_class !== '') {
                        $row_class_tokens[] = $candidate_class;
                    }
                }
            }
            $row_class_tokens = array_values(array_unique($row_class_tokens));
            $row_class = implode(' ', $row_class_tokens);
            $sections[] = self::build_section_with_columns($columns, array(
                'padding' => array('unit' => 'px', 'top' => '20', 'right' => '0', 'bottom' => '20', 'left' => '0', 'isLinked' => false),
                '_css_classes' => $row_class,
            ));
        }

        return $sections;
    }

    private static function get_team_filter_terms()
    {
        $terms = get_terms(array(
            'taxonomy' => 'team-category',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
        ));
        if (!is_array($terms) || is_wp_error($terms)) {
            return array();
        }

        $items = array();
        foreach ($terms as $term) {
            if (!($term instanceof WP_Term)) {
                continue;
            }
            $slug = sanitize_title((string) $term->slug);
            $name = trim((string) $term->name);
            if ($slug === '' || $name === '') {
                continue;
            }
            $items[] = array(
                'slug' => $slug,
                'name' => $name,
            );
        }

        return $items;
    }

    private static function build_team_filter_section_for_elementor()
    {
        $terms = self::get_team_filter_terms();
        if (count($terms) < 2) {
            return array();
        }

        $buttons = array();
        $buttons[] = '<button type="button" class="odt-group-filter is-active" data-group-filter="all">Tümü</button>';
        foreach ($terms as $term) {
            $buttons[] = '<button type="button" class="odt-group-filter" data-group-filter="' . esc_attr((string) $term['slug']) . '">' . esc_html((string) $term['name']) . '</button>';
        }

        $markup = '<div class="odt-group-filters odt-group-filters-elementor" data-working-groups-filter="team">' . implode('', $buttons) . '</div>';

        return self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('html', array(
                            'html' => $markup,
                        )),
                    ),
                ),
            ),
            array(
                'padding' => array(
                    'unit' => 'px',
                    'top' => '16',
                    'right' => '0',
                    'bottom' => '8',
                    'left' => '0',
                    'isLinked' => false,
                ),
                '_css_classes' => 'odt-el odt-el-section odt-el-about-groups-filter',
            )
        );
    }

    private static function build_elementor_document($widgets)
    {
        return array(
            self::build_section($widgets),
        );
    }

    private static function build_section_with_columns($columns, $settings = array())
    {
        $defaults = array(
            'layout' => 'full_width',
            'content_width' => 'boxed',
            'gap' => 'default',
            'padding' => array(
                'unit' => 'px',
                'top' => '40',
                'right' => '0',
                'bottom' => '40',
                'left' => '0',
                'isLinked' => false,
            ),
        );

        $section_settings = wp_parse_args(is_array($settings) ? $settings : array(), $defaults);
        if (!empty($settings['padding']) && is_array($settings['padding'])) {
            $section_settings['padding'] = $settings['padding'];
        }
        if (isset($section_settings['_css_classes']) && !isset($section_settings['css_classes'])) {
            // Elementor section/column seviyesinde sinif adi alani `css_classes` olarak beklenir.
            $section_settings['css_classes'] = (string) $section_settings['_css_classes'];
        }
        unset($section_settings['_css_classes']);

        $elements = array();
        foreach ((array) $columns as $column) {
            $size = isset($column['size']) ? (int) $column['size'] : 100;
            if ($size <= 0) {
                $size = 100;
            }

            $widgets = isset($column['widgets']) && is_array($column['widgets']) ? $column['widgets'] : array();
            $col_settings = array('_column_size' => $size);

            if (!empty($column['settings']) && is_array($column['settings'])) {
                $col_settings = wp_parse_args($column['settings'], $col_settings);
            }
            if (isset($col_settings['_css_classes']) && !isset($col_settings['css_classes'])) {
                $col_settings['css_classes'] = (string) $col_settings['_css_classes'];
            }
            unset($col_settings['_css_classes']);

            $elements[] = array(
                'id'       => self::rand_id(),
                'elType'   => 'column',
                'settings' => $col_settings,
                'elements' => $widgets,
            );
        }

        return array(
            'id'       => self::rand_id(),
            'elType'   => 'section',
            'settings' => $section_settings,
            'elements' => $elements,
        );
    }

    private static function build_section($widgets)
    {
        return self::build_section_with_columns(array(
            array(
                'size' => 100,
                'widgets' => is_array($widgets) ? $widgets : array(),
            ),
        ));
    }

    private static function build_column($widgets)
    {
        return array(
            'id'       => self::rand_id(),
            'elType'   => 'column',
            'settings' => array(
                '_column_size' => 100,
            ),
            'elements' => $widgets,
        );
    }

    private static function build_widget($widget_type, $settings)
    {
        return array(
            'id'         => self::rand_id(),
            'elType'     => 'widget',
            'widgetType' => $widget_type,
            'settings'   => is_array($settings) ? $settings : array(),
            'elements'   => array(),
        );
    }

    private static function build_events_flip_editable_widgets()
    {
        $items = array(
            array(
                'title' => 'Mezun Buluşmaları',
                'text' => 'Mezunlar Günü, Bahar Şenliği ve Yılbaşı Partisi gibi geleneksel etkinlikler, şehrin çeşitli mekanlarında sosyal buluşmalar ve partilerle farklı kuşaklardan ODTÜ\'lülerin ağlarını güçlendirmesini amaçlıyoruz.',
            ),
            array(
                'title' => 'Paneller ve Seminerler',
                'text' => 'Akademisyenler, sektör profesyonelleri ve alanlarında öncü mezunların konuşmacı olarak katıldığı paneller, söyleşiler ve kariyer seminerlerinde üyeleri buluşturuyoruz.',
            ),
            array(
                'title' => 'Kültürel ve Sosyal Etkinlikler',
                'text' => 'İstanbul içi ve çevresinde kültür gezileri, sanat galerisi turları, edebiyat söyleşileri ve film gösterimleri gibi sosyal etkinliklerle üyelerimize keyifli vakit geçirme şansı sunuyoruz.',
            ),
            array(
                'title' => 'Genç Mezun Etkinlikleri',
                'text' => 'Yeni mezun ve gençlerin ihtiyaçlarını karşılayan özel etkinlikleri gençlerle, gençler için düzenliyoruz. Genç mezunlar sosyal etkinliklerde bir araya gelerek yeni bağlar kuruyor ve mezun camiasına entegre oluyorlar.',
            ),
        );

        $widgets = array();
        foreach ($items as $index => $item) {
            $ordinal = (int) $index + 1;
            $widgets[] = self::build_widget('heading', array(
                'title' => (string) $item['title'],
                'size' => 'md',
                'align' => 'left',
                '_css_classes' => 'odt-el-events-flip-title odt-el-events-flip-title-' . $ordinal,
            ));
            $widgets[] = self::build_widget('text-editor', array(
                'editor' => '<p>' . esc_html((string) $item['text']) . '</p>',
                '_css_classes' => 'odt-el-events-flip-copy odt-el-events-flip-copy-' . $ordinal,
            ));
        }

        return $widgets;
    }

    private static function build_membership_benefits_editable_widgets()
    {
        $items = array(
            array(
                'title' => 'Dayanışma',
                'text' => 'Mezunlar, öğrenciler ve gönüllüler arasında güçlü bir dayanışma ağına katılın.',
            ),
            array(
                'title' => 'Etkinlikler',
                'text' => 'Panel, seminer, kültür gezisi ve sosyal buluşmalarda aktif yer alın.',
            ),
            array(
                'title' => 'Geri Verme',
                'text' => 'Burs, mentorluk ve gönüllülük kanallarıyla öğrencilere destek olun.',
            ),
            array(
                'title' => '+1 İndirimler',
                'text' => 'Üyelere özel kurum anlaşmaları ve dönemsel avantajlardan yararlanın.',
            ),
        );

        $widgets = array(
            self::build_widget('heading', array(
                'title' => '3+1 Üyelik Avantajları',
                'size' => 'md',
                'align' => 'left',
                '_css_classes' => 'odt-el-membership-benefits-heading',
            )),
        );

        foreach ($items as $index => $item) {
            $ordinal = (int) $index + 1;
            $widgets[] = self::build_widget('text-editor', array(
                'editor' => '<p><strong>' . esc_html((string) $item['title']) . ':</strong> ' . esc_html((string) $item['text']) . '</p>',
                '_css_classes' => 'odt-el-membership-benefits-item odt-el-membership-benefits-item-' . $ordinal,
            ));
        }

        return $widgets;
    }

    private static function build_events_gallery_editable_widgets()
    {
        $items = array();

        if (function_exists('odtumist_get_events_gallery_items')) {
            $items = odtumist_get_events_gallery_items();
        } elseif (function_exists('odtumist_get_events_gallery')) {
            $images = odtumist_get_events_gallery();
            foreach ((array) $images as $image_url) {
                $items[] = array(
                    'image' => (string) $image_url,
                    'title' => '',
                    'desc' => '',
                );
            }
        }

        if (!is_array($items) || empty($items)) {
            $items = array(
                array(
                    'image' => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&q=80&w=800',
                    'title' => 'Mezunlar Günü',
                    'desc' => 'Geleneksel buluşmadan bir kare',
                ),
                array(
                    'image' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=800',
                    'title' => 'Bahar Şenliği',
                    'desc' => 'Sosyal buluşma ve dayanışma',
                ),
                array(
                    'image' => 'https://images.unsplash.com/photo-1475721027187-4024733923f7?auto=format&fit=crop&q=80&w=800',
                    'title' => 'Panel / Seminer',
                    'desc' => 'Konuşmacı buluşmalarından kesitler',
                ),
            );
        }

        $widgets = array();
        foreach ($items as $index => $item) {
            if (!is_array($item) || empty($item['image'])) {
                continue;
            }

            $ordinal = (int) $index + 1;
            $title = isset($item['title']) ? trim((string) $item['title']) : '';
            $desc = isset($item['desc']) ? trim((string) $item['desc']) : '';

            $widgets[] = self::build_widget('image-box', array(
                'image' => array(
                    'url' => esc_url_raw((string) $item['image']),
                    'id' => '',
                ),
                'title_text' => $title !== '' ? $title : 'Etkinlik',
                'description_text' => $desc,
                'position' => 'top',
                'align' => 'left',
                '_css_classes' => 'odt-el-events-gallery-card odt-el-events-gallery-card-' . $ordinal,
            ));
        }

        return $widgets;
    }

    private static function build_about_tab_pagination_section($prev, $next, $tab_id = '')
    {
        $build_column = static function ($item, $type) {
            if (!is_array($item) || empty($item['anchor']) || empty($item['label'])) {
                return array(
                    'size' => 50,
                    'widgets' => array(
                        self::build_widget('text-editor', array(
                            'editor' => '<p>&nbsp;</p>',
                            '_css_classes' => 'odt-el-about-pag-spacer',
                        )),
                    ),
                );
            }

            $is_prev = ($type === 'prev');
            $button_text = $is_prev
                ? '← ÖNCEKİ: ' . (string) $item['label']
                : 'SONRAKİ: ' . (string) $item['label'] . ' →';

            return array(
                'size' => 50,
                'widgets' => array(
                    self::build_widget('button', array(
                        'text' => $button_text,
                        'link' => array('url' => '#' . sanitize_title((string) $item['anchor'])),
                        'size' => 'sm',
                        'align' => $is_prev ? 'left' : 'right',
                        '_css_classes' => 'odt-el-btn odt-el-about-pag-btn ' . ($is_prev ? 'odt-el-about-pag-btn-prev' : 'odt-el-about-pag-btn-next'),
                    )),
                ),
            );
        };

        $tab_slug = sanitize_html_class((string) $tab_id);
        $section_classes = 'odt-el odt-el-section odt-el-about-pagination';
        if ($tab_slug !== '') {
            $section_classes .= ' odt-el-about-pagination-' . $tab_slug;
        }

        return self::build_section_with_columns(
            array(
                $build_column($prev, 'prev'),
                $build_column($next, 'next'),
            ),
            array(
                '_css_classes' => $section_classes,
                'padding' => array(
                    'unit' => 'px',
                    'top' => '12',
                    'right' => '0',
                    'bottom' => '8',
                    'left' => '0',
                    'isLinked' => false,
                ),
            )
        );
    }

    private static function rand_id()
    {
        return substr(md5(uniqid((string) wp_rand(), true)), 0, 8);
    }

    private static function capture_template_part($slug, $name = null)
    {
        if (!function_exists('get_template_part')) {
            return '';
        }

        ob_start();
        get_template_part($slug, $name);
        return (string) ob_get_clean();
    }

    public static function shortcode_frontpage_sections($atts)
    {
        return self::capture_template_part('template-parts/sections/hero')
            . self::capture_template_part('template-parts/sections/events')
            . self::capture_template_part('template-parts/sections/membership-ctas')
            . self::capture_template_part('template-parts/sections/working-groups')
            . self::capture_template_part('template-parts/sections/newsletter')
            . self::capture_template_part('template-parts/sections/group-photo');
    }

    public static function shortcode_about_layout($atts)
    {
        return self::capture_template_part('template-parts/page/about-layout');
    }

    public static function shortcode_events_layout($atts)
    {
        return self::capture_template_part('template-parts/page/events-layout');
    }

    public static function shortcode_membership_layout($atts)
    {
        return self::capture_template_part('template-parts/page/membership-layout');
    }

    public static function shortcode_solidarity_layout($atts)
    {
        return self::capture_template_part('template-parts/page/solidarity-layout');
    }

    public static function shortcode_contact_layout($atts)
    {
        return self::capture_template_part('template-parts/page/contact-layout');
    }

    public static function shortcode_events_grid($atts)
    {
        $atts = shortcode_atts(array(
            'limit' => 6,
        ), $atts, 'odtumist_events_grid');

        $limit = max(1, min(24, absint($atts['limit'])));
        $query = new WP_Query(array(
            'post_type'      => 'event',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));

        if (!$query->have_posts()) {
            return '<p class="empty-state">Henüz yayınlanmış etkinlik bulunmuyor.</p>';
        }

        ob_start();
        ?>
        <div class="events-page-grid">
            <div class="site-container events-grid">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <?php
                    $post_id      = get_the_ID();
                    $terms        = get_the_terms($post_id, 'event-category');
                    $primary_term = (is_array($terms) && !empty($terms)) ? $terms[0] : null;
                    $cat_name     = $primary_term ? $primary_term->name : 'Etkinlik';
                    ?>
                    <article class="event-list-card">
                        <a href="<?php the_permalink(); ?>" class="event-list-thumb">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
                            <?php else : ?>
                                <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=900" alt="<?php the_title_attribute(); ?>" loading="lazy">
                            <?php endif; ?>
                        </a>
                        <div class="event-list-content">
                            <span class="event-badge"><?php echo esc_html($cat_name); ?></span>
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <p class="event-meta"><?php echo esc_html(self::get_event_datetime($post_id)); ?></p>
                            <p class="event-meta"><?php echo esc_html(self::get_event_location($post_id)); ?></p>
                            <p><?php echo esc_html(get_the_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 24)); ?></p>
                            <a class="event-more" href="<?php the_permalink(); ?>">Detayları İncele</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
        <?php
        wp_reset_postdata();
        return (string) ob_get_clean();
    }

    public static function shortcode_events_gallery($atts)
    {
        $items = array();

        if (function_exists('odtumist_get_events_gallery_items')) {
            $gallery_items = odtumist_get_events_gallery_items();
            if (is_array($gallery_items)) {
                foreach ($gallery_items as $item) {
                    if (!is_array($item) || empty($item['image'])) {
                        continue;
                    }
                    $items[] = array(
                        'image' => (string) $item['image'],
                        'title' => !empty($item['title']) ? (string) $item['title'] : 'Etkinlik',
                        'desc'  => !empty($item['desc']) ? (string) $item['desc'] : '',
                    );
                }
            }
        } elseif (function_exists('odtumist_get_events_gallery')) {
            $gallery = odtumist_get_events_gallery();
            if (is_array($gallery)) {
                foreach ($gallery as $index => $image_url) {
                    $image_url = trim((string) $image_url);
                    if ($image_url === '') {
                        continue;
                    }
                    $items[] = array(
                        'image' => $image_url,
                        'title' => 'Etkinlik #' . ((int) $index + 1),
                        'desc' => '',
                    );
                }
            }
        }

        if (empty($items)) {
            return '<p class="empty-state">Henüz etkinlik galerisi görseli eklenmedi.</p>';
        }

        ob_start();
        ?>
        <div class="odt-el-events-gallery-grid">
            <?php foreach ($items as $item) : ?>
                <figure class="odt-el-events-gallery-item">
                    <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title']); ?>" loading="lazy">
                    <figcaption>
                        <strong><?php echo esc_html($item['title']); ?></strong>
                        <?php if (!empty($item['desc'])) : ?>
                            <span><?php echo esc_html($item['desc']); ?></span>
                        <?php endif; ?>
                    </figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public static function shortcode_working_groups_grid($atts)
    {
        $atts = shortcode_atts(array(
            'limit' => 8,
        ), $atts, 'odtumist_working_groups_grid');

        $raw_limit = trim((string) $atts['limit']);
        if ($raw_limit === '' || $raw_limit === '0') {
            $raw_limit = '8';
        }

        if (in_array(strtolower($raw_limit), array('all', '-1'), true)) {
            $limit = -1;
        } else {
            $limit = max(1, min(100, absint($raw_limit)));
        }

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
            return '<p class="empty-state">Henüz yayınlanmış çalışma grubu bulunmuyor.</p>';
        }

        $filter_terms = self::get_team_filter_terms();

        ob_start();
        ?>
        <?php if (count($filter_terms) > 1) : ?>
            <div class="odt-group-filters" data-working-groups-filter="team">
                <button type="button" class="odt-group-filter is-active" data-group-filter="all"><?php esc_html_e('Tümü', 'odtumist-eb'); ?></button>
                <?php foreach ($filter_terms as $filter_term) : ?>
                    <button type="button" class="odt-group-filter" data-group-filter="<?php echo esc_attr((string) $filter_term['slug']); ?>"><?php echo esc_html((string) $filter_term['name']); ?></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="about-groups-grid">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php
                $term_slugs = array();
                $team_terms = get_the_terms(get_the_ID(), 'team-category');
                if (is_array($team_terms) && !is_wp_error($team_terms)) {
                    foreach ($team_terms as $team_term) {
                        if (!($team_term instanceof WP_Term)) {
                            continue;
                        }
                        $slug = sanitize_title((string) $team_term->slug);
                        if ($slug !== '') {
                            $term_slugs[] = $slug;
                        }
                    }
                }
                $term_slugs = array_values(array_unique($term_slugs));
                $term_slug_attr = implode(' ', $term_slugs);
                $term_class_tokens = array();
                foreach ($term_slugs as $term_slug) {
                    $term_class_tokens[] = 'odt-team-cat-' . sanitize_html_class((string) $term_slug);
                }
                ?>
                <article class="about-group-card<?php echo !empty($term_class_tokens) ? ' ' . esc_attr(implode(' ', $term_class_tokens)) : ''; ?>" data-group-cats="<?php echo esc_attr($term_slug_attr); ?>">
                    <a class="about-group-media" href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('full', array('loading' => 'lazy')); ?>
                        <?php else : ?>
                            <img src="https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&q=80&w=800" alt="<?php the_title_attribute(); ?>" loading="lazy">
                        <?php endif; ?>
                        <span class="about-group-badge">Keşfet</span>
                    </a>
                    <div class="about-group-body">
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p><?php echo esc_html(get_the_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 20)); ?></p>
                        <a class="about-group-link" href="<?php the_permalink(); ?>">Detaylı İncele &rarr;</a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php
        wp_reset_postdata();

        return (string) ob_get_clean();
    }

    public static function shortcode_contact_departments($atts)
    {
        $departments = self::get_contact_departments();
        if (empty($departments)) {
            return '<p class="empty-state">İletişim birimleri henüz tanımlanmamış.</p>';
        }

        ob_start();
        ?>
        <div class="contact-departments">
            <?php foreach ($departments as $dept) : ?>
                <article class="contact-dept-card contact-dept-<?php echo esc_attr($dept['accent']); ?>">
                    <div class="contact-dept-info">
                        <h4><?php echo esc_html($dept['title']); ?></h4>
                        <p class="contact-dept-sub"><?php echo esc_html($dept['subtitle']); ?></p>
                    </div>
                    <a class="contact-dept-email" href="mailto:<?php echo esc_attr($dept['email']); ?>"><?php echo esc_html($dept['email']); ?> &rsaquo;</a>
                </article>
            <?php endforeach; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public static function shortcode_contact_map($atts)
    {
        $map_url = get_theme_mod('odtumist_contact_map_url', 'https://www.google.com/maps?q=Cumhuriyet+Cad.+Cumhuriyet+Apt.+No:+17,+Beyoglu,+Istanbul&output=embed');

        return '<div class="contact-map-wrap"><iframe src="' . esc_url($map_url) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="ODTÜMİST Lokasyon"></iframe></div>';
    }

    public static function shortcode_contact_form($atts)
    {
        $atts = shortcode_atts(array(
            'provider'  => 'auto',
            'cf7_id'    => 0,
            'wpforms_id' => 0,
            'shortcode' => '',
        ), $atts, 'odtumist_contact_form');

        $provider = sanitize_key((string) $atts['provider']);
        if (!in_array($provider, array('auto', 'cf7', 'wpforms', 'shortcode'), true)) {
            $provider = 'auto';
        }

        $cf7_id      = absint($atts['cf7_id']);
        $wpforms_id  = absint($atts['wpforms_id']);
        $raw_shortcode = trim((string) $atts['shortcode']);

        if ($provider === 'auto') {
            if ($wpforms_id <= 0) {
                $wpforms_id = self::get_first_post_id_by_type('wpforms');
            }
            if ($wpforms_id > 0 && function_exists('wpforms_display')) {
                $provider = 'wpforms';
            } else {
                if ($cf7_id <= 0) {
                    $cf7_id = self::get_first_post_id_by_type('wpcf7_contact_form');
                }
                if ($cf7_id > 0) {
                    $provider = 'cf7';
                } else {
                    $provider = 'shortcode';
                }
            }
        }

        if ($provider === 'wpforms') {
            if ($wpforms_id <= 0) {
                $wpforms_id = self::get_first_post_id_by_type('wpforms');
            }
            if ($wpforms_id > 0 && function_exists('wpforms_display')) {
                ob_start();
                wpforms_display($wpforms_id, false, false, false);
                $html = (string) ob_get_clean();
                if (trim($html) !== '') {
                    return $html;
                }
            }
        }

        if ($provider === 'cf7') {
            if ($cf7_id <= 0) {
                $cf7_id = self::get_first_post_id_by_type('wpcf7_contact_form');
            }
            if ($cf7_id > 0) {
                $html = do_shortcode('[contact-form-7 id="' . (int) $cf7_id . '"]');
                if (trim((string) $html) !== '') {
                    return (string) $html;
                }
            }
        }

        if ($raw_shortcode === '') {
            $raw_shortcode = '[contact-form-7 id="123" title="İletişim Formu"]';
        }

        $fallback_html = do_shortcode($raw_shortcode);
        if (trim((string) $fallback_html) !== '' && trim((string) $fallback_html) !== $raw_shortcode) {
            return (string) $fallback_html;
        }

        return '<p>Form kurulumu tamamlanmadi. WPForms veya Contact Form 7 eklentisinden bir form olusturup bu alana baglayabilirsin.</p>';
    }

    public static function shortcode_social_feed($atts)
    {
        $atts = shortcode_atts(array(
            'shortcode' => '',
        ), $atts, 'odtumist_social_feed');

        $shortcode = trim((string) $atts['shortcode']);
        if ($shortcode === '') {
            $shortcode = self::get_social_feed_shortcode_option();
        } else {
            $shortcode = self::sanitize_shortcode_like_text($shortcode);
        }

        $rendered = do_shortcode($shortcode);
        $trimmed  = trim((string) $rendered);
        if ($trimmed !== '' && $trimmed !== $shortcode) {
            return (string) $rendered;
        }

        return '<p>Social feed henüz bağlanmadı. Instagram feed eklentisinde hesap bağlantısını tamamlayıp shortcode alanını güncelleyebilirsin.</p>';
    }

    private static function get_first_post_id_by_type($post_type)
    {
        if (!post_type_exists($post_type)) {
            return 0;
        }

        $ids = get_posts(array(
            'post_type'      => $post_type,
            'post_status'    => array('publish', 'draft'),
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'fields'         => 'ids',
        ));

        if (!is_array($ids) || empty($ids)) {
            return 0;
        }

        return (int) $ids[0];
    }

    private static function get_contact_departments()
    {
        if (function_exists('odtumist_get_contact_departments')) {
            $departments = odtumist_get_contact_departments();
            if (is_array($departments) && !empty($departments)) {
                return $departments;
            }
        }

        return array(
            array(
                'title'    => 'Yönetim Kurulu & Üyelik',
                'subtitle' => 'Üyelik surecleri ve genel iletisim',
                'email'    => 'dernek@odtumist.org',
                'accent'   => 'red',
            ),
            array(
                'title'    => 'Dernek Koordinatoru',
                'subtitle' => 'Buket Akpinar',
                'email'    => 'buket.akpinar@odtumist.org',
                'accent'   => 'blue',
            ),
            array(
                'title'    => 'Burs Sorumlusu',
                'subtitle' => 'Delal Filizay',
                'email'    => 'delal.filizay@odtumist.org',
                'accent'   => 'red',
            ),
        );
    }

    private static function sanitize_shortcode_like_text($value)
    {
        if (!is_string($value)) {
            return '';
        }

        $value = trim($value);
        $value = wp_strip_all_tags($value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim((string) $value);
    }

    private static function get_social_feed_shortcode_option()
    {
        $stored = get_option(self::SOCIAL_FEED_OPTION, '');
        if (!is_string($stored)) {
            $stored = '';
        }

        $stored = self::sanitize_shortcode_like_text($stored);
        if ($stored === '') {
            return self::SOCIAL_FEED_DEFAULT;
        }

        return $stored;
    }

    private static function update_social_feed_shortcode_option($value)
    {
        $shortcode = self::sanitize_shortcode_like_text((string) $value);
        if ($shortcode === '') {
            $shortcode = self::SOCIAL_FEED_DEFAULT;
        }

        update_option(self::SOCIAL_FEED_OPTION, $shortcode, false);
    }

    private static function get_event_datetime($post_id)
    {
        if (function_exists('odtumist_get_event_datetime')) {
            return (string) odtumist_get_event_datetime($post_id);
        }

        $start = get_post_meta($post_id, 'solicitor_event_start_dt', true);
        if (!$start) {
            $start = get_post_meta($post_id, 'event_start_dt', true);
        }

        if (!$start) {
            return (string) get_the_date('d M Y', $post_id);
        }

        $timestamp = strtotime((string) $start);
        if (!$timestamp) {
            return (string) $start;
        }

        if (function_exists('wp_date')) {
            return wp_date('d M Y - H:i', $timestamp);
        }

        return date_i18n('d M Y - H:i', $timestamp);
    }

    private static function get_event_location($post_id)
    {
        if (function_exists('odtumist_get_event_location')) {
            return (string) odtumist_get_event_location($post_id);
        }

        $location = get_post_meta($post_id, 'solicitor_event_address', true);
        if (!$location) {
            $location = get_post_meta($post_id, 'event_address', true);
        }

        return $location ? (string) $location : 'Konum yakinda eklenecek';
    }

    public static function render_admin_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $report = get_transient(self::REPORT_TRANSIENT);
        if ($report) {
            delete_transient(self::REPORT_TRANSIENT);
        }
        $social_feed_shortcode = self::get_social_feed_shortcode_option();

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('ODTÜMİST Elementor Bootstrap', 'odtumist-eb'); ?></h1>
            <p><?php esc_html_e('WordPress + Elementor kurulumunu hizlandirmak icin temel sayfa, menu, etkinlik ve calisma grubu yapisini otomatik kurar.', 'odtumist-eb'); ?></p>
            <p><?php esc_html_e('Varsayilan davranis mevcut icerikleri ezmez. "Zorla yeniden tohumla" secenegi sadece bilincli reset icin kullanilmalidir.', 'odtumist-eb'); ?></p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('odtumist_eb_run_nonce'); ?>
                <input type="hidden" name="action" value="odtumist_eb_run">

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Elementor Iskeletlerini Kur', 'odtumist-eb'); ?></th>
                        <td><label><input type="checkbox" name="apply_elementor" value="1" checked> <?php esc_html_e('Sayfalari Elementor ile duzenlenebilir baslangic duzenine cevir.', 'odtumist-eb'); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Tam Elementor Modu', 'odtumist-eb'); ?></th>
                        <td>
                            <label><input type="checkbox" name="elementor_full_mode" value="1" checked> <?php esc_html_e('Sayfalari shortcode yerine gercek Elementor section/widget yapisiyla kur.', 'odtumist-eb'); ?></label>
                            <p class="description"><?php esc_html_e('Bu secenek aciksa bloklar, widgetlar, slider ve gorseller Elementor icinde tek tek tasinabilir ve boyutlanabilir olur.', 'odtumist-eb'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Çalışma Grupları Kart Senkronu', 'odtumist-eb'); ?></th>
                        <td>
                            <label><input type="checkbox" name="sync_group_cards" value="1"> <?php esc_html_e('Anasayfa ve Hakkımızda sayfasındaki çalışma grubu kartlarını güncel öne çıkan görsellerle Elementor içine senkronize et.', 'odtumist-eb'); ?></label>
                            <p class="description"><?php esc_html_e('Bu işlem yalnızca çalışma grubu kart bloklarını günceller; sayfanın kalan bölümleri ezilmez.', 'odtumist-eb'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Etkinlik Kart Senkronu', 'odtumist-eb'); ?></th>
                        <td>
                            <label><input type="checkbox" name="sync_event_cards" value="1"> <?php esc_html_e('Anasayfa ve Etkinlikler sayfasındaki etkinlik kartlarını güncel event kayıtlarıyla Elementor içine senkronize et.', 'odtumist-eb'); ?></label>
                            <p class="description"><?php esc_html_e('Bu işlem yalnızca etkinlik kart bloklarını günceller; sayfanın kalan bölümleri ezilmez.', 'odtumist-eb'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Social Feed Shortcode', 'odtumist-eb'); ?></th>
                        <td>
                            <input type="text" class="regular-text code" name="social_feed_shortcode" value="<?php echo esc_attr($social_feed_shortcode); ?>">
                            <p class="description"><?php esc_html_e('Varsayılan: [instagram-feed feed="1"]. Feed eklentisi değişirse kendi shortcode’unu buraya yazabilirsin.', 'odtumist-eb'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Menuleri Yeniden Kur', 'odtumist-eb'); ?></th>
                        <td><label><input type="checkbox" name="rebuild_menus" value="1"> <?php esc_html_e('Ana ve footer menuyu sifirdan olustur/guncelle.', 'odtumist-eb'); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Varsayilan Ornekleri Temizle', 'odtumist-eb'); ?></th>
                        <td><label><input type="checkbox" name="cleanup_defaults" value="1" checked> <?php esc_html_e('Sample Page ve Hello World kayitlarini sil.', 'odtumist-eb'); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Zorla Yeniden Tohumla', 'odtumist-eb'); ?></th>
                        <td>
                            <label><input type="checkbox" name="force_reseed" value="1"> <?php esc_html_e('Sayfa/CPT/Elementor iceriklerini zorla yeniden yaz (mevcut duzenlemeleri ezebilir).', 'odtumist-eb'); ?></label>
                            <p class="description"><?php esc_html_e('Bu secenek aciksa var olan icerikler korunmaz.', 'odtumist-eb'); ?></p>
                        </td>
                    </tr>
                </table>

                <p><button type="submit" class="button button-primary button-hero"><?php esc_html_e('Temel Yapiyi Kur / Guncelle', 'odtumist-eb'); ?></button></p>
            </form>

            <?php if (is_array($report)) : ?>
                <hr>
                <h2><?php esc_html_e('Islem Sonucu', 'odtumist-eb'); ?></h2>
                <?php if (!empty($report['errors'])) : ?>
                    <div class="notice notice-error"><ul><?php foreach ($report['errors'] as $line) : ?><li><?php echo esc_html($line); ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <?php if (!empty($report['warnings'])) : ?>
                    <div class="notice notice-warning"><ul><?php foreach ($report['warnings'] as $line) : ?><li><?php echo esc_html($line); ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <?php if (!empty($report['messages'])) : ?>
                    <div class="notice notice-success"><ul><?php foreach ($report['messages'] as $line) : ?><li><?php echo esc_html($line); ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
            <?php endif; ?>

            <hr>
            <h2><?php esc_html_e('Elementor Kisa Kodlari', 'odtumist-eb'); ?></h2>
            <ul>
                <li><code>[odtumist_frontpage]</code></li>
                <li><code>[odtumist_about_layout]</code></li>
                <li><code>[odtumist_events_layout]</code></li>
                <li><code>[odtumist_membership_layout]</code></li>
                <li><code>[odtumist_solidarity_layout]</code></li>
                <li><code>[odtumist_contact_layout]</code></li>
                <li><code>[odtumist_events_grid limit="6"]</code></li>
                <li><code>[odtumist_working_groups_grid limit="8"]</code></li>
                <li><code>[odtumist_contact_departments]</code></li>
                <li><code>[odtumist_contact_map]</code></li>
                <li><code>[odtumist_contact_form provider="auto"]</code></li>
                <li><code>[odtumist_social_feed]</code></li>
            </ul>
        </div>
        <?php
    }
}

ODTUMIST_Elementor_Bootstrap::init();
