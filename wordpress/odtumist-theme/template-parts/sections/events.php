<?php
if (!defined('ABSPATH')) {
    exit;
}

$events_query = odtumist_get_featured_events(12);
$events_page  = odtumist_get_page_by_slug(array('etkinlikler', 'events'));
$events_copy  = odtumist_get_home_events_copy();
$categories   = array();

if ($events_query->have_posts()) {
    while ($events_query->have_posts()) {
        $events_query->the_post();
        $terms = get_the_terms(get_the_ID(), 'event-category');
        if (!is_array($terms) || empty($terms)) {
            $categories['diger'] = __('Diğer', 'odtumist');
            continue;
        }

        foreach ($terms as $term) {
            $categories[$term->slug] = $term->name;
        }
    }
    wp_reset_postdata();
}
?>
<section class="events-section" id="anasayfa-etkinlikler">
    <div class="site-container">
        <div class="section-head">
            <div>
                <a class="mini-link" href="<?php echo esc_url($events_page ? get_permalink($events_page) : home_url('/etkinlikler')); ?>"><?php echo esc_html($events_copy['kicker']); ?></a>
                <h2><?php echo esc_html($events_copy['title']); ?></h2>
                <p><?php echo esc_html($events_copy['description']); ?></p>
            </div>
            <div class="carousel-controls">
                <button type="button" class="carousel-arrow" data-carousel-prev="events" aria-label="<?php esc_attr_e('Önceki etkinlikler', 'odtumist'); ?>">&#8249;</button>
                <button type="button" class="carousel-arrow" data-carousel-next="events" aria-label="<?php esc_attr_e('Sonraki etkinlikler', 'odtumist'); ?>">&#8250;</button>
            </div>
        </div>

        <div class="event-filters" data-events-filter>
            <button class="is-active" type="button" data-event-filter="all"><?php esc_html_e('Tümü', 'odtumist'); ?></button>
            <?php foreach ($categories as $slug => $name) : ?>
                <button type="button" data-event-filter="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="events-carousel" data-carousel="events">
        <?php if ($events_query->have_posts()) : ?>
            <?php while ($events_query->have_posts()) : $events_query->the_post(); ?>
                <?php
                $post_id      = get_the_ID();
                $terms        = get_the_terms($post_id, 'event-category');
                $primary_term = (is_array($terms) && !empty($terms)) ? $terms[0] : null;
                $slug         = $primary_term ? $primary_term->slug : 'diger';
                $cat_name     = $primary_term ? $primary_term->name : __('Diğer', 'odtumist');
                ?>
                <article class="event-card" data-event-category="<?php echo esc_attr($slug); ?>">
                    <a class="event-card-image" href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
                        <?php else : ?>
                            <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=800" alt="<?php the_title_attribute(); ?>" loading="lazy">
                        <?php endif; ?>
                        <span class="event-badge"><?php echo esc_html($cat_name); ?></span>
                    </a>
                    <div class="event-card-body">
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="event-meta"><?php echo esc_html(odtumist_get_event_datetime($post_id)); ?></p>
                        <p class="event-meta"><?php echo esc_html(odtumist_get_event_location($post_id)); ?></p>
                        <p><?php echo esc_html(get_the_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 18)); ?></p>
                        <a class="event-more" href="<?php the_permalink(); ?>"><?php esc_html_e('Detayları İncele', 'odtumist'); ?></a>
                    </div>
                </article>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <div class="empty-state"><?php esc_html_e('Şu anda planlanmış etkinlik bulunmuyor.', 'odtumist'); ?></div>
        <?php endif; ?>
    </div>
</section>
