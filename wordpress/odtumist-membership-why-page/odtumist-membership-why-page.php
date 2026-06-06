<?php
/**
 * Plugin Name: ODTÜMİST Neden Üye Olmalıyım Sayfası
 * Description: Üyelik altında Elementor ile tamamen düzenlenebilir "Neden Üye Olmalıyım?" sayfasını güvenli şekilde oluşturur.
 * Version: 1.0.0
 * Author: Hüsamettin Gündüzoğlu
 * Text Domain: odtumist-membership-why
 */

if (!defined('ABSPATH')) {
    exit;
}

final class ODTUMIST_Membership_Why_Page
{
    const VERSION = '1.0.0';
    const TARGET_SLUG = 'neden-uye-olmaliyim';
    const PARENT_SLUG = 'uyelik';
    const PAGE_META = '_odtumist_membership_why_page';
    const NOTICE_TRANSIENT = '_odtumist_membership_why_notice';

    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'register_admin_page'));
        add_action('admin_post_odtumist_membership_why_create', array(__CLASS__, 'handle_admin_create'));
        add_action('admin_notices', array(__CLASS__, 'render_admin_notice'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'), 20);
    }

    public static function activate()
    {
        $result = self::create_or_update_page(false);
        set_transient(self::NOTICE_TRANSIENT, $result, 60);
    }

    public static function register_admin_page()
    {
        add_management_page(
            __('ODTÜMİST Neden Üye', 'odtumist-membership-why'),
            __('ODTÜMİST Neden Üye', 'odtumist-membership-why'),
            'manage_options',
            'odtumist-membership-why-page',
            array(__CLASS__, 'render_admin_page')
        );
    }

    public static function render_admin_notice()
    {
        $notice = get_transient(self::NOTICE_TRANSIENT);
        if (!is_array($notice)) {
            return;
        }
        delete_transient(self::NOTICE_TRANSIENT);

        $type = !empty($notice['type']) ? sanitize_html_class((string) $notice['type']) : 'success';
        $message = !empty($notice['message']) ? (string) $notice['message'] : '';
        if ($message === '') {
            return;
        }

        printf(
            '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
            esc_attr($type),
            wp_kses_post($message)
        );
    }

    public static function render_admin_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $parent_id = self::get_membership_parent_id(false);
        $page = self::find_target_page($parent_id);
        $page_id = $page instanceof WP_Post ? (int) $page->ID : 0;
        $edit_url = $page_id > 0 ? get_edit_post_link($page_id, '') : '';
        $elementor_url = $page_id > 0 ? admin_url('post.php?post=' . $page_id . '&action=elementor') : '';
        $view_url = $page_id > 0 ? get_permalink($page_id) : '';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('ODTÜMİST Neden Üye Olmalıyım Sayfası', 'odtumist-membership-why'); ?></h1>
            <p><?php esc_html_e('Bu eklenti yalnızca Üyelik altındaki Neden Üye Olmalıyım sayfasını oluşturur. Eski bootstrap gibi tüm site içeriğini seed etmez.', 'odtumist-membership-why'); ?></p>

            <table class="widefat striped" style="max-width: 820px; margin: 20px 0;">
                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e('Hedef URL', 'odtumist-membership-why'); ?></th>
                        <td><code><?php echo esc_html(home_url('/' . self::PARENT_SLUG . '/' . self::TARGET_SLUG . '/')); ?></code></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Durum', 'odtumist-membership-why'); ?></th>
                        <td>
                            <?php if ($page_id > 0) : ?>
                                <?php esc_html_e('Sayfa bulundu.', 'odtumist-membership-why'); ?>
                                <?php if ($view_url) : ?>
                                    <a href="<?php echo esc_url($view_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Görüntüle', 'odtumist-membership-why'); ?></a>
                                <?php endif; ?>
                                <?php if ($edit_url) : ?>
                                    | <a href="<?php echo esc_url($edit_url); ?>"><?php esc_html_e('WP ile düzenle', 'odtumist-membership-why'); ?></a>
                                <?php endif; ?>
                                <?php if ($elementor_url) : ?>
                                    | <a href="<?php echo esc_url($elementor_url); ?>"><?php esc_html_e('Elementor ile düzenle', 'odtumist-membership-why'); ?></a>
                                <?php endif; ?>
                            <?php else : ?>
                                <?php esc_html_e('Sayfa henüz yok.', 'odtumist-membership-why'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom: 16px;">
                <?php wp_nonce_field('odtumist_membership_why_create'); ?>
                <input type="hidden" name="action" value="odtumist_membership_why_create">
                <input type="hidden" name="force" value="0">
                <?php submit_button(__('Sayfayı Güvenli Oluştur', 'odtumist-membership-why'), 'primary', 'submit', false); ?>
                <p class="description"><?php esc_html_e('Sayfa yoksa oluşturur. Mevcut Elementor içeriği varsa otomatik ezmez.', 'odtumist-membership-why'); ?></p>
            </form>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Bu işlem yalnızca Neden Üye Olmalıyım hedef sayfasının Elementor içeriğini yeniden kurar. Devam edilsin mi?');">
                <?php wp_nonce_field('odtumist_membership_why_create'); ?>
                <input type="hidden" name="action" value="odtumist_membership_why_create">
                <input type="hidden" name="force" value="1">
                <?php submit_button(__('Hedef Sayfayı Yeniden Kur', 'odtumist-membership-why'), 'secondary', 'submit', false); ?>
                <p class="description"><?php esc_html_e('Sadece bu hedef sayfayı overwrite eder; diğer sayfalara, menülere, yazılara veya çalışma gruplarına dokunmaz.', 'odtumist-membership-why'); ?></p>
            </form>
        </div>
        <?php
    }

    public static function handle_admin_create()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Yetkiniz yok.', 'odtumist-membership-why'));
        }
        check_admin_referer('odtumist_membership_why_create');

        $force = !empty($_POST['force']);
        $result = self::create_or_update_page($force);
        set_transient(self::NOTICE_TRANSIENT, $result, 60);

        wp_safe_redirect(admin_url('tools.php?page=odtumist-membership-why-page'));
        exit;
    }

    public static function enqueue_assets()
    {
        $page_id = self::get_runtime_page_id();
        if ($page_id <= 0 || !self::is_target_page($page_id)) {
            return;
        }

        $css_path = plugin_dir_path(__FILE__) . 'assets/css/membership-why.css';
        $js_path = plugin_dir_path(__FILE__) . 'assets/js/membership-why.js';
        $css_ver = file_exists($css_path) ? (string) filemtime($css_path) : self::VERSION;
        $js_ver = file_exists($js_path) ? (string) filemtime($js_path) : self::VERSION;

        wp_enqueue_style(
            'odtumist-membership-why-page',
            plugin_dir_url(__FILE__) . 'assets/css/membership-why.css',
            array(),
            $css_ver
        );
        wp_enqueue_script(
            'odtumist-membership-why-page',
            plugin_dir_url(__FILE__) . 'assets/js/membership-why.js',
            array(),
            $js_ver,
            true
        );
    }

    private static function create_or_update_page($force = false)
    {
        $force = (bool) $force;
        $parent_id = self::get_membership_parent_id(true);
        if ($parent_id <= 0) {
            return array(
                'type' => 'error',
                'message' => __('Üyelik üst sayfası bulunamadı ve oluşturulamadı.', 'odtumist-membership-why'),
            );
        }

        $existing = self::find_target_page($parent_id);
        if ($existing instanceof WP_Post && !$force && !self::can_safely_update_existing_page($existing)) {
            return array(
                'type' => 'warning',
                'message' => sprintf(
                    __('Neden Üye Olmalıyım sayfası zaten var; mevcut içeriği korumak için otomatik ezilmedi. Gerekirse Araçlar > ODTÜMİST Neden Üye ekranından sadece bu sayfayı yeniden kurabilirsiniz. <a href="%s">Elementor ile aç</a>', 'odtumist-membership-why'),
                    esc_url(admin_url('post.php?post=' . (int) $existing->ID . '&action=elementor'))
                ),
            );
        }

        $post_data = array(
            'post_title'   => 'Neden Üye Olmalıyım?',
            'post_name'    => self::TARGET_SLUG,
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_parent'  => $parent_id,
            'post_content' => '',
            'menu_order'   => 10,
        );

        if ($existing instanceof WP_Post) {
            $post_data['ID'] = (int) $existing->ID;
            $page_id = wp_update_post(wp_slash($post_data), true);
        } else {
            $page_id = wp_insert_post(wp_slash($post_data), true);
        }

        if (is_wp_error($page_id)) {
            return array(
                'type' => 'error',
                'message' => $page_id->get_error_message(),
            );
        }

        self::write_elementor_document((int) $page_id);
        update_post_meta((int) $page_id, self::PAGE_META, '1');
        update_post_meta((int) $page_id, '_wp_page_template', 'default');
        self::ensure_elementor_page_support();
        self::clear_elementor_cache();

        $message = $existing instanceof WP_Post
            ? __('Neden Üye Olmalıyım sayfası yeniden kuruldu.', 'odtumist-membership-why')
            : __('Neden Üye Olmalıyım sayfası oluşturuldu.', 'odtumist-membership-why');

        return array(
            'type' => 'success',
            'message' => $message . ' <a href="' . esc_url(get_permalink((int) $page_id)) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Sayfayı görüntüle', 'odtumist-membership-why') . '</a> | <a href="' . esc_url(admin_url('post.php?post=' . (int) $page_id . '&action=elementor')) . '">' . esc_html__('Elementor ile düzenle', 'odtumist-membership-why') . '</a>',
        );
    }

    private static function can_safely_update_existing_page($page)
    {
        $page_id = (int) $page->ID;
        if (get_post_meta($page_id, self::PAGE_META, true) === '1') {
            return true;
        }

        $elementor_data = trim((string) get_post_meta($page_id, '_elementor_data', true));
        $content = trim((string) $page->post_content);

        return $elementor_data === '' && $content === '';
    }

    private static function get_membership_parent_id($create_if_missing = false)
    {
        $parent = get_page_by_path(self::PARENT_SLUG, OBJECT, 'page');
        if ($parent instanceof WP_Post) {
            return (int) $parent->ID;
        }

        $parent = get_page_by_title('Üyelik', OBJECT, 'page');
        if ($parent instanceof WP_Post) {
            return (int) $parent->ID;
        }

        if (!$create_if_missing) {
            return 0;
        }

        $parent_id = wp_insert_post(array(
            'post_title'   => 'Üyelik',
            'post_name'    => self::PARENT_SLUG,
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => '',
        ), true);

        return is_wp_error($parent_id) ? 0 : (int) $parent_id;
    }

    private static function find_target_page($parent_id = 0)
    {
        $parent_id = (int) $parent_id;
        if ($parent_id > 0) {
            $parent_uri = trim((string) get_page_uri($parent_id), '/');
            if ($parent_uri !== '') {
                $page = get_page_by_path($parent_uri . '/' . self::TARGET_SLUG, OBJECT, 'page');
                if ($page instanceof WP_Post) {
                    return $page;
                }
            }

            $pages = get_posts(array(
                'post_type'      => 'page',
                'post_status'    => 'any',
                'name'           => self::TARGET_SLUG,
                'post_parent'    => $parent_id,
                'posts_per_page' => 1,
                'orderby'        => 'ID',
                'order'          => 'ASC',
            ));
            if (!empty($pages[0]) && $pages[0] instanceof WP_Post) {
                return $pages[0];
            }
        }

        $page = get_page_by_path(self::TARGET_SLUG, OBJECT, 'page');
        return $page instanceof WP_Post ? $page : null;
    }

    private static function is_target_page($page_id)
    {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            return false;
        }

        if (get_post_meta($page_id, self::PAGE_META, true) === '1') {
            return true;
        }

        $post = get_post($page_id);
        if (!($post instanceof WP_Post) || $post->post_type !== 'page') {
            return false;
        }

        return sanitize_title((string) $post->post_name) === self::TARGET_SLUG;
    }

    private static function get_runtime_page_id()
    {
        $post_id = (int) get_queried_object_id();
        if ($post_id > 0) {
            return $post_id;
        }

        if (isset($_GET['elementor-preview'])) {
            return (int) $_GET['elementor-preview'];
        }

        return 0;
    }

    private static function ensure_elementor_page_support()
    {
        $support = get_option('elementor_cpt_support', array());
        if (!is_array($support)) {
            $support = array();
        }
        if (!in_array('page', $support, true)) {
            $support[] = 'page';
            update_option('elementor_cpt_support', array_values(array_unique($support)), false);
        }
        add_post_type_support('page', 'elementor');
    }

    private static function clear_elementor_cache()
    {
        if (!class_exists('\\Elementor\\Plugin') || !method_exists('\\Elementor\\Plugin', 'instance')) {
            return;
        }

        $plugin = \Elementor\Plugin::instance();
        if ($plugin && isset($plugin->files_manager) && method_exists($plugin->files_manager, 'clear_cache')) {
            $plugin->files_manager->clear_cache();
        }
    }

    private static function write_elementor_document($page_id)
    {
        $document = self::build_document();
        update_post_meta($page_id, '_elementor_edit_mode', 'builder');
        update_post_meta($page_id, '_elementor_template_type', 'wp-page');
        update_post_meta($page_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '3.0.0');
        update_post_meta($page_id, '_elementor_data', wp_slash(wp_json_encode($document)));
    }

    private static function build_document()
    {
        $sections = array();
        $sections[] = self::intro_section();

        foreach (self::slides() as $index => $slide) {
            $sections[] = self::slide_section($slide, $index);
        }

        $sections[] = self::transition_section();
        $sections[] = self::promise_cards_section();
        $sections[] = self::cta_section();

        return $sections;
    }

    private static function slides()
    {
        return array(
            array(
                'step' => '1',
                'title' => 'DAYANIŞMA',
                'desc' => "Mezunlar, dernek çatısı altında bir araya gelir. ODTÜ'lü arkadaşları, hocaları ve öğrencilerle iletişimde kalır.",
                'icon' => '🤝',
                'tone' => 'orange',
            ),
            array(
                'step' => '2',
                'title' => 'DERNEĞİN VARLIĞINI SÜRDÜRMESİ',
                'desc' => 'Dernek sayesinde bağlar kuran mezunlar üye olarak derneği yaşatır; gönüllü, bağışçı ve mentor olarak çalışmaları destekler.',
                'icon' => '🏛️',
                'tone' => 'blue',
            ),
            array(
                'step' => '3',
                'title' => 'MEZUNLARIN ÖĞRENCİLERE VE ÜNİVERSİTEYE FAYDA SAĞLAMASI',
                'desc' => 'Bir çatı altında buluşan mezunlar, burs ve mentorluk programlarıyla öğrencilere ve dayanışmalarıyla üniversitemizin gelişimine katkı sağlar.',
                'icon' => '🎓',
                'tone' => 'red',
            ),
            array(
                'step' => '4',
                'title' => 'YENİ MEZUNLARIN ARAMIZA KATILMASI',
                'desc' => "Mezun olup ODTÜ'yü hayatında tutmak isteyen öğrenciler, derneğe katılır ve zincirin bir sonraki halkası olurlar.",
                'icon' => '✨',
                'tone' => 'amber',
            ),
            array(
                'step' => '5',
                'title' => 'CAMİAMIZ GENİŞLEDİKÇE DAYANIŞMAMIZ BÜYÜR',
                'desc' => 'Sayıca çoğaldıkça etkimiz artar, daha fazla öğrenciye dokunur, daha büyük projelere imza atarız.',
                'icon' => '🌍',
                'tone' => 'indigo',
            ),
        );
    }

    private static function promise_cards()
    {
        return array(
            array('icon' => '💬', 'title' => 'Kendini ifade edecek bir alan bulur.'),
            array('icon' => '🗳️', 'title' => 'Sivil topluma ve aktif demokrasiye katılır.'),
            array('icon' => '💝', 'title' => 'Üniversitemize olan gönül borcunu öder.'),
            array('icon' => '🕯️', 'title' => 'ODTÜ mirasını yeni nesillere aktarır.'),
        );
    }

    private static function intro_section()
    {
        return self::section(array(
            array(
                'size' => 100,
                'widgets' => array(
                    self::widget('heading', array(
                        'title' => 'NEDEN ÜYE OLMALIYIM?',
                        'size' => 'xxl',
                        'header_size' => 'h1',
                        'align' => 'center',
                        '_css_classes' => 'odt-mwhy-title',
                    )),
                    self::widget('divider', array(
                        'align' => 'center',
                        'weight' => array('unit' => 'px', 'size' => 6, 'sizes' => array()),
                        'width' => array('unit' => 'px', 'size' => 88, 'sizes' => array()),
                        'color' => '#ed1c24',
                        '_css_classes' => 'odt-mwhy-title-divider',
                    )),
                ),
            ),
        ), array(
            'css_classes' => 'odt-mwhy odt-mwhy-intro',
            'padding' => self::padding(88, 0, 24, 0),
        ));
    }

    private static function slide_section($slide, $index)
    {
        $active_class = $index === 0 ? ' is-active' : '';
        $tone = sanitize_html_class((string) $slide['tone']);

        return self::section(array(
            array(
                'size' => 50,
                'settings' => array('css_classes' => 'odt-mwhy-copy-col'),
                'widgets' => array(
                    self::widget('heading', array(
                        'title' => (string) $slide['step'],
                        'header_size' => 'div',
                        '_css_classes' => 'odt-mwhy-step',
                    )),
                    self::widget('heading', array(
                        'title' => (string) $slide['title'],
                        'header_size' => 'h2',
                        '_css_classes' => 'odt-mwhy-slide-title',
                    )),
                    self::widget('text-editor', array(
                        'editor' => '<p>' . esc_html((string) $slide['desc']) . '</p>',
                        '_css_classes' => 'odt-mwhy-slide-copy',
                    )),
                    self::widget('button', array(
                        'text' => '‹',
                        'link' => array('url' => '#'),
                        'align' => 'left',
                        'size' => 'sm',
                        '_css_classes' => 'odt-mwhy-nav odt-mwhy-prev',
                    )),
                    self::widget('button', array(
                        'text' => '›',
                        'link' => array('url' => '#'),
                        'align' => 'left',
                        'size' => 'sm',
                        '_css_classes' => 'odt-mwhy-nav odt-mwhy-next',
                    )),
                ),
            ),
            array(
                'size' => 50,
                'settings' => array('css_classes' => 'odt-mwhy-icon-col'),
                'widgets' => array(
                    self::widget('heading', array(
                        'title' => (string) $slide['icon'],
                        'header_size' => 'div',
                        'align' => 'center',
                        '_css_classes' => 'odt-mwhy-big-icon',
                    )),
                ),
            ),
        ), array(
            'css_classes' => 'odt-mwhy odt-mwhy-slide odt-mwhy-slide-' . ((int) $index + 1) . ' odt-mwhy-tone-' . $tone . $active_class,
            'content_width' => 'boxed',
            'gap' => 'no',
            'padding' => self::padding(0, 0, 0, 0),
        ));
    }

    private static function transition_section()
    {
        return self::section(array(
            array(
                'size' => 100,
                'widgets' => array(
                    self::widget('heading', array(
                        'title' => "MEZUNLAR DERNEĞİ'NE ÜYE OLAN ODTÜ'LÜLER;",
                        'size' => 'xl',
                        'header_size' => 'h2',
                        'align' => 'center',
                        '_css_classes' => 'odt-mwhy-transition-title',
                    )),
                    self::widget('divider', array(
                        'align' => 'center',
                        'weight' => array('unit' => 'px', 'size' => 5, 'sizes' => array()),
                        'width' => array('unit' => 'px', 'size' => 180, 'sizes' => array()),
                        'color' => '#00529b',
                        '_css_classes' => 'odt-mwhy-transition-divider',
                    )),
                ),
            ),
        ), array(
            'css_classes' => 'odt-mwhy odt-mwhy-transition',
            'padding' => self::padding(24, 0, 36, 0),
        ));
    }

    private static function promise_cards_section()
    {
        $columns = array();
        foreach (self::promise_cards() as $card) {
            $columns[] = array(
                'size' => 25,
                'settings' => array('css_classes' => 'odt-mwhy-promise-col'),
                'widgets' => array(
                    self::widget('heading', array(
                        'title' => (string) $card['icon'],
                        'header_size' => 'div',
                        '_css_classes' => 'odt-mwhy-promise-icon',
                    )),
                    self::widget('heading', array(
                        'title' => (string) $card['title'],
                        'header_size' => 'h3',
                        '_css_classes' => 'odt-mwhy-promise-title',
                    )),
                ),
            );
        }

        return self::section($columns, array(
            'css_classes' => 'odt-mwhy odt-mwhy-promises',
            'padding' => self::padding(16, 0, 48, 0),
        ));
    }

    private static function cta_section()
    {
        return self::section(array(
            array(
                'size' => 100,
                'widgets' => array(
                    self::widget('heading', array(
                        'title' => "HADİ ŞİMDİ DE BU 'ÇATIDA' BULUŞALIM",
                        'size' => 'xl',
                        'header_size' => 'h2',
                        'align' => 'center',
                        '_css_classes' => 'odt-mwhy-cta-title',
                    )),
                    self::widget('button', array(
                        'text' => 'ŞİMDİ ÜYE OL',
                        'link' => array('url' => 'https://fonzip.com/odtumist/uyelik', 'is_external' => true, 'nofollow' => true),
                        'align' => 'center',
                        'size' => 'lg',
                        '_css_classes' => 'odt-mwhy-cta-button',
                    )),
                ),
            ),
        ), array(
            'css_classes' => 'odt-mwhy odt-mwhy-cta',
            'padding' => self::padding(0, 0, 96, 0),
        ));
    }

    private static function section($columns, $settings = array())
    {
        $defaults = array(
            'layout' => 'full_width',
            'content_width' => 'boxed',
            'gap' => 'default',
            'padding' => self::padding(40, 0, 40, 0),
        );
        $settings = wp_parse_args(is_array($settings) ? $settings : array(), $defaults);

        $elements = array();
        foreach ((array) $columns as $column) {
            $size = isset($column['size']) ? (int) $column['size'] : 100;
            $column_settings = array('_column_size' => $size);
            if (!empty($column['settings']) && is_array($column['settings'])) {
                $column_settings = wp_parse_args($column['settings'], $column_settings);
            }
            $elements[] = array(
                'id' => self::element_id(),
                'elType' => 'column',
                'settings' => $column_settings,
                'elements' => isset($column['widgets']) && is_array($column['widgets']) ? $column['widgets'] : array(),
            );
        }

        return array(
            'id' => self::element_id(),
            'elType' => 'section',
            'settings' => $settings,
            'elements' => $elements,
        );
    }

    private static function widget($type, $settings)
    {
        return array(
            'id' => self::element_id(),
            'elType' => 'widget',
            'widgetType' => (string) $type,
            'settings' => is_array($settings) ? $settings : array(),
            'elements' => array(),
        );
    }

    private static function padding($top, $right, $bottom, $left)
    {
        return array(
            'unit' => 'px',
            'top' => (string) $top,
            'right' => (string) $right,
            'bottom' => (string) $bottom,
            'left' => (string) $left,
            'isLinked' => false,
        );
    }

    private static function element_id()
    {
        return substr(md5(uniqid('odt-mwhy-', true)), 0, 8);
    }
}

register_activation_hook(__FILE__, array('ODTUMIST_Membership_Why_Page', 'activate'));
ODTUMIST_Membership_Why_Page::init();
