<?php namespace Lean\Woocommerce\Controllers;

/**
 * Class UserController.
 *
 * @package Lean\Woocommerce\Controllers
 */
class UserController {

	const USER_TOKEN = 'user_token';

	/**
	 * Check if there is any user with the given token
	 * to perform more actions.
	 *
	 * @param string $token_id	The token.
	 * @return \WP_User|0
	 */
	public static function get_user_by_token( $token_id ) {
		$user = get_users(
			array(
				'meta_key' => self::USER_TOKEN,
				'meta_value' => $token_id,
				'number' => 1,
				'count_total' => false,
			)
		);

		return ! empty( $user ) ? $user[0] : 0;
	}
}
