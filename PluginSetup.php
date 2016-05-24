<?php namespace Lean\Woocommerce;

/**
 * Main class loader for initializing and  setting up the plugin.
 *
 * @since 0.1.0
 */
class PluginSetup {

	/**
	 * Checks program environment to see if all dependencies are available. If at least one
	 * dependency is absent, deactivate the plugin.
	 *
	 * @since 0.1.0
	 */
	public static function maybe_deactivate() {

		global $wp_version;

		load_plugin_textdomain( LEAN_WOOCOMMERCE_API_TEXT_DOMAIN );

		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( version_compare( $wp_version, LEAN_WOOCOMMERCE_API_MINIMUM_WP_VERSION, '<' ) ) {

			deactivate_plugins( LEAN_WOOCOMMERCE_API_PLUGIN_NAME );

			echo wp_kses(
				sprintf(
					esc_html__(
						'Plugin %s requires WordPress %s or higher.',
						LEAN_WOOCOMMERCE_API_TEXT_DOMAIN
					), LEAN_WOOCOMMERCE_API_API_VERSION, LEAN_WOOCOMMERCE_API_MINIMUM_WP_VERSION
				),
				array()
			);
			wp_die();
			exit;
		}
	}
}
