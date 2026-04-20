<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$about_page = function_exists('odtumist_get_page_by_slug')
    ? odtumist_get_page_by_slug(array('hakkimizda', 'about'))
    : null;

$back_url = ($about_page instanceof WP_Post)
    ? trailingslashit(get_permalink($about_page)) . '#calisma-gruplarimiz'
    : home_url('/hakkimizda/#calisma-gruplarimiz');
?>
<main id="site-content" class="site-main">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class('team-single'); ?>>
                <section class="page-hero page-hero-dark team-single-hero">
                    <div class="site-container">
                        <h1><?php the_title(); ?></h1>
                    </div>
                </section>

                <?php if (has_post_thumbnail()) : ?>
                    <section class="team-single-media-block">
                        <div class="site-container">
                            <figure class="team-single-media">
                                <?php the_post_thumbnail('large', array('loading' => 'eager')); ?>
                            </figure>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="page-content-block team-single-content-block">
                    <div class="site-container prose-block team-single-content">
                        <?php
                        $raw_content = (string) get_the_content();
                        $title_text  = trim(wp_strip_all_tags(get_the_title()));

                        // İçerik editöründe aynı başlık tekrar yazıldıysa tekil sayfada çakışmayı önle.
                        if ($title_text !== '') {
                            $pattern = '/^\s*<(h[1-3])[^>]*>\s*' . preg_quote($title_text, '/') . '\s*<\/\1>\s*/iu';
                            $raw_content = (string) preg_replace($pattern, '', $raw_content, 1);
                        }

                        echo apply_filters('the_content', $raw_content);
                        ?>
                    </div>
                </section>

                <section class="team-single-back">
                    <div class="site-container">
                        <a class="btn btn-secondary" href="<?php echo esc_url($back_url); ?>">
                            <?php esc_html_e('Çalışma Gruplarına Dön', 'odtumist'); ?>
                        </a>
                    </div>
                </section>
            </article>
        <?php endwhile; ?>
    <?php endif; ?>
</main>
<?php
get_footer();
