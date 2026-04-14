<?php
if (!defined('ABSPATH')) {
    exit;
}

$closing = odtumist_get_home_closing_content();
?>
<section class="closing-banner" id="anasayfa-dayanisma">
    <div class="site-container">
        <div class="closing-card">
            <img src="<?php echo esc_url($closing['image']); ?>" alt="<?php esc_attr_e('Dayanışma görseli', 'odtumist'); ?>" loading="lazy">
            <div class="closing-overlay"></div>
            <div class="closing-content">
                <h3><?php echo esc_html($closing['title']); ?></h3>
                <p><?php echo esc_html($closing['description']); ?></p>
            </div>
        </div>
    </div>
</section>
