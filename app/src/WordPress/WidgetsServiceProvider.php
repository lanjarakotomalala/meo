<?php

namespace MyApp\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Register widgets and sidebars.
 */
class WidgetsServiceProvider implements ServiceProviderInterface {
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
		add_action( 'widgets_init', [$this, 'registerWidgets'] );
		add_action( 'widgets_init', [$this, 'registerSidebars'] );
	}

	/**
	 * Register widgets.
	 *
	 * @return void
	 */
	public function registerWidgets() {
		// phpcs:ignore
		// register_widget( MyWidgetClass::class );
	}

	/**
	 * Register sidebars.
	 *
	 * @return void
	 */
	public function registerSidebars() {
		/**
		 * Array of default options.
		 *
		 * @var array
		 */

		$default_options = [
			'before_widget' => '<li id="%1$s" class="widget %2$s">',
			'after_widget'  => '</li>',
			'before_title'  => '<div class="widget__title">',
			'after_title'   => '</div>',
		];

		/**
		 * Default sidebar.
		 */
		register_sidebar(
			array_merge(
				$default_options,
				[
					'name' => __( 'Footer Left', 'meo' ),
					'id'   => 'footer-left-sidebar',
				]
			)
		);

		register_sidebar(
			array_merge(
				$default_options,
				[
					'name' => __( 'Footer Center', 'meo' ),
					'id'   => 'footer-center-sidebar',
				]
			)
		);

		register_sidebar(
			array_merge(
				$default_options,
				[
					'name' => __( 'Footer Right', 'meo' ),
					'id'   => 'footer-right-sidebar',
				]
			)
		);

		register_sidebar(
			array_merge(
				$default_options,
				[
					'name' => __( 'Social', 'meo' ),
					'id'   => 'footer-social-sidebar',
				]
			)
		);
	}
}
