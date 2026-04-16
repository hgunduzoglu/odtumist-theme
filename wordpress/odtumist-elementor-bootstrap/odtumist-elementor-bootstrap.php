<?php
/**
 * Plugin Name: ODTUMIST Elementor Bootstrap
 * Description: Elementor Pro odakli ODTUMIST kurulumu icin sayfa, menu, CPT ve temel icerikleri tek tikla olusturur/gunceller.
 * Version: 1.1.0
 * Author: ODTUMIST
 */

if (!defined('ABSPATH')) {
    exit;
}

final class ODTUMIST_Elementor_Bootstrap
{
    const REPORT_TRANSIENT = 'odtumist_eb_report';

    public static function init()
    {
        add_action('init', array(__CLASS__, 'register_cpts'), 20);
        add_action('init', array(__CLASS__, 'register_shortcodes'), 21);
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
                'has_archive'  => true,
                'rewrite'      => array('slug' => 'etkinlikler'),
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
                    'name'          => __('Calisma Gruplari', 'odtumist-eb'),
                    'singular_name' => __('Calisma Grubu', 'odtumist-eb'),
                ),
                'public'       => true,
                'show_in_rest' => true,
                'has_archive'  => true,
                'rewrite'      => array('slug' => 'calisma-gruplari'),
                'supports'     => array('title', 'editor', 'excerpt', 'thumbnail'),
                'menu_icon'    => 'dashicons-groups',
            ));
        }

        if (!taxonomy_exists('team-category')) {
            register_taxonomy('team-category', 'team', array(
                'labels'       => array('name' => __('Calisma Grubu Kategorileri', 'odtumist-eb')),
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
        add_shortcode('odtumist_working_groups_grid', array(__CLASS__, 'shortcode_working_groups_grid'));
        add_shortcode('odtumist_contact_departments', array(__CLASS__, 'shortcode_contact_departments'));
        add_shortcode('odtumist_contact_map', array(__CLASS__, 'shortcode_contact_map'));
        add_shortcode('odtumist_contact_form', array(__CLASS__, 'shortcode_contact_form'));

        // Kisa takma isimler
        add_shortcode('odtumist-home', array(__CLASS__, 'shortcode_frontpage_sections'));
        add_shortcode('odtumist-about', array(__CLASS__, 'shortcode_about_layout'));
        add_shortcode('odtumist-events-layout', array(__CLASS__, 'shortcode_events_layout'));
        add_shortcode('odtumist-membership', array(__CLASS__, 'shortcode_membership_layout'));
        add_shortcode('odtumist-solidarity', array(__CLASS__, 'shortcode_solidarity_layout'));
        add_shortcode('odtumist-contact', array(__CLASS__, 'shortcode_contact_layout'));
        add_shortcode('odtumist-events', array(__CLASS__, 'shortcode_events_grid'));
        add_shortcode('odtumist-groups', array(__CLASS__, 'shortcode_working_groups_grid'));
    }

    public static function register_admin_page()
    {
        add_management_page(
            __('ODTUMIST Elementor Bootstrap', 'odtumist-eb'),
            __('ODTUMIST Bootstrap', 'odtumist-eb'),
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
            'rebuild_menus'  => !empty($_POST['rebuild_menus']),
            'cleanup_defaults' => !empty($_POST['cleanup_defaults']),
        );

        if ($options['force_reseed']) {
            $options['rebuild_menus'] = true;
        }

        $report = self::run($options);
        set_transient(self::REPORT_TRANSIENT, $report, 180);

        wp_safe_redirect(admin_url('tools.php?page=odtumist-elementor-bootstrap'));
        exit;
    }

    /**
     * WP-CLI: wp odtumist bootstrap [--force=1] [--elementor=1] [--menus=1] [--cleanup=1]
     */
    public static function cli_run($args, $assoc_args)
    {
        $options = array(
            'force_reseed'    => !empty($assoc_args['force']),
            'apply_elementor' => !isset($assoc_args['elementor']) || (bool) $assoc_args['elementor'],
            'rebuild_menus'   => !empty($assoc_args['menus']),
            'cleanup_defaults' => !isset($assoc_args['cleanup']) || (bool) $assoc_args['cleanup'],
        );

        if ($options['force_reseed']) {
            $options['rebuild_menus'] = true;
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
            \WP_CLI::error('ODTUMIST bootstrap tamamlansa da hata kayitlari var.');
        }

        \WP_CLI::success('ODTUMIST bootstrap tamamlandi.');
    }

    private static function run($options)
    {
        $defaults = array(
            'force_reseed'    => false,
            'apply_elementor' => true,
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

        self::ensure_permalink_structure($report);
        $page_ids = self::upsert_pages($report, (bool) $options['force_reseed']);
        self::upsert_teams($report, (bool) $options['force_reseed']);
        self::upsert_events($report, (bool) $options['force_reseed']);
        self::build_menus($page_ids, $report, (bool) $options['rebuild_menus']);
        self::apply_reading_settings($page_ids, $report);

        if ($options['apply_elementor']) {
            self::apply_elementor_defaults($report);
            self::seed_elementor_pages($page_ids, $report, (bool) $options['force_reseed']);
        }

        if ($options['cleanup_defaults']) {
            self::cleanup_defaults($report);
        }

        return $report;
    }

    private static function ensure_permalink_structure(&$report)
    {
        $current = (string) get_option('permalink_structure', '');
        if ($current !== '/%postname%/') {
            update_option('permalink_structure', '/%postname%/');
            flush_rewrite_rules(false);
            $report['messages'][] = __('Kalici baglanti yapisi "Yazi ismi" olarak ayarlandi.', 'odtumist-eb');
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
                'title'   => 'Hakkimizda',
                'excerpt' => "Istanbul'un dinamizminde ODTU ruhunu, dayanismasini ve kulturunu yasatan toplulugumuza hos geldin.",
                'content' => "<h2 id=\"neler-yapiyoruz\">Neler Yapiyoruz?</h2>\n<p>Uyelerimizi, gonullulerimizi ve destekcilerimizi ayni dayanisma aginda bulusturuyoruz.</p>\n<h2 id=\"calisma-gruplarimiz\">Calisma Gruplarimiz</h2>\n<p>Edebiyat, felsefe, fotograf, sosyal komite, burs, IK & uye gelistirme, spor & maraton dahil farkli alanlarda uretiyoruz.</p>\n<h2 id=\"sen-de-katil\">Sen de Katil Hocam!</h2>\n<p>Uyelik ve gonulluluk ile toplulugumuza katilabilirsin.</p>\n<h2 id=\"tarihce\">Tarihce</h2>\n<p>ODTUMIST, uzun yillara dayanan bir mezun dayanisma yapisidir.</p>\n<h2 id=\"yonetim\">Yonetim</h2>\n<p>Yonetim ve kurul bilgileri duzenli olarak guncellenir.</p>",
            ),
            'etkinlikler' => array(
                'title'   => 'Etkinlikler',
                'excerpt' => 'Takvimdeki etkinlikleri inceleyebilir, detay sayfalarindan kayit ve katilim bilgilerine ulasabilirsin.',
                'content' => '<p>Etkinlik kartlari "Etkinlikler" icerik tipinden otomatik cekilir.</p>',
            ),
            'uyelik' => array(
                'title'   => 'Uyelik',
                'excerpt' => 'ODTUMIST uyeligi; dayanisma, aidiyet ve ogrencilere uzanan etkiyi buyuten guclu bir topluluk catisidir.',
                'content' => "<h2 id=\"neden-uye-olmaliyim\">Neden Uye Olmaliyim?</h2>\n<p>Dayanisma agimiza katilmak icin uyelik basvurusu yapabilirsin.</p>\n<h2 id=\"bilgi-guncelleme\">Bilgi Guncelleme</h2>\n<p>Mezun bilgi alanlarini guncel tutman iletisimi guclendirir.</p>\n<h2 id=\"aidat-odeme\">Aidat Odeme</h2>\n<p>Aidat islemleri dijital olarak takip edilir.</p>\n<h2 id=\"uyelik-avantajlari\">Uyelik Avantajlari</h2>\n<p>Etkinlik, mentorluk ve guclu mezun agi imkanlari sunulur.</p>",
            ),
            'dayanisma' => array(
                'title'   => 'Dayanisma',
                'excerpt' => 'ODTU mezunu olmanin getirdigi bag, ODTUMIST catisi altinda ortak bir etki alanina donusuyor.',
                'content' => "<h2 id=\"networking\">Networking</h2>\n<p>Mezunlar arasi profesyonel baglar guclenir.</p>\n<h2 id=\"burs\">Burs Programlari</h2>\n<p>Ogrencilere surekli burs destegi saglanir.</p>\n<h2 id=\"maraton\">Maraton & Spor</h2>\n<p>Iyilik icin kosu ve dayanisma etkinlikleri duzenlenir.</p>\n<h2 id=\"mentorluk\">Mentorluk</h2>\n<p>Mezunlar, ogrenci ve yeni mezunlara mentorluk saglar.</p>",
            ),
            'iletisim' => array(
                'title'   => 'Iletisim',
                'excerpt' => "Istanbul'daki ODTU ruhunun merkezi ODTUPARK'ta sizleri bekliyoruz.",
                'content' => '<p>Bu sayfada form alani, iletisim kartlari ve harita bolumu bulunur.</p>',
            ),
            'haberler' => array(
                'title'   => 'Haberler',
                'excerpt' => '',
                'content' => '<p>Guncel duyuru ve haber iceriklerinizi bu sayfadan yayinlayabilirsiniz.</p>',
            ),
        );
    }

    private static function team_seed_data()
    {
        return array(
            array('slug' => 'edebiyat', 'title' => 'Edebiyat', 'excerpt' => 'Edebiyat okumalari ve yazar bulusmalari.', 'content' => 'Edebiyat grubu iceriklerini buradan yonetebilirsiniz.', 'category' => 'kultur'),
            array('slug' => 'felsefe', 'title' => 'Felsefe', 'excerpt' => 'Felsefi tartismalar ve metin okumalari.', 'content' => 'Felsefe grubu iceriklerini buradan yonetebilirsiniz.', 'category' => 'kultur'),
            array('slug' => 'fotograf', 'title' => 'Fotograf', 'excerpt' => 'Fotograf uretimi, geziler ve sergiler.', 'content' => 'Fotograf grubu iceriklerini buradan yonetebilirsiniz.', 'category' => 'sanat'),
            array('slug' => 'sosyal-komite', 'title' => 'Sosyal Komite', 'excerpt' => 'Gezi, etkinlik, bulusma ve sosyal organizasyonlar.', 'content' => 'Sosyal komite iceriklerini buradan yonetebilirsiniz.', 'category' => 'topluluk'),
            array('slug' => 'burs', 'title' => 'Burs', 'excerpt' => 'Burs fonu ve bursiyer destek programlari.', 'content' => 'Burs grubu iceriklerini buradan yonetebilirsiniz.', 'category' => 'dayanisma'),
            array('slug' => 'ik-uye-gelistirme', 'title' => 'IK & Uye Gelistirme', 'excerpt' => 'Uyelik ve mezun agi gelisim calismalari.', 'content' => 'IK ve uye gelistirme grubunu buradan yonetebilirsiniz.', 'category' => 'gelisim'),
            array('slug' => 'spor-maraton', 'title' => 'Spor & Maraton', 'excerpt' => 'Spor etkinlikleri ve maraton calismalari.', 'content' => 'Spor ve maraton grubu iceriklerini buradan yonetebilirsiniz.', 'category' => 'spor'),
        );
    }

    private static function event_seed_data()
    {
        return array(
            array('slug' => 'geleneksel-visnelik-bulusmasi', 'title' => 'Geleneksel Visnelik Bulusmasi', 'excerpt' => 'Mezunlar bulusmasi ve sosyal paylasim etkinligi.', 'content' => 'Etkinlik detaylarini buradan duzenleyebilirsiniz.', 'category' => 'sosyal', 'location' => 'ODTUPARK Ulus', 'start_dt' => '2026-06-15 19:00:00'),
            array('slug' => 'istanbul-maratonu-hazirlik-kosusu', 'title' => 'Istanbul Maratonu Hazirlik Kosusu', 'excerpt' => 'Maraton oncesi hazirlik kosusu.', 'content' => 'Etkinlik detaylarini buradan duzenleyebilirsiniz.', 'category' => 'spor', 'location' => 'Caddebostan Sahili', 'start_dt' => '2026-07-06 09:00:00'),
            array('slug' => 'yapay-zeka-ve-sanat-soylesisi', 'title' => 'Yapay Zeka ve Sanat Soylesisi', 'excerpt' => 'Teknoloji ve sanat uzerine mezun soylesisi.', 'content' => 'Etkinlik detaylarini buradan duzenleyebilirsiniz.', 'category' => 'soylesi', 'location' => 'Online', 'start_dt' => '2026-08-22 20:30:00'),
            array('slug' => 'siyah-beyaz-istanbul-fotograf-atolyesi', 'title' => 'Siyah Beyaz Istanbul Fotograf Atolyesi', 'excerpt' => 'Fotograf grubundan uygulamali atolye.', 'content' => 'Etkinlik detaylarini buradan duzenleyebilirsiniz.', 'category' => 'atolye', 'location' => 'Beyoglu', 'start_dt' => '2026-09-12 14:00:00'),
        );
    }

    private static function upsert_pages(&$report, $force_reseed)
    {
        $ids = array();
        foreach (self::page_seed_data() as $slug => $page) {
            $status  = 'skipped';
            $post_id = self::upsert_post('page', $slug, $page, $force_reseed, $status);

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
        return $ids;
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

    private static function upsert_post($post_type, $slug, $payload, $force_update, &$result_status)
    {
        $result_status = 'skipped';
        $slug          = sanitize_title($slug);
        $existing      = get_posts(array(
            'post_type'      => $post_type,
            'name'           => $slug,
            'post_status'    => array('publish', 'draft', 'pending', 'future', 'private'),
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ));

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

        $main_menu_id   = self::get_or_create_menu('ODTUMIST Main Menu');
        $footer_menu_id = self::get_or_create_menu('ODTUMIST Footer Menu');

        if ($main_menu_id < 1 || $footer_menu_id < 1) {
            $report['warnings'][] = __('Menu olusturma adimi atlandi.', 'odtumist-eb');
            return;
        }

        $should_rebuild_main   = $rebuild_menus || !self::menu_has_items($main_menu_id);
        $should_rebuild_footer = $rebuild_menus || !self::menu_has_items($footer_menu_id);

        if ($should_rebuild_main) {
            self::clear_menu_items($main_menu_id);
            self::fill_main_menu($main_menu_id, $page_ids);
            $report['messages'][] = __('Ana menu guncellendi.', 'odtumist-eb');
        } else {
            $report['messages'][] = __('Ana menu korunarak birakildi (ezilmedi).', 'odtumist-eb');
        }

        if ($should_rebuild_footer) {
            self::clear_menu_items($footer_menu_id);
            self::fill_footer_menu($footer_menu_id, $page_ids);
            $report['messages'][] = __('Footer menu guncellendi.', 'odtumist-eb');
        } else {
            $report['messages'][] = __('Footer menu korunarak birakildi (ezilmedi).', 'odtumist-eb');
        }

        self::apply_menu_locations($main_menu_id, $footer_menu_id);
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
                    array('title' => 'Neler Yapiyoruz?', 'url' => $about_url . '#neler-yapiyoruz'),
                    array('title' => 'Calisma Gruplarimiz', 'url' => $about_url . '#calisma-gruplarimiz'),
                    array('title' => 'Sen de Katil Hocam!', 'url' => $about_url . '#sen-de-katil'),
                    array('title' => 'Tarihce', 'url' => $about_url . '#tarihce'),
                    array('title' => 'Yonetim', 'url' => $about_url . '#yonetim'),
                ),
            ),
            array('title' => 'ETKINLIKLER', 'object' => 'page', 'id' => $page_ids['etkinlikler'], 'children' => array()),
            array(
                'title'  => 'UYELIK',
                'object' => 'page',
                'id'     => $page_ids['uyelik'],
                'children' => array(
                    array('title' => 'Neden Uye Olmaliyim?', 'url' => $membership_url . '#neden-uye-olmaliyim'),
                    array('title' => 'Bilgi Guncelleme', 'url' => $membership_url . '#bilgi-guncelleme'),
                    array('title' => 'Aidat Odeme', 'url' => $membership_url . '#aidat-odeme'),
                    array('title' => 'Uyelik Avantajlari', 'url' => $membership_url . '#uyelik-avantajlari'),
                ),
            ),
            array(
                'title'  => 'DAYANISMA',
                'object' => 'page',
                'id'     => $page_ids['dayanisma'],
                'children' => array(
                    array('title' => 'Networking', 'url' => $solidarity_url . '#networking'),
                    array('title' => 'Burs', 'url' => $solidarity_url . '#burs'),
                    array('title' => 'Maraton', 'url' => $solidarity_url . '#maraton'),
                    array('title' => 'Mentorluk', 'url' => $solidarity_url . '#mentorluk'),
                ),
            ),
            array('title' => 'ILETISIM', 'object' => 'page', 'id' => $page_ids['iletisim'], 'children' => array()),
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
            array('title' => 'Hakkimizda', 'id' => $page_ids['hakkimizda']),
            array('title' => 'Etkinlikler', 'id' => $page_ids['etkinlikler']),
            array('title' => 'Uyelik', 'id' => $page_ids['uyelik']),
            array('title' => 'Dayanisma', 'id' => $page_ids['dayanisma']),
            array('title' => 'Iletisim', 'id' => $page_ids['iletisim']),
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

    private static function apply_menu_locations($main_menu_id, $footer_menu_id)
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

        $report['messages'][] = __('Elementor temel ayarlari uygulandi.', 'odtumist-eb');
    }

    private static function seed_elementor_pages($page_ids, &$report, $force_reseed)
    {
        if (!class_exists('Elementor\\Plugin')) {
            return;
        }

        $blueprints = self::page_elementor_blueprints($page_ids);
        foreach ($blueprints as $slug => $payload) {
            if (empty($page_ids[$slug])) {
                continue;
            }

            $page_id = (int) $page_ids[$slug];
            if (!$force_reseed && self::is_elementor_data_present($page_id)) {
                $report['messages'][] = sprintf('Elementor duzeni korundu (ezilmedi): %s', $payload['title']);
                continue;
            }

            $data_json = wp_json_encode(self::build_elementor_document($payload['widgets']));
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

    private static function is_elementor_data_present($post_id)
    {
        $data = get_post_meta($post_id, '_elementor_data', true);
        return is_string($data) && trim($data) !== '' && trim($data) !== '[]';
    }

    private static function page_elementor_blueprints($page_ids)
    {
        $home_widgets = array(
            self::build_widget('shortcode', array('shortcode' => '[odtumist_frontpage]')),
        );

        $about_widgets = array(
            self::build_widget('shortcode', array('shortcode' => '[odtumist_about_layout]')),
        );

        $events_widgets = array(
            self::build_widget('shortcode', array('shortcode' => '[odtumist_events_layout]')),
        );

        $membership_widgets = array(
            self::build_widget('shortcode', array('shortcode' => '[odtumist_membership_layout]')),
        );

        $solidarity_widgets = array(
            self::build_widget('shortcode', array('shortcode' => '[odtumist_solidarity_layout]')),
        );

        $contact_widgets = array(
            self::build_widget('shortcode', array('shortcode' => '[odtumist_contact_layout]')),
        );

        $news_widgets = array(
            self::build_widget('heading', array('title' => 'Haberler', 'size' => 'xxl', 'align' => 'left')),
            self::build_widget('text-editor', array('editor' => '<p>Bu sayfa WordPress yazilarini listeler. Haberleri Yazilar menusu altindan yonetebilirsin.</p>')),
            self::build_widget('shortcode', array('shortcode' => '[odtumist_events_grid limit="6"]')),
        );

        return array(
            'anasayfa'   => array('title' => 'Anasayfa', 'widgets' => $home_widgets),
            'hakkimizda' => array('title' => 'Hakkimizda', 'widgets' => $about_widgets),
            'etkinlikler' => array('title' => 'Etkinlikler', 'widgets' => $events_widgets),
            'uyelik'     => array('title' => 'Uyelik', 'widgets' => $membership_widgets),
            'dayanisma'  => array('title' => 'Dayanisma', 'widgets' => $solidarity_widgets),
            'iletisim'   => array('title' => 'Iletisim', 'widgets' => $contact_widgets),
            'haberler'   => array('title' => 'Haberler', 'widgets' => $news_widgets),
        );
    }

    private static function build_elementor_document($widgets)
    {
        return array(
            self::build_section($widgets),
        );
    }

    private static function build_section($widgets)
    {
        return array(
            'id'       => self::rand_id(),
            'elType'   => 'section',
            'settings' => array(
                'layout'        => 'full_width',
                'content_width' => 'boxed',
                'gap'           => 'default',
                'padding'       => array(
                    'unit'     => 'px',
                    'top'      => '60',
                    'right'    => '0',
                    'bottom'   => '60',
                    'left'     => '0',
                    'isLinked' => false,
                ),
            ),
            'elements' => array(
                self::build_column($widgets),
            ),
        );
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
            return '<p class="empty-state">Henuz yayinlanmis etkinlik bulunmuyor.</p>';
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
                            <a class="event-more" href="<?php the_permalink(); ?>">Detaylari Incele</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
        <?php
        wp_reset_postdata();
        return (string) ob_get_clean();
    }

    public static function shortcode_working_groups_grid($atts)
    {
        $atts = shortcode_atts(array(
            'limit' => 8,
        ), $atts, 'odtumist_working_groups_grid');

        $limit = max(1, min(24, absint($atts['limit'])));
        $query = new WP_Query(array(
            'post_type'      => 'team',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));

        if (!$query->have_posts()) {
            return '<p class="empty-state">Henuz yayinlanmis calisma grubu bulunmuyor.</p>';
        }

        ob_start();
        ?>
        <div class="about-groups-grid">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <article class="about-group-card">
                    <a class="about-group-media" href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
                        <?php else : ?>
                            <img src="https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&q=80&w=800" alt="<?php the_title_attribute(); ?>" loading="lazy">
                        <?php endif; ?>
                        <span class="about-group-badge">Kesfet</span>
                    </a>
                    <div class="about-group-body">
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p><?php echo esc_html(get_the_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 20)); ?></p>
                        <a class="about-group-link" href="<?php the_permalink(); ?>">Detayli Incele &rarr;</a>
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
            return '<p class="empty-state">Iletisim birimleri henuz tanimlanmamis.</p>';
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
        $map_url = get_theme_mod('odtumist_contact_map_url', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3008.38870198594!2d29.02340337656644!3d41.06047247134375!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab660f7e4f35b%3A0x868b20463c630807!2zT0RUw5xNwLBTVCBWacWfbmVsaWsgVGVzaXNsZXJp!5e0!3m2!1str!2str!4v1700000000000!5m2!1str!2str');

        return '<div class="contact-map-wrap"><iframe src="' . esc_url($map_url) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="ODTUMIST Lokasyon"></iframe></div>';
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
            $raw_shortcode = '[contact-form-7 id="123" title="Iletisim Formu"]';
        }

        $fallback_html = do_shortcode($raw_shortcode);
        if (trim((string) $fallback_html) !== '' && trim((string) $fallback_html) !== $raw_shortcode) {
            return (string) $fallback_html;
        }

        return '<p>Form kurulumu tamamlanmadi. WPForms veya Contact Form 7 eklentisinden bir form olusturup bu alana baglayabilirsin.</p>';
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
                'title'    => 'Yonetim Kurulu & Uyelik',
                'subtitle' => 'Uyelik surecleri ve genel iletisim',
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

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('ODTUMIST Elementor Bootstrap', 'odtumist-eb'); ?></h1>
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
            </ul>
        </div>
        <?php
    }
}

ODTUMIST_Elementor_Bootstrap::init();
