<div class="language-switcher-mobile">
	<button class="language-switcher-mobile__current" aria-expanded="false" title="<?php _e( 'Change language', 'meo' ); ?>">
		<?php echo pll_current_language( 'name' ); ?>
	</button>
	<ul class="language-switcher-mobile__list">
		<?php pll_the_languages(); ?>
	</ul>
</div>