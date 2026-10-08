<?php

global $product;

$radar     = get_field( 'radar' );
$radar_alt = get_field( 'radar_alt' );
$price_per_kilo = get_price_per_kg(get_the_ID());

?>
<h2><?php _e( 'Description', 'meo' ); ?></h2>

<div class="wc-tab__columns">
	<div class="wc-tab__column">
		<?php the_content(); ?>

		<?php if ( $product->has_attributes() ) : ?>
        <div class="product-attributes-header">
            <span><?= __('Plus d\'informations') ?></span>
            <button class="toggle"></button>
        </div>
		<ul class="attributes product-attributes">

			<?php foreach( $product->get_attributes() as $taxonomy => $attribute_obj ) : ?>
                <?php if (!$attribute_obj->get_visible()) continue; ?>
				<?php if( sanitize_title( $attribute_obj['name'] ) != 'prix-au-kg' ) : ?>
				<li>
					<strong><?php echo taxonomy_exists($taxonomy) ? wc_attribute_label( $taxonomy ) : $attribute_obj['name']; ?> : </strong>
					<?php echo $product->get_attribute( $attribute_obj['name'] ); ?>
				</li>
				<?php endif; ?>
			<?php endforeach; ?>

            <?php if ($price_per_kilo): ?>
                <li><?php printf(__('<strong>Prix au kilo :</strong> %s €', 'meo'), $price_per_kilo); ?></li>
            <?php endif; ?>

		</ul>
        <?php elseif ($price_per_kilo): ?>
            <ul>
                <li><strong><?php printf(__('Prix au kilo : %s €', 'meo'), $price_per_kilo); ?></strong></li>
            </ul>
		<?php endif; ?>
	</div>

	<?php if ( $radar ) : ?>
	<div class="wc-tab__column">
		<img src="<?php echo $radar; ?>" alt="">

		<?php if( $radar_alt ) : ?>

		<p class="sr"><?php echo $radar_alt; ?></p>

		<?php endif; ?>
	</div>
	<?php endif; ?>
</div>
