<div class="hidden products-container store-<?php echo esc_attr($args['store_id']); ?>">
    <div class="wpgb-map-marker-body">
        <h3 class="wpgb-map-marker-title"><?php echo $args['title']; ?></h3>
        <?php if (!empty($args['excerpt'])) : ?>
            <p class="wpgb-map-marker-content"><?php echo wp_kses_post($args['excerpt']); ?></p>
        <?php endif; ?>
    </div>
    <div class="close-popup"></div>

    <?php if (!empty($args['products'])) : ?>
        <div class="wp-grid-builder wpgb-template wpgb-grid-woocommerce-store-products-loop products column-3 wpgb-enabled">
                <?php foreach ($args['products'] as $productId) :
                    setup_postdata($productId); ?>
            <div class="product product-<?php echo esc_attr($productId); ?>">
                <a href="<?php echo esc_url(get_permalink($productId)); ?>">
                    <div class="container-product-thumbnail"><?php echo get_the_post_thumbnail($productId); ?></div>
                    <p><?php echo get_the_title($productId); ?></p>
                </a>
            </div>
            <?php
            endforeach;
            wp_reset_postdata(); ?>
        </div>
    <?php endif; ?>
</div>
