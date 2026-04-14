<?php
if (!defined('ABSPATH')) {
    exit;
}

$slides = odtumist_get_home_hero_slides();
?>
<section class="hero" id="hero-slider" aria-label="<?php esc_attr_e('Ana tanıtım alanı', 'odtumist'); ?>">
    <?php foreach ($slides as $index => $slide) : ?>
        <article class="hero-slide<?php echo $index === 0 ? ' is-active' : ''; ?>" data-slide="<?php echo esc_attr((string) $index); ?>">
            <img src="<?php echo esc_url($slide['image']); ?>" alt="<?php echo esc_attr($slide['title']); ?>">
            <div class="hero-overlay"></div>
            <div class="site-container hero-content">
                <h1>
                    <?php
                    // Support gradient text via *text* markers in Customizer
                    $title = esc_html($slide['title']);
                    $title = preg_replace('/\*([^*]+)\*/', '<span class="gradient-text">$1</span>', $title);
                    echo wp_kses($title, array('span' => array('class' => array()), 'br' => array()));
                    ?>
                </h1>
                <p><?php echo esc_html($slide['desc']); ?></p>
                <div class="hero-actions">
                    <?php if (!empty($slide['primary']['label']) && !empty($slide['primary']['url'])) : ?>
                        <a class="btn btn-solid hero-btn-primary" href="<?php echo esc_url($slide['primary']['url']); ?>"<?php echo odtumist_is_external_url((string) $slide['primary']['url']) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html($slide['primary']['label']); ?></a>
                    <?php endif; ?>
                    <?php if (!empty($slide['secondary'])) : ?>
                        <a class="btn btn-secondary" href="<?php echo esc_url($slide['secondary']['url']); ?>"<?php echo odtumist_is_external_url((string) $slide['secondary']['url']) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html($slide['secondary']['label']); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    <?php endforeach; ?>

    <div class="hero-controls site-container">
        <button class="hero-arrow" type="button" data-hero-prev aria-label="<?php esc_attr_e('Önceki', 'odtumist'); ?>">&#8249;</button>
        <button class="hero-arrow" type="button" data-hero-next aria-label="<?php esc_attr_e('Sonraki', 'odtumist'); ?>">&#8250;</button>
    </div>

    <div class="hero-dots site-container" role="tablist" aria-label="<?php esc_attr_e('Slaytlar', 'odtumist'); ?>">
        <?php foreach ($slides as $index => $slide) : ?>
            <button class="hero-dot<?php echo $index === 0 ? ' is-active' : ''; ?>" data-hero-dot="<?php echo esc_attr((string) $index); ?>" type="button" aria-label="<?php echo esc_attr(sprintf(__('Slayt %d', 'odtumist'), $index + 1)); ?>"></button>
        <?php endforeach; ?>
    </div>
</section>
