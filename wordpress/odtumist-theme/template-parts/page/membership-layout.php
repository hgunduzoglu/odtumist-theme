<?php
if (!defined('ABSPATH')) {
    exit;
}

$ctas           = odtumist_get_primary_cta_links();
$tabs           = odtumist_get_membership_tab_defaults();
$current_page_id = (int) get_the_ID();
$current_slug    = sanitize_title((string) get_post_field('post_name', $current_page_id));
$membership_root = odtumist_get_page_by_slug(array('uyelik', 'membership'));
$membership_root_id = ($membership_root instanceof WP_Post) ? (int) $membership_root->ID : 0;

$is_membership_root = in_array($current_slug, array('uyelik', 'membership'), true) || ($membership_root_id > 0 && $membership_root_id === $current_page_id);
$content_source_id  = $is_membership_root ? $current_page_id : ($membership_root_id > 0 ? $membership_root_id : $current_page_id);

$raw_content = (string) get_post_field('post_content', $content_source_id);
$sections    = odtumist_extract_content_sections($raw_content);
$hero_title  = $content_source_id > 0 ? get_the_title($content_source_id) : get_the_title();
$hero_excerpt = (string) get_post_field('post_excerpt', $content_source_id);

$membership_initial_tab_map = array(
    'neden-uye-olmaliyim' => 'neden-uye-olmaliyim',
    'uyelik-avantajlari'  => 'uyelik-avantajlari',
    'bilgi-guncelleme'    => 'bilgi-guncelleme',
    'aidat-odeme'         => 'aidat-odeme',
    'nasil-uye-olabilirsiniz' => 'nasil-uye-olabilirsiniz',
    'yeni-mezunlar-icin-uyelik' => 'yeni-mezunlar-icin-uyelik',
    'uyelik-sss' => 'uyelik-sss',
);
$initial_tab = isset($membership_initial_tab_map[$current_slug]) ? $membership_initial_tab_map[$current_slug] : 'neden-uye-olmaliyim';
?>

<section class="page-hero page-hero-light">
    <div class="site-container">
        <h1><?php echo esc_html($hero_title); ?></h1>
        <p>
            <?php
            if (trim($hero_excerpt) !== '') {
                echo esc_html($hero_excerpt);
            } else {
                esc_html_e('ODTÜMİST üyeliği; dayanışma, aidiyet ve öğrencilere uzanan etkiyi büyüten güçlü bir topluluk çatısıdır.', 'odtumist');
            }
            ?>
        </p>
    </div>
</section>

<section class="membership-nav" data-membership-nav data-membership-initial="<?php echo esc_attr($initial_tab); ?>">
    <div class="site-container membership-nav-wrap">
        <div class="membership-nav-list" role="tablist" aria-label="<?php esc_attr_e('Üyelik Alt Menü', 'odtumist'); ?>">
            <?php foreach ($tabs as $i => $tab) : ?>
                <?php $is_active_tab = ($tab['id'] === $initial_tab); ?>
                <button type="button" class="membership-nav-btn<?php echo $is_active_tab ? ' is-active' : ''; ?>" data-membership-tab="<?php echo esc_attr($tab['id']); ?>" aria-selected="<?php echo $is_active_tab ? 'true' : 'false'; ?>"><?php echo esc_html($tab['label']); ?></button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="membership-panels">
    <div class="site-container">

        <!-- Neden Üye Olmalıyım -->
        <article class="membership-panel<?php echo $initial_tab === 'neden-uye-olmaliyim' ? ' is-active' : ''; ?>" data-membership-panel="neden-uye-olmaliyim" data-membership-anchors="neden-uye-olmaliyim">
            <?php $why_section = odtumist_pick_content_section($sections, array('neden-uye-olmaliyim', 'neden-uye-olmaliyim-2')); ?>
            <div class="membership-why-header">
                <h2><?php esc_html_e('Neden Üye Olmalıyım?', 'odtumist'); ?></h2>
                <div class="membership-divider"></div>
            </div>

            <?php if (!empty($why_section['body'])) : ?>
                <div class="membership-richtext prose-block"><?php echo wp_kses_post($why_section['body']); ?></div>
            <?php endif; ?>

            <div class="membership-why-steps">
                <?php
                $step_defaults = array(
                    1 => 'Dayanışma',
                    2 => 'Derneğin Varlığını Sürdürmesi',
                    3 => 'Mezunların Öğrencilere Fayda Sağlaması',
                    4 => 'Yeni Mezunların Aramıza Katılması',
                    5 => 'Camiamız Genişledikçe Dayanışmamız Büyür',
                );
                $step_icons = array('&#129309;', '&#127963;', '&#127891;', '&#10024;', '&#127758;');
                $step_bgs   = array('tone-orange', 'tone-blue', 'tone-red', 'tone-orange', 'tone-dark');
                $steps = array();
                for ($si = 1; $si <= 5; $si++) {
                    $steps[] = array(
                        'num'   => $si,
                        'title' => get_theme_mod("odtumist_memstep_{$si}_title", $step_defaults[$si]),
                        'icon'  => $step_icons[$si - 1],
                        'bg'    => $step_bgs[$si - 1],
                    );
                }
                foreach ($steps as $step) : ?>
                    <div class="why-step-card <?php echo esc_attr($step['bg']); ?>">
                        <span class="why-step-num"><?php echo esc_html((string) $step['num']); ?></span>
                        <span class="why-step-icon"><?php echo $step['icon']; ?></span>
                        <h3><?php echo esc_html($step['title']); ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="membership-final-cta cta-red-block">
                <h3><?php esc_html_e('Hadi Şimdi Bu Çatıda Buluşalım', 'odtumist'); ?></h3>
                <a class="btn btn-white" href="<?php echo esc_url($ctas['membership']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Şimdi Üye Ol', 'odtumist'); ?></a>
            </div>
        </article>

        <!-- Üyelik Avantajları -->
        <article class="membership-panel<?php echo $initial_tab === 'uyelik-avantajlari' ? ' is-active' : ''; ?>" data-membership-panel="uyelik-avantajlari" data-membership-anchors="uyelik-avantajlari">
            <?php $benefits_section = odtumist_pick_content_section($sections, array('uyelik-avantajlari')); ?>
            <div class="membership-why-header">
                <h2><?php esc_html_e('Üyelik Avantajları', 'odtumist'); ?></h2>
                <div class="membership-divider"></div>
            </div>

            <?php if (!empty($benefits_section['body'])) : ?>
                <div class="membership-richtext prose-block"><?php echo wp_kses_post($benefits_section['body']); ?></div>
            <?php endif; ?>

            <div class="membership-benefits-3p1">
                <div class="membership-benefits-grid">
                    <article class="membership-benefit-card">
                        <h3><?php esc_html_e('Dayanışma', 'odtumist'); ?></h3>
                        <p><?php esc_html_e('Mezunlar, öğrenciler ve gönüllüler arasında güçlü bir dayanışma ağına katılırsın.', 'odtumist'); ?></p>
                    </article>
                    <article class="membership-benefit-card">
                        <h3><?php esc_html_e('Etkinlikler', 'odtumist'); ?></h3>
                        <p><?php esc_html_e('Panel, seminer, kültür gezisi ve sosyal buluşmalara öncelikli erişim sağlarsın.', 'odtumist'); ?></p>
                    </article>
                    <article class="membership-benefit-card">
                        <h3><?php esc_html_e('Geri Verme', 'odtumist'); ?></h3>
                        <p><?php esc_html_e('Burs, mentorluk ve gönüllülük kanallarıyla öğrencilere dokunursun.', 'odtumist'); ?></p>
                    </article>
                </div>
                <article class="membership-benefit-plus">
                    <h3><?php esc_html_e('+1 İndirimler', 'odtumist'); ?></h3>
                    <p><?php esc_html_e('Üyelere özel kurum anlaşmaları ve dönemsel avantajlardan yararlanırsın.', 'odtumist'); ?></p>
                </article>
            </div>

            <div class="membership-final-cta cta-orange-block">
                <h3><?php esc_html_e('Hadi Şimdi Bu Çatıda Buluşalım', 'odtumist'); ?></h3>
                <p><?php esc_html_e('ODTÜ\'lülerle birlikte olma, birlikte üretme: İletişim ve Networking İmkanları!', 'odtumist'); ?></p>
                <a class="btn btn-white" href="<?php echo esc_url($ctas['membership']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Üye Ol', 'odtumist'); ?></a>
            </div>
        </article>

        <!-- Bilgi Güncelleme -->
        <article class="membership-panel<?php echo $initial_tab === 'bilgi-guncelleme' ? ' is-active' : ''; ?>" data-membership-panel="bilgi-guncelleme" data-membership-anchors="bilgi-guncelleme">
            <?php $update_section = odtumist_pick_content_section($sections, array('bilgi-guncelleme')); ?>
            <div class="membership-why-header">
                <h2><?php esc_html_e('Bilgi Güncelleme', 'odtumist'); ?></h2>
                <p class="membership-subtitle"><?php esc_html_e('Ağımızı canlı tutmak için iletişim bilgilerinizi güncel tutmanız bizim için çok değerli Hocam.', 'odtumist'); ?></p>
            </div>

            <?php if (!empty($update_section['body'])) : ?>
                <div class="membership-richtext prose-block"><?php echo wp_kses_post($update_section['body']); ?></div>
            <?php endif; ?>

            <div class="membership-richtext prose-block" style="margin-top:1rem;">
                <?php echo do_shortcode('[odtumist_contact_form provider="wpforms"]'); ?>
            </div>
        </article>

        <!-- Aidat Ödeme -->
        <article class="membership-panel<?php echo $initial_tab === 'aidat-odeme' ? ' is-active' : ''; ?>" data-membership-panel="aidat-odeme" data-membership-anchors="aidat-odeme">
            <?php $dues_section = odtumist_pick_content_section($sections, array('aidat-odeme')); ?>
            <div class="membership-why-header" style="text-align:center">
                <h2><?php esc_html_e('Aidat Ödeme', 'odtumist'); ?></h2>
                <p class="membership-subtitle"><?php esc_html_e('Aidat borcunuzu görüntülemek, tek seferde veya taksitle ödemek için Fonzip\'e giriş yapın.', 'odtumist'); ?></p>
            </div>

            <?php if (!empty($dues_section['body'])) : ?>
                <div class="membership-richtext prose-block"><?php echo wp_kses_post($dues_section['body']); ?></div>
            <?php endif; ?>

            <div style="text-align:center; margin-top:2rem;">
                <a class="btn btn-solid" href="https://fonzip.com/odtumist/odeme" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Aidat Ödeme Sayfasına Git', 'odtumist'); ?></a>
            </div>
        </article>

        <!-- Nasıl Üye Olabilirsiniz -->
        <article class="membership-panel<?php echo $initial_tab === 'nasil-uye-olabilirsiniz' ? ' is-active' : ''; ?>" data-membership-panel="nasil-uye-olabilirsiniz" data-membership-anchors="nasil-uye-olabilirsiniz">
            <?php $howto_section = odtumist_pick_content_section($sections, array('nasil-uye-olabilirsiniz')); ?>
            <div class="membership-why-header">
                <h2><?php esc_html_e('Nasıl Üye Olabilirsiniz?', 'odtumist'); ?></h2>
                <p class="membership-subtitle"><?php esc_html_e('Başvuru adımlarını takip ederek kısa sürede ODTÜMİST ağına katılabilirsiniz.', 'odtumist'); ?></p>
            </div>

            <?php if (!empty($howto_section['body'])) : ?>
                <div class="membership-richtext prose-block"><?php echo wp_kses_post($howto_section['body']); ?></div>
            <?php endif; ?>

            <div style="margin-top:1.5rem;">
                <a class="btn btn-solid" href="<?php echo esc_url($ctas['membership']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Üyelik Başvurusuna Git', 'odtumist'); ?></a>
            </div>
        </article>

        <!-- Yeni Mezunlar İçin Üyelik -->
        <article class="membership-panel<?php echo $initial_tab === 'yeni-mezunlar-icin-uyelik' ? ' is-active' : ''; ?>" data-membership-panel="yeni-mezunlar-icin-uyelik" data-membership-anchors="yeni-mezunlar-icin-uyelik">
            <?php $newgrad_section = odtumist_pick_content_section($sections, array('yeni-mezunlar-icin-uyelik')); ?>
            <div class="membership-why-header">
                <h2><?php esc_html_e('Yeni Mezunlar İçin Üyelik', 'odtumist'); ?></h2>
                <p class="membership-subtitle"><?php esc_html_e('Yeni mezunlara özel etkinlik, mentorluk ve dayanışma fırsatlarını bu bölümden takip edebilirsiniz.', 'odtumist'); ?></p>
            </div>

            <?php if (!empty($newgrad_section['body'])) : ?>
                <div class="membership-richtext prose-block"><?php echo wp_kses_post($newgrad_section['body']); ?></div>
            <?php endif; ?>

            <div style="margin-top:1.5rem;">
                <a class="btn btn-solid" href="<?php echo esc_url($ctas['membership']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Yeni Mezun Başvurusu', 'odtumist'); ?></a>
            </div>
        </article>

        <!-- Üyelik SSS -->
        <article class="membership-panel<?php echo $initial_tab === 'uyelik-sss' ? ' is-active' : ''; ?>" data-membership-panel="uyelik-sss" data-membership-anchors="uyelik-sss">
            <?php $faq_section = odtumist_pick_content_section($sections, array('uyelik-sss')); ?>
            <div class="membership-why-header">
                <h2><?php esc_html_e('Üyelik SSS', 'odtumist'); ?></h2>
                <p class="membership-subtitle"><?php esc_html_e('Sık sorulan sorulara ek olarak farklı bir konuda desteğe ihtiyaç duyarsanız bize ulaşabilirsiniz.', 'odtumist'); ?></p>
            </div>

            <?php if (!empty($faq_section['body'])) : ?>
                <div class="membership-richtext prose-block"><?php echo wp_kses_post($faq_section['body']); ?></div>
            <?php endif; ?>

            <div style="margin-top:1.5rem;">
                <a class="btn btn-solid" href="<?php echo esc_url(home_url('/iletisim/')); ?>"><?php esc_html_e('İletişime Geç', 'odtumist'); ?></a>
            </div>
        </article>

    </div>
</section>
