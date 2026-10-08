<?php

namespace MyApp\WooCommerce;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

class StructuredDatas implements ServiceProviderInterface
{
    public function register($container): void
    {
        // Nothing to register.
    }
    public function bootstrap($container): void
    {
        add_filter('seopress_schemas_auto_product_json', [$this, 'aggregateRating'], 99);
        add_filter('seopress_schemas_auto_product_json', [$this, 'removeReviewAuthor'], 99);
    }

    public function aggregateRating(array $markup): array
    {
        global $product;

        $review_widget = get_post_meta($product->get_id(), '_judgeme_widget_review_widget', true);

        if (! $review_widget || ! isset($review_widget['widget'])) {
            return $markup;
        }

        $html = html_entity_decode(htmlspecialchars(substr($review_widget['widget'], 0, 150)));
        $pattern = '/(\w+)=\'([^\']*)\'/';
        preg_match_all($pattern, $html, $attributes);

        if (empty($attributes) || empty($attributes[1]) || empty($attributes[2])) {
            return $markup;
        }

        $results = [];

        for ($i = 0; $i < count($attributes[1]); $i++) {
            $results[$attributes[1][$i]] = $attributes[2][$i];
        }

        if (! isset($results['rating']) || empty($results['reviews'])) {
            return $markup;
        }

        $markup['aggregateRating'] = array(
            '@type'       => 'AggregateRating',
            'ratingValue' => $results['rating'],
            'reviewCount' => $results['reviews'],
        );

        return $markup;
    }

    public function removeReviewAuthor(array $markup): array
    {
        if (isset($markup['review']['author'])) {
            unset($markup['review']['author']);
        }

        return $markup;
    }
}
