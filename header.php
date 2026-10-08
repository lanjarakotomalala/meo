<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package shoptimizer
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="height=device-height, width=device-width, initial-scale=1, maximum-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

<?php do_action('shoptimizer_before'); ?>

<div id="page" class="hfeed site">

    <?php
    do_action('shoptimizer_before_site');
    do_action('shoptimizer_before_header');
    ?>

    <?php do_action('shoptimizer_topbar'); ?>

    <header id="masthead" class="site-header">

        <div class="menu-overlay"></div>

        <div class="main-header col-full">
            <?php if (is_wc_endpoint_url('order-received') || ! is_checkout()) : ?>
            <div class="main-header__secondary">
                <button class="btn--toggle-search" aria-expanded="false">
                    <span class="sr"><?php _e('Show', 'meo'); ?></span>
                    <?php _e('Search'); ?>
                </button>
                <div class="site-search">
                    <button class="btn--close">
                        <span class="sr"><?php _e('Fermer', 'meo'); ?></span>
                    </button>
                    <div class="site-search__wrapper">
                        <div class="site-search__content">
                            <span class="site-search__logo"></span>
                            <?php the_widget('WC_Widget_Product_Search', 'title='); ?>
                        </div>
                    </div>
                </div>
                <?php

                wp_nav_menu(
                    array(
                        'theme_location' => 'header-left',
                        'menu_id'        => 'header-menu-secondary',
                        'container'      => '',
                        'menu_class'     => 'header-menu'
                    )
                );

                ?>
            </div>
            <?php endif; ?>

            <div class="main-header__brand">
                <div class="site-branding">
                    <button class="menu-toggle" aria-controls="site-navigation" aria-expanded="true">
                        <span class="bar"></span><span class="bar"></span><span class="bar"></span>
                        <span class="sr"><?php _e('Menu', 'meo'); ?></span>
                    </button>
                    <?php if (is_front_page() || ( is_front_page() && is_home() )) : ?>
                    <div class="site-title">
                        <div class="sr"><?php bloginfo('name'); ?></div>
                    </div>
                    <?php else : ?>
                    <div class="site-title">
                        <a href="<?php echo esc_url(home_url()); ?>" rel="home">
                            <div class="sr"><?php bloginfo('name'); ?></div>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (is_wc_endpoint_url('order-received') || ! is_checkout()) : ?>
            <div class="main-header__shop">
                <?php

                wp_nav_menu(
                    array(
                        'theme_location' => 'header-right',
                        'menu_id'        => 'header-menu-shop',
                        'container'      => '',
                        'menu_class'     => 'header-menu'
                    )
                );

                shoptimizer_header_cart();

                ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="s-observer"></div>
    </header><!-- #masthead -->


    <div class="col-full-nav">

    <?php
    /**
     * Functions hooked into shoptimizer_header action
     *
     * @hooked shoptimizer_primary_navigation_wrapper       - 42
     * @hooked shoptimizer_primary_navigation               - 50
     * @hooked shoptimizer_header_cart                      - 60
     * @hooked shoptimizer_primary_navigation_wrapper_close - 68
     */
    do_action('shoptimizer_navigation');

    ?>

    </div>

    <?php
    /**
     * Functions hooked in to shoptimizer_before_content
     *
     * @hooked shoptimizer_header_widget_region - 10
     */
    do_action('shoptimizer_before_content');
    ?>

    <div id="content" class="site-content" tabindex="-1">

        <div class="shoptimizer-archive">

        <div class="archive-header">
            <div class="col-full">
                <?php
                /**
                 * Functions hooked in to shoptimizer_content_top
                 *
                 * @hooked woocommerce_breadcrumb - 10
                 */
                do_action('shoptimizer_content_top');
                ?>
            </div>
        </div>

        <div class="col-full">
