<?php namespace Lean\Woocommerce\Api;

use Lean\AbstractEndpoint;

/**
 * Class Order. This class implements the order endpoints. Users are able
 * to create a new order with the current session cart or retreive their
 * orders (this is only possible for authenticated users!).
 *
 * @package Lean\Woocommerce\Api
 */
class Order extends AbstractEndpoint
{

	const ORDERS_PER_PAGE = 10;
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

		if ( \WP_REST_Server::CREATABLE === $method ) {
			// Create an order.
			return self::place_order( $request );
		} else if ( \WP_REST_Server::READABLE === $method ) {
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

		$cart = Cart::get_cart();

		if ( $cart->is_empty() ) {
			return new \WP_Error( 400, 'Your cart is empty. Order was not created.', [ 'status' => 400 ] );
		}

		do_action( 'ln_wc_pre_order', $request, $cart );

		// Load our cart.
		\WC()->cart = $cart;

		$checkout = new \WC_Checkout();
		$order_id = $checkout->create_order();
		$order = new \WC_Order( $order_id );

		// Empty the current cart.
		$cart->empty_cart();

		return $order;
	}

	/**
	 * Return all logged user orders. If the user is not logged_in returns an empty array.
	 *
	 * @return array Orders|Empty array.
	 */
	public static function get_user_orders() {
		if ( is_user_logged_in() ) {
			$customer_orders = get_posts( array(
				'posts_per_page'	=> self::ORDERS_PER_PAGE,
				'meta_key'    		=> '_customer_user',
				'meta_value'  		=> get_current_user_id(),
				'post_type'   		=> wc_get_order_types(),
				'post_status' 		=> array_keys( wc_get_order_statuses() ),
			) );

			return $customer_orders;
		}

		return [];
	}
}
