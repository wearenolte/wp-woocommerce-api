<?php namespace Lean\Woocommerce\Controllers;

/**
 * Class ProductController
 */
class ProductController extends \WC_REST_Products_Controller {
	public function get_product( $product ) {
		return $this::get_product_data( $product );
	}
}