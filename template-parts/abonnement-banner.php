<div class="container-banner-bundle">
    <div <?php if ($args['bg-img']) : ?> style="background: linear-gradient(120deg, rgba(50, 50, 50, 0.5), rgba(0, 0, 0, 0.3)), url(<?php echo esc_attr($args['bg-img']); ?>);" <?php endif; ?>>
        <h1 class="product_title entry-title"><?php echo esc_html($args['title']); ?></h1>
        <?php echo $args['subtitle']; ?>
    </div>
</div>
<div class="archive-header">
    <div class="col-full">
        <?php do_action( 'shoptimizer_content_top' ); ?>
    </div>
</div>
<?php if ($args['content'] !== '') : ?>
<ul class="woocommerce-product-details__quick-links">
    <li><a href="#tab-description"><?php _e('Détails du produit', 'meo'); ?></a></li>
</ul>
<?php endif; ?>
