<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/yurtdisi-data.php';

function bursmetre_yurtdisi_format_breakdown_tooltip($breakdown)
{
    if (empty($breakdown)) {
        return '';
    }

    $parts = [];
    foreach ($breakdown as $entry) {
        $parts[] = $entry['formatted'];
    }
    return implode(' · ', $parts);
}

function bursmetre_render_yurtdisi_dashboard()
{
    $data = bursmetre_yurtdisi_get_data();
    $options = get_option('bursmetre_options', []);

    $goal_raw = isset($options['yurtdisi_goal_amount']) ? $options['yurtdisi_goal_amount'] : '';
    $goal = !empty($data['goal_try']) ? $data['goal_try'] : (bursmetre_yurtdisi_parse_amount($goal_raw) ?? 0);

    $total_try = isset($data['total_try']) ? $data['total_try'] : 0.0;
    $progress = ($goal > 0) ? round(($total_try / $goal) * 100, 1) : 0;
    $campaign_count = isset($data['campaigns']) ? count($data['campaigns']) : 0;
    $students = $total_try > 0 ? max(0, (int) floor($total_try / 49500)) : 0;

    $breakdown_tooltip = bursmetre_yurtdisi_format_breakdown_tooltip($data['currency_breakdown']);
    $chart_payload = isset($data['chart_payload']) ? wp_json_encode($data['chart_payload'], JSON_UNESCAPED_UNICODE) : '{}';
    $error_message = isset($data['error']) ? $data['error'] : '';

    $last_updated_raw = isset($data['last_updated']) ? $data['last_updated'] : '';
    $timestamp = $last_updated_raw ? strtotime($last_updated_raw) : false;
    $formatted_last_updated = $timestamp
        ? wp_date('d F Y H:i', $timestamp)
        : __('Bilinmiyor', 'bursmetre');

    $yurti_ci_url = apply_filters('bursmetre_dashboard_home_url', bursmetre_get_dashboard_url());
    $rank_list = isset($data['campaigns']) ? $data['campaigns'] : [];

    ob_start();
    ?>
    <div class="burs-dashboard burs-dashboard--yurtdisi">
        <div class="burs-breadcrumb">
            <a class="burs-breadcrumb__back" href="<?php echo esc_url($yurti_ci_url); ?>">
                ← <?php esc_html_e('Yurt İçi Bursmetre', 'bursmetre'); ?>
            </a>
            <span class="burs-breadcrumb__meta">
                <?php
                printf(
                    esc_html__('Son güncelleme: %s', 'bursmetre'),
                    esc_html($formatted_last_updated)
                );
                ?>
            </span>
        </div>

        <?php if (!empty($error_message)) : ?>
            <div class="burs-alert burs-alert--warning">
                <?php echo esc_html($error_message); ?>
            </div>
        <?php endif; ?>

        <section class="burs-hero">
            <div class="burs-hero__body">
                <div class="burs-hero__top">
                    <div class="burs-hero__meta">
                        <span class="burs-hero__chip burs-hero__chip--meta">
                            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 3l9 4v4c0 5.52-4.48 10-10 10S2 16.52 2 11V7zM4 8.2v2.8c0 4.41 3.59 8 8 8s8-3.59 8-8V8.2l-8-3.6zM12 9a3 3 0 0 1 2.82 4H15a2 2 0 0 0-2-2a2 2 0 0 0-1.87 1.25l-1.84-.78A3.99 3.99 0 0 1 12 9Z"/></svg>
                            <?php echo esc_html(sprintf('%d yurtdışı kampanya', $campaign_count)); ?>
                        </span>
                    </div>
                    <div class="burs-hero__actions">
                        <a class="burs-btn burs-btn--ghost" href="<?php echo esc_url($yurti_ci_url); ?>">
                            <span class="burs-btn__icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="m4 12l7-7v4h9v6h-9v4z"/></svg>
                            </span>
                            <?php esc_html_e('Yurt İçi Bursmetre', 'bursmetre'); ?>
                        </a>
                    </div>
                </div>
                <h1><?php esc_html_e('Yurtdışı Kampanyaları', 'bursmetre'); ?></h1>
                <p><?php esc_html_e('Yurtdışı burs kampanyalarının güncel durumu', 'bursmetre'); ?></p>
                <div class="burs-hero__sub">
                    <span class="burs-hero__chip burs-hero__chip--students">
                        <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 3L1 9l11 6l9-4.91V17h2V9zM12 21l-8-4.5V11l8 4.5l8-4.5v5.5z"/></svg>
                        <span><strong><?php echo esc_html(number_format_i18n($students)); ?></strong> <?php esc_html_e('Öğrencinin Bursu Toplandı', 'bursmetre'); ?></span>
                    </span>
                </div>
                <div class="burs-hero__progress">
                    <div class="burs-progress">
                        <div class="burs-progress__inner" data-progress="<?php echo esc_attr($progress); ?>"></div>
                    </div>
                    <div class="burs-hero__totals" title="<?php echo esc_attr($breakdown_tooltip); ?>">
                        <div>
                            <span class="burs-hero__label"><?php esc_html_e('Toplam Yurtdışı Bağış', 'bursmetre'); ?></span>
                            <strong><?php echo esc_html(bursmetre_yurtdisi_format_try($total_try)); ?></strong>
                            <span class="burs-hero__divider">/</span>
                            <span>
                                <?php
                                echo $goal > 0
                                    ? esc_html(bursmetre_yurtdisi_format_try($goal))
                                    : esc_html__('Hedef Tanımlanmadı', 'bursmetre');
                                ?>
                            </span>
                        </div>
                        <div class="burs-hero__percentage">%<?php echo esc_html(number_format_i18n($progress)); ?></div>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($data['fx']['date'])) : ?>
            <p class="burs-fx-note">
                <?php echo esc_html('TCMB döviz alış · ' . date_i18n('d.m.Y', strtotime($data['fx']['date'] . ' UTC'))); ?>
                <?php foreach ($data['currency_breakdown'] as $entry) : ?>
                    <?php if ($entry['currency'] !== 'TRY') : ?>
                        <span><?php echo esc_html('1 ' . $entry['currency'] . ' = ' . number_format($data['fx']['rates'][$entry['currency']], 4, ',', '.') . ' TL'); ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </p>
        <?php endif; ?>

        <section class="burs-stats">
            <article class="burs-stat burs-stat--primary">
                <div class="burs-stat__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 1a9 9 0 0 0-9 9h2a7 7 0 1 1 7 7v2.07A9.001 9.001 0 0 0 12 1m-.5 5v2.09A3 3 0 0 0 9 11c0 1.31.84 2.42 2 2.83V17h1v-3.17A3 3 0 0 0 15 11h-1a2 2 0 0 1-2 2a2 2 0 0 1 0-4h2V7h-1.5V5z"/></svg>
                </div>
                <h3><?php esc_html_e('Toplam Bağış', 'bursmetre'); ?></h3>
                <p>
                    <?php echo esc_html(bursmetre_yurtdisi_format_try($total_try)); ?>
                    <small class="burs-stat__sub"><?php echo esc_html($breakdown_tooltip); ?></small>
                </p>
            </article>
            <article class="burs-stat burs-stat--focus">
                <div class="burs-stat__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 8a4 4 0 1 0 4 4h2a6 6 0 1 1-6-6z"/><path fill="currentColor" d="M22 13h-2.05A8.005 8.005 0 0 1 11 4.05V2h2V0h-2a1 1 0 0 0-1 1v2.05A8.005 8.005 0 0 0 4.05 11H2v2h2.05A8.005 8.005 0 0 0 11 19.95V22h2v-2.05A8.005 8.005 0 0 0 19.95 13H22z"/></svg>
                </div>
                <h3><?php esc_html_e('Aktif Kampanya', 'bursmetre'); ?></h3>
                <p><?php echo esc_html(number_format_i18n($campaign_count)); ?></p>
            </article>
            <article class="burs-stat burs-stat--info">
                <div class="burs-stat__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3s1.34 3 3 3m-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5S5 6.34 5 8s1.34 3 3 3m0 2c-2.33 0-7 1.17-7 3.5V20h14v-3.5c0-2.33-4.67-3.5-7-3.5m8 0c-.29 0-.62.02-.97.05c1.16.84 1.97 1.97 1.97 3.45V20h6v-3.5c0-2.33-4.67-3.5-7-3.5"/></svg>
                </div>
                <h3><?php esc_html_e('Son Güncelleme', 'bursmetre'); ?></h3>
                <p><?php echo esc_html($formatted_last_updated); ?></p>
            </article>
        </section>

        <section class="burs-grid">
            <article class="burs-panel burs-panel--departments">
                <header class="burs-departments__header">
                    <div class="burs-departments__title">
                        <span class="burs-icon burs-icon--badge"></span>
                        <h2><?php esc_html_e('Döviz Bazında Toplamlar', 'bursmetre'); ?></h2>
                    </div>
                    <span class="burs-departments__amount"><?php echo esc_html(bursmetre_yurtdisi_format_try($total_try)); ?></span>
                </header>
                <div class="burs-departments">
                    <?php if (!empty($data['currency_breakdown'])) : ?>
                        <?php foreach ($data['currency_breakdown'] as $entry) : ?>
                            <?php
                            $share = $total_try > 0 ? round(($entry['amount_try'] / $total_try) * 100, 1) : 0;
                            ?>
                            <div class="burs-departments__item">
                                <div class="burs-departments__badge"><?php echo esc_html($entry['currency']); ?></div>
                                <div class="burs-departments__body">
                                    <div class="burs-departments__top">
                                        <strong><?php echo esc_html($entry['formatted']); ?></strong>
                                        <span class="burs-departments__amount"><?php echo esc_html(bursmetre_yurtdisi_format_try($entry['amount_try'])); ?></span>
                                    </div>
                                    <div class="burs-progress burs-progress--departments">
                                        <div class="burs-progress__inner" data-progress="<?php echo esc_attr($share); ?>"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="burs-empty"><?php esc_html_e('Henüz döviz verisi bulunamadı.', 'bursmetre'); ?></p>
                    <?php endif; ?>
                </div>
            </article>
            <article class="burs-panel burs-panel--chart">
                <header class="burs-panel__header">
                    <div class="burs-panel__title">
                        <span class="burs-icon burs-icon--chart"></span>
                        <h2><?php esc_html_e('En Yüksek Bağış Toplayan Kampanyalar', 'bursmetre'); ?></h2>
                    </div>
                </header>
                <?php if (!empty($data['campaigns'])) : ?>
                <div class="burs-chart"><canvas id="<?php echo esc_attr(wp_unique_id('burs-overseas-')); ?>" data-chart-kind="campaigns" role="img" aria-label="Yurtdışında en yüksek bağış toplayan kampanyalar" data-chart='<?php echo esc_attr($chart_payload); ?>'>Grafik verileri aşağıdaki kampanya listesinde de yer alır.</canvas></div>
                <?php else : ?>
                    <p class="burs-empty"><?php esc_html_e('Grafik için yeterli veri bulunamadı.', 'bursmetre'); ?></p>
                <?php endif; ?>
            </article>
        </section>

        <section class="burs-panel burs-panel--ranking">
            <header class="burs-panel__header">
                <div class="burs-panel__title">
                    <span class="burs-icon burs-icon--trophy"></span>
                    <h2><?php esc_html_e('Yurtdışı Kampanyaları Sıralaması', 'bursmetre'); ?></h2>
                </div>
            </header>
            <div class="burs-ranking">
                <?php if (!empty($rank_list)) : ?>
                    <?php
                    $position = 1;
                    foreach ($rank_list as $campaign) :
                        $pct = $campaign['goal_original'] > 0
                            ? round(($campaign['amount_original'] / $campaign['goal_original']) * 100, 1)
                            : 0;
                        ?>
                        <article class="burs-ranking__item">
                            <div class="burs-ranking__order">#<?php echo esc_html($position); ?></div>
                            <div class="burs-ranking__avatar" data-burs-avatar>
                                <?php
                                $initials = function_exists('mb_substr')
                                    ? mb_substr($campaign['name'], 0, 2, 'UTF-8')
                                    : substr($campaign['name'], 0, 2);
                                ?>
                                <span aria-hidden="true"><?php echo esc_html($initials); ?></span>
                                <?php if (!empty($campaign['image'])) : ?>
                                    <img src="<?php echo esc_url($campaign['image']); ?>" alt="" width="60" height="60" loading="lazy" decoding="async" />
                                <?php endif; ?>
                            </div>
                            <div class="burs-ranking__body">
                                <div class="burs-ranking__header">
                                    <h3><?php echo esc_html($campaign['name']); ?></h3>
                                    <?php if (!empty($campaign['url'])) : ?>
                                        <a class="burs-ranking__link" href="<?php echo esc_url($campaign['url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($campaign['name'] . ' kampanyasına bağış yap'); ?>">
                                            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 4h12v12m0-12L7 17"/></svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <p class="burs-ranking__currency">
                                    <?php echo esc_html(bursmetre_yurtdisi_format_try($campaign['amount_try'])); ?>
                                    <small><?php echo esc_html($campaign['amount_original_formatted']); ?></small>
                                </p>
                                <?php if ($campaign['goal_original'] > 0) : ?>
                                    <p class="burs-ranking__goal">
                                        <?php echo esc_html('Hedef: ' . $campaign['goal_original_formatted']); ?>
                                        <strong><?php echo esc_html('%' . number_format_i18n($pct, 1)); ?></strong>
                                    </p>
                                    <div class="burs-progress burs-progress--ranking" aria-label="<?php echo esc_attr('Hedefe ulaşma oranı: %' . number_format_i18n($pct, 1)); ?>">
                                        <div class="burs-progress__inner" data-progress="<?php echo esc_attr($pct); ?>"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                        <?php
                        $position++;
                    endforeach;
                    ?>
                <?php else : ?>
                    <p class="burs-empty"><?php esc_html_e('Yurtdışı kampanyası bulunamadı.', 'bursmetre'); ?></p>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <?php
    return ob_get_clean();
}

if (!function_exists('bursmetre_yurtdisi_remote_refresh_job')) {
    function bursmetre_yurtdisi_remote_refresh_job()
    {
        bursmetre_yurtdisi_get_data(true);
    }
    add_action('bursmetre_yurtdisi_remote_refresh', 'bursmetre_yurtdisi_remote_refresh_job');
}
