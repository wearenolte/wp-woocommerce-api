<?php namespace Lean\Woocommerce;

use Lean\Woocommerce\Api\Cart;

/**
 * Class Bootstrap. This class is used to load all the endpoints of the Plugin.
 *
 * @package Leean\Woocomerce\Modules
 */
class Bootstrap
{
	/**
	 * Init function.
	 */
	public static function init() {
		Cart::init();
	}
}
