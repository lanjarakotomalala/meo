<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package shoptimizer
 */

$shoptimizer_layout_woocommerce_sidebar = '';
$shoptimizer_layout_woocommerce_sidebar = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_sidebar' );
?>

<?php if ( 'no-woocommerce-sidebar' !== $shoptimizer_layout_woocommerce_sidebar && ! meo_has_sidebar_disabled() ) { ?>
<div class="secondary-wrapper">
	<div id="secondary" class="widget-area" role="complementary">
        <div class="mobile-filter sidebar_close">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
		<?php

		if( is_archive( 'product_cat' ) ) {
			$filters = get_field( 'filters', 'options' );

			echo '<p class="filters__title">' . __( 'Filtres', 'meo' ) . '</p>';

			foreach( $filters as $filter ) {
				wpgb_render_facet(
					[
						'id'   => $filter[ 'id_facet' ],
						'grid' => 'woocommerce-product-loop'
					]
				);
			}
		}

		dynamic_sidebar( 'sidebar-1' );

		?>
	</div><!-- #secondary -->
	<div class="filters close-drawer"></div>
</div>
<?php } ?>
