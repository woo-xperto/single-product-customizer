<?php

/**
 * Checkout shipping form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-shipping.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

if ( ! isset( $sppcfw_checkout ) || ! is_a( $sppcfw_checkout, 'WC_Checkout' ) ) {
	$sppcfw_checkout = WC()->checkout();
}

$sppcfw_fields = $sppcfw_checkout->get_checkout_fields('shipping');

?>
<div class="woocommerce-shipping-fields">
	<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

		<h3 id="ship-to-different-address">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" /> <span><?php esc_html_e( 'Ship to a different address?', 'single-product-customizer' ); ?></span>
			</label>
		</h3>

		<div class="shipping_address">

			<?php do_action( 'woocommerce_before_checkout_shipping_form', $sppcfw_checkout ); ?>

			<div class="woocommerce-shipping-fields__field-wrapper">
				<?php
				$sppcfw_fields = $sppcfw_checkout->get_checkout_fields( 'shipping' );

				foreach ( $sppcfw_fields as $sppcfw_key => $sppcfw_field ) {
					woocommerce_form_field( $sppcfw_key, $sppcfw_field, $sppcfw_checkout->get_value( $sppcfw_key ) );
				}
				?>
			</div>

			<?php do_action( 'woocommerce_after_checkout_shipping_form', $sppcfw_checkout ); ?>

		</div>

	<?php endif; ?>
</div>
<div class="woocommerce-additional-fields">
	<?php do_action( 'woocommerce_before_order_notes', $sppcfw_checkout ); ?>

	<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>

		<?php if ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) : ?>

			<h3><?php esc_html_e( 'Additional information', 'single-product-customizer' ); ?></h3>

		<?php endif; ?>

		<div class="woocommerce-additional-fields__field-wrapper">
			<?php foreach ( $sppcfw_checkout->get_checkout_fields( 'order' ) as $sppcfw_key => $sppcfw_field ) : ?>
				<?php woocommerce_form_field( $sppcfw_key, $sppcfw_field, $sppcfw_checkout->get_value( $sppcfw_key ) ); ?>
			<?php endforeach; ?>
		</div>

	<?php endif; ?>

	<?php do_action( 'woocommerce_after_order_notes', $sppcfw_checkout ); ?>
</div>