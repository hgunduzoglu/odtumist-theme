<?php
if (!defined('ABSPATH')) {
    exit;
}

$groups_query = odtumist_get_working_groups(18);
$groups_copy  = odtumist_get_home_groups_copy();
?>
<section class="groups-section" id="anasayfa-gruplar">
    <div class="site-container">
        <div class="section-head section-head-light">
            <div>
                <span class="section-kicker"><?php echo esc_html($groups_copy['kicker']); ?></span>
                <h2><?php echo esc_html($groups_copy['title']); ?></h2>
            </div>
            <div class="section-head-right">
                <div class="decorative-bars">
                    <span class="bar bar-red"></span>
                    <span class="bar bar-blue"></span>
                </div>
                <div class="carousel-controls">
                    <button type="button" class="carousel-arrow light" data-carousel-prev="groups" aria-label="<?php esc_attr_e('Önceki grup', 'odtumist'); ?>">&#8249;</button>
                    <button type="button" class="carousel-arrow light" data-carousel-next="groups" aria-label="<?php esc_attr_e('Sonraki grup', 'odtumist'); ?>">&#8250;</button>
                </div>
            </div>
        </div>
    </div>

    <div class="groups-carousel" data-carousel="groups">
        <?php if ($groups_query->have_posts()) : ?>
            <?php while ($groups_query->have_posts()) : $groups_query->the_post(); ?>
                <article class="group-card flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="group-media">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
                                <?php else : ?>
                                    <img src="https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&q=80&w=800" alt="<?php the_title_attribute(); ?>" loading="lazy">
                                <?php endif; ?>
                            </div>
                            <div class="group-body">
                                <h3><?php the_title(); ?></h3>
                                <span class="group-link"><?php esc_html_e('İncele', 'odtumist'); ?> &rarr;</span>
                            </div>
                        </div>
                        <div class="flip-card-back">
                            <div class="flip-back-icon">O</div>
                            <h3><?php the_title(); ?></h3>
                            <p><?php echo esc_html(get_the_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 20)); ?></p>
                            <a class="btn btn-white flip-btn" href="<?php the_permalink(); ?>"><?php esc_html_e('Detaylı İncele', 'odtumist'); ?></a>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <div class="empty-state light"><?php esc_html_e('Şu anda yayınlanmış grup bulunmuyor.', 'odtumist'); ?></div>
        <?php endif; ?>
    </div>
</section>
