<?php
/**
 * Checkout billing form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
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

defined( 'ABSPATH' ) || exit;

$fields = WC()->checkout()->get_checkout_fields( 'billing' );

?>
<div class="woocommerce-billing-fields">
	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

	<div class="woocommerce-billing-fields__field-wrapper">
		<?php
		foreach ( $fields as $key => $field ) {
			woocommerce_form_field( $key, $field, WC()->checkout()->get_value( $key ) );
		}
		?>
	</div>

	<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</div>

<?php
if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) {
	do_action( 'woocommerce_before_checkout_registration_form', $checkout );

	if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) {
		?>

		<div class="woocommerce-account-fields">
			<?php if ( ! get_option( 'woocommerce_registration_generate_password' ) ) { ?>
				<p class="form-row form-row-wide create-account">
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
						<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'single-product-customizer' ); ?></span>
					</label>
				</p>

				<?php
				$account_fields = $checkout->get_checkout_fields( 'account' );

				foreach ( $account_fields as $key => $field ) {
					$field['class'][] = 'form-row-wide';
					$field['required'] = false;
					woocommerce_form_field( $key, $field, WC()->checkout()->get_value( $key ) );
				}
				?>
			<?php } else { ?>
				<p><?php esc_html_e( 'Create an account by entering the information below. If you are a returning customer please login at the top of the page.', 'single-product-customizer' ); ?></p>
				<?php
				$account_fields = $checkout->get_checkout_fields( 'account' );

				foreach ( $account_fields as $key => $field ) {
					$field['class'][] = 'form-row-wide';
					woocommerce_form_field( $key, $field, WC()->checkout()->get_value( $key ) );
				}
				?>
			<?php } ?>
		</div>

		<?php
	}

	do_action( 'woocommerce_after_checkout_registration_form', $checkout );
}
?>
