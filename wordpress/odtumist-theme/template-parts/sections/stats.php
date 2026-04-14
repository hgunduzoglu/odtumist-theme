<?php
if (!defined('ABSPATH')) {
    exit;
}

$stats = odtumist_get_home_stats();
?>
<section class="stats-section" id="anasayfa-istatistikler">
    <div class="site-container stats-grid">
        <?php foreach ($stats as $stat) : ?>
            <div class="stat-item">
                <span class="stat-value"><?php echo esc_html($stat['value']); ?></span>
                <span class="stat-label"><?php echo esc_html($stat['label']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>
