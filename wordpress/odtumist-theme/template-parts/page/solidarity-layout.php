<?php
if (!defined('ABSPATH')) {
    exit;
}

$contact_page  = odtumist_get_page_by_slug(array('iletisim', 'contact'));
$raw_content   = (string) get_post_field('post_content', get_the_ID());
$sections_map  = odtumist_extract_content_sections($raw_content);
$sol_defaults  = odtumist_get_solidarity_section_defaults();
?>

<section class="solidarity-hero page-hero-dark">
    <div class="solidarity-hero-glow solidarity-hero-glow-left"></div>
    <div class="solidarity-hero-glow solidarity-hero-glow-right"></div>
    <div class="site-container solidarity-hero-inner">
        <span class="solidarity-kicker">&#10024; <?php esc_html_e('Dayanışma Ekosistemi', 'odtumist'); ?></span>
        <h1><?php the_title(); ?></h1>
        <p>
            <?php
            if (get_the_excerpt()) {
                echo esc_html(get_the_excerpt());
            } else {
                esc_html_e('ODTÜ mezunu olmanın getirdiği bağ, ODTÜMİST çatısı altında ortak bir etki alanına dönüşüyor.', 'odtumist');
            }
            ?>
        </p>
    </div>
</section>

<section class="solidarity-sections">
    <?php foreach ($sol_defaults as $section) :
        $content_section = odtumist_pick_content_section($sections_map, array($section['id']));
        $tone = $section['tone'];
    ?>
        <div class="solidarity-item <?php echo esc_attr($tone); ?>" id="<?php echo esc_attr($section['id']); ?>">
            <div class="site-container solidarity-item-inner">
                <div class="solidarity-icon-wrap <?php echo esc_attr($tone); ?>">
                    <span class="solidarity-icon"><?php echo $section['icon']; ?></span>
                </div>
                <div class="solidarity-item-content">
                    <h2><?php echo esc_html($section['title']); ?></h2>
                    <?php if (!empty($content_section['body'])) : ?>
                        <div class="solidarity-richtext"><?php echo wp_kses_post($content_section['body']); ?></div>
                    <?php endif; ?>
                    <a class="btn <?php echo (strpos($tone, 'dark') !== false || strpos($tone, 'blue') !== false || strpos($tone, 'red') !== false || strpos($tone, 'orange') !== false) ? 'btn-white' : 'btn-dark'; ?>" href="<?php echo esc_url($contact_page ? get_permalink($contact_page) : home_url('/iletisim')); ?>"><?php echo esc_html(strtoupper($section['btn'])); ?></a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<section class="solidarity-cta">
    <div class="site-container">
        <h2><?php esc_html_e('ODTÜ Ruhunu Şimdi Yaşatın', 'odtumist'); ?></h2>
        <p><?php esc_html_e('Siz hangi alanda dayanışmaya katılmak istersiniz Hocam?', 'odtumist'); ?></p>
        <a class="btn btn-white" href="<?php echo esc_url($contact_page ? get_permalink($contact_page) : home_url('/iletisim')); ?>"><?php esc_html_e('İletişime Geçin', 'odtumist'); ?></a>
    </div>
</section>
