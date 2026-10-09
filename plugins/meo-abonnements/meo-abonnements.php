<?php
/**
 * Plugin Name: Méo Abonnements
 * Description: Tableau de bord simple pour les abonnements WooCommerce de Méo.
 * Version: 0.1.0
 * Requires at least: 5.3
 * Requires PHP: 7.4
 * Text Domain: meo-abonnements
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/src/Repository.php';
require_once __DIR__ . '/src/Actions.php';
require_once __DIR__ . '/src/Admin.php';

add_action('init', static function () {
    if (!class_exists('WooCommerce') || !function_exists('wcs_get_subscription')) {
        add_action('admin_notices', static function () {
            if (!current_user_can('activate_plugins')) {
                return;
            }

            echo '<div class="notice notice-warning"><p>' . esc_html__(
                'Méo Abonnements nécessite WooCommerce et WooCommerce Subscriptions. Le tableau de bord reste inactif tant que ces extensions ne sont pas disponibles.',
                'meo-abonnements'
            ) . '</p></div>';
        });
        return;
    }

    $repository = new \Meo\Abonnements\Repository();
    $actions = new \Meo\Abonnements\Actions($repository);
    $admin = new \Meo\Abonnements\Admin($repository, $actions);
    $admin->register();
}, 20);
