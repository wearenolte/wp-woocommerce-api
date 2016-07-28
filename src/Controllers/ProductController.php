<?php namespace Lean\Woocommerce\Controllers;

/**
 * Class ProductController
 */
class ProductController extends \WC_REST_Products_Controller {

	/**
	 * Get formatted product data.
	 *
	 * @param \WC_PRODUCT $product The product.
	 * @return array Product info in an array.
	 */
	public function get_product( $product ) {
		return $this::get_product_data( $product );
	}
}
