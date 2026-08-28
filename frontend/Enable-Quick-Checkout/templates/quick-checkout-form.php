<?php
/**
 * Quick Checkout Form Template
 * Uses WooCommerce's default checkout form
 *
 * @package Single_Product_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

// Setup quick checkout cart (adds product if not in cart)
do_action( 'sppcfw_before_quick_checkout_form', $product );
?>

<div class="sppcfw-quick-checkout">
	<h3 class="sppcfw-checkout-title"><?php esc_html_e( 'Quick Checkout', 'single-product-customizer' ); ?></h3>
	
	<input type="hidden" id="sppcfw-product-id" value="<?php echo esc_attr( $product->get_id() ); ?>">
	
	<div class="sppcfw-checkout-wrapper">
		<?php
			// Always show checkout form
			$sppcfw_checkout = WC()->checkout();
			// Load checkout form template (it already includes before/after hooks)
			wc_get_template( 'checkout/form-checkout.php', array( 'checkout' => $sppcfw_checkout ) );
		?>
	</div>
	
	<div class="sppcfw-messages"></div>
</div>