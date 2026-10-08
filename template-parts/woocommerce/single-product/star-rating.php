<?php
$rating = ($args['rating'] * 100) / 5;
$count = $args['count'];
?>

<span style="clip-path: polygon(0 0, <?php echo $rating; ?>% 0, <?php echo $rating; ?>% 100%, 0 100%);">
	<?php _e( 'Note', 'meo' ); ?>
	<strong class="rating"><?php echo $rating; ?></strong> <?php _e( 'sur', 'meo' ); ?> <?php echo $count; ?>
</span>
