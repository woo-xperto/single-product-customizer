<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Basic options
include __DIR__ . '/basic/enable-plus-minus-button/enable-plus-minus-button.php'; // ok
include __DIR__ . '/basic/out-of-stock/out-of-stock.php'; // ok
include __DIR__ . '/basic/change-sales-badge-text/change-sales-badge-text.php'; // ok
include __DIR__ . '/basic/change-add-to-cart-button-text/change-add-to-cart-button-text.php'; // ok
include __DIR__ . '/basic/remove-product-meta/remove-product-meta.php'; // ok
include __DIR__ . '/basic/remove-related-product-section/remove-related-product-section.php'; // ok
include __DIR__ . '/basic/remove-rating/remove-rating.php'; // ok
include __DIR__ . '/basic/hide-price/hide-price.php'; // ok
include __DIR__ . '/basic/hide-add-to-cart-button/hide-add-to-cart-button.php'; // ok
include __DIR__ . '/basic/hide-short-description/hide-short-description.php'; // ok

// Advanced options
include __DIR__ . '/advanced/related-products-title/related-products-title.php'; // OK
include __DIR__ . '/advanced/upsell-product-title/upsell-product-title.php'; // ok
include __DIR__ . '/advanced/backorder-text/backorder-text.php'; // ok
include __DIR__ . '/advanced/variation-reset-text/variation-reset-text.php'; // ok
include __DIR__ . '/advanced/enable-custom-message/enable-custom-message.php'; // ok
include __DIR__ . '/advanced/ajax-add-to-cart/ajax-add-to-cart.php'; // ok
include __DIR__ . '/advanced/change-tab-default-label/change-tab-default-label.php'; // ok
include __DIR__ . '/advanced/variation-switcher/variation-switcher.php'; // ok
include __DIR__ . '/advanced/show-variation-table/show-variation-table.php'; // ok

// Quick Checkout
include __DIR__ . '/Enable-Quick-Checkout/quick-checkout-frontend.php'; // ok