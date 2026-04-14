<?php
get_header();
?>
<main id="site-content" class="site-main">
    <section class="page-hero page-hero-light">
        <div class="site-container">
            <h1><?php echo esc_html(get_the_title(get_option('page_for_posts')) ?: __('Haberler', 'odtumist')); ?></h1>
        </div>
    </section>

    <section class="archive-listing">
        <div class="site-container archive-grid">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article <?php post_class('archive-card'); ?>>
                        <a class="archive-thumb" href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
                            <?php else : ?>
                                <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=900" alt="<?php the_title_attribute(); ?>" loading="lazy">
                            <?php endif; ?>
                        </a>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p><?php echo esc_html(get_the_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 24)); ?></p>
                    </article>
                <?php endwhile; ?>

                <div class="pagination-wrap">
                    <?php the_posts_pagination(); ?>
                </div>
            <?php else : ?>
                <p class="empty-state"><?php esc_html_e('Henüz içerik bulunmuyor.', 'odtumist'); ?></p>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php
get_footer();
