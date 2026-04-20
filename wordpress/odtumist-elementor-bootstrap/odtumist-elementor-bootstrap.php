<?php
/**
 * Plugin Name: ODTUMIST Elementor Bootstrap
 * Description: Elementor Pro odaklı ODTÜMİST kurulumu için sayfa, menü, CPT ve temel içerikleri tek tıkla oluşturur/günceller.
 * Version: 1.1.1
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
                    'name'          => __('Çalışma Grupları', 'odtumist-eb'),
                    'singular_name' => __('Çalışma Grubu', 'odtumist-eb'),
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
            'elementor_full_mode' => !empty($_POST['elementor_full_mode']),
            'sync_group_cards' => !empty($_POST['sync_group_cards']),
            'sync_event_cards' => !empty($_POST['sync_event_cards']),
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
     * WP-CLI: wp odtumist bootstrap [--force=1] [--elementor=1] [--full=1] [--sync-groups=1] [--sync-events=1] [--menus=1] [--cleanup=1]
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

        self::ensure_permalink_structure($report);
        $page_ids = self::upsert_pages($report, (bool) $options['force_reseed']);
        self::upsert_teams($report, (bool) $options['force_reseed']);
        self::upsert_events($report, (bool) $options['force_reseed']);
        self::build_menus($page_ids, $report, (bool) $options['rebuild_menus']);
        self::apply_reading_settings($page_ids, $report);
        self::ensure_home_slider_theme_mods($page_ids, $report, (bool) $options['force_reseed']);

        if ($options['apply_elementor']) {
            if ($options['elementor_full_mode']) {
                update_option('odtumist_lock_templates', false);
                $report['messages'][] = 'Tema template kilidi kapatildi (Elementor duzenleme serbest).';
            }
            self::apply_elementor_defaults($report);
            self::seed_elementor_pages(
                $page_ids,
                $report,
                (bool) $options['force_reseed'],
                (bool) $options['elementor_full_mode']
            );
            if ($options['sync_group_cards'] && $options['elementor_full_mode']) {
                self::sync_group_cards_into_elementor_pages($page_ids, $report);
            }
            if ($options['sync_event_cards'] && $options['elementor_full_mode']) {
                self::sync_event_cards_into_elementor_pages($page_ids, $report);
            }
            self::clear_elementor_runtime_cache($report);
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
                'title'   => 'Hakkımızda',
                'excerpt' => "İstanbul'un dinamizminde ODTÜ ruhunu, dayanışmasını ve kültürünü yaşatan topluluğumuza hoş geldin.",
                'content' => "<h2 id=\"neler-yapiyoruz\">Neler Yapıyoruz?</h2>\n<p>Üyelerimizi, gönüllülerimizi ve destekçilerimizi aynı dayanışma ağında buluşturuyoruz.</p>\n<h2 id=\"calisma-gruplarimiz\">Çalışma Gruplarımız</h2>\n<p>Edebiyat, felsefe, fotoğraf, sosyal komite, burs, İK & üye geliştirme, spor & maraton dahil farklı alanlarda üretiyoruz.</p>\n<h2 id=\"sen-de-katil\">Sen de Katıl Hocam!</h2>\n<p>Üyelik ve gönüllülük ile topluluğumuza katılabilirsin.</p>\n<h2 id=\"tarihce\">Tarihçe</h2>\n<p>ODTÜMİST, uzun yıllara dayanan bir mezun dayanışma yapısıdır.</p>\n<h2 id=\"yonetim\">Yönetim</h2>\n<p>Yönetim ve kurul bilgileri düzenli olarak güncellenir.</p>",
            ),
            'neler-yapiyoruz' => array(
                'title'   => 'Neler Yapıyoruz?',
                'excerpt' => '',
                'content' => '<p>Üyelerimizi, gönüllülerimizi, bursiyerlerimizi ve destekçilerimizi aynı dayanışma ağında buluşturuyoruz.</p>',
                'parent'  => 'hakkimizda',
            ),
            'calisma-gruplarimiz' => array(
                'title'   => 'Çalışma Gruplarımız',
                'excerpt' => '',
                'content' => '<p>Edebiyat, felsefe, fotoğraf, sosyal komite, burs, İK & üye geliştirme ve spor-maraton dahil farklı alanlarda birlikte üretiyoruz.</p>',
                'parent'  => 'hakkimizda',
            ),
            'sen-de-katil' => array(
                'title'   => 'Sen de Katıl Hocam!',
                'excerpt' => '',
                'content' => '<p>Üyelik ve gönüllülük ile topluluğumuza katılabilir, çalışma gruplarımızda aktif rol alabilirsin.</p>',
                'parent'  => 'hakkimizda',
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
            'etkinlikler' => array(
                'title'   => 'Etkinlikler',
                'excerpt' => 'Takvimdeki etkinlikleri inceleyebilir, detay sayfalarından kayıt ve katılım bilgilerine ulaşabilirsin.',
                'content' => '<p>Etkinlik kartları "Etkinlikler" içerik tipinden otomatik çekilir.</p>',
            ),
            'uyelik' => array(
                'title'   => 'Üyelik',
                'excerpt' => 'ODTÜMİST üyeliği; dayanışma, aidiyet ve öğrencilere uzanan etkiyi büyüten güçlü bir topluluk çatısıdır.',
                'content' => "<h2 id=\"neden-uye-olmaliyim\">Neden Üye Olmalıyım?</h2>\n<p>Dayanışma ağımıza katılmak için üyelik başvurusu yapabilirsin.</p>\n<h2 id=\"bilgi-guncelleme\">Bilgi Güncelleme</h2>\n<p>Mezun bilgi alanlarını güncel tutman iletişimi güçlendirir.</p>\n<h2 id=\"aidat-odeme\">Aidat Ödeme</h2>\n<p>Aidat işlemleri dijital olarak takip edilir.</p>\n<h2 id=\"uyelik-avantajlari\">Üyelik Avantajları</h2>\n<p>Etkinlik, mentorluk ve güçlü mezun ağı imkânları sunulur.</p>",
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
            'dayanisma' => array(
                'title'   => 'Dayanışma',
                'excerpt' => 'ODTÜ mezunu olmanın getirdiği bağ, ODTÜMİST çatısı altında ortak bir etki alanına dönüşüyor.',
                'content' => "<h2 id=\"networking\">Networking</h2>\n<p>Mezunlar arası profesyonel bağlar güçlenir.</p>\n<h2 id=\"burs\">Burs Programları</h2>\n<p>Öğrencilere sürekli burs desteği sağlanır.</p>\n<h2 id=\"maraton\">Maraton & Spor</h2>\n<p>İyilik için koşu ve dayanışma etkinlikleri düzenlenir.</p>\n<h2 id=\"mentorluk\">Mentorluk</h2>\n<p>Mezunlar, öğrenci ve yeni mezunlara mentorluk sağlar.</p>\n<h2 id=\"bursiyerler\">Bursiyerler</h2>\n<p>Bursiyer öğrencilerimiz ile mezunlarımız arasında sürekli iletişim ve dayanışma köprüsü kuruyoruz.</p>\n<h2 id=\"gonulluler\">Gönüllüler</h2>\n<p>Projelerimizin sahada büyümesini sağlayan gönüllü ağımızı birlikte güçlendiriyoruz.</p>\n<h2 id=\"bagiscilar\">Bağışçılar</h2>\n<p>Destekçilerimizin katkısıyla burs, etkinlik ve sosyal etki çalışmalarımızı sürdürüyoruz.</p>\n<h2 id=\"paydaslar\">Paydaşlarımız</h2>\n<p>Üniversiteler, kurumlar ve sivil toplum paydaşlarımızla ortak üreterek etkimizin kapsamını büyütüyoruz.</p>",
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
                'content' => '<p>Öğrencilerimizin eğitim hayatına kesintisiz devam edebilmesi için burs dayanışmamızı büyütüyoruz.</p>',
                'parent'  => 'dayanisma',
            ),
            'maraton' => array(
                'title'   => 'Maraton',
                'excerpt' => '',
                'content' => '<p>İyilik için koşu ve spor etkinlikleri ile burs fonunu güçlendiriyoruz.</p>',
                'parent'  => 'dayanisma',
            ),
            'mentorluk' => array(
                'title'   => 'Mentorluk',
                'excerpt' => '',
                'content' => '<p>Mezunlarımızın birikimini öğrenci ve yeni mezunlara aktararak kariyer yolculuğuna destek oluyoruz.</p>',
                'parent'  => 'dayanisma',
            ),
            'bursiyerler' => array(
                'title'   => 'Bursiyerler',
                'excerpt' => '',
                'content' => '<p>Bursiyer öğrencilerimizle sürekli iletişim ve gelişim odaklı dayanışmanın bir parçası oluyoruz.</p>',
                'parent'  => 'dayanisma',
            ),
            'gonulluler' => array(
                'title'   => 'Gönüllüler',
                'excerpt' => '',
                'content' => '<p>Gönüllü ağımızla etkinlik, burs ve mentorluk çalışmalarımızın saha operasyonunu birlikte yürütüyoruz.</p>',
                'parent'  => 'dayanisma',
            ),
            'bagiscilar' => array(
                'title'   => 'Bağışçılar',
                'excerpt' => '',
                'content' => '<p>Bağışçılarımızın desteğiyle öğrencilere uzanan etkimizi sürdürüyor, yeni dayanışma modelleri geliştiriyoruz.</p>',
                'parent'  => 'dayanisma',
            ),
            'paydaslar' => array(
                'title'   => 'Paydaşlarımız',
                'excerpt' => '',
                'content' => '<p>Paydaş kurumlarla birlikte ürettiğimiz projeler, toplumsal fayda alanımızı her yıl daha da genişletiyor.</p>',
                'parent'  => 'dayanisma',
            ),
            'iletisim' => array(
                'title'   => 'İletişim',
                'excerpt' => "İstanbul'daki ODTÜ ruhunun merkezi ODTÜPARK'ta sizleri bekliyoruz.",
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
        $ids = array();
        foreach ($seed_data as $slug => $page) {
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
                    array('title' => 'Neler Yapıyoruz?', 'slug' => 'neler-yapiyoruz', 'url' => $about_url . '#neler-yapiyoruz'),
                    array('title' => 'Çalışma Gruplarımız', 'slug' => 'calisma-gruplarimiz', 'url' => $about_url . '#calisma-gruplarimiz'),
                    array('title' => 'Sen de Katıl Hocam!', 'slug' => 'sen-de-katil', 'url' => $about_url . '#sen-de-katil'),
                    array('title' => 'Tarihçe', 'slug' => 'tarihce', 'url' => $about_url . '#tarihce'),
                    array('title' => 'Yönetim', 'slug' => 'yonetim', 'url' => $about_url . '#yonetim'),
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
                ),
            ),
            array(
                'title'  => 'DAYANIŞMA',
                'object' => 'page',
                'id'     => $page_ids['dayanisma'],
                'children' => array(
                    array('title' => 'Networking', 'slug' => 'networking', 'url' => $solidarity_url . '#networking'),
                    array('title' => 'Burs', 'slug' => 'burs', 'url' => $solidarity_url . '#burs'),
                    array('title' => 'Maraton', 'slug' => 'maraton', 'url' => $solidarity_url . '#maraton'),
                    array('title' => 'Mentorluk', 'slug' => 'mentorluk', 'url' => $solidarity_url . '#mentorluk'),
                    array('title' => 'Bursiyerler', 'slug' => 'bursiyerler', 'url' => $solidarity_url . '#bursiyerler'),
                    array('title' => 'Gönüllüler', 'slug' => 'gonulluler', 'url' => $solidarity_url . '#gonulluler'),
                    array('title' => 'Bağışçılar', 'slug' => 'bagiscilar', 'url' => $solidarity_url . '#bagiscilar'),
                    array('title' => 'Paydaşlarımız', 'slug' => 'paydaslar', 'url' => $solidarity_url . '#paydaslar'),
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
        foreach ($blueprints as $slug => $payload) {
            if (empty($page_ids[$slug])) {
                continue;
            }

            $page_id = (int) $page_ids[$slug];
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

        if (!empty($page_ids['hakkimizda'])) {
            // Hakkimizda/Calisma Gruplari sekmesinde de tum yayinlanmis gruplari goster.
            $about_sections = self::build_card_sections_for_post_type('team', -1, $fallback, false, false, 'odt-el-about-groups-row');
            if (!empty($about_sections)) {
                $about_synced = self::sync_elementor_section_rows(
                    (int) $page_ids['hakkimizda'],
                    array('odt-el-about-groups-row', 'odt-el-about-groups-dynamic'),
                    'odt-el-about-groups-intro',
                    $about_sections
                );
                if ($about_synced) {
                    $report['messages'][] = 'Hakkimizda calisma grubu kartlari shortcode yerine Elementor kartlarina tasindi.';
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
        return is_string($data) && trim($data) !== '' && trim($data) !== '[]';
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

        $about_id       = !empty($page_ids['hakkimizda']) ? (int) $page_ids['hakkimizda'] : 0;
        $about_excerpt  = self::get_page_excerpt_or_default($about_id, "İstanbul'un dinamizminde ODTÜ ruhunu, dayanışmasını ve kültürünü yaşatan topluluğumuza hoş geldin.");
        $about_sections = self::extract_sections_from_page($about_id);
        $news_url       = !empty($page_ids['haberler']) ? get_permalink((int) $page_ids['haberler']) : home_url('/haberler/');

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
            '<p>Uzmanlık alanlarına göre ayrılan çalışma gruplarımızla birlikte üretiyor, etkinlik ve projeler geliştiriyoruz.</p>'
        );
        $join_section = $get_about_section(
            array('sen-de-katil'),
            'Sen de Katıl Hocam!',
            '<p>Üyelik ve gönüllülük ile topluluğumuza katılabilir, çalışma gruplarımızda aktif rol alabilirsin.</p>'
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
                        self::build_widget('heading', array('title' => 'MERHABA HOCAM!', 'size' => 'xxl', 'align' => 'center', '_css_classes' => 'odt-el-about-hero-title')),
                        self::build_widget('text-editor', array('editor' => '<p style="text-align:center">' . esc_html($about_excerpt) . '</p>', '_css_classes' => 'odt-el-about-hero-subtitle')),
                        self::build_widget('text-editor', array('editor' => '<p style="text-align:center">&#10084; Dayanışma gücümüzdür.</p>', '_css_classes' => 'odt-el-about-motto')),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-about-hero')
        );

        $about_nav_links = array(
            array('label' => 'Neler Yapıyoruz?', 'anchor' => '#neler-yapiyoruz'),
            array('label' => 'Çalışma Gruplarımız', 'anchor' => '#calisma-gruplarimiz'),
            array('label' => 'Sen de Katıl Hocam!', 'anchor' => '#sen-de-katil'),
            array('label' => 'Tarihçe', 'anchor' => '#tarihce'),
            array('label' => 'Yönetim', 'anchor' => '#yonetim'),
        );
        $about_nav_columns = array();
        foreach ($about_nav_links as $nav_item) {
            $about_nav_columns[] = array(
                'size' => 20,
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
                        self::build_widget('heading', array('title' => 'Çalışma Gruplarımız', 'size' => 'xl', 'align' => 'center', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => '<p style="text-align:center">Uzmanlık alanlarına veya ilgi alanlarına göre ayrılmış gruplarımızda birlikte üretiyoruz.</p>', '_css_classes' => 'odt-el-subtitle')),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-about-groups-intro')
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
            array('anchor' => 'sen-de-katil', 'label' => 'Sen de Katıl Hocam!'),
            'groups'
        );

        $about_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 100,
                    'widgets' => array(
                        self::build_widget('menu-anchor', array('anchor' => 'sen-de-katil')),
                        self::build_widget('heading', array('title' => 'ODTÜ Ruhunu Birlikte Yaşatalım', 'size' => 'xl', 'align' => 'center', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => (string) $join_section['body'], '_css_classes' => 'odt-el-richtext')),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-about-panel odt-el-about-panel-sen-de-katil')
        );

        $about_doc[] = self::build_section_with_columns(
            array(
                array(
                    'size' => 50,
                    'settings' => array('_css_classes' => 'odt-el-about-join-card odt-el-about-join-card-blue'),
                    'widgets' => array(
                        self::build_widget('heading', array('title' => '👤', 'size' => 'xl', 'align' => 'center', '_css_classes' => 'odt-el-about-join-icon')),
                        self::build_widget('heading', array('title' => 'Üye Ol', 'size' => 'xl', 'align' => 'center', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => '<p style="text-align:center">İstanbul\'da ODTÜ ruhunu yeniden keşfet, dayanışma ağının bir parçası ol.</p>', '_css_classes' => 'odt-el-subtitle')),
                        self::build_widget('button', array('text' => 'ÜYE OL', 'link' => array('url' => $links['membership_ext']), 'align' => 'center', '_css_classes' => 'odt-el-btn odt-el-btn-primary odt-el-about-join-btn')),
                    ),
                ),
                array(
                    'size' => 50,
                    'settings' => array('_css_classes' => 'odt-el-about-join-card odt-el-about-join-card-red'),
                    'widgets' => array(
                        self::build_widget('heading', array('title' => '&#10084;', 'size' => 'xl', 'align' => 'center', '_css_classes' => 'odt-el-about-join-icon')),
                        self::build_widget('heading', array('title' => 'Gönüllü Ol', 'size' => 'xl', 'align' => 'center', '_css_classes' => 'odt-el-title')),
                        self::build_widget('text-editor', array('editor' => '<p style="text-align:center">Etkinliklerden burs ve mentorluğa kadar birçok alanda katkı sağlayabilirsin.</p>', '_css_classes' => 'odt-el-subtitle')),
                        self::build_widget('button', array('text' => 'GÖNÜLLÜ OL', 'link' => array('url' => $links['contact']), 'align' => 'center', '_css_classes' => 'odt-el-btn odt-el-btn-secondary odt-el-about-join-btn')),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-about-join-actions')
        );
        $about_doc[] = self::build_about_tab_pagination_section(
            array('anchor' => 'calisma-gruplarimiz', 'label' => 'Çalışma Gruplarımız'),
            array('anchor' => 'tarihce', 'label' => 'Tarihçe'),
            'join'
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
            array('anchor' => 'sen-de-katil', 'label' => 'Sen de Katıl Hocam!'),
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
                'url' => $links['about'] . '#yonetim',
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
                'title' => 'Geçmiş Yönetimler',
                'desc' => '1986\'dan bugüne derneğimize emek vermiş tüm kurullarımız ve yöneticilerimiz.',
                'icon' => '&#128336;',
                'url' => $links['about'] . '#tarihce',
                'accent' => 'dark',
            ),
            array(
                'title' => 'Dernek Tüzüğü ve Yönetmelikler',
                'desc' => 'Şeffaf yönetişim ilkelerimiz, tüzüğümüz ve çalışma yönetmeliklerimiz.',
                'icon' => '&#128196;',
                'url' => $news_url,
                'accent' => 'blue',
            ),
            array(
                'title' => 'Faaliyet Raporları',
                'desc' => 'Yıllık çalışma raporlarımız, mali tablolarımız ve kurumsal başarı hikayelerimiz.',
                'icon' => '&#128188;',
                'url' => $news_url,
                'accent' => 'red',
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
        $events_doc = array_merge($events_doc, self::build_card_sections_for_post_type(
            'event',
            12,
            'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=900',
            true,
            false,
            'odt-el-events-page-row'
        ));

        $membership_id      = !empty($page_ids['uyelik']) ? (int) $page_ids['uyelik'] : 0;
        $membership_excerpt = self::get_page_excerpt_or_default($membership_id, 'ODTÜMİST üyeliği; dayanışma, aidiyet ve öğrencilere uzanan etkiyi büyüten güçlü bir topluluk çatısıdır.');
        $membership_doc     = array(
            self::build_section_with_columns(
                array(
                    array(
                        'size' => 100,
                        'widgets' => array(
                            self::build_widget('heading', array('title' => 'Üyelik', 'size' => 'xxl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                            self::build_widget('text-editor', array('editor' => '<p>' . esc_html($membership_excerpt) . '</p>', '_css_classes' => 'odt-el-subtitle')),
                        ),
                    ),
                ),
                array('_css_classes' => 'odt-el odt-el-section odt-el-page-hero odt-el-membership-hero')
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
                'text'  => 'FONZİP\'E GİRİŞ',
                'url'   => 'https://fonzip.com/odtumist/login',
                'class' => 'odt-el-btn odt-el-btn-primary',
            ),
        );
        $membership_panel_emojis = array(
            'neden-uye-olmaliyim' => '🤝',
            'uyelik-avantajlari'  => '🎁',
            'bilgi-guncelleme'    => '📝',
            'aidat-odeme'         => '💳',
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
                            'link' => array('url' => 'https://fonzip.com/odtumist/login'),
                            'align' => 'right',
                            '_css_classes' => 'odt-el-btn odt-el-btn-secondary',
                        )),
                    ),
                ),
            ),
            array('_css_classes' => 'odt-el odt-el-section odt-el-membership-actions')
        );

        $solidarity_id      = !empty($page_ids['dayanisma']) ? (int) $page_ids['dayanisma'] : 0;
        $solidarity_excerpt = self::get_page_excerpt_or_default($solidarity_id, 'ODTÜ mezunu olmanın getirdiği bağ, ODTÜMİST çatısı altında ortak bir etki alanına dönüşüyor.');
        $solidarity_doc     = array(
            self::build_section_with_columns(
                array(
                    array(
                        'size' => 100,
                        'widgets' => array(
                            self::build_widget('heading', array('title' => 'Dayanışma', 'size' => 'xxl', 'align' => 'left', '_css_classes' => 'odt-el-title')),
                            self::build_widget('text-editor', array('editor' => '<p>' . esc_html($solidarity_excerpt) . '</p>', '_css_classes' => 'odt-el-subtitle')),
                        ),
                    ),
                ),
                array('_css_classes' => 'odt-el odt-el-section odt-el-page-hero odt-el-solidarity-hero')
            ),
        );
        $solidarity_sections = self::extract_sections_from_page($solidarity_id);
        $solidarity_icons = array(
            'networking' => '🌐',
            'burs' => '🎓',
            'maraton' => '🏃',
            'mentorluk' => '☕',
            'bursiyerler' => '👥',
            'gonulluler' => '❤️',
            'bagiscilar' => '🤝',
            'paydaslar' => '🏢',
        );
        $solidarity_ctas = array(
            'networking' => array('text' => 'AĞA KATIL', 'url' => $links['contact'], 'class' => 'odt-el-btn odt-el-btn-secondary'),
            'burs' => array('text' => 'KEŞFET', 'url' => $links['donation_ext'], 'class' => 'odt-el-btn odt-el-btn-primary'),
            'maraton' => array('text' => 'DESTEKLE', 'url' => $links['donation_ext'], 'class' => 'odt-el-btn odt-el-btn-primary'),
            'mentorluk' => array('text' => 'KATIL', 'url' => $links['contact'], 'class' => 'odt-el-btn odt-el-btn-secondary'),
            'bursiyerler' => array('text' => 'MEZUN-ÖĞRENCİ DAYANIŞMASI', 'url' => $links['contact'], 'class' => 'odt-el-btn odt-el-btn-secondary'),
            'gonulluler' => array('text' => 'HAREKETE GEÇ', 'url' => $links['contact'], 'class' => 'odt-el-btn odt-el-btn-primary'),
            'bagiscilar' => array('text' => 'İNCELE', 'url' => $links['donation_ext'], 'class' => 'odt-el-btn odt-el-btn-secondary'),
            'paydaslar' => array('text' => 'KEŞFET', 'url' => $links['contact'], 'class' => 'odt-el-btn odt-el-btn-secondary'),
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
        $contact_excerpt = self::get_page_excerpt_or_default($contact_id, "İstanbul'daki ODTÜ ruhunun merkezi ODTÜPARK'ta sizleri bekliyoruz.");
        $contact         = self::get_contact_fields();
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
                                'editor' => '<p><strong>Adres:</strong> ' . esc_html($contact['address']) . '</p>'
                                    . '<p><strong>Telefon:</strong> ' . esc_html($contact['phone']) . '</p>'
                                    . '<p><strong>E-posta:</strong> <a href="mailto:' . esc_attr($contact['email']) . '">' . esc_html($contact['email']) . '</a></p>',
                                '_css_classes' => 'odt-el-richtext',
                            )),
                            self::build_widget('shortcode', array('shortcode' => '[odtumist_contact_departments]', '_css_classes' => 'odt-el-contact-depts')),
                        ),
                    ),
                    array(
                        'size' => 50,
                        'widgets' => array(
                            self::build_widget('html', array(
                                'html' => '<iframe src="' . esc_url($contact['map_url']) . '" style="width:100%; min-height:360px; border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
                                '_css_classes' => 'odt-el-map',
                            )),
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

        return array(
            'anasayfa' => array('title' => 'Anasayfa', 'document' => $home_doc),
            'hakkimizda' => array('title' => 'Hakkımızda', 'document' => $about_doc),
            'etkinlikler' => array('title' => 'Etkinlikler', 'document' => $events_doc),
            'uyelik' => array('title' => 'Üyelik', 'document' => $membership_doc),
            'dayanisma' => array('title' => 'Dayanışma', 'document' => $solidarity_doc),
            'iletisim' => array('title' => 'İletişim', 'document' => $contact_doc),
            'haberler' => array('title' => 'Haberler', 'document' => $news_doc),
        );
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
            'address' => 'Levazım Mah. Koru Sok. Beşiktaş / İstanbul (ODTÜPARK)',
            'phone' => '+90 (212) 281 40 47',
            'email' => 'dernek@odtumist.org',
            'map_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3008.38870198594!2d29.02340337656644!3d41.06047247134375!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab660f7e4f35b%3A0x868b20463c630807!2zT0RUw5xNwLBTVCBWacWfbmVsaWsgVGVzaXNsZXJp!5e0!3m2!1str!2str!4v1700000000000!5m2!1str!2str',
        );
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

        $query_orderby = ($post_type === 'team') ? 'modified' : 'date';

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

        $query = new WP_Query(array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'orderby' => $query_orderby,
            'order' => 'DESC',
        ));

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

                $cards[] = array(
                    'title' => get_the_title(),
                    'description' => $description,
                    'image' => $image,
                    'url' => get_permalink($post_id),
                );
            }
            wp_reset_postdata();
        }

        if (empty($cards)) {
            return $sections;
        }

        $rows = $single_row ? array($cards) : array_chunk($cards, 3);
        $card_type_class = $is_event ? 'odt-el-event-card' : 'odt-el-group-card';
        foreach ($rows as $row) {
            $columns = array();
            $col_count = count($row);
            $col_size = $col_count > 0 ? (int) floor(100 / $col_count) : 100;

            foreach ($row as $card) {
                $columns[] = array(
                    'size' => $col_size,
                    'widgets' => array(
                        self::build_widget('image-box', array(
                            'image' => array('url' => (string) $card['image'], 'id' => ''),
                            'title_text' => (string) $card['title'],
                            'description_text' => (string) $card['description'],
                            'link' => array('url' => (string) $card['url']),
                            'position' => 'top',
                            'align' => 'left',
                            '_css_classes' => 'odt-el-card ' . $card_type_class,
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
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ));

        if (!$query->have_posts()) {
            return '<p class="empty-state">Henüz yayınlanmış çalışma grubu bulunmuyor.</p>';
        }

        ob_start();
        ?>
        <div class="about-groups-grid">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <article class="about-group-card">
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
        $map_url = get_theme_mod('odtumist_contact_map_url', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3008.38870198594!2d29.02340337656644!3d41.06047247134375!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab660f7e4f35b%3A0x868b20463c630807!2zT0RUw5xNwLBTVCBWacWfbmVsaWsgVGVzaXNsZXJp!5e0!3m2!1str!2str!4v1700000000000!5m2!1str!2str');

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
