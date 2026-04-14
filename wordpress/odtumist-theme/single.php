<?php
get_header();
?>
<main id="site-content" class="site-main">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class(); ?>>
                <section class="page-hero page-hero-light">
                    <div class="site-container">
                        <h1><?php the_title(); ?></h1>
                        <p>
                            <?php
                            if (get_the_excerpt()) {
                                echo esc_html(get_the_excerpt());
                            } else {
                                echo esc_html(get_the_date('d M Y'));
                            }
                            ?>
                        </p>
                    </div>
                </section>

                <?php if (has_post_thumbnail()) : ?>
                    <section class="archive-listing">
                        <div class="site-container">
                            <figure class="gallery-grid" style="grid-template-columns:1fr;">
                                <?php the_post_thumbnail('full', array('loading' => 'eager')); ?>
                            </figure>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="page-content-block">
                    <div class="site-container prose-block">
                        <?php the_content(); ?>
                    </div>
                </section>
            </article>
        <?php endwhile; ?>
    <?php endif; ?>
</main>
<?php
get_footer();
