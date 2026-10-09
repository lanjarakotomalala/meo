<?php

/**
 * Declare any actions and filters here.
 * In most cases you should use a service provider, but in cases where you
 * just need to add an action/filter and forget about it you can add it here.
 *
 * @package MyApp
 */

use DigitalBit\Hooks\Filter;

if (!defined('ABSPATH')) {
    exit;
}

// Campaign copy and photography stay editable without changing the homepage blocks.
add_action('customize_register', function ($customizer) {
    $customizer->add_section('meo_home_hero', [
        'title' => __('Accueil — carrousel', 'meo'),
        'priority' => 35,
    ]);

    foreach (['first' => 'Le goût du café depuis 1928', 'second' => 'Nos cafés sont torréfiés en France'] as $key => $default) {
        $id = 'meo_announcement_' . $key;
        $customizer->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $customizer->add_control($id, [
            'label' => __('Bandeau défilant', 'meo') . ' ' . ($key === 'first' ? '1' : '2'),
            'section' => 'meo_home_hero',
            'type' => 'text',
        ]);
    }

    $slides = [
        'promo' => ['label' => __('Promo du mois', 'meo'), 'title' => 'La promo du mois', 'text' => 'Vos cafés préférés à savourer à prix doux.'],
        'coffee' => ['label' => __('Café du mois', 'meo'), 'title' => 'Le café du mois', 'text' => 'Une nouvelle rencontre, une nouvelle façon de prendre le temps.'],
        'brand' => ['label' => __('La marque', 'meo'), 'title' => 'Le goût des belles histoires', 'text' => 'Une maison de café, un savoir-faire et le plaisir de partager.'],
    ];

    foreach ($slides as $key => $slide) {
        foreach (['title' => $slide['title'], 'text' => $slide['text'], 'link' => '', 'image' => '', 'mobile_image' => ''] as $field => $default) {
            $id = 'meo_hero_' . $key . '_' . $field;
            $customizer->add_setting($id, [
                'default' => $default,
                'sanitize_callback' => in_array($field, ['link', 'image', 'mobile_image'], true) ? 'esc_url_raw' : 'sanitize_text_field',
            ]);
            $labels = [
                'title' => __('Titre', 'meo'),
                'text' => __('Texte', 'meo'),
                'link' => __('Lien du bouton', 'meo'),
                'image' => __('Image', 'meo'),
                'mobile_image' => __('Image mobile', 'meo'),
            ];
            $args = [
                'label' => $slide['label'] . ' — ' . $labels[$field],
                'section' => 'meo_home_hero',
                'settings' => $id,
            ];
            if (in_array($field, ['image', 'mobile_image'], true)) {
                $customizer->add_control(new WP_Customize_Image_Control($customizer, $id, $args));
            } else {
                $customizer->add_control($id, $args + ['type' => $field === 'text' ? 'textarea' : 'text']);
            }
        }
    }
});

function meo_disable_sidebar($body_class)
{
    $blacklisted_classes = [
        'left-woocommerce-sidebar right-archives-sidebar right-page-sidebar right-post-sidebar',
    ];
    $body_class = array_diff($body_class, $blacklisted_classes);
    $body_class[] = 'page-template-template-fullwidth-php ';
    return $body_class;
}

// Active le mode pleine largeur sur les pages et le checkout personnalisé
add_action('wp', function () {
    if (is_page() || get_post_type() === 'cartflows_step') {
        add_action('body_class', 'meo_disable_sidebar', 99);
        remove_action('shoptimizer_sidebar', 'shoptimizer_get_sidebar', 10);
        remove_action('shoptimizer_page_sidebar', 'shoptimizer_pages_sidebar', 10);
    }

    remove_action('woocommerce_review_before', 'woocommerce_review_display_gravatar', 10);
    remove_action('shoptimizer_footer', 'shoptimizer_footer_widgets', 20);
    remove_action('shoptimizer_footer', 'shoptimizer_footer_copyright', 30);
    add_action('shoptimizer_footer', 'shoptimizer_footer_copyright', 31);

    remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
    remove_action('woocommerce_shop_loop_item_title', 'shoptimizer_loop_product_title', 10);

    remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);


    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);

    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 10);
    remove_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 10);

    remove_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_after_shop_loop', 'shoptimizer_product_cat_display_details_meta', 40);

    remove_action('woocommerce_before_main_content', 'shoptimizer_archives_title', 20);

    if (is_singular('product')) {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    }

    remove_action('woocommerce_before_cart', 'shoptimizer_cart_progress');
    remove_action('woocommerce_before_checkout_form', 'shoptimizer_cart_progress', 5);

    if (is_checkout()) {
        remove_action('shoptimizer_navigation', 'shoptimizer_primary_navigation_wrapper', 42);
        remove_action('shoptimizer_navigation', 'shoptimizer_primary_navigation', 50);
        remove_action('shoptimizer_navigation', 'shoptimizer_primary_navigation_wrapper_close', 68);
    }

    if (is_wc_endpoint_url('order-received')) {
        add_action('shoptimizer_navigation', 'shoptimizer_primary_navigation_wrapper', 42);
        add_action('shoptimizer_navigation', 'shoptimizer_primary_navigation', 50);
        add_action('shoptimizer_navigation', 'shoptimizer_primary_navigation_wrapper_close', 68);
        add_action('shoptimizer_before_footer', 'shoptimizer_below_content', 11);
    }

    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
});

add_action('woocommerce_before_shop_loop', function () {
    ?>
    <div class="shoptimizer-sorting">
        <div class="woocommerce-ordering">
            <?php
            wpgb_render_facet(
                [
                    'id' => get_field('product_sort', 'options'),
                    'grid' => 'woocommerce-product-loop'
                ]
            );
            ?>
        </div>
    </div>
    <?php
}, 10);

add_filter('woocommerce_show_page_title', '__return_true');

add_filter('option_kirki_downloaded_font_files', function ($values) {
    foreach ($values as $google_font => $local_font) {
        if (strpos($local_font, '/deployments/releases/') !== false) {
            $split_path = explode('/web/app/', $local_font);
            $values[$google_font] = WP_CONTENT_DIR . '/' . $split_path[1];
        }
    }
    return $values;
});

function meo_remove_image_zoom_support()
{
    remove_theme_support('wc-product-gallery-zoom');
}

add_action('wp', 'meo_remove_image_zoom_support', PHP_INT_MAX);

// Fix pour l'url de typo
add_filter('kirki_config', function ($config) {
    $config['url_path'] = home_url() . '/app/plugins/kirki/';
    return $config;
}, 999);

// phpcs:ignore
// add_action( 'some_action', 'some_function' );

/* Afficher "À partir de" pour les produits variables */
add_filter('woocommerce_variable_sale_price_html', 'meo_variation_price_format', 10, 2);
add_filter('woocommerce_variable_price_html', 'meo_variation_price_format', 10, 2);

function meo_variation_price_format($price, $product)
{
    // On récupère le prix min et max du produit variable
    $min_price = $product->get_variation_price('min', true);
    $max_price = $product->get_variation_price('max', true);

    // Si les prix sont différents, on affiche "À partir de" suivi du prix
    if ($min_price != $max_price) {
        $price = sprintf(__("à partir de", "woocommerce")) . '&nbsp;' . wc_price($min_price);
        return $price;
        // Sinon on affiche juste le prix
    } else {
        $price = wc_price($min_price);
        return $price;
    }
}

add_action('woocommerce_single_product_summary', 'custom_single_product_summary', 2);
function custom_single_product_summary()
{
    global $product;

    if (! $product instanceof \WC_Product) return;

    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);

    if ('bundle' === $product->get_type()) {
        return;
    }

    add_action('woocommerce_single_product_summary', 'meo_custom_single_excerpt', 20);
}

function meo_custom_single_excerpt()
{
    global $product;

    if (! $product instanceof \WC_Product) return;

    if ($product->get_type() !== 'bundle') {
        do_action('meo/single-product/before_short_description');
        get_template_part('template-parts/woocommerce/single-product/short-description');
        do_action('meo/single-product/after_short_description');
    }
}

add_filter('woocommerce_product_tabs', 'meo_remove_product_tabs', PHP_INT_MAX);
function meo_remove_product_tabs($tabs)
{
    unset($tabs['additional_information']);
    return $tabs;
}

/**
 * Add a custom product data tab
 */
add_filter('woocommerce_product_tabs', 'meo_new_product_tab');
function meo_new_product_tab($tabs)
{
    global $post;
    $preparation = ((string)get_field('preparation', $post->ID) !== '');

    if ($preparation) {
        $tabs['preparation'] = array(
            'title' => __('Aide à la préparation', 'meo'),
            'priority' => 10,
            'callback' => 'meo_preparation_product_tab_content'
        );
    }

    return $tabs;
}

function meo_preparation_product_tab_content()
{
    global $post;
    $preparation = ((string)get_field('preparation', $post->ID) !== '');

    if ($preparation) {
        get_template_part('template-parts/woocommerce/single-product/tab-preparation');
    }
}


/**
 * Customize product data tabs
 */
add_filter('woocommerce_product_tabs', 'meo_custom_description_tab', PHP_INT_MAX);
function meo_custom_description_tab($tabs)
{
    if (isset($tabs['description'])) {
        $tabs['description']['callback'] = 'meo_custom_description_tab_content';
    }
    return $tabs;
}

function meo_custom_description_tab_content()
{
    get_template_part('template-parts/woocommerce/single-product/tab-description');
}

add_filter('woocommerce_default_address_fields', function ($fields) {
    $fields['postcode']['class'] = ['form-row-first'];
    $fields['city']['class'] = ['form-row-last'];
    return $fields;
});

add_filter('woocommerce_billing_fields', function ($fields) {
    $fields['billing_phone']['class'] = ['form-row-first'];
    $fields['billing_email']['class'] = ['form-row-last'];
    return $fields;
});

add_action('woocommerce_after_shop_loop_item_title', function () {
    $price_per_kilo = get_price_per_kg(get_the_ID());

    if ($price_per_kilo) {
        echo '<div class="price-kilo-container">'.sprintf('(Prix au kilo: <span class="price-per-kilo">%s €</span>)', esc_html($price_per_kilo)).'</div>';
    }
});

add_action('wp', function () {
    remove_action('woocommerce_before_shop_loop_item_title', 'shoptimizer_change_displayed_sale_price_html', 3);
    remove_action('woocommerce_single_product_summary', 'shoptimizer_change_displayed_sale_price_html', 10);
}, 999);

add_filter('woocommerce_get_star_rating_html', 'meo_woocommerce_get_star_rating_html', 10, 3);
function meo_woocommerce_get_star_rating_html($html, $rating, $count)
{
    ob_start();

    get_template_part('template-parts/woocommerce/single-product/star', 'rating', ['rating' => $rating, 'count' => $count]);
    $html = ob_get_contents();

    ob_end_clean();

    return $html;
}


add_action('woocommerce_shop_loop_item_title', 'meo_change_products_title', PHP_INT_MAX);
function meo_change_products_title()
{
    get_template_part('template-parts/woocommerce/loop/product-title');
}

add_action('woocommerce_before_shop_loop', 'meo_mobile_filter', 11);
function meo_mobile_filter()
{
    get_template_part('template-parts/woocommerce/mobile-filter');
}

add_action('shoptimizer_page_after', 'meo_subscription_product_content', PHP_INT_MAX);
function meo_subscription_product_content()
{
    if (!is_cart()) {
        get_template_part('template-parts/abonnement-bottom-content');
    }
}

add_filter('woocommerce_checkout_fields', 'meo_remove_woocommerce_checkout_fields');
function meo_remove_woocommerce_checkout_fields($fields)
{

    unset($fields['billing']['billing_company']);

    unset($fields['shipping']['shipping_company']);
    //unset( $fields['shipping']['shipping_country'] );

    unset($fields['order']['order_comments']);

    //$fields['billing']['billing_phone']['priority'] = 22;
    //$fields['billing']['billing_email']['priority'] = 21;

    return $fields;
}

add_action('template_redirect', 'meo_template_redirect');
function meo_template_redirect()
{
    if (get_post_type() === 'cartflows_step' && isset($_GET['wcf-key']) && isset($_GET['wcf-order'])) {
        $key = sanitize_text_field($_GET['wcf-key']);
        $order = intval(sanitize_text_field($_GET['wcf-order']));
        $checkout_url = get_permalink(wc_get_page_id('checkout'));
        $order_received_endpoint = get_option('woocommerce_checkout_order_received_endpoint');
        $format_url = '%s%s/%d/?key=%s&abo';

        wp_redirect(sprintf($format_url, $checkout_url, $order_received_endpoint, $order, $key));
        die;
    }
}


/*
add_action( 'woocommerce_review_order_before_submit', 'meo_add_checkout_privacy_policy', 9 );

function meo_add_checkout_privacy_policy() {

    woocommerce_form_field( 'cgv', array(
        'type'     => 'checkbox',
        'class'    => array( 'input-checkbox' ),
        'label'    => sprintf( __( "J'ai lu et j'accèpte les <a href=\"%s\">conditions générales de vente</a>", 'meo' ), get_permalink( get_option( 'woocommerce_terms_page_id' ) ) ),
        'required' => true,
    ) );
}
*/

/*
add_action( 'woocommerce_checkout_process', 'meo_not_approved_privacy' );


function meo_not_approved_privacy() {
    if ( ! (int) isset( $_POST['cgv'] ) ) {
        wc_add_notice( __( 'Merci de valider les conditions générales de vente.', 'meo' ), 'error' );
    }
}*/

function meo_filter_theme_locale($var, $domain)
{
    if ($domain === 'woocommerce-subscriptions' || $domain === 'cartflows' || $domain === 'cartflows-pro') {
        return $domain . '-' . $var;
    }

    return $var;
}

add_filter('theme_locale', 'meo_filter_theme_locale', 10, 2);


/**
 * Remove rating ordering option
 */
add_filter("woocommerce_catalog_orderby", "meo_catalog_orderby", 20);
function meo_catalog_orderby($options)
{
    unset($options['rating']);
    return $options;
}

/**
 * Hide empty facet
 */
add_filter('wp_grid_builder/facet/html', 'prefix_facet_html', 10, 2);
function prefix_facet_html($html, $facet_id)
{
    if (strpos($html, 'min="0" max="0"') !== false) {
        return false;
    }

    return $html;
}

add_filter('woocommerce_update_order_review_fragments', function ($fragments) {
    // Get checkout payment fragment.
    ob_start();
    ?>
    <div class="shipping-methods-wrapper">
        <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
            <?php do_action('woocommerce_review_order_before_shipping'); ?>
            <?php wc_cart_totals_shipping_html(); ?>
            <?php do_action('woocommerce_review_order_after_shipping'); ?>
        <?php endif; ?>
    </div>
    <?php
    $woocommerce_checkout_shipping = ob_get_clean();
    $fragments['.woocommerce-review-order-shipping .shipping-methods-wrapper'] = $woocommerce_checkout_shipping;
    return $fragments;
});

add_action('woocommerce_before_customer_login_form', 'meo_custom_login_text');
function meo_custom_login_text()
{
    if (!is_user_logged_in()) {
        $reset = get_field('reset_password_text', 'options');

        if ($reset) {
            echo '<p>' . $reset . '</p>';
        }
    }
}

add_action('wp_head', 'meo_google_tracking');
function meo_google_tracking()
{
    if (is_cookies_accepted()) :
        ?>
        <!-- Global site tag (gtag.js) - Google Ads: 1008848927 -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=AW-1008848927"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }

            gtag('js', new Date());

            gtag('config', 'AW-1008848927');
        </script>

        <?php if (is_checkout() && !empty(is_wc_endpoint_url('order-received'))) : ?>
        <!-- Event snippet for COMMANDE conversion page -->
        <script>
            gtag('event', 'conversion', {
                'send_to': 'AW-1008848927/nERBCNnZ0gIQn6CH4QM',
                'value': 1.0,
                'currency': 'EUR'
            });
        </script>

        <script
            src="https://www.paypal.com/sdk/js?client-id=AQYig2exlhiGj5WxoGt-4JdA_ktTo0yPyp2b8JTTRS2Npd2DOdZuRcmqtB8L1Y0d_bpFMNG77sQHVCPm&currency=EUR&components=messages">
        </script>

        <!-- Meta Pixel Code -->
        <script>
            !function (f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function () {
                    n.callMethod ?
                        n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '1140361133446687');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
                       src="https://www.facebook.com/tr?id=1140361133446687&ev=PageView&noscript=1"
            /></noscript>
        <!-- End Meta Pixel Code -->
    <?php endif; ?>

        <script type="text/javascript">
            (function () {
                window.sib = {equeue: [], client_key: "p8pqifrp8nbi07pgomom0"};
                /* OPTIONAL: email to identify request*/
                // window.sib.email_id = 'example@domain.com';
                /* OPTIONAL: to hide the chat on your script uncomment this line (0 = chat hidden; 1 = display chat) */
                // window.sib.display_chat = 0;
                // window.sib.display_logo = 0;
                /* OPTIONAL: to overwrite the default welcome message uncomment this line*/
                // window.sib.custom_welcome_message = 'Hello, how can we help you?';
                /* OPTIONAL: to overwrite the default offline message uncomment this line*/
                // window.sib.custom_offline_message = 'We are currently offline. In order to answer you, please indicate your email in your messages.';
                window.sendinblue = {};
                for (var j = ['track', 'identify', 'trackLink', 'page'], i = 0; i < j.length; i++) {
                    (function (k) {
                        window.sendinblue[k] = function () {
                            var arg = Array.prototype.slice.call(arguments);
                            (window.sib[k] || function () {
                                var t = {};
                                t[k] = arg;
                                window.sib.equeue.push(t);
                            })(arg[0], arg[1], arg[2]);
                        };
                    })(j[i]);
                }
                var n = document.createElement("script"), i = document.getElementsByTagName("script")[0];
                n.type = "text/javascript", n.id = "sendinblue-js", n.async = !0, n.src = "https://sibautomation.com/sa.js?key=" + window.sib.client_key, i.parentNode.insertBefore(n, i), window.sendinblue.page();
            })();
        </script>

    <?php
    endif;
}

add_action('wp_footer', 'meo_tracking_code');

function meo_tracking_code()
{
    if (is_cookies_accepted()) :
        ?>
        <!-- meocafe - All Page -->
        <script async="async" src="//events.sk.ht/meocafe/lib.js"></script>
        <script>
            var skaze = skaze || {};
            skaze.cmd = skaze.cmd || [];
            skaze.cmd.push(function () {
                skaze.init({
                    siteIdentifier: "meocafe"
                });
                skaze.pushEvent({
                    name: "meocafe - All Page",
                    properties: {}
                });
            });
        </script>
        <?php if (is_front_page()) : ?>
        <!-- meocafe - Home Page -->
        <script async="async" src="//events.sk.ht/meocafe/lib.js"></script>
        <script>
            var skaze = skaze || {};
            skaze.cmd = skaze.cmd || [];
            skaze.cmd.push(function () {
                skaze.init({
                    siteIdentifier: "meocafe"
                });
                skaze.pushEvent({
                    name: "meocafe - Home Page",
                    properties: {}
                });
            });
        </script>

        <script type="text/javascript">
            var TDConf = TDConf || {};
            var TDConf = TDConf || {};
            TDConf.Config = {
                protocol: document.location.protocol,
                containerTagId: "24479"
            };

            if (typeof (TDConf) != "undefined") {
                TDConf.sudomain = ("https:" == document.location.protocol) ? "swrap" : "wrap";
                TDConf.host = ".tradedoubler.com/wrap";
                TDConf.containerTagURL = (("https:" == document.location.protocol) ?
                    "https://" : "http://") + TDConf.sudomain + TDConf.host;
                if (typeof (TDConf.Config) != "undefined") {
                    var tdSscript = document.createElement('script');
                    tdSscript.src = TDConf.containerTagURL + "?id=" + TDConf.Config.containerTagId;
                    var s0 = document.getElementsByTagName('script')[0];
                    s0.parentNode.insertBefore(tdSscript, s0);
                }
            }
        </script>
    <?php endif; ?>
        <?php if (is_product_category()) : ?>
        <?php

        $products = [];

        $i = 0;

        while (have_posts()) {
            the_post();
            $product = wc_get_product(get_the_ID());

            $products[$i] = [
                'id' => $product->get_id(),
                'price' => $product->get_price(),
                'salePrice' => $product->get_sale_price(),
                'currency' => 'EUR',
            ];


            $term_list = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'ids'));
            if (isset($term_list[0])) {
                $term = get_term($term_list[0]);
                $products[$i]['category'] = $term->name;
            }

            if (isset($term_list[0])) {
                $term = get_term($term_list[0]);
                $products[$i]['subCategory'] = $term->name;
            }

            $i++;
        }

        ?>
        <!-- meocafe - Products List -->
        <script async="async" src="//events.sk.ht/meocafe/lib.js"></script>
        <script>
            var skaze = skaze || {};
            skaze.cmd = skaze.cmd || [];
            skaze.cmd.push(function () {
                skaze.init({
                    siteIdentifier: "meocafe"
                });
                skaze.pushEvent({
                    name: "meocafe - Products List",
                    properties: {
                        products: <?php echo json_encode($products); ?>,
                    }
                });
            });
        </script>

        <script type="text/javascript">
            var TDConf = TDConf || {};
            TDConf.Config = {
                protocol: document.location.protocol,
                containerTagId: "24480"
            };

            if (typeof (TDConf) != "undefined") {
                TDConf.sudomain = ("https:" == document.location.protocol) ? "swrap" : "wrap";
                TDConf.host = ".tradedoubler.com/wrap";
                TDConf.containerTagURL = (("https:" == document.location.protocol) ?
                    "https://" : "http://") + TDConf.sudomain + TDConf.host;
                if (typeof (TDConf.Config) != "undefined") {
                    var tdSscript = document.createElement('script');
                    tdSscript.src = TDConf.containerTagURL + "?id=" + TDConf.Config.containerTagId;
                    var s0 = document.getElementsByTagName('script')[0];
                    s0.parentNode.insertBefore(tdSscript, s0);
                }
            }
        </script>
    <?php endif; ?>
        <?php if (is_product()) : ?>
        <?php
        $product = wc_get_product(get_the_ID());
        ?>

        <!-- meocafe - Product -->
        <script async="async" src="//events.sk.ht/meocafe/lib.js"></script>
        <script>
            <?php
            $product = wc_get_product(get_the_ID());

            $productDatas = [
                'id' => $product->get_sku(),
                'price' => $product->get_price(),
                'salePrice' => $product->get_sale_price(),
                'imageUrl' => get_the_post_thumbnail_url(),
                'description' => strip_tags(get_the_content()),
                'url' => get_permalink(),
                'currency' => 'EUR',
            ];

            $term_list = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'ids'));
            if (isset($term_list[0])) {
                $term = get_term($term_list[0]);
                $productDatas['category'] = $term->name;
            }

            if (isset($term_list[0])) {
                $term = get_term($term_list[0]);
                $productDatas['subCategory'] = $term->name;
            }

            $productDatas = (object)$productDatas;
            ?>

            var skaze = skaze || {};
            skaze.cmd = skaze.cmd || [];
            skaze.cmd.push(function () {
                skaze.init({
                    siteIdentifier: "meocafe"
                });
                skaze.pushEvent({
                    name: "meocafe - Product",
                    properties: <?php echo json_encode($productDatas); ?>
                });
            });
        </script>

        <script type="text/javascript">
            var TDConf = TDConf || {};
            TDConf.Config = {
                protocol: document.location.protocol,
                containerTagId: "24481"
            };

            if (typeof (TDConf) != "undefined") {
                TDConf.sudomain = ("https:" == document.location.protocol) ? "swrap" : "wrap";
                TDConf.host = ".tradedoubler.com/wrap";
                TDConf.containerTagURL = (("https:" == document.location.protocol) ?
                    "https://" : "http://") + TDConf.sudomain + TDConf.host;
                if (typeof (TDConf.Config) != "undefined") {
                    var tdSscript = document.createElement('script');
                    tdSscript.src = TDConf.containerTagURL + "?id=" + TDConf.Config.containerTagId;
                    var s0 = document.getElementsByTagName('script')[0];
                    s0.parentNode.insertBefore(tdSscript, s0);
                }
            }
        </script>
    <?php endif; ?>
        <?php if (is_cart()) : ?>
        <?php
        global $woocommerce;

        $cart_key = key(WC()->cart->get_cart_contents());

        $items = $woocommerce->cart->get_cart();

        $i = 0;

        foreach ($items as $item => $values) {
            $product = $values['data'];

            if (!($product instanceof WC_Product)) {
                continue;
            }

            $products[$i] = [
                'id' => $product->get_sku(),
                'price' => $product->get_price(),
                'salePrice' => $product->get_sale_price(),
                'currency' => 'EUR',
            ];

            $term_list = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'ids'));
            if (isset($term_list[0])) {
                $term = get_term($term_list[0]);
                $products[$i]['category'] = $term->name;
            }

            if (isset($term_list[0])) {
                $term = get_term($term_list[0]);
                $products[$i]['subCategory'] = $term->name;
            }

            $i++;
        }
        ?>
        <!-- meocafe - Basket -->
        <script async="async" src="//events.sk.ht/meocafe/lib.js"></script>
        <script>
            var skaze = skaze || {};
            skaze.cmd = skaze.cmd || [];
            skaze.cmd.push(function () {
                skaze.init({
                    siteIdentifier: "meocafe"
                });
                skaze.pushEvent({
                    name: "meocafe - Basket",
                    properties: {
                        basketId: "<?php echo $cart_key; ?>",
                        basketValue: "<?php echo WC()->cart->get_total('float'); ?>",
                        currency: "EUR",
                        products: <?php echo json_encode($products); ?>,
                    }
                });
            });
        </script>

        <script type="text/javascript">
            var TDConf = TDConf || {};
            TDConf.Config = {
                protocol: document.location.protocol,
                containerTagId: "24482"
            };

            if (typeof (TDConf) != "undefined") {
                TDConf.sudomain = ("https:" == document.location.protocol) ? "swrap" : "wrap";
                TDConf.host = ".tradedoubler.com/wrap";
                TDConf.containerTagURL = (("https:" == document.location.protocol) ?
                    "https://" : "http://") + TDConf.sudomain + TDConf.host;
                if (typeof (TDConf.Config) != "undefined") {
                    var tdSscript = document.createElement('script');
                    tdSscript.src = TDConf.containerTagURL + "?id=" + TDConf.Config.containerTagId;
                    var s0 = document.getElementsByTagName('script')[0];
                    s0.parentNode.insertBefore(tdSscript, s0);
                }
            }
        </script>
    <?php endif; ?>
        <?php if (is_checkout() && !empty(is_wc_endpoint_url('order-received'))) : ?>
        <?php
        global $wp_query;
        $order_id = (int)get_query_var('order-received');
        $order = wc_get_order($order_id);
        $order_total = $order->get_total('float');
        $coupons = $order->get_coupons();
        $emailMd5 = md5($order->get_billing_email());
        $emailSha256 = hash('sha256', $order->get_billing_email());
        $phoneMd5 = md5($order->get_billing_phone());
        $phoneSha256 = hash('sha256', $order->get_billing_phone());

        $items = $order->get_items();

        $i = 0;

        foreach ($items as $item => $values) {
            $product = wc_get_product($values->get_product_id());

            $products[$i] = [
                'id' => $product->get_sku(),
                'price' => $product->get_price(),
                'salePrice' => $product->get_sale_price(),
                'currency' => 'EUR',
            ];

            $term_list = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'ids'));
            if (isset($term_list[0])) {
                $term = get_term($term_list[0]);
                $products[$i]['category'] = $term->name;
            }

            if (isset($term_list[0])) {
                $term = get_term($term_list[0]);
                $products[$i]['subCategory'] = $term->name;
            }

            $i++;
        }


        ?>

        <!-- meocafe - Conversion -->
        <script async="async" src="//events.sk.ht/meocafe/lib.js"></script>
        <script>
            var skaze = skaze || {};
            skaze.cmd = skaze.cmd || [];
            skaze.cmd.push(function () {
                skaze.init({
                    siteIdentifier: "meocafe"
                });
                skaze.pushEvent({
                    name: "meocafe - Conversion",
                    properties: {
                        orderId: "<?php echo $order_id; ?>",
                        orderValue: "<?php echo $order_total; ?>",
                        currency: "EUR",
                        promoCode: "<?php echo $coupons[0]; ?>",
                        emailMD5: "<?php echo $emailMd5; ?>",
                        emailSHA256: "<?php echo $emailSha256; ?>",
                        phoneMD5: "<?php echo $phoneMd5; ?>",
                        phoneSHA256: "<?php echo $phoneSha256; ?>",
                        products: <?php echo json_encode($products); ?>,
                    }
                });
            });
        </script>
    <?php endif; ?>
        <?php if (is_checkout() && !empty(is_wc_endpoint_url('order-received')) && isset($_COOKIE['_ga'])) : ?>
        <img
            src="https://tbs.tradedoubler.com/report?organization=2283321&event=409127&orderNumber=<?php echo $order_id; ?>&orderValue=<?php echo $order_total; ?>&currency=EUR"
            width="1" height="1" border="0">
        <script type="text/javascript">
            var TDConf = TDConf || {};
            TDConf.Config = {
                orderNumber: "<?php echo $order_id; ?>",
                orderValue: "<?php echo $order_total; ?>",
                currency: "EUR",
                containerTagId: "24483"
            };
            if (typeof (TDConf) != "undefined") {
                TDConf.sudomain = ("https:" == document.location.protocol) ? "swrap" : "wrap";
                TDConf.host = ".tradedoubler.com/wrap";
                TDConf.containerTagURL = (("https:" == document.location.protocol) ? "https://" : "http://") + TDConf.sudomain + TDConf.host;
                if (typeof (TDConf.Config) != "undefined") {
                    var tdSscript = document.createElement('script');
                    tdSscript.src = TDConf.containerTagURL + "?id=" + TDConf.Config.containerTagId;
                    var s0 = document.getElementsByTagName('script')[0];
                    s0.parentNode.insertBefore(tdSscript, s0);
                }
            }
        </script>
    <?php endif;
    endif;
    if (is_checkout() && !empty(is_wc_endpoint_url('order-received')) && !isset($_COOKIE['_ga'])) :
        global $wp_query;
        $order_id = (int)get_query_var('order-received');
        $order = wc_get_order($order_id);
        $order_total = $order->get_total('float');
        ?>
        <!-- meocafe - Conversion Anonymised -->
        <iframe
            src="https://tbs.tradedoubler.com/report?organization=2283321&event=409127&ordernumber=auto&orderValue=<?php echo $order_total; ?>&currency=EUR&type=iframe"
            width="1" height="1" frameborder="0"></iframe>
    <?php endif;
}

/**
 * Create the section beneath the tva tab
 */
add_filter('woocommerce_get_sections_tax', 'meo_delivery_tva_add_section');
function meo_delivery_tva_add_section($sections)
{
    $sections['tva-livraison'] = __('TVA de livraison pour facture', 'text-domain');
    return $sections;
}


add_action('woocommerce_settings_save_tax', 'meo_update_delivery_tva_settings');

function meo_update_delivery_tva_settings()
{
    $settings = meo_get_delivery_tva_settings();
    WC_Admin_Settings::save_fields($settings);
}

/**
 * Add settings to the specific section we created before
 */
add_filter('woocommerce_get_settings_tax', 'meo_delivery_tva_all_settings', 10, 2);
function meo_delivery_tva_all_settings($settings)
{
    $current_section = $_GET['section'];
    if ($current_section == 'tva-livraison') {
        return meo_get_delivery_tva_settings();
    } else {
        return $settings;
    }
}

function meo_get_delivery_tva_settings()
{
    $settings_deliverytva = array();
    // Add Title to the Settings
    $settings_deliverytva[] = array(
        'name' => __('Réglages TVA de livraison', 'text-domain'),
        'type' => 'title',
        'desc' => __('Les options suivantes sont utilisées afin de configurer la TVA de la livraison', 'text-domain'),
        'id' => 'tva-livraison'
    );
    // Add text field option
    $settings_deliverytva[] = array(
        'name' => __('TVA a appliquer (%)', 'text-domain'),
        'desc_tip' => __('Merci de ne remplir ce champ qu\'avec des chiffres, exemple : 20, 19.6', 'text-domain'),
        'id' => 'deliverytva_pourcent',
        'type' => 'text',
        'desc' => __('Ce champ correspond à la TVA qui sera prise en compte pour le calcul sur les factures', 'text-domain'),
    );
    $settings_deliverytva[] = array('type' => 'sectionend', 'id' => 'tva-livraison');
    return $settings_deliverytva;
}

/**
 * Check if guest email is already used by a register account
 */
add_action('woocommerce_after_checkout_validation', 'meo_validate_guest_email', 10, 2);
function meo_validate_guest_email($data, $error)
{
    $email = $data['billing_email'];
    if (!is_user_logged_in() && email_exists($email)) {
        $error->add('email', __('Cet e-mail est déjà utilisé. Veuillez-vous connecter ou renseignez une autre adresse e-mail', 'meo'));
    }
}

add_filter('woocommerce_get_breadcrumb', 'remove_shop_crumb', 20, 2);
function remove_shop_crumb($crumbs, $breadcrumb)
{
    if (is_category()) {
        $blogCrumb = [__('Blog', 'meo'), esc_url(get_permalink(get_option('page_for_posts')))];
        array_splice($crumbs, 1, 0, array($blogCrumb));
    }
    foreach ($crumbs as $key => $crumb) {
        if ($crumb[0] === 'Boutique') {
            unset($crumbs[$key]);
        }
    }
    return $crumbs;
}

add_action('woocommerce_widget_shopping_cart_before_buttons', 'minicart_count_before_content');
function minicart_count_before_content()
{
    $cart_url = wc_get_cart_url(); // Récupère l'URL du panier WooCommerce
    echo '<a href="' . $cart_url . '" class="woocommerce-mini-cart__code-promo">' . __("> Vous avez un code promo ?", "meo") . '</a>';
}

add_filter('wp_grid_builder_map/marker_content', function ($content, $marker) {
    $excerpt = strip_tags(get_the_content($marker['id']), '<br>');
    $products = meo_get_store_products_assoc($marker['id']);

    ob_start();

    get_template_part('template-parts/store-map-marker-content', '', [
        'title' => get_the_title(),
        'excerpt' => $excerpt,
        'post_id' => $marker['id'],
        'products' => $products
    ]);

    $content = ob_get_clean();

    return $content;
}, 10, 2);


add_action('wp_grid_builder/card/wrapper_end', 'meo_store_list_card');
function meo_store_list_card($card)
{
    $post = wpgb_get_post();

    if ('revendeur' === $post->post_type) {
        $excerpt = strip_tags($post->post_content, '<br>');
        $relatedProducts = meo_get_store_products_assoc($post->ID);
        $geoloc = get_post_meta($post->ID, 'localisation_revendeur', true);

        if (!empty($relatedProducts)) {
            $products = get_posts([
                'post_type' => 'product',
                'posts_per_page' => -1,
                'post__in' => $relatedProducts,
                'fields' => 'ids'
            ]);
            printf('<p><button class="store-products-btn-popup" id="%s">%s</button></p>', $post->ID, __('Voir les produits', 'meo'));
            printf('<p class="hidden"><span id="lat">%s</span><span id="lng">%s</span></p>', $geoloc['lat'], $geoloc['lng']);
            meo_store_related_products_popup_content($products, $post->ID, $excerpt);
        }
    }
}

add_filter('wp_grid_builder/frontend/register_scripts', 'meo_load_wpgb_script');
function meo_load_wpgb_script($scripts)
{
    $scripts[] = [
        'handle' => 'store-locator',
        'source' => get_stylesheet_directory_uri() . '/resources/wpgb/store-locator.js',
        'version' => '1.0.0',
    ];
    return $scripts;
}

/**
 * Remove add to cart button for subscription products and add a redirection button to the product page instead
 */
add_action('wp', 'my_remove_add_to_cart');

function my_remove_add_to_cart() {
    if (is_subscription_archive()) {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
    }
}

function add_view_product_button()
{
    global $product;

    if (! $product instanceof \WC_Product) return;

    $product_categories = $product->get_category_ids();
    $link = $product->get_permalink();

    if (!($sub_cat = is_subscription_archive())) {
        return;
    }

    if (in_array($sub_cat->term_id, $product_categories) || in_array($product->get_type(), ['subscription', 'bundle'])) {
        echo '<a href="' . $link . '" class="button add_to_cart_button">Voir l\'abonnement</a>';
    } else{
        echo woocommerce_template_loop_add_to_cart();
    }
}

add_action('woocommerce_after_shop_loop_item', 'add_view_product_button', 10);

remove_action('woocommerce_bundled_item_details', 'wc_pb_template_bundled_item_description', 20, 2);

add_action('loop_start', 'meo_manage_product_bundle_template', 1);
function meo_manage_product_bundle_template()
{
    global $product;

    if (! $product instanceof \WC_Product) return;

    if (!is_admin() && is_product() && 'bundle' === $product->get_type()) {
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
        remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price');
        remove_action('woocommerce_bundles_add_to_cart_wrap', 'wc_pb_template_add_to_cart_wrap');
        remove_action('woocommerce_single_product_summary', 'shoptimizer_product_custom_content', 45);
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
        add_action('woocommerce_after_add_to_cart_button', 'shoptimizer_product_custom_content', 45);
        add_action('woocommerce_single_product_summary', function () {
            add_action('woocommerce_bundles_add_to_cart_wrap', 'wc_pb_template_add_to_cart_wrap');
        });
    }
}

add_action('meo_subscription_steps', 'meo_add_steps');
function meo_add_steps($product)
{
    $sortedBundledItems = getSortedBundledItems($product);
    get_template_part('template-parts/abonnement-steps', '', [
        'product' => $product,
        'sorted-bundle-items' => $sortedBundledItems,
        'machine-step-tab-title' => get_field('field_coffee_machine_tab_title', 'options'),
        'machine-step-title' => get_field('field_coffee_machine_title', 'options'),
        'machine-step-text' => get_field('field_coffee_machine_text', 'options'),
        'has-machine' => !empty($sortedBundledItems['machine-step']),
        'pods-step-tab-title' => get_field('field_coffee_pods_tab_title', 'options'),
        'pods-step-title' => get_field('field_coffee_pods_title', 'options'),
        'pods-step-text' => get_field('field_coffee_pods_text', 'options'),
        'accessories-step-tab-title' => get_field('field_accessories_tab_title', 'options'),
        'accessories-step-title' => get_field('field_accessories_title', 'options'),
        'accessories-step-text' => get_field('field_accessories_text', 'options'),
    ]);
}

add_action('woocommerce_single_product_summary', function () {
    global $product;

    if (! $product instanceof \WC_Product) return;

    if ('bundle' !== $product->get_type()) {
        return;
    }

    $productId = $product->get_id();

    get_template_part('template-parts/abonnement-banner', '', [
        'title' => get_the_title($productId),
        'subtitle' => apply_filters('woocommerce_short_description', get_the_excerpt($productId)),
        'bg-img'   => wp_get_attachment_image_url(get_field('subscription_banner_image', get_the_ID()), 'full'),
        'content'  => get_the_content($productId)
    ]);
    ?>

    <?php
}, 20);

add_action('woocommerce_single_product_summary', function(){
    global $product;

    if (! $product instanceof \WC_Product) return;

    if ('bundle' !== $product->get_type()) {
        get_template_part('template-parts/woocommerce/single-product/sticky-cart', '', ['product_type' => $product->get_type()]);
    }
}, 2);

add_action('woocommerce_before_add_to_cart_button', function () {
    global $product;

    if (! $product instanceof \WC_Product) return;

    if ('bundle' !== $product->get_type()) {
        return;
    }

    get_template_part('template-parts/abonnement-cart-summary');
});

function prefix_facet_title_tag($tag_name): string
{
    // We replace the default h4 tag by p tag.
    return 'div';
}

add_filter('wp_grid_builder/facet/title_tag', 'prefix_facet_title_tag');

add_action('woocommerce_after_bundle_price', 'meo_after_bundle_price');
function meo_after_bundle_price()
{
    printf(
        '<div class="subtext-bundle-price hidden"><p>%s</p><p>%s</p></div>',
        __('* Ce montant sera prélevé chaque mois lors de l\'envoi de votre colis.', 'meo'),
        __('* Cet abonnement vous engage pour une durée de 1 an.', 'meo')
    );
}

add_filter('woocommerce_bundle_front_end_params', 'meo_add_bundle_price_label');
function meo_add_bundle_price_label($params)
{
    $params['i18n_price_format'] = '<span class="bundle-price-label">' . $params['i18n_subtotal'] . '</span> ' . $params['i18n_price_format'];

    return $params;
}

add_action('woocommerce_bundled_item_details', 'meo_display_price_simple_product_bundle', 20, 2);
function meo_display_price_simple_product_bundle($bundled_item, $product)
{
    if ($bundled_item->product->get_type() === 'subscription') {
        return $bundled_item;
    }

    // If $sub_datas => sub plan set on product admin page
    $sub_datas = get_post_meta($bundled_item->product->id, '_wcsatt_schemes', true);
    $bundled_price = $bundled_item->product->get_price();

    if ((float) $bundled_item->get_discount() === 0.0) {
        if ($sub_datas) {
            if ($sub_datas[0]['subscription_pricing_method'] === 'inherit') {
                if (empty($sub_datas[0]['subscription_discount'])) {
                    $price = wc_price($bundled_price);
                } else {
                    $price = wc_format_sale_price(
                        $bundled_price,
                        wc_price($bundled_price - (($bundled_price * $sub_datas[0]['subscription_discount'])/100))
                    );
                }
            } else {
                if (empty($sub_datas[0]['subscription_sale_price'])) {
                    $price = wc_price($sub_datas[0]['subscription_regular_price']);
                } else {
                    $price = wc_format_sale_price(
                        $sub_datas[0]['subscription_regular_price'],
                        $sub_datas[0]['subscription_sale_price'],
                    );
                }
            }
            $price .= ' chacun / mois';
        } else {
            $price = wc_price($bundled_price, [
                'decimal_separator' => ',',
                'thousand_separator' => ' '
            ]);
        }
    } else {
        if ($sub_datas) {
            if ($sub_datas[0]['subscription_pricing_method'] === 'inherit') {
                $price = wc_format_sale_price(
                    $bundled_price,
                    $bundled_price - (($bundled_price * $sub_datas[0]['subscription_discount'])/100)
                );
            } else {
                if (empty($sub_datas[0]['subscription_sale_price'])) {
                    $price = $sub_datas[0]['subscription_regular_price'];
                } else {
                    $price = wc_format_sale_price(
                        $sub_datas[0]['subscription_regular_price'],
                        $sub_datas[0]['subscription_sale_price'],
                    );
                }
            }
        }

        if (! isset($price)) {
            $price = wc_format_sale_price(
                $bundled_price,
                (float)$bundled_price - (((float)$bundled_price * (float)$bundled_item->get_discount())/100)
            );
        }

        $price .= ' chacun / mois';
    }

    ?>
    <p><span><?php echo $price; ?></span></p>
    <?php
}

add_filter('woocommerce_cart_item_subtotal', 'meo_cart_bundle_subtotal_simple_product', 10, 3);
function meo_cart_bundle_subtotal_simple_product($subtotal, $cart_item, $cart_item_key)
{
    $product = wc_get_product($cart_item['product_id']);

    if ($product->get_type() === 'subscription' || $cart_item['wcsatt_data']['active_subscription_scheme']) {
        return $subtotal;
    }

    $subtotal = sprintf('<span>%s</span>', wc_price($product->get_price()));

    return $subtotal;
}


add_filter('woocommerce_add_to_cart_redirect', function ($url) {
    return $url;
});

add_filter('wc_points_rewards_redeem_points_message', function ($message) {
    if (!is_checkout() && !is_cart()) {
        return '';
    }
    return $message;
});

add_filter('wc_points_rewards_earn_points_message', function ($message) {
    if (!is_checkout() && !is_cart()) {
        return '';
    }
    return $message;
});


//Display custom price on subscription page list
add_filter('formatted_woocommerce_price', 'meo_sub_price_page_list');
add_filter('woocommerce_format_sale_price', 'meo_sub_price_page_list');
function meo_sub_price_page_list($price)
{
    global $product;

    if (! $product instanceof \WC_Product) return $price;

    if (is_null($product) || is_admin() || get_queried_object_id() !== get_field('listing_subscription_term', 'options')) {
        return $price;
    }

    if ($product->get_type() !== 'bundle') {
        return $price;
    }

    $sub_price_list = (float) get_field('subscription_price', $product->get_id());

    if (!$sub_price_list) {
        return $price;
    }

    $decimals = wc_get_price_decimals();
    $decimal_separator = wc_get_price_decimal_separator();
    $thousand_separator = wc_get_price_thousand_separator();

    $price = number_format($sub_price_list, $decimals, $decimal_separator, $thousand_separator);

    return $price;
}

add_filter('woocommerce_format_sale_price', function ($price, $reg) {
    global $product;

    if (! $product instanceof \WC_Product) return $price;

    if (is_admin() || get_queried_object_id() !== get_field('listing_subscription_term', 'options')){
        return $price;
    }

    if ($product->get_type() !== 'bundle') {
        return $price;
    }

    return $reg;
}, 11, 2);

function meo_has_sidebar_disabled()
{
    if (!is_tax('product_cat')) {
        return false;
    }
    return get_field('disable_sidebar', 'product_cat_' . get_queried_object()->term_id);
}

add_filter('body_class', function ($classes) {
    global $product;

    if (! $product instanceof \WC_Product) return $classes;

    if (is_product() && $product instanceof \WC_Product) {
        if ($product->get_type() === 'bundle') {
            $classes[] = 'product-bundle';
        }
    }

    $disabledSidebar = meo_has_sidebar_disabled();
    if (!$disabledSidebar) {
        return $classes;
    }

    $classes = array_filter($classes, function ($arr) {
        return $arr !== 'left-woocommerce-sidebar right-archives-sidebar right-page-sidebar right-post-sidebar';
    });

    $classes[] = 'page-template-template-fullwidth-contained';

    return $classes;

}, 1, 999);


add_filter('woocommerce_product_add_to_cart_text', function ($html, $product) {
    if (class_exists('WC_Subscriptions_Product') && class_exists('WC_Product_Bundle')) {
        if ($product instanceof WC_Product_Bundle) {
            foreach ($product->get_bundled_items() as $bundled_item) {
                if ($bundled_item->product->get_type() === 'subscription') {
                    return \WC_Subscriptions_Product::get_add_to_cart_text();
                }
            }
        }
    }
    return $html;
}, 99, 2);

add_filter('woocommerce_product_get_image', function ($image, $instance, $size, $attr, $placeholder, $image_class) {
    $isInCart = false;

    if (WC()->cart) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $isInCart = $cart_item['product_id'] == $instance->get_id() ? true : $isInCart;
        }

        if ($isInCart) {
            $dom = new DOMDocument();
            $dom->loadHTML($image);
            $domImage = $dom->getElementsByTagName('img')[0];
            $src = $domImage->getAttribute('data-src');
            $domImage->setAttribute('src', $src);
            return $dom->saveHTML($domImage);
        }
    }
    return $image;
}, 10, 6);

// Move Apple Pay Button on checkout page
if (class_exists('WC_Stripe_Payment_Request')) {
    remove_action('woocommerce_checkout_before_customer_details', [WC_Stripe_Payment_Request::instance(), 'display_payment_request_button_html'], 1);
    remove_action('woocommerce_checkout_before_customer_details', [WC_Stripe_Payment_Request::instance(), 'display_payment_request_button_separator_html'], 2);
    add_action('meo_after_payment_methods_list', [WC_Stripe_Payment_Request::instance(), 'display_payment_request_button_html']);
}

add_filter('wp_grid_builder/facet/query_string', function ($settings, $grid_id, $action) {
    global $wp_query;

    $isBlogPage = is_home() && !is_front_page();

    if (is_category() || $isBlogPage) { // Afficher uniquement 10 articles sur la page de blog et catégorie
        $wp_query->query_vars['posts_per_page'] = 10;
    }

    if (is_category() || $isBlogPage) {
        return $settings;
    }

    // Seulement sur le listing produit et boutique
    if (
        !(
            isset($wp_query->query['product_cat'])
            || (
                isset($wp_query->query['post_type']) && $wp_query->query['post_type'] === 'product'
            )
            || $action !== 'refresh'
        )
    ) {
        return $settings;
    }

    if (($action === 'render' || $action === 'refresh') && ! is_post_type_archive('revendeur') && $grid_id !== 1) {
        if (isset($settings['order']) && !empty($settings['order'])) {
            return $settings;
        }
        $settings['order'] = ['total_sales_desc'];
    }

    return $settings;
}, 10, 3);

add_filter('wp_grid_builder/frontend/register_scripts', 'meo_load_products_archive_script');
function meo_load_products_archive_script($scripts)
{
    $scripts[] = [
        'handle' => 'products-archive',
        'source' => get_stylesheet_directory_uri() . '/resources/wpgb/products-archive.js',
        'version' => '1.0.1',
    ];
    return $scripts;
}


add_filter('wp_grid_builder/templates', function ($templates) {
    $templates['woocommerce-product-loop'] = [
        'class' => 'products column-3',
        'source_type' => 'post_type',
        'is_main_query' => true,
        'render_callback' => 'meo_store_archive_render_callback',
        'noresults_callback' => 'meo_store_archive_noresults_callback',
    ];

    return $templates;

}, 10, 1);

// Supprime l'action de big blue pour l'ajout de point relai
add_action('init', function () {
    global $wp_filter;
    // Configuration des hooks avec nom de méthode et priorité
    $hooks = [
        ['hook' => 'woocommerce_review_order_before_payment', 'method' => 'inject_preact_root_div', 'priority' => 10],
        ['hook' => 'woocommerce_after_checkout_form', 'method' => 'enqueue_pickup_points', 'priority' => 10],
    ];

    foreach ($hooks as $hook_config) {
        $hook_name = $hook_config['hook'];
        $method = $hook_config['method'];
        $priority = $hook_config['priority'];

        if (!isset($wp_filter[$hook_name][$priority]) || !is_iterable($wp_filter[$hook_name][$priority])) {
            continue;
        }

        foreach ($wp_filter[$hook_name][$priority] as $unique_id => $filter_array) {
            if (is_array($filter_array['function']) && is_object($filter_array['function'][0])) {
                $object = $filter_array['function'][0];
                $method_to_check = $filter_array['function'][1];

                // Vérifier si l'objet est une instance de Bigblue_Public et si la méthode correspond
                if ($object instanceof Bigblue_Public && $method_to_check === $method) {
                    // Retirer l'action
                    remove_action($hook_name, array($object, $method), $priority);
                    // Pas besoin de sortir de la boucle puisqu'il peut y avoir plusieurs actions à retirer
                }
            }
        }
    }
});

add_action('meo/cart/after_shipping_destination', function () {
    ?>
    <style>
        .woocommerce-shipping-totals.shipping .bb-tag-root a {
            font-weight: bold;
            color: #1F2937;
        }
        .woocommerce-shipping-totals.shipping .bb-tag-root {
            padding: 0 0 10px;
            background: transparent;
        }
    </style>
    <bigblue data-component="fast-tag-cart"></bigblue>
    <?php
});

add_action('meo/single-product/after_short_description', function () {
    ?>
    <div class="woocommerce-product-details__short-description">
        <?php
        $product = wc_get_product();
        if ($product) {
            if ($product->is_type('variable')) {
                $childrens = $product->get_children();
                if ($childrens) {
                    $defaultvariation = "";

                    foreach ($childrens as $productChild) {
                        $defaultvariation = wc_get_product($productChild)->get_variation_id();
                    }

                    echo "<bigblue data-component=\"fast-tag-product\" data-product=\"" . $product->get_id() . "\" data-variant=\"" . $defaultvariation . "\"></bigblue>";

                }

                wc_enqueue_js("

                $( 'form.variations_form' ).on('show_variation', function(_, data){

                    var updatedVariation = data.variation_id.toString()

                    if( updatedVariation != \"\" ) {

                        var tags = document.body.querySelectorAll('bigblue[data-component=\"fast-tag-product\"]')

                        if (tags !== undefined){

                            for (var i=0; i < tags.length; i++) {

                                if (tags[i].getAttribute(\"data-product\") === \"" . $product->get_id() . "\") {

                                    tags[i].setAttribute(\"data-variant\", updatedVariation);

                                }

                            }

                        }

                    }

                });

            ");

            } else {

                echo "<bigblue data-component=\"fast-tag-product\" data-product=\"" . $product->get_id() . "\"></bigblue>";

            }

        }

        ?>

    </div>
    <?php
});

// Update mini cart quantity
function dynamic_qty_update()
{
    foreach (['security', 'key', 'number', 'product_id'] as $field) {
        if (!isset($_POST[$field]) || !is_scalar($_POST[$field])) {
            wp_send_json_error(['error' => 'invalid_request'], 400);
        }
    }

    $security = sanitize_text_field(wp_unslash((string) $_POST['security']));
    if (!wp_verify_nonce($security, 'dynamic-quantity-ajax')) {
        wp_send_json_error(['error' => 'invalid_nonce'], 403);
    }

    $key = sanitize_text_field(wp_unslash((string) $_POST['key']));
    $number = filter_var(wp_unslash((string) $_POST['number']), FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0],
    ]);
    $product_id_input = wp_unslash((string) $_POST['product_id']);
    if ($product_id_input !== '0' && !preg_match('/^product-[1-9][0-9]*$/', $product_id_input)) {
        wp_send_json_error(['error' => 'invalid_request'], 400);
    }
    $product_id = $product_id_input === '0' ? 0 : (int) substr($product_id_input, 8);
    $cart = WC()->cart;

    if ($number === false || !$cart || !isset($cart->cart_contents[$key])) {
        wp_send_json_error(['error' => 'invalid_cart_item'], 400);
    }

    $item = $cart->cart_contents[$key];
    if (($product_id > 0 && !in_array($product_id, [
        (int) $item['product_id'],
        (int) ($item['variation_id'] ?? 0),
    ], true))
        || !isset($item['data']) || !($item['data'] instanceof WC_Product)) {
        wp_send_json_error(['error' => 'invalid_cart_item'], 400);
    }

    $product = $item['data'];
    $max_quantity = $product->get_max_purchase_quantity();
    if ($number > 0 && $max_quantity > 0 && $number > $max_quantity) {
        wp_send_json_error(['error' => 'invalid_quantity'], 400);
    }

    $is_subscription = $product->is_type(['subscription', 'subscription_variation'])
        || !empty($item['wcsatt_data']['active_subscription_scheme']);

    if ($is_subscription && $number > 0) {
        $item_product_id = (int) $item['product_id'];
        $sub_datas = get_post_meta($item_product_id, '_wcsatt_schemes', true);
        $catalog_product = wc_get_product(!empty($item['variation_id'])
            ? (int) $item['variation_id']
            : $item_product_id);
        $price = $catalog_product
            ? (float) $catalog_product->get_price()
            : (float) $product->get_price();
        if (is_array($sub_datas) && isset($sub_datas[0]) && is_array($sub_datas[0])) {
            $scheme = $sub_datas[0];
            $active_scheme = $item['wcsatt_data']['active_subscription_scheme'] ?? '';
            foreach ($sub_datas as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }
                $candidate_key = ($candidate['subscription_period_interval'] ?? '')
                    . '_' . ($candidate['subscription_period'] ?? '');
                if ($candidate_key === $active_scheme) {
                    $scheme = $candidate;
                    break;
                }
            }
            if (($scheme['subscription_pricing_method'] ?? '') === 'override') {
                $price = (float) ($scheme['subscription_price'] ?? $price);
            } elseif (!empty($scheme['subscription_discount'])) {
                $price -= $price * ((float) $scheme['subscription_discount'] / 100);
            }
        }

        if ($price * $number < 20) {
            wp_send_json_error([
                'error' => 'sub_under_20',
                'qty' => $price > 0 ? (int) ceil(20 / $price) : (int) $item['quantity'],
            ]);
        }
    }

    if (!$cart->set_quantity($key, $number)) {
        wp_send_json_error(['error' => 'invalid_quantity'], 400);
    }

    if ($number === 0) {
        $cart->calculate_totals();
    }

    $updated = $cart->get_cart();
    wp_send_json_success([
        'count' => $cart->cart_contents_count,
        'total' => $cart->get_cart_total(),
        'item_price' => isset($updated[$key])
            ? $cart->get_product_subtotal($updated[$key]['data'], $number)
            : 0,
    ]);
}
add_action('wp_ajax_dynamic_qty_update', 'dynamic_qty_update');
add_action('wp_ajax_nopriv_dynamic_qty_update', 'dynamic_qty_update');

// Add custom quantity buttons to mini cart
function mini_cart_quantity_buttons()
{
    if (is_cart() || is_checkout()) {
        return;
    }

    echo '<div class="dynamic-qty"><button type="button" class="dynamic-qty-btn" data-type="plus"> +</button><button type="button" class="dynamic-qty-btn" data-type="minus">-</button></div>';
}
add_action('woocommerce_after_quantity_input_field', 'mini_cart_quantity_buttons', 10, 2);

/**
Add Custom Icon For Cash On Delivery
 **/
function cod_gateway_icon($gateways)
{
    if (isset($gateways['stripe'])) {
        $gateways['stripe']->icon = get_stylesheet_directory_uri() . '/resources/images/payment_CB.png';
    }
    if (isset($gateways['paypal'])) {
        $gateways['paypal']->icon = get_stylesheet_directory_uri() . '/resources/images/payment_Paypal.png';
    }
    return $gateways;
}
add_filter('woocommerce_available_payment_gateways', 'cod_gateway_icon');

// Remove add to cart link for subscription on product archive page and replace it by subscription product link
add_filter('woocommerce_loop_add_to_cart_link', function ($html, $product, $args) {

    if ($product->get_type() !== 'bundle') {
        return $html;
    }

    $html = sprintf(
        '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
        esc_url(get_permalink($product->get_id())),
        esc_attr(isset($args['quantity']) ? $args['quantity'] : 1),
        esc_attr(isset($args['class']) ? $args['class'] : 'button'),
        isset($args['attributes']) ? wc_implode_html_attributes($args['attributes']) : '',
        esc_html($product->add_to_cart_text())
    );

    return $html;
}, 10, 3);

/* Avoid scroll to notices on cart update */
add_action('wp_enqueue_scripts', function () {
    if (!is_cart()) {
        return;
    }
    wp_add_inline_script('woocommerce', '
        jQuery(function($) {
            const noop = () => {};
            Object.defineProperty($, "scroll_to_notices", {
                configurable: true,
                get: () => noop,
                set: noop,
            });
        });
    ');
});
