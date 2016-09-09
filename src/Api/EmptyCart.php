<?php namespace Lean\Woocommerce\Api;

use Lean\AbstractEndpoint;
use Epoch2\HttpCodes;
use Lean\Woocommerce\Utils\ErrorCodes;
use Lean\Woocommerce\Controllers\UserController;

/**
 * Class Cart.
 *
 * @package Leean\Woocomerce\Modules\Cart
 */
class EmptyCart extends AbstractEndpoint {
	/**
	 * Endpoint path
	 *
	 * @Override
	 * @var String
	 */
	protected $endpoint = '/ecommerce/empty_cart';

	/**
	 * Endpoint callback override.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return array|\WP_Error
	 */
	public function endpoint_callback( \WP_REST_Request $request ) {
		$method = $request->get_method();

		if ( \WP_REST_Server::CREATABLE === $method ) {
			return self::empty_cart( $request );
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
	 * Set the args for this endpoint.
	 *
	 * @return array
	 */
	public function endpoint_args() {
		return [
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
	 * Delete all items from the cart.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return array
	 */
	public static function empty_cart( \WP_REST_Request $request ) {
		$token_id = $request->get_param( 'token_id' ) ? $request->get_param( 'token_id' ) : false;

		$cart = Cart::get_cart( $token_id );

		$cart->empty_cart( true );

		if ( $token_id ) {
			$user = UserController::get_user_by_token( $token_id );

			if ( $user ) {
				update_user_meta( $user->ID, Cart::CART_USER_META, $cart );
			}
		}

		return $cart;
	}
}
