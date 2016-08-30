<?php namespace Lean\Woocommerce\Api;

use Lean\AbstractEndpoint;
use Epoch2\HttpCodes;
use Lean\Woocommerce\Utils\ErrorCodes;
use Lean\Woocommerce\Utils\Hooks;
use Lean\Woocommerce\Controllers\UserController;

/**
 * Class Cart.
 *
 * @package Leean\Woocomerce\Modules\Cart
 */
class Cart extends AbstractEndpoint {

	const CART_USER_META = 'ln_wc_meta_cart';

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
			$token_id = $request->get_param( 'token_id' ) ? $request->get_param( 'token_id' ) : false;
			return self::get_cart( $token_id );
		} else if ( in_array( $method, str_getcsv( \WP_REST_Server::EDITABLE ), true ) ) {
			return self::add_to_cart( $request );
		} else if ( \WP_REST_Server::DELETABLE === $method ) {
			return self::delete_from_cart( $request );
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
				[ \WP_REST_Server::READABLE, \WP_REST_Server::DELETABLE ],
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
			'item_key' => [
				'default' => false,
				'required' => false,
				'validate_callback' => function( $item_key ) {
					return false === $item_key || is_string( $item_key );
				},
			],
		];
	}

	/**
	 * Delete an item from the cart and return the cart or empty array.
	 *
	 * @param \WP_REST_Request $request The request.
	 * @return array
	 */
	public static function delete_from_cart( \WP_REST_Request $request ) {
		$product_key = $request->get_param( 'item_key' ) ? $request->get_param( 'item_key' ) : false;

		if ( ! $product_key ) {
			return new \WP_Error(
				ErrorCodes::BAD_REQUEST,
				'Invalid data, product_id is required.',
				[ 'status' => HttpCodes::HTTP_BAD_REQUEST ]
			);
		}

		$token_id = $request->get_param( 'token_id' ) ? $request->get_param( 'token_id' ) : false;

		$cart = self::get_cart( $token_id );

		$cart->remove_cart_item( $product_key );

		if ( $token_id ) {
			$user = UserController::get_user_by_token( $token_id );

			if ( $user ) {
				update_user_meta( $user->ID, self::CART_USER_META, $cart );
			}
		}

		return $cart;
	}

	/**
	 * Add an item to the cart, and return the new cart.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return array
	 */
	public static function add_to_cart( \WP_REST_Request $request ) {

		$product_id = $request->get_param( 'product_id' );
		$token_id = $request->get_param( 'token_id' ) ? $request->get_param( 'token_id' ) : false;

		if ( ! $product_id ) {
			return new \WP_Error(
				ErrorCodes::BAD_REQUEST,
				'Invalid data, product_id is required.',
				[ 'status' => HttpCodes::HTTP_BAD_REQUEST ]
			);
		}

		$cart = self::get_cart( $token_id );

		$cart = self::add_product_by_id( $cart, $product_id );

		if ( $token_id ) {
			$user = UserController::get_user_by_token( $token_id );

			if ( $user ) {
				update_user_meta( $user->ID, self::CART_USER_META, $cart );
			}
		}

		return $cart;
	}

	/**
	 * Add a product by ID.
	 *
	 * @param \WC_Cart $cart  The cart.
	 * @param int      $product_id Product ID.
	 * @param int      $quantity   Quantity of products.
	 * @param array	   $cart_item_data Custom data for the item.
	 * @param bool	   $key If True, return array instead of cart.
	 * @return \Wc_Cart
	 */
	public static function add_product_by_id( $cart, $product_id, $quantity = 1, $cart_item_data = [], $key = false ) {
		// If this is a variation of a product instead of a simple one, we need to prepare the data.
		if ( 'product_variation' === get_post_type( $product_id ) ) {
			$variation = wc_get_product( $product_id );
			$variation_id = $product_id;
			$product_id   = wp_get_post_parent_id( $variation_id );
			$item_key = $cart->add_to_cart( $product_id, intval( $quantity ), intval( $variation_id ) , (array) $variation->variation_data );
		} else {
			$item_key = $cart->add_to_cart( $product_id, intval( $quantity ), 0, [], $cart_item_data );
		}

		if ( $key ) {
			return $item_key;
		}

		return $cart;
	}

	/**
	 * Get the cart for the current session user.
	 *
	 * @param bool|string $token_id Token id.
	 * @return \WC_Cart
	 */
	public static function get_cart( $token_id = false ) {
		// If we have a token_id that means the request is made from a mobile app.
		if ( $token_id ) {

			$user = UserController::get_user_by_token( $token_id );

			if ( isset( $user->ID ) ) {
				$cart = get_user_meta( $user->ID, self::CART_USER_META, true );

				if ( $cart ) {
					return $cart;
				} else {
					$cart = new \WC_Cart();
					update_user_meta( $user->ID, self::CART_USER_META, $cart );
					return $cart;
				}
			}
		}

		// Else, we use the session cart.
		\WC()->cart->get_cart_from_session();

		return \WC()->cart;
	}
}
