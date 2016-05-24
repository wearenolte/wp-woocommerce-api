<?php namespace Lean\Woocommerce;
/**
 * Plugin Name: Lean Woocommerce Api
 * Description: Woocommerce API endpoints for Lean.
 * Version: 0.1.0
 * Author: Moxie
 * Author URI: http://getmoxied.net
 * Text Domain: leanwoocommerceapi
 */


// General constants.
define( 'LEAN_WOOCOMMERCE_API_PLUGIN_NAME', 'Lean Woocommerce Api' );
define( 'LEAN_WOOCOMMERCE_API_PLUGIN_VERSION', '0.1.0' );
define( 'LEAN_WOOCOMMERCE_API_MINIMUM_WP_VERSION', '4.3.1' );
define( 'LEAN_WOOCOMMERCE_API_MINIMUM_WC_VERSION', '2.5.5' );
define( 'LEAN_WOOCOMMERCE_API_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LEAN_WOOCOMMERCE_API_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEAN_WOOCOMMERCE_API_TEXT_DOMAIN', 'leanwoocommerceapi' );

// Load Composer autoloader.
require_once LEAN_WOOCOMMERCE_API_PLUGIN_DIR . 'vendor/autoload.php';

// Run the plugin setup.
$setup = new PluginSetup();
register_activation_hook( __FILE__, array( $setup, 'maybe_deactivate' ) );
