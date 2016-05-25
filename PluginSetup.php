<?php namespace Lean\Woocommerce;


use Lean\Woocommerce\Modules\Bootstrap;


/**
 * Main class loader for initializing and  setting up the plugin.
 *
 * @since 0.1.0
 */
class PluginSetup {

	const WOOCOMMERCE_PATH = 'woocommerce/woocommerce.php';

	public static function init() {
		Bootstrap::init();
	}

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

		// Check WP Version.
		self::deactivate(
			version_compare( $wp_version, LEAN_WOOCOMMERCE_API_MINIMUM_WP_VERSION, '<' ),
			[ __CLASS__, 'wp_invalid_message' ]
		);

		// Check if WooCommerce is active.
		self::deactivate( ! is_plugin_active( self::WOOCOMMERCE_PATH ), [ __CLASS__, 'wc_invalid_message' ] );

	}

	/**
	 * Creates the Error message when the minium version of WP is not installed.
	 *
	 * @return mixed
	 */
	public static function wp_invalid_message() {
		return sprintf(
			esc_html__(
				'Plugin %s requires WordPress %s or higher.',
				LEAN_WOOCOMMERCE_API_TEXT_DOMAIN
			), LEAN_WOOCOMMERCE_API_PLUGIN_NAME, LEAN_WOOCOMMERCE_API_MINIMUM_WP_VERSION
		);
	}

	/**
	 * Creates the Error message when the WC Plugin is not active.
	 *
	 * @return mixed
	 */
	public static function wc_invalid_message() {
		return sprintf(
			esc_html__(
				'%s requires Woocommerce Plugin %s or higher activated.',
				LEAN_WOOCOMMERCE_API_TEXT_DOMAIN
			), LEAN_WOOCOMMERCE_API_PLUGIN_NAME, LEAN_WOOCOMMERCE_API_MINIMUM_WC_VERSION
		);
	}

	/**
	 * Deactivates the plugin if the condition is satisfied, and call the callback function.
	 *
	 * @param bool	   $condition Boolean condition.
	 * @param callable $callback Callback function to receive the error message.
	 */
	public static function deactivate( $condition, $callback ) {
		if ( $condition ) {
			$message = call_user_func( $callback );
			deactivate_plugins( LEAN_WOOCOMMERCE_API_PLUGIN_NAME );
			echo wp_kses( $message, array() );
			wp_die();
			exit;
		}
	}
}
