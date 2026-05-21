<?php
get_header();

$front_page_id         = (int) get_option('page_on_front');
$render_with_elementor = $front_page_id > 0 && odtumist_should_render_with_elementor($front_page_id);
?>
<main id="site-content" class="site-main">
    <?php if ($render_with_elementor && have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('elementor-page-content'); ?>>
                <?php the_content(); ?>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <?php get_template_part('template-parts/sections/hero'); ?>
        <?php get_template_part('template-parts/sections/events'); ?>
        <?php get_template_part('template-parts/sections/membership-ctas'); ?>
        <?php get_template_part('template-parts/sections/working-groups'); ?>
        <?php get_template_part('template-parts/sections/newsletter'); ?>
        <?php get_template_part('template-parts/sections/group-photo'); ?>

        <?php if ('page' === get_option('show_on_front') && have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <?php if (trim((string) get_the_content()) !== '') : ?>
                    <section class="page-content-block">
                        <div class="site-container prose-block">
                            <?php the_content(); ?>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php
get_footer();
