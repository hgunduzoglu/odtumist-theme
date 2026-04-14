<?php
get_header();
?>
<main id="site-content" class="site-main">
    <section class="page-hero page-hero-light">
        <div class="site-container">
            <h1><?php esc_html_e('Sayfa Bulunamadı', 'odtumist'); ?></h1>
            <p><?php esc_html_e('Aradığınız içerik taşınmış ya da silinmiş olabilir.', 'odtumist'); ?></p>
            <a class="btn btn-solid" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Anasayfaya Dön', 'odtumist'); ?></a>
        </div>
    </section>
</main>
<?php
get_footer();
