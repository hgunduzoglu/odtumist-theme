<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<section class="newsletter-section" id="anasayfa-bulten">
    <div class="site-container newsletter-grid">
        <div class="newsletter-copy">
            <span class="section-kicker"><?php esc_html_e('Topluluk Bülteni', 'odtumist'); ?></span>
            <h2><?php esc_html_e('E-bültene Kaydol', 'odtumist'); ?></h2>
            <p><?php esc_html_e('İletişimde kalın: etkinlikler, burs, mentorluk ve dayanışma haberlerini e-posta ile alın.', 'odtumist'); ?></p>
        </div>
        <div class="newsletter-form prose-block">
            <?php echo do_shortcode('[odtumist_contact_form provider="wpforms"]'); ?>
        </div>
    </div>
</section>
