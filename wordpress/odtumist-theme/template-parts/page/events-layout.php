<?php
if (!defined('ABSPATH')) {
    exit;
}

$events_query = odtumist_get_featured_events(24);
?>
<section class="page-hero page-hero-light">
    <div class="site-container">
        <h1><?php the_title(); ?></h1>
        <p>
            <?php
            if (get_the_excerpt()) {
                echo esc_html(get_the_excerpt());
            } else {
                esc_html_e('Takvimdeki etkinlikleri inceleyebilir, detay sayfalarından kayıt ve katılım bilgilerine ulaşabilirsin.', 'odtumist');
            }
            ?>
        </p>
    </div>
</section>

<section class="events-page-grid">
    <div class="site-container events-grid">
        <?php if ($events_query->have_posts()) : ?>
            <?php while ($events_query->have_posts()) : $events_query->the_post(); ?>
                <?php
                $post_id      = get_the_ID();
                $terms        = get_the_terms($post_id, 'event-category');
                $primary_term = (is_array($terms) && !empty($terms)) ? $terms[0] : null;
                $cat_name     = $primary_term ? $primary_term->name : __('Etkinlik', 'odtumist');
                ?>
                <article class="event-list-card">
                    <a href="<?php the_permalink(); ?>" class="event-list-thumb">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
                        <?php else : ?>
                            <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=900" alt="<?php the_title_attribute(); ?>" loading="lazy">
                        <?php endif; ?>
                    </a>
                    <div class="event-list-content">
                        <span class="event-badge"><?php echo esc_html($cat_name); ?></span>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p class="event-meta"><?php echo esc_html(odtumist_get_event_datetime($post_id)); ?></p>
                        <p class="event-meta"><?php echo esc_html(odtumist_get_event_location($post_id)); ?></p>
                        <p><?php echo esc_html(get_the_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 24)); ?></p>
                        <a class="event-more" href="<?php the_permalink(); ?>"><?php esc_html_e('Detayları İncele', 'odtumist'); ?></a>
                    </div>
                </article>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <p class="empty-state"><?php esc_html_e('Henüz yayınlanmış etkinlik bulunmuyor.', 'odtumist'); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php
$gallery = odtumist_get_events_gallery();
if (!empty($gallery)) :
?>
<section class="events-gallery">
    <div class="site-container">
        <div class="events-gallery-header">
            <div>
                <span class="section-kicker">&#128247; <?php esc_html_e('Vizörden ODTÜMİST', 'odtumist'); ?></span>
                <h2><?php esc_html_e('Etkinliklerden', 'odtumist'); ?> <span class="text-red"><?php esc_html_e('Kareler', 'odtumist'); ?></span></h2>
            </div>
        </div>
        <div class="events-gallery-grid">
            <?php foreach ($gallery as $img_url) : ?>
                <figure class="events-gallery-item">
                    <img src="<?php echo esc_url($img_url); ?>" alt="<?php esc_attr_e('Etkinlik Karesi', 'odtumist'); ?>" loading="lazy">
                    <div class="events-gallery-overlay">
                        <span class="events-gallery-zoom">&#128269;</span>
                    </div>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (trim((string) get_the_content()) !== '') : ?>
    <section class="page-content-block">
        <div class="site-container prose-block">
            <?php the_content(); ?>
        </div>
    </section>
<?php endif; ?>
