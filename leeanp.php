<?php namespace LeanWoocommerceApi;
/**
 * Plugin Name: Lean Woocommerce Api
 * Description: Woocommerce API endpoints for Lean.
 * Version: 0.1.0
 * Author: Moxie
 * Author URI: http://getmoxied.net
 * Text Domain: leanwoocommerceapi
 */

// General constants.
define( 'LEANWOOCOMMERCEAPI_PLUGIN_NAME', 'LeanWoocommerceApi Plugin' );
define( 'LEANWOOCOMMERCEAPI_PLUGIN_VERSION', '0.1.0' );
define( 'LEANWOOCOMMERCEAPI_MINIMUM_WP_VERSION', '4.3.1' );
define( 'LEANWOOCOMMERCEAPI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LEANWOOCOMMERCEAPI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEANWOOCOMMERCEAPI_TEXT_DOMAIN', 'leanwoocommerceapi' );

// Load Composer autoloader.
require_once LEANWOOCOMMERCEAPI_PLUGIN_DIR . 'vendor/autoload.php';

// Run the plugin setup.
require_once LEANWOOCOMMERCEAPI_PLUGIN_DIR . 'PluginSetup.php';
$class_name = __NAMESPACE__ . '\\PluginSetup';
register_activation_hook( __FILE__, array( $class_name, 'maybe_deactivate' ) );
register_deactivation_hook( __FILE__, array( $class_name, 'flush_rewrite_rules' ) );
$class_name::init();
