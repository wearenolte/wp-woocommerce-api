<?php namespace Lean\Woocommerce\Modules;

use Lean\Woocommerce\Modules\Cart\CartEndpoint;

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
		CartEndpoint::init();
	}
}