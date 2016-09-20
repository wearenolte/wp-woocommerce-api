<?php namespace Lean\Woocommerce\Utils;

/**
 * Interface Hooks. Custom Filters and Actions for the plugin.
 *
 * @package Lean\Woocommerce\Utils
 */
interface Hooks {
	const PRE_ORDER = 'ln_wc_pre_order';
	const AFTER_ORDER = 'ln_wc_after_order';
	const GUEST_PRE_UPDATE_ORDER = 'ln_wc_pre_update_guest_order';
	const GUEST_AFTER_UPDATE_ORDER = 'ln_wc_after_update_guest_order';
	const PRE_CHECKOUT = 'ln_wc_pre_checkout';
	const AFTER_CHECKOUT = 'ln_wc_after_checkout';
	const PRE_MULTIPLE_CART_ITEMS = 'ln_wc_pre_multiple_cart';
	const AFTER_MULTIPLE_CART_ITEMS = 'ln_wc_pre_after_cart';
	const FORMAT_ORDER_FILTER = 'ln_wc_format_order';
	const ORDER_STATUSES = 'ln_wc_order_statuses';
}
