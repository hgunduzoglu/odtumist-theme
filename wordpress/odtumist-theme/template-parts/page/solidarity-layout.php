<?php
if (!defined('ABSPATH')) {
    exit;
}

$contact_page     = odtumist_get_page_by_slug(array('iletisim', 'contact'));
$current_page_id  = (int) get_the_ID();
$current_slug     = sanitize_title((string) get_post_field('post_name', $current_page_id));
$solidarity_root  = odtumist_get_page_by_slug(array('dayanisma', 'solidarity'));
$solidarity_root_id = ($solidarity_root instanceof WP_Post) ? (int) $solidarity_root->ID : 0;

$is_solidarity_root = in_array($current_slug, array('dayanisma', 'solidarity'), true) || ($solidarity_root_id > 0 && $solidarity_root_id === $current_page_id);
$content_source_id  = $is_solidarity_root ? $current_page_id : ($solidarity_root_id > 0 ? $solidarity_root_id : $current_page_id);

$raw_content   = (string) get_post_field('post_content', $content_source_id);
$sections_map  = odtumist_extract_content_sections($raw_content);
$sol_defaults  = odtumist_get_solidarity_section_defaults();
$hero_title    = $content_source_id > 0 ? get_the_title($content_source_id) : get_the_title();
$hero_excerpt  = (string) get_post_field('post_excerpt', $content_source_id);

$solidarity_initial_anchor_map = array(
    'networking' => 'networking',
    'burs' => 'burs',
    'maraton' => 'maraton',
    'mentorluk' => 'mentorluk',
    'bursiyerler' => 'bursiyerler',
    'gonulluler' => 'gonulluler',
    'bagiscilar' => 'bagiscilar',
    'paydaslar' => 'paydaslar',
);
$initial_anchor = isset($solidarity_initial_anchor_map[$current_slug]) ? $solidarity_initial_anchor_map[$current_slug] : '';
?>

<section class="solidarity-hero page-hero-dark">
    <div class="solidarity-hero-glow solidarity-hero-glow-left"></div>
    <div class="solidarity-hero-glow solidarity-hero-glow-right"></div>
    <div class="site-container solidarity-hero-inner">
        <span class="solidarity-kicker">&#10024; <?php esc_html_e('Dayanışma Ekosistemi', 'odtumist'); ?></span>
        <h1><?php echo esc_html($hero_title); ?></h1>
        <p>
            <?php
            if (trim($hero_excerpt) !== '') {
                echo esc_html($hero_excerpt);
            } else {
                esc_html_e('ODTÜ mezunu olmanın getirdiği bağ, ODTÜMİST çatısı altında ortak bir etki alanına dönüşüyor.', 'odtumist');
            }
            ?>
        </p>
    </div>
</section>

<section class="solidarity-sections" data-solidarity-initial="<?php echo esc_attr($initial_anchor); ?>">
    <?php foreach ($sol_defaults as $section) :
        $content_section = odtumist_pick_content_section($sections_map, array($section['id']));
        $tone = $section['tone'];
    ?>
        <div class="solidarity-item <?php echo esc_attr($tone); ?><?php echo ($initial_anchor === $section['id']) ? ' is-initial' : ''; ?>" id="<?php echo esc_attr($section['id']); ?>">
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
