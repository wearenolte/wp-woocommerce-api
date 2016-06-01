<?php namespace Lean\Woocommerce\Api;

use Lean\AbstractEndpoint;
use Epoch2\HttpCodes;
use Lean\Woocommerce\Utils\ErrorCodes;
use Lean\Woocommerce\Utils\Hooks;
use Lean\Woocommerce\Api\Order;

/**
 * Class Checkout. 
 * 
 * @package Lean\Woocommerce\Api
 */
class Checkout extends AbstractEndpoint
{

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
		];
	}

	/**
	 * Performs a checkout with the `First` active gateway available. Please, be sure
	 * you have the desired gateway active and/or
	 *
	 * @param $request
	 * @return array|\WP_Error
	 */
	public static function checkout( $request ) {
		if ( ! $request->get_param( self::ORDER_ID_PARAM ) ) {
			return new \WP_Error(
				ErrorCodes::BAD_REQUEST,
				'Invalid data, you need to provide an order_id and a payment gateway.',
				[ 'status' => HttpCodes::HTTP_BAD_REQUEST ]
			);
		}

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
		if ( is_user_logged_in() ) {
			if ( ! self::is_user_order( $order_id ) ) {
				return new \WP_Error(
					ErrorCodes::BAD_CONFIGURED,
					'This order does not belongs to the logged user.',
					[ 'status' => HttpCodes::HTTP_FORBIDDEN ]
				);
			}
		}

		// If the order is ok, or if the user is a guest, we can proceed with the checkout.

		do_action( Hooks::PRE_CHECKOUT, $order_id );

		$payment = $active->process_payment( $order_id );
		
		do_action( Hooks::AFTER_CHECKOUT, $order_id );

		return $payment;
	}

	public static function is_user_order( $order_id ) {
		$orders = Order::get_user_orders();

		foreach ( $orders as $order ) {
			if ( $order_id === $order->id ) {
				return true;
			}
		}

		return false;
	}
}