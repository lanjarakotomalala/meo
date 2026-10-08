<?php

$title_font_1 = get_field( 'title_font_1' );
$title_font_2 = get_field( 'title_font_2' );

if( empty( $title_font_1 ) && empty( $title_font_2 ) ) {
	$title_font_1 = get_the_title();
}

?>
<div class="woocommerce-loop-product__title">
    <h3>
        <a href="<?php echo get_the_permalink(); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
            <span><?php echo $title_font_1; ?></span>
            <span><?php echo $title_font_2; ?></span>
        </a>
    </h3>
</div>
