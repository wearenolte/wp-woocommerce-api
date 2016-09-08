<?php namespace Lean\Woocommerce\Api;

use Lean\AbstractEndpoint;
use Epoch2\HttpCodes;
use Lean\Woocommerce\Utils\ErrorCodes;
use Lean\Woocommerce\Controllers\UserController;
use Lean\Woocommerce\Api\Cart;
use Lean\Woocommerce\Utils\Hooks;

/**
 * Class Cart.
 *
 * @package Leean\Woocomerce\Modules\Cart
 */
class MultipleCart extends AbstractEndpoint {
	/**
	 * Endpoint path
	 *
	 * @Override
	 * @var String
	 */
	protected $endpoint = '/ecommerce/cart_multiple';

	/**
	 * Endpoint callback override.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return array|\WP_Error
	 */
	public function endpoint_callback( \WP_REST_Request $request ) {
		$method = $request->get_method();

		if ( in_array( $method, str_getcsv( \WP_REST_Server::EDITABLE ), true ) ) {
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
			'methods' => [ \WP_REST_Server::CREATABLE ],
			'callback' => [ $this, 'endpoint_callback' ],
			'args' => $this->endpoint_args(),
		];
	}


	/**
	 * Add an item to the cart, and return the new cart.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return array
	 */
	public static function add_to_cart( \WP_REST_Request $request ) {
		$params = $request->get_json_params();
		$token_id = $request->get_param( 'token_id' ) ? $request->get_param( 'token_id' ) : false;

		if ( ! is_array( $params ) || empty( $params ) ) {
			return new \WP_Error(
				ErrorCodes::BAD_REQUEST,
				'Invalid data, array of objects expected.',
				[ 'status' => HttpCodes::HTTP_BAD_REQUEST ]
			);
		}

		$validate = self::validate_params( $params );

		if ( is_wp_error( $validate ) ) {
			return $validate;
		}

		$cart_class = new Cart();
		$cart = $cart_class::get_cart();

		if ( $token_id ) {
			$user = UserController::get_user_by_token( $token_id );

			if ( $user ) {
				$cart = get_user_meta( $user->ID, Cart::CART_USER_META, true );
			}
		}

		do_action( Hooks::PRE_MULTIPLE_CART_ITEMS, $cart, $request );

		foreach ( $params as $product ) {
			$quantity = isset( $product['quantity'] ) ? $product['quantity'] : 1;
			$cart = $cart_class::add_product_by_id( $cart, $product['product_id'], $quantity );
		}

		if ( $token_id ) {
			$user = UserController::get_user_by_token( $token_id );

			if ( $user ) {
				update_user_meta( $user->ID, Cart::CART_USER_META, $cart );
			}
		}

		do_action( Hooks::AFTER_MULTIPLE_CART_ITEMS, $cart, $request );

		return $cart;
	}

	/**
	 * Check if the JSON parameters are correctly formatted.
	 *
	 * @param array $params The parameters.
	 * @return bool|\WP_Error
	 */
	public static function validate_params( $params ) {
		foreach ( $params as $param ) {
			if ( ! isset( $param['product_id'] ) ) {
				return new \WP_Error(
					ErrorCodes::BAD_REQUEST,
					'Invalid data, all objects in array must have at least a product_id.',
					[ 'status' => HttpCodes::HTTP_BAD_REQUEST ]
				);
			}
		}

		return true;
	}
}
