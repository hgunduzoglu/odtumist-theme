<?php
if (!defined('ABSPATH')) {
    exit;
}

$legacy_event_cards_enabled = function_exists('odtumist_legacy_event_cards_enabled') ? odtumist_legacy_event_cards_enabled() : false;
$events_query = $legacy_event_cards_enabled ? odtumist_get_featured_events(24) : null;
$gallery_items = function_exists('odtumist_get_events_gallery_items') ? odtumist_get_events_gallery_items() : array();
$event_filter_terms = array();
if ($legacy_event_cards_enabled) {
    $event_filter_terms = get_terms(array(
        'taxonomy'   => 'event-category',
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ));
    if (!is_array($event_filter_terms) || is_wp_error($event_filter_terms)) {
        $event_filter_terms = array();
    }
}
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

<section class="events-flip-block">
    <div class="site-container">
        <div class="events-gallery-header">
            <div>
                <span class="section-kicker"><?php esc_html_e('Blok #1', 'odtumist'); ?> &#128377;</span>
                <h2><?php esc_html_e('Etkinlik Kategorileri', 'odtumist'); ?></h2>
            </div>
        </div>
        <div class="events-flip-grid">
            <article class="events-flip-card">
                <div class="events-flip-card-inner">
                    <div class="events-flip-front"><h3><?php esc_html_e('Mezun Buluşmaları', 'odtumist'); ?></h3></div>
                    <div class="events-flip-back"><p><?php esc_html_e('Mezunlar Günü, Bahar Şenliği ve Yılbaşı Partisi gibi geleneksel etkinlikler, şehrin çeşitli mekanlarında sosyal buluşmalar ve partilerle farklı kuşaklardan ODTÜ’lülerin ağlarını güçlendirmesini amaçlıyoruz.', 'odtumist'); ?></p></div>
                </div>
            </article>
            <article class="events-flip-card">
                <div class="events-flip-card-inner">
                    <div class="events-flip-front"><h3><?php esc_html_e('Paneller ve Seminerler', 'odtumist'); ?></h3></div>
                    <div class="events-flip-back"><p><?php esc_html_e('Akademisyenler, sektör profesyonelleri ve alanlarında öncü mezunların konuşmacı olarak katıldığı paneller, söyleşiler ve kariyer seminerlerinde üyeleri buluşturuyoruz.', 'odtumist'); ?></p></div>
                </div>
            </article>
            <article class="events-flip-card">
                <div class="events-flip-card-inner">
                    <div class="events-flip-front"><h3><?php esc_html_e('Kültürel ve Sosyal Etkinlikler', 'odtumist'); ?></h3></div>
                    <div class="events-flip-back"><p><?php esc_html_e('İstanbul içi ve çevresinde kültür gezileri, sanat galerisi turları, edebiyat söyleşileri ve film gösterimleri gibi sosyal etkinliklerle üyelerimize keyifli vakit geçirme şansı sunuyoruz.', 'odtumist'); ?></p></div>
                </div>
            </article>
            <article class="events-flip-card">
                <div class="events-flip-card-inner">
                    <div class="events-flip-front"><h3><?php esc_html_e('Genç Mezun Etkinlikleri', 'odtumist'); ?></h3></div>
                    <div class="events-flip-back"><p><?php esc_html_e('Yeni mezun ve gençlerin ihtiyaçlarını karşılayan özel etkinlikleri gençlerle, gençler için düzenliyoruz. Genç mezunlar sosyal etkinliklerde bir araya gelerek yeni bağlar kuruyor ve mezun camiasına entegre oluyorlar.', 'odtumist'); ?></p></div>
                </div>
            </article>
        </div>
    </div>
</section>

<?php if ($legacy_event_cards_enabled && $events_query instanceof WP_Query) : ?>
<section class="events-page-grid">
    <div class="site-container">
        <div class="events-gallery-header">
            <div>
                <span class="section-kicker"><?php esc_html_e('Blok #2', 'odtumist'); ?></span>
                <h2><?php esc_html_e('Yaklaşan Etkinlikler', 'odtumist'); ?></h2>
            </div>
        </div>
        <?php if (count($event_filter_terms) > 1) : ?>
            <div class="event-filters" data-events-filter="events-page">
                <button class="is-active" type="button" data-event-filter="all"><?php esc_html_e('Tümü', 'odtumist'); ?></button>
                <?php foreach ($event_filter_terms as $filter_term) : ?>
                    <?php if (!($filter_term instanceof WP_Term)) {
                        continue;
                    } ?>
                    <button type="button" data-event-filter="<?php echo esc_attr(sanitize_title((string) $filter_term->slug)); ?>"><?php echo esc_html((string) $filter_term->name); ?></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="events-grid">
            <?php if ($events_query->have_posts()) : ?>
                <?php while ($events_query->have_posts()) : $events_query->the_post(); ?>
                    <?php
                    $post_id      = get_the_ID();
                    $terms        = get_the_terms($post_id, 'event-category');
                    $primary_term = (is_array($terms) && !empty($terms)) ? $terms[0] : null;
                    $cat_name     = $primary_term ? $primary_term->name : __('Etkinlik', 'odtumist');
                    $term_slugs   = array();
                    if (is_array($terms) && !is_wp_error($terms)) {
                        foreach ($terms as $term_item) {
                            if (!($term_item instanceof WP_Term)) {
                                continue;
                            }
                            $term_slug = sanitize_title((string) $term_item->slug);
                            if ($term_slug !== '') {
                                $term_slugs[] = $term_slug;
                            }
                        }
                    }
                    $term_slugs = array_values(array_unique($term_slugs));
                    if (empty($term_slugs)) {
                        $term_slugs[] = 'diger';
                    }
                    ?>
                    <article class="event-list-card" data-event-category="<?php echo esc_attr(implode(' ', $term_slugs)); ?>">
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
    </div>
</section>
<?php endif; ?>

<?php if (!empty($gallery_items)) : ?>
<section class="events-gallery">
    <div class="site-container">
        <div class="events-gallery-header">
            <div>
                <span class="section-kicker"><?php esc_html_e('Blok #3', 'odtumist'); ?> &#128247;</span>
                <h2><?php esc_html_e('Etkinliklerden Kareler', 'odtumist'); ?></h2>
            </div>
        </div>
        <div class="events-gallery-grid">
            <?php foreach ($gallery_items as $item) : ?>
                <figure class="events-gallery-item">
                    <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title']); ?>" loading="lazy">
                    <figcaption class="events-gallery-caption">
                        <strong><?php echo esc_html($item['title']); ?></strong>
                        <?php if (!empty($item['desc'])) : ?>
                            <span><?php echo esc_html($item['desc']); ?></span>
                        <?php endif; ?>
                    </figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="events-social-feed">
    <div class="site-container">
        <h2><?php esc_html_e('Social Media Feed', 'odtumist'); ?></h2>
        <div class="prose-block">
            <?php echo do_shortcode('[odtumist_social_feed]'); ?>
        </div>
    </div>
</section>

<?php if (trim((string) get_the_content()) !== '') : ?>
    <section class="page-content-block">
        <div class="site-container prose-block">
            <?php the_content(); ?>
        </div>
    </section>
<?php endif; ?>
