<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Shoptimizer
 */

$footer_informations = get_field('field_options_footer_group', 'options');
?>

</div><!-- .col-full -->
</div><!-- #content -->

<div class="site-footer-wrapper">
    <?php do_action('shoptimizer_before_footer'); ?>

    <?php if (is_front_page()) : ?>
        <section class="site-footer-social">
            <div class="site-footer-social__links">
                <div class="col-full">
                    <?php

                    wp_nav_menu(
                        array(
                            'theme_location' => 'social',
                            'menu_id' => 'social-menu',
                            'container' => '',
                            'menu_class' => 'social-menu'
                        )
                    );

                    ?>
                    <ul class="site-footer-social__links__content">
                        <?php dynamic_sidebar('footer-social-sidebar'); ?>
                    </ul>
                </div>
            </div>
        </section>
    <?php endif; ?>
    <?= do_shortcode('[instagram-feed]') ?>

    <?php if (is_front_page() && !empty($footer_informations) && $footer_informations['options_footer_show_main_settings']) : ?>
        <section class="site-footer-informations">
            <?php if (!empty($footer_informations['options_footer_title_main_settings'])) : ?>
                <h2 class="title"><?= $footer_informations['options_footer_title_main_settings'] ?></h2>
            <?php endif; ?>
            <div class="content">
                <div>
                    <?php if (!empty($footer_informations['options_footer_image_main_settings'])) : ?>
                        <?= wp_get_attachment_image($footer_informations['options_footer_image_main_settings'], 'medium_large') ?>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if (!empty($footer_informations['options_footer_text_main_settings'])) : ?>
                        <?= $footer_informations['options_footer_text_main_settings'] ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
    <?php if (is_wc_endpoint_url('order-received') || !is_checkout()) : ?>
        <footer class="site-footer">
            <div class="col-full">
                <ul class="sidebar">
                    <?php dynamic_sidebar('footer-left-sidebar'); ?>
                </ul>
                <ul class="sidebar">
                    <?php dynamic_sidebar('footer-center-sidebar'); ?>
                </ul>
                <ul class="sidebar">
                    <?php dynamic_sidebar('footer-right-sidebar'); ?>

                    <li class="widget widget-social">
                        <div class="widget__title"><?php _e('Suivez-nous', 'meo'); ?></div>
                    </li>
                </ul>
            </div>
        </footer>
    <?php endif; ?>
    <?php
    /**
     * Functions hooked in to shoptimizer_footer action
     */
    /*
    add_action( 'shoptimizer_footer', 'shoptimizer_footer_widgets', 20 );
    add_action( 'shoptimizer_footer', 'shoptimizer_footer_copyright', 30 );
     */
    do_action('shoptimizer_footer');
    ?>

    <?php do_action('shoptimizer_after_footer'); ?>

</div><!-- .site-footer-wrapper -->

</div><!-- #page -->
</div>

<?php wp_footer(); ?>

</body>
</html>
