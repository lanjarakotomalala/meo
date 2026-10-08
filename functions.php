<?php
/**
 * Bootstrap theme.
 *
 * The purpose of this file is to bootstrap your theme by loading all dependencies and helpers.
 *
 * YOU SHOULD NORMALLY NOT NEED TO ADD ANYTHING HERE - any custom functionality unrelated
 * to bootstrapping the theme should go into a service provider or a separate helper file
 * (refer to the directory structure in README.md).
 *
 * @package MyApp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_stylesheet_directory() . '/includes/fields/category.php';

/**
 * Define a content width for the theme.
 *
 * @link https://developer.wordpress.com/themes/content-width/
 */
if (!isset($content_width)) {
    $content_width = 1080;
}
add_filter('jetpack_photon_any_extension_for_domain', '__return_true');
add_filter('jetpack_photon_development_mode', '__return_false');


// Make sure we can load a compatible version of WP Emerge.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'version.php';

$name = trim(get_file_data(__DIR__ . DIRECTORY_SEPARATOR . 'style.css', ['Theme Name'])[0]);
$load = my_app_should_load_wpemerge($name, '0.16.0', '2.0.0');

if (!$load) {
    // An incompatible WP Emerge version is already loaded - stop further execution.
    // my_app_should_load_wpemerge() will automatically add an admin notice.
    return;
}

// Load composer dependencies.
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
}

my_app_declare_loaded_wpemerge($name, 'theme', __FILE__);

// Load helpers.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'MyApp.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'helpers.php';

// Bootstrap theme after all dependencies and helpers are loaded.
\MyApp::make()->bootstrap(require __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config.php');

// Register hooks.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'hooks.php';

add_action('woocommerce_checkout_order_processed', function () {
    foreach (WC()->cart->get_applied_coupons() as $coupon) {
        if (strpos($coupon, 'wc_points_redemption_') === 0) {
            WC()->cart->remove_coupon($coupon);
        }
    }
});

function set_cta_custom_color()
{
    $ctas_color = get_field('ctas_color', 'option');
    $secondary_ctas_color = get_field('secondary_ctas_color', 'option');
    ?>
    <style>:root {
        --ctas-color: <?php echo $ctas_color ?? '#0e6000';?>;
        --secondary-ctas-color: <?php echo $secondary_ctas_color ?? '#0a0a0a';?>;
      }</style>
    <?php
}
add_action('wp_head', 'set_cta_custom_color');

//Remove subscription item from account menu
function remove_subscription_item($items)
{
    unset($items['subscriptions']);

    return $items;
}
add_filter('woocommerce_account_menu_items', 'remove_subscription_item');


add_action('after_setup_theme', function () {
    remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 30);
    remove_action('woocommerce_before_shop_loop', 'shoptimizer_woocommerce_pagination', 30);
});

// Sets when the session is about to expire
add_filter('wc_session_expiring', 'woocommerce_cart_session_about_to_expire');
function woocommerce_cart_session_about_to_expire()
{
    // Default value is 47sui
    return 60 * 60 * 24;
}

// Sets when the session will expire
add_filter('wc_session_expiration', 'woocommerce_cart_session_expires');
function woocommerce_cart_session_expires()
{
    // Default value is 48
    return 60 * 60 * 24;
}

// Disable persistent cart
add_filter('woocommerce_persistent_cart_enabled', '__return_false');

// Fix m2ecloud setup
add_filter('wp_redirect', function ($location) {
    if (!defined('M2ECLOUD_NAME')) {
        return $location;
    }
    $site_url = site_url();
    $home_url = home_url();

    if ($site_url === $home_url) {
        return $location;
    }
    $wc_auth_path = '/wc-auth/v';
    if (strpos($location, $site_url . $wc_auth_path) !== false) {
        $location = str_replace($site_url . $wc_auth_path, $home_url . $wc_auth_path, $location);
    }
    return $location;
});
