<?php namespace Lean\Woocommerce\Utils;

/**
 * Interface ErrorCodes
 *
 * @package Lean\Woocommerce\Utils
 */
interface ErrorCodes {
	const METHOD_ERROR = 'method_not_registered';
	const BAD_REQUEST = 'request_error';
	const BAD_CONFIGURED = 'bad_configured';
	const BAD_PERMISSIONS = 'bad_permissions';
	const SERVER_ERROR = 'internal_error';
}
