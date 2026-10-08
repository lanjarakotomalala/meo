<?php get_header(); ?>
    <div id="primary" class="content-area">

        <main id="main" class="site-main">

            <div class="error-404 not-found">

                <div class="page-content">

                    <header class="page-header">
                        <h1 class="page-title">Oups ! Cette page n'existe pas.</h1>
                    </header><!-- .page-header -->
                    <p>Il semble que vous vous soyez égaré dans les grains de café. Retournez à l'accueil et explorez nos délicieuses variétés de café.</p>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-back-home">Retour à l'accueil</a>

                    <?php

                    if ( shoptimizer_is_woocommerce_activated() ) {

                        echo '<section aria-label="' . esc_html__( 'Produits populaires', 'shoptimizer' ) . '">';

                        echo '<h2>' . esc_html__( 'Produits populaires', 'shoptimizer' ) . '</h2>';

                        echo shoptimizer_do_shortcode( 'best_selling_products', array(
                            'per_page' => 8,
                            'columns'  => 4,
                        ) );

                        echo '</section>';

                    }
                    ?>

                </div><!-- .page-content -->
            </div><!-- .error-404 -->

        </main><!-- #main -->
    </div><!-- #primary -->

<?php get_footer();
