<?php namespace Lean\Woocommerce\Controllers;

/**
 * Class ProductController
 */
class ProductController extends \WC_REST_Products_Controller {

	/**
	 * Get formatted product data.
	 *
	 * @param \WC_Product $product The product.
	 * @return array Product info in an array.
	 */
	public function get_product( $product ) {
		$product = new \WC_Product_Variable( $product->id );
		$result =  $this::get_product_data( $product );

		$result['variations'] = $this::get_variation_data( $product );

		return $result;
	}
}
