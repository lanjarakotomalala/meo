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
                        <div class="sr"><?php bloginfo('name'); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="s-observer"></div>
    </header><!-- #masthead -->

    <div id="content" class="site-content" tabindex="-1">

        <div class="shoptimizer-archive">

        <div class="col-full">
