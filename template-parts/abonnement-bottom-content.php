<?php

$image    = false;
$image_id = get_field( 'image' );

if ( $image_id ) {
    $image = wp_get_attachment_image( $image_id );
}

if ( get_field( 'title' ) ) :
?>
<div class="archive-details-meta alignfull">
    <?php if ( $image ) : ?>
    <div class="archive-details-meta__thumbnail">
        <?= $image; ?>
    </div>
    <?php endif; ?>
    <div class="archive-details-meta__content">
        <p><?php echo get_field( 'title' ); ?></p>
        <?php echo get_field( 'text' ); ?>
    </div>
</div>
<?php endif; ?>
