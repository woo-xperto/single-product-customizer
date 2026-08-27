<?php
/**
 * Review order table - Custom version for quick checkout
 * Adds editable quantity input in order review table
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

if (function_exists('sppcfw_is_valid_single_product_referer') && sppcfw_is_valid_single_product_referer()) {
	include 'quick-review-order.php';
} else {
	include 'default-review-order.php';
}