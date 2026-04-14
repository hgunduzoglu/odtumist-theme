<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if (!function_exists('elementor_theme_do_location') || !elementor_theme_do_location('header')) : ?>
    <?php
    $social_links = odtumist_get_social_links();
    $cta_links    = odtumist_get_primary_cta_links();
    $cta_labels   = array(
        'donation'   => get_theme_mod('odtumist_header_donation_label', __('Bağış Yap', 'odtumist')),
        'membership' => get_theme_mod('odtumist_header_membership_label', __('Üye Ol', 'odtumist')),
    );
    ?>
    <header id="site-header" class="site-header">
        <div class="site-container nav-inner">
            <?php if (has_custom_logo()) : ?>
                <div class="site-brand site-brand-logo"><?php the_custom_logo(); ?></div>
            <?php else : ?>
                <a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Anasayfa', 'odtumist'); ?>">
                    <span class="site-brand-title"><?php echo esc_html(get_bloginfo('name')); ?></span>
                </a>
            <?php endif; ?>

            <div class="desktop-right">
                <div class="social-links" aria-label="<?php esc_attr_e('Sosyal Medya', 'odtumist'); ?>">
                    <?php foreach ($social_links as $network => $url) : ?>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr(ucfirst((string) $network)); ?>">
                            <?php echo odtumist_get_social_icon_svg($network); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <nav class="desktop-nav" aria-label="<?php esc_attr_e('Ana Menü', 'odtumist'); ?>">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary-menu',
                        'container'      => false,
                        'menu_class'     => 'desktop-menu',
                        'fallback_cb'    => 'odtumist_render_fallback_menu',
                        'depth'          => 2,
                    ));
                    ?>
                </nav>

                <div class="header-ctas">
                    <a class="btn btn-outline" href="<?php echo esc_url($cta_links['donation']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($cta_labels['donation']); ?></a>
                    <a class="btn btn-solid" href="<?php echo esc_url($cta_links['membership']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($cta_labels['membership']); ?></a>
                </div>
            </div>

            <button id="mobile-toggle" class="mobile-toggle" type="button" aria-expanded="false" aria-controls="mobile-panel" aria-label="<?php esc_attr_e('Menüyü Aç/Kapat', 'odtumist'); ?>">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

        <div id="mobile-panel" class="mobile-panel" hidden>
            <nav aria-label="<?php esc_attr_e('Mobil Menü', 'odtumist'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary-menu',
                    'container'      => false,
                    'menu_class'     => 'mobile-menu',
                    'fallback_cb'    => 'odtumist_render_fallback_menu',
                    'depth'          => 2,
                ));
                ?>
            </nav>

            <div class="mobile-ctas">
                <a class="btn btn-outline" href="<?php echo esc_url($cta_links['donation']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($cta_labels['donation']); ?></a>
                <a class="btn btn-solid" href="<?php echo esc_url($cta_links['membership']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($cta_labels['membership']); ?></a>
            </div>

            <div class="social-links mobile-social" aria-label="<?php esc_attr_e('Sosyal Medya', 'odtumist'); ?>">
                <?php foreach ($social_links as $network => $url) : ?>
                    <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr(ucfirst((string) $network)); ?>">
                        <?php echo odtumist_get_social_icon_svg($network); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </header>
<?php endif; ?>
