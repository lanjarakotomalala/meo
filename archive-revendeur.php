<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package shoptimizer
 */

global $wp_query;

$shoptimizer_layout_archives_sidebar = '';
$shoptimizer_layout_archives_sidebar = shoptimizer_get_option('shoptimizer_layout_archives_sidebar');

$shoptimizer_layout_blog = '';
$shoptimizer_layout_blog = shoptimizer_get_option('shoptimizer_layout_blog');

get_header(); ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main <?php echo shoptimizer_safe_html($shoptimizer_layout_blog); ?>">
            <h1><?php echo esc_html(__('Où nous trouver ?', 'meo')); ?></h1>
            <div class="revendeurs">
                <div class="revendeurs__list">
                    <div class="facets">
                        <?php
                        # Type d'établissement
                        // wpgb_render_facet(
                        //     [
                        //         'id' => 2, // Facet id.
                        //         'grid' => 1, // Grid or template id.
                        //     ]
                        // );

                        # Ville ou CP
                        wpgb_render_facet(
                            [
                                'id' => 3,
                                'grid' => 1,
                            ]
                        );

                        # Réinitialiser
                        wpgb_render_facet(
                            [
                                'id' => 4,
                                'grid' => 1,
                            ]
                        );

                        wpgb_render_facet(
                            [
                                'id' => 17,
                                'grid' => 1,
                            ]
                        );
                        ?>
                    </div>

                    <div class="store-locator-founded">
                        <p><?php printf('Points de vente (%s)', $wp_query->found_posts) ?></p>
                    </div>

                    <?php wpgb_render_grid(['id' => 1, 'is_main_query' => false]); ?>
                </div>

                <div class="revendeurs__map">
                    <?php wpgb_render_facet(['id' => 1, 'grid' => 1]); ?>
                </div>
            </div>

        </main><!-- #main -->
    </div><!-- #primary -->

<?php
get_footer();
?>

<div class="hidden container-store-product-popup">
    <div class="store-product-popup">
    </div>
</div>
