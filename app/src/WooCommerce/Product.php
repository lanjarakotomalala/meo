<?php

namespace MyApp\WooCommerce;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

class Product implements ServiceProviderInterface
{
    public function register($container): void
    {
    }

    public function bootstrap($container): void
    {
        add_action('template_redirect', [$this, 'fifuMetaAlt'], 5);
        add_filter('woocommerce_gallery_image_html_attachment_image_params', [$this, 'setAutoAltProductImages'], 999, 2);
        add_filter('wp_get_attachment_image_attributes', [$this, 'setAltProductGalleryImages'], 999, 2);
        add_filter('woocommerce_single_product_image_thumbnail_html', [$this, 'preventMainGalleryImageLazyLoad'], 999, 2);
        add_action('woocommerce_after_product_object_save', [$this, 'syncPrixKilo']);
    }

    public function fifuMetaAlt(): void
    {
        if (! is_product()) {
            return;
        }

        if (! ($product_id = get_queried_object_id())) {
            return;
        }

        if (get_post_meta($product_id, 'fifu_image_alt', true)) {
            return;
        }

        update_post_meta($product_id, 'fifu_image_alt', get_the_title($product_id));
    }

    public function setAutoAltProductImages(array $details, int $image_id): array
    {
        global $product;

        $image_title = get_the_title($image_id);

        if ($image_title === '') {
            $image_title = $product->get_name();
        }

        $details['alt'] = $image_title;

        return $details;
    }

    /**
     * Empêche le plugin FIFU de convertir l'image principale de la galerie
     * produit en lazy-load.
     */
    public function preventMainGalleryImageLazyLoad(string $html, ?int $post_thumbnail_id): string
    {
        if (strpos($html, '<img') === false || strpos($html, 'fifu-replaced') !== false) {
            return $html;
        }

        return preg_replace('/<img\s/', '<img fifu-replaced="1" ', $html, 1);
    }

    public function setAltProductGalleryImages(array $attr, \WP_Post $attachment): array
    {
        $post_parent = $attachment->post_parent ?? '';

        if (! $post_parent) {
            return $attr;
        }

        $product = wc_get_product($post_parent);

        if (! $product) {
            return $attr;
        }


        $image_title = $attachment->post_title;

        if ($image_title === '') {
            $image_title = $product->get_name();
        }

        $attr['alt'] = $image_title;

        return $attr;
    }

    /**
     *
     * Show subcategories of a WooCommerce parent category
     *
     * @param string $parent_slug Parent category slug (ex: 'cafe-en-grain')
     */
    public static function displayCategoryChildren(): void
    {
        if (!is_product_category()) {
            return;
        }

        $current_term = get_queried_object();

        if (!$current_term instanceof \WP_Term || is_wp_error($current_term)) {
            return;
        }

        $args = [
            'taxonomy'   => 'product_cat',
            'parent'     => $current_term->term_id,
            'hide_empty' => false,
        ];

        $child_terms = get_terms($args);

        $filtered_terms = array_filter($child_terms, function ($term) {
            return get_field('display_in_front', 'product_cat_' . $term->term_id) ?? true;
        });

        if (!empty($filtered_terms)) {
            echo '<ul class="subcategories">';
            foreach ($filtered_terms as $term) {
                $term_link = get_term_link($term);
                echo '<li><a href="' . esc_url($term_link) . '">' . esc_html($term->name) . '</a></li>';
            }
            echo '</ul>';
        }
    }

    public function syncPrixKilo(\WC_Product $product): void
    {
        $price_per_kg = get_price_per_kg($product);
        if ($price_per_kg !== false) {
            update_post_meta($product->get_id(), 'prix_kilo', $price_per_kg);
        } else {
            delete_post_meta($product->get_id(), 'prix_kilo');
        }
    }
}
