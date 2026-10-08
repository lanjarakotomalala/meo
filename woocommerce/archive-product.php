<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

use MyApp\WooCommerce\Product;

defined('ABSPATH') || exit;

global $wp_query;

$current_categorie       = $wp_query->get_queried_object();
$thumbnail = false;
if (isset($current_categorie->term_id)) {
    $thumbnail_id            = get_term_meta( $current_categorie->term_id, 'thumbnail_id', true );
    $thumbnail               = wp_get_attachment_image( $thumbnail_id, 'medium' );
}
$below_category_image = get_field( 'below_category_image',  $current_categorie );

get_header('shop');

?>

<header class="woocommerce-products-header">
    <?php if (is_search()): ?>
        <header class="page-header">
            <h1 class="page-title"><?php printf( esc_attr__( 'Search Results for: %s', 'shoptimizer' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
        </header><!-- .page-header -->
    <?php elseif (is_shop()): ?>
        <div class="woocommerce-products-header__title"><?php __('Shop', 'woocommerce'); ?></div>
    <?php elseif (is_archive()): ?>
        <h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
        <div class="woocommerce-products-header__description text-mask">
            <?php the_archive_description( '<div>', '</div>' );
            if (category_description()) { ?>
                <button type="button"><?php _e( 'Voir +', 'meo' ); ?></button>
            <?php } ?>
        </div>
    <?php else: ?>
        <div class="woocommerce-products-header__title"><?php __('Shop', 'woocommerce'); ?></div>
    <?php endif; ?>

    <div class="woocommerce-products-header__thumbnail">
	    <?php

	    if ( $thumbnail ) {
		    echo $thumbnail;
		}

	    ?>
    </div>
</header>
<?php

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action('woocommerce_before_main_content');

if (woocommerce_product_loop()) {

    /**
     * Hook: woocommerce_before_shop_loop.
     *
     * @hooked woocommerce_output_all_notices - 10
     * @hooked woocommerce_result_count - 20
     * @hooked woocommerce_catalog_ordering - 30
     */
    do_action('woocommerce_before_shop_loop');

    if (is_product_category()) {
        Product::displayCategoryChildren();
    }

    //woocommerce_product_loop_start();

    wpgb_render_template('woocommerce-product-loop');

    //woocommerce_product_loop_end();
?>
<div class="woocommerce-product-loop__more">
<?php

wpgb_render_facet(
	[
		'id'   => get_field( 'more', 'options' ),
		'grid' => 'woocommerce-product-loop'
	]
);

?>
</div>
<?php
    /**
     * Hook: woocommerce_after_shop_loop.
     *
     * @hooked woocommerce_pagination - 10
     */
    do_action('woocommerce_after_shop_loop');
} else {
    /**
     * Hook: woocommerce_no_products_found.
     *
     * @hooked wc_no_products_found - 10
     */
    do_action('woocommerce_no_products_found');
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

//below_category_image

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action('woocommerce_sidebar');

?>
<div class="clear"></div>

<?php if (get_query_var('paged') <= 1): ?>
    <?php
    $term = get_queried_object()->term_id;
    $term_meta = get_term_meta( $term, 'below_category_content', true );

    if ($term_meta) : ?>
        <div class="archive-details-meta">
            <?php
            if ( $below_category_image && function_exists('jetpack_photon_url') ) {
                // Generate Photon URL.
                $url = jetpack_photon_url( $below_category_image );
                $image = sprintf(
                        '<img data-src="%s" class="%s" onerror="%s"/>',
                        str_replace('?ssl=1', '?w=300&resize=300&ssl=1', $url),
                        str_replace('?ssl=1', '?w=300&resize=300&ssl=1', $url),
                        fifu_jetpack_get_set($url, true),
                        'below-category-image',
                        "jQuery(this).hide();");

                echo '<div class="archive-details-meta__thumbnail">';
                echo $image;
                echo '</div>';
            }

            ?>
            <div class="archive-details-meta__content">
                <p><?php woocommerce_page_title(); ?></p>
                <?php shoptimizer_product_cat_display_details_meta(); ?>
            </div>
        </div>
    <?php endif;
endif;

get_footer('shop');
