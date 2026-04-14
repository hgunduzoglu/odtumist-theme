<?php
if (!defined('ABSPATH')) {
    exit;
}

$queried_post_id = (int) get_queried_object_id();
$is_elementor_page = $queried_post_id > 0 ? odtumist_should_render_with_elementor($queried_post_id) : false;
?>
<?php if (is_front_page() && !$is_elementor_page) : ?>
    <?php get_template_part('template-parts/sections/stats'); ?>
<?php endif; ?>
<?php if (!function_exists('elementor_theme_do_location') || !elementor_theme_do_location('footer')) : ?>
    <?php
    $social_links    = odtumist_get_social_links();
    $contact_content = odtumist_get_contact_content();
    $footer_content  = odtumist_get_footer_content();
    ?>
    <footer class="site-footer">
        <div class="site-container footer-grid">
            <div class="footer-brand">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo" aria-label="<?php esc_attr_e('Anasayfa', 'odtumist'); ?>">O</a>
                <h2><?php echo esc_html(get_bloginfo('name')); ?></h2>
                <p class="footer-subtitle"><?php esc_html_e('İstanbul ODTÜ Mezunlar Derneği', 'odtumist'); ?></p>
                <p class="footer-desc"><?php echo esc_html($footer_content['description']); ?></p>

                <div class="footer-social" aria-label="<?php esc_attr_e('Sosyal Medya', 'odtumist'); ?>">
                    <?php foreach ($social_links as $network => $url) : ?>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr(ucfirst((string) $network)); ?>">
                            <?php echo odtumist_get_social_icon_svg($network); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="footer-menu-wrap">
                <h3><?php esc_html_e('Hızlı Erişim', 'odtumist'); ?></h3>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer-menu',
                    'container'      => false,
                    'menu_class'     => 'footer-menu',
                    'fallback_cb'    => 'odtumist_render_fallback_menu',
                    'depth'          => 1,
                ));
                ?>
            </div>

            <div class="footer-menu-wrap">
                <h3><?php esc_html_e('Kurumsal', 'odtumist'); ?></h3>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer-corporate-menu',
                    'container'      => false,
                    'menu_class'     => 'footer-menu',
                    'fallback_cb'    => 'odtumist_render_fallback_menu',
                    'depth'          => 1,
                ));
                ?>
            </div>

            <div class="footer-menu-wrap">
                <h3><?php esc_html_e('Bilgi Merkezi', 'odtumist'); ?></h3>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer-info-menu',
                    'container'      => false,
                    'menu_class'     => 'footer-menu',
                    'fallback_cb'    => 'odtumist_render_fallback_menu',
                    'depth'          => 1,
                ));
                ?>
            </div>

            <div class="footer-contact">
                <h3><?php esc_html_e('İletişim', 'odtumist'); ?></h3>
                <ul>
                    <li><?php echo esc_html($contact_content['address']); ?></li>
                    <li><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $contact_content['phone'])); ?>"><?php echo esc_html($contact_content['phone']); ?></a></li>
                    <li><a href="mailto:<?php echo esc_attr($contact_content['email']); ?>"><?php echo esc_html($contact_content['email']); ?></a></li>
                </ul>
            </div>
        </div>

        <div class="site-container footer-bottom">
            <p>&copy; <?php echo esc_html((string) current_time('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>. <?php esc_html_e('Tüm hakları saklıdır.', 'odtumist'); ?></p>
        </div>
    </footer>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
