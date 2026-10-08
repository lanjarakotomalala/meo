<div class="wpgb-map-marker-body">
    <h3 class="wpgb-map-marker-title"><?php echo $args['title']; ?></h3>
    <?php if (!empty($args['excerpt'])) : ?>
        <p class="wpgb-map-marker-content"><?php echo wp_kses_post($args['excerpt']); ?></p>
    <?php endif; ?>
    <?php if (!empty($args['products'])) : ?>
        <p>
            <button class="store-products-btn-popup"><?php _e('Voir les produits', 'meo'); ?></button>
        </p>
    <?php endif; ?>
</div>

<?php if (!empty($args['products'])) meo_store_related_products_popup_content($args['products'], $args['post_id'], $args['excerpt']); ?>
