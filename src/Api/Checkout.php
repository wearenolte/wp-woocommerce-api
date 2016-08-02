<?php namespace Lean\Woocommerce\Api;

use Lean\AbstractEndpoint;
use Epoch2\HttpCodes;
use Lean\Woocommerce\Utils\ErrorCodes;
use Lean\Woocommerce\Utils\Hooks;
use Lean\Woocommerce\Controllers\UserController;

/**
 * Class Checkout.
 *
 * @package Lean\Woocommerce\Api
 */
class Checkout extends AbstractEndpoint {

	const ORDER_ID_PARAM = 'order_id';

	/**
	 * Endpoint path.
	 *
	 * @Override
	 * @var string
	 */
	protected $endpoint = '/ecommerce/checkout';

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
			return self::checkout( $request );
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
			self::ORDER_ID_PARAM => [
				'default' => false,
				'required' => true,
				'validate_callback' => function( $order_id ) {
					return intval( $order_id ) >= 0;
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
	 * Performs a checkout with the `First` active gateway available. Please, be sure
	 * you have the desired gateway active and/or
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return array|\WP_Error
	 */
	public static function checkout( $request ) {
		if ( ! $request->get_param( self::ORDER_ID_PARAM ) ) {
			return new \WP_Error(
				ErrorCodes::BAD_REQUEST,
				'Invalid data, you need to provide an order_id.',
				[ 'status' => HttpCodes::HTTP_BAD_REQUEST ]
			);
		}
		$token_id = $request->get_param( 'token_id' ) ? $request->get_param( 'token_id' ) : false;
		$order_id = $request->get_param( self::ORDER_ID_PARAM );

		$gateways = \WC()->payment_gateways()->payment_gateways();
		$active = null;

		foreach ( $gateways as $gateway ) {
			if ( 'yes' === $gateway->settings['enabled'] ) {
				$active = $gateway;
			}
		}

		if ( ! isset( $active ) ) {
			return new \WP_Error(
				ErrorCodes::BAD_CONFIGURED,
				'There is no active Gateway set in the Server Settings.',
				[ 'status' => HttpCodes::HTTP_INTERNAL_SERVER_ERROR ]
			);
		}

		// If the user is logged in, we can check if the $order_id is one of their own orders.
		if ( is_user_logged_in() || $token_id ) {
			if ( ! self::is_user_order( $order_id, $request ) ) {
				return new \WP_Error(
					ErrorCodes::BAD_CONFIGURED,
					'This order does not belongs to the logged user.',
					[ 'status' => HttpCodes::HTTP_FORBIDDEN ]
				);
			}
		}
		// If the order is ok, or if the user is a guest, we can proceed with the checkout.
		do_action( Hooks::PRE_CHECKOUT, $order_id );

		// Calculate totals of the order before doing the checkout.
		$order = new \WC_Order( $order_id );
		$order->calculate_totals();

		// Make the payment.
		$payment = $active->process_payment( $order_id );

		do_action( Hooks::AFTER_CHECKOUT, $order_id );

		if ( isset( $payment ) ) {
			return $payment;
		} else {
			return new \WP_Error(
				ErrorCodes::SERVER_ERROR,
				'The payment could not be completed due to an error with the Payment Gateway.',
				[ 'status' => HttpCodes::HTTP_INTERNAL_SERVER_ERROR ]
			);
		}
	}

	/**
	 * Check if the order belongs to the logged user.
	 *
	 * @param int 			   $order_id The order id.
	 * @param \WP_REST_Request $request The request.
	 * @return bool
	 */
	public static function is_user_order( $order_id, $request ) {
		$orders = Order::get_user_orders( $request );

		foreach ( $orders as $order ) {
			if ( (int) $order_id === (int) $order->ID ) {
				return true;
			}
		}

		return false;
	}
}
