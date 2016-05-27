<?php namespace Lean\Woocommerce\Api;

use Lean\AbstractEndpoint;

/**
 * Class Order.
 *
 * @package Lean\Woocommerce\Api
 */
class Order extends AbstractEndpoint
{
	/**
	 * Endpoint path
	 *
	 * @Override
	 * @var String
	 */
	protected $endpoint = '/ecommerce/order';

	/**
	 * Endpoint callback override.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return array|\WP_Error
	 */
	public function endpoint_callback( \WP_REST_Request $request ) {
		$method = $request->get_method();

		if ( $method ===  \WP_REST_Server::CREATABLE  ) {
			// Create an order.
			return self::place_order($request);
		} else if ( $method === \WP_REST_Server::READABLE ) {
			// Get all Logged User orders. Returns nothing if the user is not logged in.
			return self::get_user_orders();
		} else {
			return new \WP_Error( 405, 'Method not allowed', [ 'status' => 405 ] );
		}
	}

	/**
	 * Set the options of the endpoint. Allow http methods.
	 *
	 * @return array
	 */
	protected function endpoint_options() {
		return [
			'methods' => [ \WP_REST_Server::CREATABLE , \WP_REST_Server::READABLE ],
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
				'validate_callback' => function( $order_id ) {
					return false === $order_id || intval( $order_id ) >= 0;
				},
			],
		];
	}


	/**
	 * Create the order based on the current cart, also it returns the order.
	 * Client cart can't be empty to create the order.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return array Order.
	 */
	public static function place_order( \WP_REST_Request $request ) {
		// TODO: Create a hook with POST information in $request.

		$cart = Cart::get_cart();

		if ( $cart->is_empty() ) {
			return new \WP_Error( 400, 'Your cart is empty. Order was not created.', [ 'status' => 400 ] );
		}

		$checkout = new \WC_Checkout();
		$order_id = $checkout->create_order();
		$order = new \WC_Order( $order_id );

		// Empty the current cart.
		$cart->empty_cart();

		return $order;
	}

	/**
	 * Return all logged user orders.
	 */
	public static function get_user_orders() {
		// TODO.
		return [];
	}

}
