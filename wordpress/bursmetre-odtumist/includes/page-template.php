<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header();
?>
<main id="site-content" class="site-main bursmetre-page">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('bursmetre-page-content'); ?>>
            <?php the_content(); ?>
        </article>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
