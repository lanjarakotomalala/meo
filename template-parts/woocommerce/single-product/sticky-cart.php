<div class="sticky_cart hide-sticky-cart">
    <div class="sticky_wrapper">
        <div class="sticky_title"><strong><?php the_title(); ?></strong></div>
        <div class="sticky_container">
            <?php if ($args['product_type'] !== 'variable') : ?>
                <div class="sticky_price"><?php woocommerce_template_single_price(); ?></div>
            <?php endif; ?>
            <div class="sticky-variation"><?php woocommerce_template_single_add_to_cart(); ?></div>
        </div>
    </div>
</div>
