<?php

namespace MyApp\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Register widgets and sidebars.
 */
class MenusServiceProvider implements ServiceProviderInterface {
	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		// Nothing to register.
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		add_action( 'after_setup_theme', [$this, 'registerMenus'] );
	}

	/**
	 * Register menu locations.
	 *
	 * @return void
	 */
	public function registerMenus() {
		register_nav_menus(
			[
				'header-left'   => __( 'Header left', 'meo' ),
				'header-right'  => __( 'Header right', 'meo' ),
				'addons-mobile' => __( 'Addons mobile', 'meo' ),
				'social'        => __( 'Social menu', 'meo' ),
			]
		);
	}
}
