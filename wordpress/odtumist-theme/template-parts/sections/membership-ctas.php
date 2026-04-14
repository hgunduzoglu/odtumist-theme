<?php
if (!defined('ABSPATH')) {
    exit;
}

$ctas         = odtumist_get_primary_cta_links();
$contact_page = odtumist_get_page_by_slug(array('iletisim', 'contact'));
$cta_copy     = odtumist_get_home_cta_content();
?>
<section class="cta-stack" id="anasayfa-uyelik">
    <article class="cta-block cta-red">
        <div class="site-container cta-inner">
            <div>
                <h2><?php echo esc_html($cta_copy['membership']['title']); ?></h2>
                <p><?php echo esc_html($cta_copy['membership']['description']); ?></p>
                <a class="btn btn-white" href="<?php echo esc_url($ctas['membership']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($cta_copy['membership']['button_label']); ?></a>
            </div>
            <div class="cta-emoji" aria-hidden="true">&#128100;</div>
        </div>
    </article>

    <article class="cta-block cta-blue">
        <div class="site-container cta-inner reverse">
            <div>
                <h2><?php echo esc_html($cta_copy['volunteer']['title']); ?></h2>
                <p><?php echo esc_html($cta_copy['volunteer']['description']); ?></p>
                <a class="btn btn-dark" href="<?php echo esc_url($contact_page ? get_permalink($contact_page) : home_url('/iletisim')); ?>"><?php echo esc_html($cta_copy['volunteer']['button_label']); ?></a>
            </div>
            <div class="cta-emoji" aria-hidden="true">&#128101;</div>
        </div>
    </article>
</section>
