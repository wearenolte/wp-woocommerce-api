<?php namespace Lean\Woocommerce\Utils;

/**
 * Class Filters. Custom filters for the plugin.
 *
 * @package Lean\Woocommerce\Utils
 */
class Hooks
{
	const PRE_ORDER = 'ln_wc_pre_order';
	const AFTER_ORDER = 'ln_wc_after_order';
	const GUEST_PRE_UPDATE_ORDER = 'ln_wc_pre_update_guest_order';
	const GUEST_AFTER_UPDATE_ORDER = 'ln_wc_after_update_guest_order';
}
