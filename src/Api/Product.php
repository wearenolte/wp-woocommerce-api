<?php namespace Lean\Woocommerce\Api;

use Lean\AbstractEndpoint;
use Epoch2\HttpCodes;
use Lean\Woocommerce\Utils\ErrorCodes;

/**
 * Class Product.
 *
 * @package Leean\Woocomerce\Modules\Product
 */
class Product extends AbstractEndpoint {
	/**
	 * Endpoint path
	 *
	 * @Override
	 * @var String
	 */
	protected $endpoint = '/ecommerce/products';

	/**
	 * Endpoint callback override.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return array|\WP_Error
	 */
	public function endpoint_callback( \WP_REST_Request $request ) {
		$method = $request->get_method();

		if ( \WP_REST_Server::READABLE === $method ) {
			return self::get_products();
		} else {
			return new \WP_Error(
				ErrorCodes::METHOD_ERROR,
				'Method not allowed',
				[ 'status' => HttpCodes::HTTP_METHOD_NOT_ALLOWED ]
			);
		}
	}

	/**
	 * Set the options of the endpoint. Allow http methods.
	 *
	 * @return array
	 */
	protected function endpoint_options() {
		return [
			'methods' => [ \WP_REST_Server::READABLE ],
			'callback' => [ $this, 'endpoint_callback' ],
			'args' => $this->endpoint_args(),
		];
	}

	/**
	 * Get a list of all available products
	 *
	 * @return array
	 */
	public static function get_products() {
		$products = [];
		$args = array(
			'post_type' => 'product',
		);
		$loop = new \WP_Query( $args );

		if ( $loop->have_posts() ) {
			while ( $loop->have_posts() ) : $loop->the_post();
				$theid = get_the_ID();
				$product = new \WC_Product($theid);
				$products[] = $product->();
			endwhile;
		}
		wp_reset_postdata();

		return $products;
	}
}
