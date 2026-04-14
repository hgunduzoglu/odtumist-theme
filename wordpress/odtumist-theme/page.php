<?php
get_header();
?>
<main id="site-content" class="site-main">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php if (odtumist_should_render_with_elementor(get_the_ID())) : ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('elementor-page-content'); ?>>
                    <?php the_content(); ?>
                </article>
            <?php else : ?>
                <?php
                $slug        = get_post_field('post_name', get_the_ID());
                $layout_slug = odtumist_get_page_layout_slug((string) $slug);
                get_template_part('template-parts/page/' . $layout_slug);
                ?>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php endif; ?>
</main>
<?php
get_footer();
