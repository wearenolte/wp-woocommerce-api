<?php namespace Lean\Woocommerce;

use Lean\Woocommerce\Api;

/**
 * Class Bootstrap. This class is used to load all the endpoints of the Plugin.
 *
 * @package Lean\Woocomerce\Modules
 */
class Bootstrap {
	/**
	 * Init function.
	 */
	public static function init() {
		Api\Cart::init();
		Api\Order::init();
		Api\Checkout::init();
	}
}
