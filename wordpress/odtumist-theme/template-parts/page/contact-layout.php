<?php
if (!defined('ABSPATH')) {
    exit;
}

$social      = odtumist_get_social_links();
$contact     = odtumist_get_contact_content();
$departments = odtumist_get_contact_departments();
$form_title  = get_theme_mod('odtumist_contact_form_title', __('Sizi Dinlemeye Hazırız', 'odtumist'));
$form_desc   = get_theme_mod('odtumist_contact_form_desc', __('Bize her konuda yazabilirsiniz Hocam.', 'odtumist'));
$form_provider = get_theme_mod('odtumist_contact_form_provider', 'cf7');
$cf7_form_id = (int) get_theme_mod('odtumist_contact_cf7_form_id', 0);
$wpforms_form_id = (int) get_theme_mod('odtumist_contact_wpforms_form_id', 0);
$form_shortcode = trim((string) get_theme_mod('odtumist_contact_form_shortcode', '[contact-form-7 id="123" title="İletişim Formu"]'));
?>

<section class="contact-hero">
    <img src="<?php echo esc_url($contact['hero_image']); ?>" alt="<?php esc_attr_e('İletişim görseli', 'odtumist'); ?>">
    <div class="contact-hero-overlay"></div>
    <div class="site-container contact-hero-content">
        <div class="contact-hero-box">
            <h1><?php the_title(); ?></h1>
            <p>
                <?php
                if (get_the_excerpt()) {
                    echo esc_html(get_the_excerpt());
                } else {
                    echo esc_html($contact['hero_text']);
                }
                ?>
            </p>
        </div>
    </div>
    <div class="contact-wave">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5,73.84-4.36,147.54,16.88,218.2,35.26,69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113,2.03,1200,0V0Z"></path></svg>
    </div>
</section>

<section class="contact-main">
    <div class="site-container contact-grid-enhanced">
        <div class="contact-left">
            <div class="contact-cards">
                <div class="contact-cards-grid">
                    <div class="contact-card-item">
                        <h3><?php esc_html_e('İletişim Bilgileri', 'odtumist'); ?></h3>
                        <div class="contact-detail-row">
                            <span class="contact-detail-icon">&#128205;</span>
                            <div>
                                <p class="contact-detail-title"><?php esc_html_e('ODTÜPARK Ulus', 'odtumist'); ?></p>
                                <p class="contact-detail-text"><?php echo esc_html($contact['address']); ?></p>
                            </div>
                        </div>
                        <div class="contact-detail-row">
                            <span class="contact-detail-icon">&#128222;</span>
                            <p class="contact-detail-title"><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $contact['phone'])); ?>"><?php echo esc_html($contact['phone']); ?></a></p>
                        </div>
                    </div>
                    <div class="contact-card-item contact-card-social">
                        <h3><?php esc_html_e('Sosyal Medya', 'odtumist'); ?></h3>
                        <div class="social-links contact-social-grid">
                            <?php foreach ($social as $network => $url) : ?>
                                <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr(ucfirst((string) $network)); ?>">
                                    <?php echo odtumist_get_social_icon_svg($network); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

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
        </div>

        <div class="contact-right">
            <div class="contact-map-header">
                <h3><?php esc_html_e('Buluşma Noktamız:', 'odtumist'); ?> <span class="text-blue"><?php esc_html_e('Ulus ODTÜPARK', 'odtumist'); ?></span></h3>
                <div class="contact-map-divider"></div>
            </div>
            <div class="contact-map-wrap">
                <iframe src="<?php echo esc_url($contact['map_url']); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php esc_attr_e('ODTÜMİST Lokasyon', 'odtumist'); ?>"></iframe>
            </div>
            <div class="contact-map-cta">
                <a class="btn btn-dark" href="https://maps.app.goo.gl/QGGZtNl7QrMxFSI6L" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Yol Almaya Başla', 'odtumist'); ?></a>
            </div>
        </div>
    </div>
</section>

<section class="contact-form-block">
    <div class="site-container">
        <div class="contact-form-header">
            <h2><?php echo esc_html($form_title); ?></h2>
            <p><?php echo esc_html($form_desc); ?></p>
        </div>
        <div class="contact-form-card prose-block">
            <?php
            $rendered_form = '';

            if ($form_provider === 'cf7' && $cf7_form_id > 0) {
                $rendered_form = do_shortcode('[contact-form-7 id="' . (int) $cf7_form_id . '"]');
            } elseif ($form_provider === 'wpforms' && $wpforms_form_id > 0 && function_exists('wpforms_display')) {
                ob_start();
                wpforms_display($wpforms_form_id, false, false, false);
                $rendered_form = (string) ob_get_clean();
            } elseif ($form_provider === 'shortcode' && $form_shortcode !== '') {
                $rendered_form = do_shortcode($form_shortcode);
            }

            if (trim((string) $rendered_form) !== '' && trim((string) $rendered_form) !== $form_shortcode) {
                echo wp_kses_post($rendered_form);
            } else {
                ?>
                <h3><?php esc_html_e('Form kurulumu bekleniyor', 'odtumist'); ?></h3>
                <p><?php esc_html_e('Görünüm > Özelleştir > İletişim ve Footer Bilgileri bölümünden form sağlayıcı ve form seçimini yapın.', 'odtumist'); ?></p>
                <?php
            }
            ?>
        </div>
    </div>
</section>
