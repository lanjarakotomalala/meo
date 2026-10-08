<?php

global $post, $product;

$short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt );
$preparation = ( (string)get_field('preparation', $post->ID) !== '');

if ( ! $short_description )
    return;

if ( $product->has_attributes() ) {
	$attr = $product->get_attribute( 'prix-au-kg' );

	if ( $attr ) {
		echo '<div class="woocommerce-product-details__price-per-unit">' . $attr . '</div>';
	}
}

?>
<div class="woocommerce-product-details__short-description">
	<div class="woocommerce-product-details__short-description__content text-mask">
		<div>
    		<?php echo $short_description; ?>
    	</div>

    	<button type="button"><?php _e( 'Voir +', 'meo' ); ?></button>
    </div>

    <ul class="woocommerce-product-details__quick-links">
    	<li><a href="#tab-description"><?php _e( 'Détails du produit', 'meo' ); ?></a></li>
        <?php if ($preparation): ?>
    	    <li><a href="#tab-preparation"><?php _e( 'Aide à la préparation', 'meo' ); ?></a></li>
        <?php endif; ?>
    </ul>
</div>
