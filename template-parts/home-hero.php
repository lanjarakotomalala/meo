<?php
/** Editorial carousel displayed before the homepage content. */

$shop_url = function_exists('wc_get_page_permalink') ? (wc_get_page_permalink('shop') ?: home_url('/boutique/')) : home_url('/boutique/');
$coffee_url = $shop_url;
if (taxonomy_exists('product_cat')) {
    $coffee_term = get_term_by('slug', 'cafes', 'product_cat');
    if ($coffee_term) {
        $term_url = get_term_link($coffee_term);
        if (!is_wp_error($term_url)) {
            $coffee_url = $term_url;
        }
    }
}
$brand_page = get_page_by_path('notre-histoire');
$brand_url = $brand_page ? get_permalink($brand_page) : '#content';

// Editors can override each visual and link in the Customizer.
$slides = [
    [
        'eyebrow' => 'La sélection du moment',
        'title' => get_theme_mod('meo_hero_promo_title', 'La promo du mois'),
        'text' => get_theme_mod('meo_hero_promo_text', 'Vos cafés préférés à savourer à prix doux.'),
        'button' => 'Découvrir l’offre',
        'url' => get_theme_mod('meo_hero_promo_link') ?: $shop_url,
        'image' => get_theme_mod('meo_hero_promo_image', ''),
        'mobile_image' => get_theme_mod('meo_hero_promo_mobile_image', ''),
        'class' => 'meo-home-hero__slide--promo',
    ],
    [
        'eyebrow' => 'À découvrir',
        'title' => get_theme_mod('meo_hero_coffee_title', 'Le café du mois'),
        'text' => get_theme_mod('meo_hero_coffee_text', 'Une nouvelle rencontre, une nouvelle façon de prendre le temps.'),
        'button' => 'Explorer nos cafés',
        'url' => get_theme_mod('meo_hero_coffee_link') ?: $coffee_url,
        'image' => get_theme_mod('meo_hero_coffee_image', ''),
        'mobile_image' => get_theme_mod('meo_hero_coffee_mobile_image', ''),
        'class' => 'meo-home-hero__slide--coffee',
    ],
    [
        'eyebrow' => 'Depuis 1928',
        'title' => get_theme_mod('meo_hero_brand_title', 'Le goût des belles histoires'),
        'text' => get_theme_mod('meo_hero_brand_text', 'Une maison de café, un savoir-faire et le plaisir de partager.'),
        'button' => 'Découvrir Méo',
        'url' => get_theme_mod('meo_hero_brand_link') ?: $brand_url,
        'image' => get_theme_mod('meo_hero_brand_image', ''),
        'mobile_image' => get_theme_mod('meo_hero_brand_mobile_image', ''),
        'class' => 'meo-home-hero__slide--brand',
    ],
];

if (empty($slides[2]['image']) && has_post_thumbnail(get_queried_object_id())) {
    $slides[2]['image'] = get_the_post_thumbnail_url(get_queried_object_id(), 'full');
}

// Existing catalogue photography is used until dedicated campaign images are selected.
if (function_exists('wc_get_products') && (empty($slides[0]['image']) || empty($slides[1]['image']))) {
    if (empty($slides[0]['image']) && empty($slides[0]['mobile_image'])) {
        $sale_products = wc_get_products(['status' => 'publish', 'limit' => 1, 'on_sale' => true]);
        if ($sale_products && $sale_products[0]->get_image_id()) {
            $slides[0]['image'] = wp_get_attachment_image_url($sale_products[0]->get_image_id(), 'large');
            $slides[0]['class'] .= ' meo-home-hero__slide--product-image';
        }
    }
    if (empty($slides[1]['image']) && empty($slides[1]['mobile_image'])) {
        $coffee_products = wc_get_products(['status' => 'publish', 'limit' => 1, 'orderby' => 'date', 'order' => 'DESC', 'category' => ['cafe-en-grain']]);
        if ($coffee_products && $coffee_products[0]->get_image_id()) {
            $slides[1]['image'] = wp_get_attachment_image_url($coffee_products[0]->get_image_id(), 'large');
            $slides[1]['class'] .= ' meo-home-hero__slide--product-image';
        }
    }
}
?>
<section class="meo-home-hero" aria-label="À la une" aria-roledescription="carrousel" tabindex="0">
    <div class="meo-home-hero__slides">
        <?php foreach ($slides as $index => $slide) : ?>
            <article class="meo-home-hero__slide <?php echo esc_attr($slide['class']); ?><?php echo $index === 0 ? ' is-active' : ''; ?>" aria-roledescription="diapositive" aria-label="<?php echo esc_attr(($index + 1) . ' sur ' . count($slides)); ?>" aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>">
                <?php if ($slide['image'] || $slide['mobile_image']) : ?>
                    <div class="meo-home-hero__visual" style="--meo-visual-desktop: url('<?php echo esc_url($slide['image'] ?: $slide['mobile_image']); ?>');<?php if ($slide['mobile_image']) : ?> --meo-visual-mobile: url('<?php echo esc_url($slide['mobile_image']); ?>');<?php endif; ?>"></div>
                <?php endif; ?>
                <div class="meo-home-hero__shade"></div>
                <div class="meo-home-hero__body">
                    <span class="meo-home-hero__eyebrow"><?php echo esc_html($slide['eyebrow']); ?></span>
                    <?php if ($index === 0) : ?><h1><?php else : ?><h2><?php endif; ?><?php echo esc_html($slide['title']); ?><?php if ($index === 0) : ?></h1><?php else : ?></h2><?php endif; ?>
                    <p><?php echo esc_html($slide['text']); ?></p>
                    <a class="meo-home-hero__link" href="<?php echo esc_url($slide['url']); ?>"<?php echo $index === 0 ? '' : ' tabindex="-1"'; ?>><?php echo esc_html($slide['button']); ?><span aria-hidden="true"> ↗</span></a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <div class="meo-home-hero__controls">
        <div class="meo-home-hero__pagination" aria-label="Choisir une diapositive">
            <?php foreach ($slides as $index => $slide) : ?>
                <button type="button" class="<?php echo $index === 0 ? 'is-active' : ''; ?>" aria-label="Afficher <?php echo esc_attr($slide['title']); ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" data-slide="<?php echo esc_attr($index); ?>"><span><?php echo esc_html(sprintf('%02d', $index + 1)); ?></span></button>
            <?php endforeach; ?>
        </div>
        <div class="meo-home-hero__arrows"><button type="button" data-direction="-1" aria-label="Diapositive précédente">←</button><button type="button" data-direction="1" aria-label="Diapositive suivante">→</button></div>
    </div>
    <a class="meo-home-hero__scroll" href="#content">Défiler pour découvrir <span aria-hidden="true">↓</span></a>
</section>
