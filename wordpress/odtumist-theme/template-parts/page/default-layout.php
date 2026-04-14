<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<section class="page-hero page-hero-light">
    <div class="site-container">
        <h1><?php the_title(); ?></h1>
    </div>
</section>

<section class="page-content-block">
    <div class="site-container prose-block">
        <?php the_content(); ?>
    </div>
</section>
