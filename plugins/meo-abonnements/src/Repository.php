<?php

namespace Meo\Abonnements;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Read subscriptions through WooCommerce's data APIs (including HPOS stores).
 */
final class Repository
{
    public const PAGE_SIZE = 20;

    public function find(int $id)
    {
        if ($id < 1) {
            return false;
        }

        return wcs_get_subscription($id);
    }

    public function statuses(): array
    {
        return function_exists('wcs_get_subscription_statuses')
            ? wcs_get_subscription_statuses()
            : [];
    }

    public function listSubscriptions(int $page, string $status, string $search)
    {
        $args = [
            'type' => 'shop_subscription',
            'status' => $status ? 'wc-' . $status : 'any',
            'limit' => self::PAGE_SIZE,
            'page' => max(1, $page),
            'paginate' => true,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if ($search !== '') {
            if (ctype_digit($search)) {
                $args['include'] = [(int) $search];
            } elseif (is_email($search)) {
                $args['billing_email'] = $search;
            } else {
                return (object) ['orders' => [], 'total' => 0, 'max_num_pages' => 0];
            }
        }

        return wc_get_orders($args);
    }

    public function count(string $status): int
    {
        return function_exists('wc_orders_count')
            ? (int) wc_orders_count('wc-' . $status, 'shop_subscription')
            : 0;
    }

    public function listPlanProducts(string $kind, int $page): \WP_Query
    {
        $args = [
            'post_type' => 'product',
            'post_status' => ['publish', 'private', 'draft'],
            'posts_per_page' => self::PAGE_SIZE,
            'paged' => max(1, $page),
            'orderby' => 'title',
            'order' => 'ASC',
            'fields' => 'ids',
        ];

        if ($kind === 'plans') {
            $args['meta_query'] = [[
                'key' => '_wcsatt_schemes',
                'compare' => 'EXISTS',
            ]];
        } else {
            $args['tax_query'] = [[
                'taxonomy' => 'product_type',
                'field' => 'slug',
                'terms' => ['subscription', 'variable-subscription'],
            ]];
        }

        return new \WP_Query($args);
    }
}
