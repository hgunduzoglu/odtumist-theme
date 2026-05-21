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
    $contact_phones  = odtumist_get_contact_phone_lines($contact_content['phone']);
    $footer_content  = odtumist_get_footer_content();
    $brand_name      = odtumist_get_brand_name();
    $footer_logo_image = isset($footer_content['logo_image']) ? trim((string) $footer_content['logo_image']) : '';
    $has_footer_logo_image = $footer_logo_image !== '';
    ?>
    <footer class="site-footer">
        <div class="site-container footer-grid">
            <div class="footer-brand">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo<?php echo $has_footer_logo_image ? ' has-image' : ''; ?>" aria-label="<?php esc_attr_e('Anasayfa', 'odtumist'); ?>">
                    <?php if ($has_footer_logo_image) : ?>
                        <img class="footer-logo-image" src="<?php echo esc_url($footer_logo_image); ?>" alt="<?php echo esc_attr($brand_name); ?>" decoding="async">
                    <?php else : ?>
                        <?php echo esc_html((string) $footer_content['logo_text']); ?>
                    <?php endif; ?>
                </a>
                <h2><?php echo esc_html($brand_name); ?></h2>
                <p class="footer-subtitle"><?php echo esc_html((string) $footer_content['subtitle']); ?></p>
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
                <h3><?php echo esc_html((string) $footer_content['quick_title']); ?></h3>
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
                <h3><?php echo esc_html((string) $footer_content['corp_title']); ?></h3>
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
                <h3><?php echo esc_html((string) $footer_content['info_title']); ?></h3>
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
                    <h3><?php echo esc_html((string) $footer_content['contact_title']); ?></h3>
                    <ul>
                        <li><?php echo nl2br(esc_html($contact_content['address'])); ?></li>
                        <?php foreach ($contact_phones as $phone) : ?>
                            <?php $phone_href = preg_replace('/[^0-9+]/', '', (string) $phone); ?>
                            <li><a href="tel:<?php echo esc_attr($phone_href); ?>"><?php echo esc_html($phone); ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="mailto:<?php echo esc_attr($contact_content['email']); ?>"><?php echo esc_html($contact_content['email']); ?></a></li>
                    </ul>
                </div>
        </div>

        <div class="site-container footer-bottom">
            <p>&copy; <?php echo esc_html((string) current_time('Y')); ?> <?php echo esc_html($brand_name); ?>. <?php echo esc_html((string) $footer_content['copyright']); ?></p>
        </div>
    </footer>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
