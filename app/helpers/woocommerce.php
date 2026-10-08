<?php
/**
 * This callback is called for each post in the loop.
 *
 * @param object $post Holds post, term or user object (depending of the source_type).
 */
function meo_store_archive_render_callback($post)
{
    get_template_part('woocommerce/content', 'product');
}

/**
 * This callback is called when no results match selected facets.
 */
function meo_store_archive_noresults_callback()
{
    do_action('woocommerce_no_products_found');
}

/**
 * Get all products related to a store
 *
 * @param $store_id
 * @return false|int[]|WP_Post[]
 */
function meo_get_store_products_assoc($store_id) {
    $productAssocMeta = get_post_meta($store_id, 'products_assoc', true);

    if ($productAssocMeta === '') {
        return false;
    }

    $storeProductsAssoc = explode(',', $productAssocMeta);

    $args = [
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => 'ean',
                'value' => $storeProductsAssoc,
                'compare' => 'IN'
            ]
        ]
    ];
    $products = new WP_Query($args);

    return $products->posts;
}

/**
 * Card for popup store related product list
 */
function meo_store_product_related_card($post) {
    ?>
    <div class="product product-<?php echo esc_attr($post->ID); ?>">
        <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
            <div class="container-product-thumbnail"><?php the_post_thumbnail($post->ID); ?></div>
            <p><?php echo get_the_title($post->ID); ?></p>
        </a>
    </div>
    <?php
}

/**
 * Get store related products popup content
 */
function meo_store_related_products_popup_content($products, $post_id, $excerpt) {
    ob_start();

    get_template_part('template-parts/store-map-popup-content', '', [
        'store_id' => $post_id,
        'title' =>  get_the_title($post_id),
        'excerpt' => $excerpt,
        'products' => $products
    ]);

    $content = ob_get_clean();

    echo $content;
}

/**
 * Returns an array with the elements of the box sorted
 * to be displayed correctly in the correct steps of the subscription
 */
function getSortedBundledItems($product)
{
    $bundledItems = $product->get_bundled_items();

    $sortedBundledItems = [];

    $machineCat = get_field('field_coffee_machine_cat', 'options');
    $coffeeCat = get_field('field_coffee_pods_cat', 'options');
    $accessoriesCat = get_field('field_accessories_cat', 'options');

    foreach ($bundledItems as $bundledItem) {
        $productId = $bundledItem->get_product_id();
        $productCategories = wp_get_post_terms($productId, 'product_cat', ['fields' => 'ids']);

        if (count(array_intersect($machineCat, $productCategories)) > 0) {
            $sortedBundledItems['machine-step'][] = $bundledItem;
        }
        if (count(array_intersect($coffeeCat, $productCategories)) > 0) {
            $sortedBundledItems['coffee-step'][] = $bundledItem;
        }
        if (count(array_intersect($accessoriesCat, $productCategories)) > 0) {
            $sortedBundledItems['accessories-step'][] = $bundledItem;
        }
    }


    return $sortedBundledItems;
}

function is_subscription_archive() {
    $sub_cat = get_term_by('slug', 'abonnement', 'product_cat');

    if (!$sub_cat) {
        return false;
    }

    return $sub_cat;
}

// Display only bundle products on subscription archive page
add_action('woocommerce_product_query', 'filter_subscriptions_archive');
function filter_subscriptions_archive($q) {
    if (is_product_category() && get_queried_object_id() === get_field('listing_subscription_term', 'options')) {
        $tax_query = $q->get('tax_query');
        $tax_query[] = array(
            'taxonomy' => 'product_type',
            'field' => 'slug',
            'terms' => 'bundle',
        );

        $q->set('tax_query', $tax_query);
    }
}

function meo_is_custom_sub($product) {
    $sub_datas = get_post_meta($product->get_id(), '_wcsatt_schemes', true);

    return (bool) $sub_datas;
}

add_action('woocommerce_product_options_pricing', 'meo_price_per_kilo_field');
function meo_price_per_kilo_field() {
    global $thepostid;

    $price_per_kilo = get_post_meta($thepostid, 'prix_kilo', true);

    if ($price_per_kilo) {
        woocommerce_wp_text_input(
            [
                'id' => 'custom_price',
                'name' => '_custom_price',
                'value' => number_format($price_per_kilo, 2, ',', ' '),
                'class' => 'wc_input_price short',
                'label' => __('Price per kilo', 'meo'),
                'custom_attributes' => ['readonly' => 'readonly'],
            ]
        );
    }
}


/**
 * Calcule le prix au kg d'un produit WooCommerce
 *
 * @param WC_Product|int $product Produit ou ID du produit
 * @return float|false Prix au kg ou false si non calculable
 */
function get_price_per_kg($product)
{
    // Si on passe un ID, récupérer l'objet produit
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }

    if (!$product instanceof WC_Product) {
        return false;
    }

    // Récupération du prix actif (promo ou normal)
    $price = (float)$product->get_price();
    if ($price <= 0) {
        return false;
    }

    // Récupération du poids du produit
    $weight = (float)$product->get_weight();
    if ($weight <= 0) {
        return false;
    }

    // Unité de poids définie dans WooCommerce
    $unit = get_option('woocommerce_weight_unit', 'kg');

    // Conversion du poids en kilogrammes
    switch (strtolower($unit)) {
        case 'g':
        case 'gram':
        case 'grams':
            $weight_in_kg = $weight / 1000;
            break;
        case 'kg':
        case 'kilogram':
        case 'kilograms':
            $weight_in_kg = $weight;
            break;
        case 'lbs':
        case 'lb':
        case 'pound':
        case 'pounds':
            $weight_in_kg = $weight * 0.45359237;
            break;
        case 'oz':
        case 'ounce':
        case 'ounces':
            $weight_in_kg = $weight * 0.02834952;
            break;
        default:
            // Si unité inconnue, on suppose kilo
            $weight_in_kg = $weight;
            break;
    }

    if ($weight_in_kg <= 0) {
        return false;
    }

    // Prix au kg
    return round($price / $weight_in_kg, 2);
}
