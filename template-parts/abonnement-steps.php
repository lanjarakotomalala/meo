<div class="step-app" id="subscription-steps">
    <ul class="step-steps">
        <?php if ($args['has-machine']) : ?>
            <li data-step-target="machine"><span class="step-num">1</span><?php echo esc_html($args['machine-step-tab-title']); ?></li>
            <li data-step-target="coffee-"><span class="step-num">2</span><?php echo esc_html($args['pods-step-tab-title']); ?></li>
            <li data-step-target="accessories"><span class="step-num">3</span><?php echo esc_html($args['accessories-step-tab-title']); ?></li>
        <?php else: ?>
            <li data-step-target="coffee-"><span class="step-num">1</span><?php echo esc_html($args['pods-step-tab-title']); ?></li>
            <li data-step-target="accessories"><span class="step-num">2</span><?php echo esc_html($args['accessories-step-tab-title']); ?></li>
        <?php endif; ?>
    </ul>
    <div class="step-content">
        <?php if ($args['has-machine']) : ?>
            <div class="bundled_item-group eq-1 step-tab-panel" data-step="machine">
                <div class="step-desc">
                    <p class="step-title"><?php echo esc_html($args['machine-step-title']) ?></p>
                    <p class="step-text"><?php echo esc_html($args['machine-step-text']) ?></p>
                </div>
                <div class="toggle-zone <?php if (count($args['sorted-bundle-items']['machine-step']) > 2) : ?>mh-items<?php endif; ?>">
                    <?php if (isset($args['sorted-bundle-items']['machine-step']) && is_array($args['sorted-bundle-items']['machine-step'])) : ?>
                        <?php foreach ($args['sorted-bundle-items']['machine-step'] as $item) : ?>
                            <?php
                            do_action('woocommerce_bundled_item_details', $item, $args['product']);
                            ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="bundled_item-group eq-2 step-tab-panel" data-step="coffee">
            <div class="step-desc">
                <p class="step-title"><?php echo esc_html($args['pods-step-title']) ?></p>
                <p class="step-text"><?php echo esc_html($args['pods-step-text']) ?></p>
            </div>
            <div class="toggle-zone <?php if (count($args['sorted-bundle-items']['coffee-step']) > 2) : ?>mh-items<?php endif; ?>">
                <?php if (isset($args['sorted-bundle-items']['coffee-step']) && is_array($args['sorted-bundle-items']['coffee-step'])) : ?>
                    <?php foreach ($args['sorted-bundle-items']['coffee-step'] as $item) : ?>
                        <?php
                        if ($item->get_stock_status() === 'in_stock' || $item->get_stock_quantity() > 0)  {
                            do_action('woocommerce_bundled_item_details', $item, $args['product']);
                        }
                        ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($args['sorted-bundle-items']['accessories-step'])) : ?>
            <div class="bundled_item-group eq-3 step-tab-panel" data-step="accessories">
                <div class="step-desc">
                    <p class="step-title"><?php echo esc_html($args['accessories-step-title']) ?></p>
                    <p class="step-text"><?php echo esc_html($args['accessories-step-text']) ?></p>
                </div>
                <?php if (isset($args['sorted-bundle-items']['accessories-step']) && is_array($args['sorted-bundle-items']['accessories-step'])) : ?>
                    <div class="toggle-zone <?php if (count($args['sorted-bundle-items']['accessories-step']) > 2) : ?>mh-items<?php endif; ?>">
                        <?php foreach ($args['sorted-bundle-items']['accessories-step'] as $item) : ?>
                            <?php
                            do_action('woocommerce_bundled_item_details', $item, $args['product']);
                            ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="step-footer">
        <button data-step-action="prev" class="step-btn">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/resources/images/icon-chevron-right-white.svg" alt="right" width="9" height="15">
            <?php _e('Etape précédente', 'meo'); ?>
        </button>
        <button data-step-action="next" class="step-btn">
            <?php _e('Etape suivante', 'meo'); ?>
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/resources/images/icon-chevron-right-white.svg" alt="right" width="9" height="15">
        </button>
        <button data-step-action="finish" class="step-btn"><?php _e('Ajouter au panier', 'meo'); ?></button>
    </div>
</div>
