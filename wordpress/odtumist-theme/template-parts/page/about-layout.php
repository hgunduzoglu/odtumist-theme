<?php
if (!defined('ABSPATH')) {
    exit;
}

$raw_content = (string) get_post_field('post_content', get_the_ID());
$sections    = odtumist_extract_content_sections($raw_content);

$doing_section      = odtumist_pick_content_section($sections, array('neler-yapiyoruz'));
$groups_section     = odtumist_pick_content_section($sections, array('calisma-gruplarimiz', 'calisma-gruplarimiz-2'));
$join_section       = odtumist_pick_content_section($sections, array('sen-de-katil', 'sen-de-katil-hocam'));
$history_section    = odtumist_pick_content_section($sections, array('tarihce'));
$management_section = odtumist_pick_content_section($sections, array('yonetim'));

$groups_query = odtumist_get_working_groups(12);
$ctas         = odtumist_get_primary_cta_links();
$contact_page = odtumist_get_page_by_slug(array('iletisim', 'contact'));

$about_intro = get_the_excerpt()
    ? get_the_excerpt()
    : "İstanbul'un dinamizminde ODTÜ ruhunu, dayanışmasını ve kültürünü yaşatan topluluğumuza hoş geldin.";

$history_facts = array();
$hf_defaults = array(
    1 => array('title' => 'Efsanevi "Et Arabası"', 'desc' => '1970 öncesi kampüste servis aracı olarak kullanılan ve öğrencilerin "Et Arabası" dediği meşhur kırmızı otobüslerden sonuncusu, hurdaya gitmek üzereyken Mersin\'de bulunup İstanbul\'a getirildi.', 'image' => 'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&q=80&w=800'),
    2 => array('title' => 'Bilim Ağacı\'nın Taşınma Hikayesi', 'desc' => 'Hazırlık okulunun oradaki Bilim Ağacı heykelinin görünürlüğü azaldığında, derneğin ısrarlı çabalarıyla 1991 yılında bugünkü yerine taşındı.', 'image' => 'https://images.unsplash.com/photo-1549490349-8643362247b5?auto=format&fit=crop&q=80&w=800'),
    3 => array('title' => 'Beyaz Masa\'nın Doğuşu', 'desc' => '1994-95 yıllarında derneğin öncülük ettiği çevre platformu çalışmaları, İstanbul Büyükşehir Belediyesi\'ndeki Beyaz Masa modelinin kurulmasına katkı sundu.', 'image' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&q=80&w=800'),
    4 => array('title' => '"Bi\' Dünya ODTÜ\'lü"', 'desc' => 'Pandemi döneminde fiziksel buluşmalar iptal olunca, dernek 28 farklı oturumla küresel bir dijital mezun buluşması organize etti.', 'image' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&q=80&w=800'),
);
for ($hf_i = 1; $hf_i <= 4; $hf_i++) {
    $history_facts[] = array(
        'title' => get_theme_mod("odtumist_history_{$hf_i}_title", $hf_defaults[$hf_i]['title']),
        'desc'  => get_theme_mod("odtumist_history_{$hf_i}_desc", $hf_defaults[$hf_i]['desc']),
        'image' => get_theme_mod("odtumist_history_{$hf_i}_image", $hf_defaults[$hf_i]['image']),
    );
}

$about_tab_defaults = array(
    1 => 'Neler Yapıyoruz?',
    2 => 'Çalışma Gruplarımız',
    3 => 'Sen de Katıl Hocam!',
    4 => 'Tarihçe',
    5 => 'Yönetim',
);
$about_tabs = array(
    array('id' => 'doing',      'label' => get_theme_mod('odtumist_abouttab_1_label', $about_tab_defaults[1]), 'icon' => '&#10024;'),
    array('id' => 'groups',     'label' => get_theme_mod('odtumist_abouttab_2_label', $about_tab_defaults[2]), 'icon' => '&#127919;'),
    array('id' => 'join',       'label' => get_theme_mod('odtumist_abouttab_3_label', $about_tab_defaults[3]), 'icon' => '&#128101;'),
    array('id' => 'history',    'label' => get_theme_mod('odtumist_abouttab_4_label', $about_tab_defaults[4]), 'icon' => '&#128336;'),
    array('id' => 'management', 'label' => get_theme_mod('odtumist_abouttab_5_label', $about_tab_defaults[5]), 'icon' => '&#8505;'),
);
?>

<section class="about-hero">
    <div class="about-hero-glow about-hero-glow-left"></div>
    <div class="about-hero-glow about-hero-glow-right"></div>

    <div class="site-container about-hero-inner">
        <h1 class="about-hero-title">MERHABA HOCAM!</h1>
        <p class="about-hero-intro"><?php echo esc_html($about_intro); ?></p>
        <p class="about-hero-motto">&#10084; Dayanışma gücümüzdür.</p>
    </div>
</section>

<section class="about-page-nav" data-about-nav>
    <div class="site-container about-page-nav-wrap">
        <div class="about-page-nav-list" role="tablist" aria-label="<?php esc_attr_e('Hakkımızda Alt Menü', 'odtumist'); ?>">
            <?php foreach ($about_tabs as $i => $tab) : ?>
                <button type="button" class="about-page-nav-btn<?php echo $i === 0 ? ' is-active' : ''; ?>" data-about-tab="<?php echo esc_attr($tab['id']); ?>" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>">
                    <span class="about-tab-icon"><?php echo $tab['icon']; ?></span>
                    <?php echo esc_html($tab['label']); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="about-panels">
    <div class="site-container">

        <!-- Neler Yapıyoruz -->
        <article class="about-tab-panel is-active" data-about-panel="doing" data-about-anchors="neler-yapiyoruz">
            <section id="neler-yapiyoruz">
                <h2 class="about-panel-title"><?php esc_html_e('Etkileşimi Güçlendiren Bir Köprü', 'odtumist'); ?></h2>
                <div class="about-richtext">
                    <?php if (!empty($doing_section['body'])) : ?>
                        <?php echo wp_kses_post($doing_section['body']); ?>
                    <?php elseif (trim($raw_content) !== '') : ?>
                        <?php echo wp_kses_post(apply_filters('the_content', $raw_content)); ?>
                    <?php endif; ?>
                </div>
                <div class="about-quote-block">
                    <p><?php esc_html_e('"Bütün bu faydayı, Çalışma Gruplarımızın gönüllü katkıları ile devam ettiriyoruz."', 'odtumist'); ?></p>
                </div>
            </section>
            <?php odtumist_render_about_pagination($about_tabs, 'doing'); ?>
        </article>

        <!-- Çalışma Gruplarımız -->
        <article class="about-tab-panel" data-about-panel="groups" data-about-anchors="calisma-gruplarimiz">
            <section id="calisma-gruplarimiz">
                <h2 class="about-panel-title"><?php esc_html_e('Çalışma Gruplarımız', 'odtumist'); ?></h2>

                <?php if (!empty($groups_section['body'])) : ?>
                    <div class="about-richtext about-groups-intro">
                        <?php echo wp_kses_post($groups_section['body']); ?>
                    </div>
                <?php endif; ?>

                <div class="about-groups-grid">
                    <?php if ($groups_query->have_posts()) : ?>
                        <?php while ($groups_query->have_posts()) : $groups_query->the_post(); ?>
                            <article class="about-group-card">
                                <a class="about-group-media" href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
                                    <?php else : ?>
                                        <img src="https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&q=80&w=800" alt="<?php the_title_attribute(); ?>" loading="lazy">
                                    <?php endif; ?>
                                    <span class="about-group-badge"><?php esc_html_e('Keşfet', 'odtumist'); ?></span>
                                </a>
                                <div class="about-group-body">
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p><?php echo esc_html(get_the_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 18)); ?></p>
                                    <a class="about-group-link" href="<?php the_permalink(); ?>"><?php esc_html_e('Detaylı İncele', 'odtumist'); ?> &rarr;</a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    <?php else : ?>
                        <p class="empty-state"><?php esc_html_e('Henüz yayınlanmış çalışma grubu bulunmuyor.', 'odtumist'); ?></p>
                    <?php endif; ?>
                </div>
            </section>
            <?php odtumist_render_about_pagination($about_tabs, 'groups'); ?>
        </article>

        <!-- Sen de Katıl -->
        <article class="about-tab-panel" data-about-panel="join" data-about-anchors="sen-de-katil">
            <section id="sen-de-katil">
                <h2 class="about-panel-title"><?php esc_html_e('ODTÜ Ruhunu Birlikte Yaşatalım', 'odtumist'); ?></h2>

                <div class="about-join-grid">
                    <a class="about-join-card" href="<?php echo esc_url($ctas['membership']); ?>" target="_blank" rel="noopener noreferrer">
                        <span class="about-join-icon">&#128100;</span>
                        <span class="about-join-title"><?php esc_html_e('Üye Ol', 'odtumist'); ?></span>
                    </a>
                    <a class="about-join-card about-join-card-red" href="<?php echo esc_url($contact_page ? get_permalink($contact_page) : home_url('/iletisim/')); ?>">
                        <span class="about-join-icon">&#10084;</span>
                        <span class="about-join-title"><?php esc_html_e('Gönüllü Ol', 'odtumist'); ?></span>
                    </a>
                </div>

                <?php if (!empty($join_section['body'])) : ?>
                    <div class="about-richtext">
                        <?php echo wp_kses_post($join_section['body']); ?>
                    </div>
                <?php endif; ?>
            </section>
            <?php odtumist_render_about_pagination($about_tabs, 'join'); ?>
        </article>

        <!-- Tarihçe -->
        <article class="about-tab-panel" data-about-panel="history" data-about-anchors="tarihce">
            <section id="tarihce">
                <div class="about-history-intro">
                    <h2><?php esc_html_e('Bir Meşalenin İstanbul Yolculuğu', 'odtumist'); ?></h2>
                    <?php if (!empty($history_section['body'])) : ?>
                        <div class="about-richtext"><?php echo wp_kses_post($history_section['body']); ?></div>
                    <?php else : ?>
                        <p><?php esc_html_e('ODTÜMİST 1986 yılında İstanbul\'daki ODTÜ\'lülerin emekleriyle şube olarak yolculuğuna başladı ve 2001 yılında bağımsız bir dernek oldu. 40 yıldır binlerce gönüllünün enerjisiyle ODTÜ Ruhu\'nu İstanbul\'da yaşatıyoruz.', 'odtumist'); ?></p>
                    <?php endif; ?>
                </div>

                <h3 class="about-history-subtitle">&#10067; <?php esc_html_e('Biliyor Muydunuz?', 'odtumist'); ?></h3>
                <div class="about-history-grid">
                    <?php foreach ($history_facts as $fact) : ?>
                        <article class="about-history-card">
                            <div class="about-history-media">
                                <img src="<?php echo esc_url($fact['image']); ?>" alt="<?php echo esc_attr($fact['title']); ?>" loading="lazy">
                                <div class="about-history-media-overlay">
                                    <h4><?php echo esc_html($fact['title']); ?></h4>
                                </div>
                            </div>
                            <div class="about-history-body">
                                <p><?php echo esc_html($fact['desc']); ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php odtumist_render_about_pagination($about_tabs, 'history'); ?>
        </article>

        <!-- Yönetim -->
        <article class="about-tab-panel" data-about-panel="management" data-about-anchors="yonetim">
            <section id="yonetim">
                <div class="about-management-intro">
                    <h2><?php esc_html_e('Yönetim', 'odtumist'); ?></h2>
                    <p><?php esc_html_e('Mevcut ve geçmiş yönetimler, çalışma gruplarımız, tüzük ve faaliyet raporlarımızı görüntüleyebilirsiniz.', 'odtumist'); ?></p>
                </div>

                <?php if (!empty($management_section['body'])) : ?>
                    <div class="about-richtext">
                        <?php echo wp_kses_post($management_section['body']); ?>
                    </div>
                <?php endif; ?>

                <div class="about-management-grid">
                    <?php
                    $mgmt_card_defaults = array(
                        1 => array('title' => 'Dernek Yönetim Organları', 'desc' => 'Yönetim Kurulu, Denetleme Kurulu, Disiplin Kurulu ve Danışma Kurulu üyelerimizin biyografileri.'),
                        2 => array('title' => 'Çalışma Gruplarımız', 'desc' => 'Derneğimizi yaşatan çalışma gruplarımızın katkılarıyla büyümeye devam ediyoruz.'),
                        3 => array('title' => 'Geçmiş Yönetimler', 'desc' => '1986\'dan bugüne derneğimize emek vermiş tüm kurullarımız ve yöneticilerimiz.'),
                        4 => array('title' => 'Dernek Tüzüğü ve Yönetmelikler', 'desc' => 'Şeffaf yönetişim ilkelerimiz, tüzüğümüz ve çalışma yönetmeliklerimiz.'),
                        5 => array('title' => 'Faaliyet Raporları', 'desc' => 'Yıllık çalışma raporlarımız, mali tablolarımız ve kurumsal başarı hikayelerimiz.'),
                    );
                    $mgmt_icons   = array('&#128737;', '&#127919;', '&#128336;', '&#128196;', '&#128188;');
                    $mgmt_accents = array('blue', 'red', 'dark', 'blue', 'red');
                    $mgmt_cards = array();
                    for ($mc = 1; $mc <= 5; $mc++) {
                        $mgmt_cards[] = array(
                            'icon'   => $mgmt_icons[$mc - 1],
                            'title'  => get_theme_mod("odtumist_mgmt_{$mc}_title", $mgmt_card_defaults[$mc]['title']),
                            'desc'   => get_theme_mod("odtumist_mgmt_{$mc}_desc", $mgmt_card_defaults[$mc]['desc']),
                            'accent' => $mgmt_accents[$mc - 1],
                        );
                    }
                    foreach ($mgmt_cards as $card) : ?>
                        <article class="about-management-card about-mgmt-<?php echo esc_attr($card['accent']); ?>">
                            <span class="about-mgmt-icon"><?php echo $card['icon']; ?></span>
                            <div class="about-mgmt-content">
                                <h3><?php echo esc_html($card['title']); ?></h3>
                                <p><?php echo esc_html($card['desc']); ?></p>
                            </div>
                            <span class="about-mgmt-arrow">&rsaquo;</span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php odtumist_render_about_pagination($about_tabs, 'management'); ?>
        </article>

    </div>
</section>

<?php
/* Pagination helper - rendered inline */
function odtumist_render_about_pagination($tabs, $current_id)
{
    $ids = array_column($tabs, 'id');
    $idx = array_search($current_id, $ids, true);
    if ($idx === false) {
        return;
    }
    $prev = $idx > 0 ? $tabs[$idx - 1] : null;
    $next = $idx < count($tabs) - 1 ? $tabs[$idx + 1] : null;
    ?>
    <div class="about-pagination">
        <?php if ($prev) : ?>
            <button type="button" class="about-page-nav-btn about-pag-btn" data-about-tab="<?php echo esc_attr($prev['id']); ?>">
                &larr; <?php echo esc_html('Önceki: ' . $prev['label']); ?>
            </button>
        <?php else : ?>
            <span></span>
        <?php endif; ?>
        <?php if ($next) : ?>
            <button type="button" class="about-page-nav-btn about-pag-btn about-pag-next" data-about-tab="<?php echo esc_attr($next['id']); ?>">
                <?php echo esc_html('Sonraki: ' . $next['label']); ?> &rarr;
            </button>
        <?php else : ?>
            <span></span>
        <?php endif; ?>
    </div>
    <?php
}
