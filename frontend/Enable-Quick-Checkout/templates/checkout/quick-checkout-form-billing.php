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

if ( ! isset( $sppcfw_checkout ) || ! is_a( $sppcfw_checkout, 'WC_Checkout' ) ) {
	$sppcfw_checkout = WC()->checkout();
}

$sppcfw_fields = $sppcfw_checkout->get_checkout_fields( 'billing' );

?>
<div class="woocommerce-billing-fields">
	<?php do_action( 'woocommerce_before_checkout_billing_form', $sppcfw_checkout ); ?>

	<div class="woocommerce-billing-fields__field-wrapper">
		<?php
		foreach ( $sppcfw_fields as $sppcfw_key => $sppcfw_field ) {
			woocommerce_form_field( $sppcfw_key, $sppcfw_field, WC()->checkout()->get_value( $sppcfw_key ) );
		}
		?>
	</div>

	<?php do_action( 'woocommerce_after_checkout_billing_form', $sppcfw_checkout ); ?>
</div>

<?php
if ( ! is_user_logged_in() && $sppcfw_checkout->is_registration_enabled() ) {
	do_action( 'woocommerce_before_checkout_registration_form', $sppcfw_checkout );

	if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) {
		?>

		<div class="woocommerce-account-fields">
			<?php if ( ! get_option( 'woocommerce_registration_generate_password' ) ) { ?>
				<p class="form-row form-row-wide create-account">
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
						<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $sppcfw_checkout->get_value( 'createaccount' ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'single-product-customizer' ); ?></span>
					</label>
				</p>

				<?php
				$sppcfw_account_fields = $sppcfw_checkout->get_checkout_fields( 'account' );

				foreach ( $sppcfw_account_fields as $sppcfw_key => $sppcfw_field ) {
					$sppcfw_field['class'][]  = 'form-row-wide';
					$sppcfw_field['required'] = false;
					woocommerce_form_field( $sppcfw_key, $sppcfw_field, WC()->checkout()->get_value( $sppcfw_key ) );
				}
				?>
			<?php } else { ?>
				<p><?php esc_html_e( 'Create an account by entering the information below. If you are a returning customer please login at the top of the page.', 'single-product-customizer' ); ?></p>
				<?php
				$sppcfw_account_fields = $sppcfw_checkout->get_checkout_fields( 'account' );

				foreach ( $sppcfw_account_fields as $sppcfw_key => $sppcfw_field ) {
					$sppcfw_field['class'][] = 'form-row-wide';
					woocommerce_form_field( $sppcfw_key, $sppcfw_field, WC()->checkout()->get_value( $sppcfw_key ) );
				}
				?>
			<?php } ?>
		</div>

		<?php
	}

	do_action( 'woocommerce_after_checkout_registration_form', $sppcfw_checkout );
}
?>
