<?php namespace Lean\Woocommerce\Api;

use Lean\AbstractEndpoint;
use Epoch2\HttpCodes;
use Lean\Woocommerce\Utils\ErrorCodes;

/**
 * Class Cart.
 *
 * @package Leean\Woocomerce\Modules\Cart
 */
class Cart extends AbstractEndpoint {
	/**
	 * Endpoint path
	 *
	 * @Override
	 * @var String
	 */
	protected $endpoint = '/ecommerce/cart';

	/**
	 * Endpoint callback override.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return array|\WP_Error
	 */
	public function endpoint_callback( \WP_REST_Request $request ) {
		$method = $request->get_method();

		if ( in_array( $method, [ \WP_REST_Server::READABLE ], true ) ) {
			return self::get_cart();
		} else if ( in_array( $method, str_getcsv( \WP_REST_Server::EDITABLE ), true ) ) {
			return self::add_to_cart( $request );
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
			'methods' => array_merge(
				[ \WP_REST_Server::READABLE ],
				str_getcsv( \WP_REST_Server::EDITABLE )
			),
			'callback' => [ $this, 'endpoint_callback' ],
			'args' => $this->endpoint_args(),
		];
	}

	/**
	 * Set the args for this endpoint.
	 *
	 * @return array
	 */
	public function endpoint_args() {
		return [
			'product_id' => [
				'default' => false,
				'required' => false,
				'validate_callback' => function( $product_id ) {
					return false === $product_id || intval( $product_id ) >= 0;
				},
			],
			'token_id' => [
				'default' => false,
				'required' => false,
				'validate_callback' => function( $token_id ) {
					return false === $token_id || is_string( $token_id );
				},
			],
		];
	}

	/**
	 * Add an item to the cart, and return the new cart.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return array
	 */
	public static function add_to_cart( \WP_REST_Request $request ) {

		$product_id = $request->get_param( 'product_id' );

		if ( ! $product_id ) {
			return new \WP_Error(
				ErrorCodes::BAD_REQUEST,
				'Invalid data, product_id is required.',
				[ 'status' => HttpCodes::HTTP_BAD_REQUEST ]
			);
		}

		$cart = self::get_cart();
		$cart->add_to_cart( intval( $product_id ) );

		return $cart;
	}

	/**
	 * Get the cart for the current session user.
	 *
	 * @return array
	 */
	public static function get_cart() {
		\WC()->cart->get_cart_from_session();

		return \WC()->cart;
	}
}
