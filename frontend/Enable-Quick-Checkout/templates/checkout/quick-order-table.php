<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<table class="shop_table woocommerce-checkout-review-order-table">
	<thead>
		<tr>
			<th class="product-name"><?php esc_html_e('Product', 'single-product-customizer'); ?></th>
			<th class="product-total"><?php esc_html_e('Subtotal', 'single-product-customizer'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php

		do_action('woocommerce_review_order_before_cart_contents');

		?>
		<tr>
			<td colspan="2">
				<?php
				global $product;
				if ($product && is_a($product, 'WC_Product')) {

					include __DIR__ . '/quick-variable-add-to-cart.php';
				}

				?>
			</td>
		</tr>
		<?php

		foreach (WC()->cart->get_cart() as $sppcfw_cart_item_key => $sppcfw_cart_item) {
			$sppcfw_cart_item_product = apply_filters('woocommerce_cart_item_product', $sppcfw_cart_item['data'], $sppcfw_cart_item, $sppcfw_cart_item_key);

			if ($sppcfw_cart_item_product && $sppcfw_cart_item_product->exists() && $sppcfw_cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $sppcfw_cart_item, $sppcfw_cart_item_key)) {
		?>
				<tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $sppcfw_cart_item, $sppcfw_cart_item_key)); ?>">
					<td class="product-name">
						<?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $sppcfw_cart_item_product->get_name(), $sppcfw_cart_item, $sppcfw_cart_item_key)) . '&nbsp;'; ?>
						<?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', $sppcfw_cart_item['quantity']) . '</strong>', $sppcfw_cart_item, $sppcfw_cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
						?>
						<?php echo wc_get_formatted_cart_item_data($sppcfw_cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
						?>
					</td>
					<td class="product-total">
						<?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($sppcfw_cart_item_product, $sppcfw_cart_item['quantity']), $sppcfw_cart_item, $sppcfw_cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
						?>
					</td>
				</tr>
		<?php
			}
		}

		do_action('woocommerce_review_order_after_cart_contents');
		?>
	</tbody>
	<tfoot>

		<tr class="cart-subtotal">
			<th><?php esc_html_e('Subtotal', 'single-product-customizer'); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach (WC()->cart->get_coupons() as $sppcfw_code => $sppcfw_coupon) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($sppcfw_code)); ?>">
				<th><?php wc_cart_totals_coupon_label($sppcfw_coupon); ?></th>
				<td><?php wc_cart_totals_coupon_html($sppcfw_coupon); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>

			<?php do_action('woocommerce_review_order_before_shipping'); ?>

			<?php wc_cart_totals_shipping_html(); ?>

			<?php do_action('woocommerce_review_order_after_shipping'); ?>

		<?php endif; ?>

		<?php foreach (WC()->cart->get_fees() as $sppcfw_fee) : ?>
			<tr class="fee">
				<th><?php echo esc_html($sppcfw_fee->name); ?></th>
				<td><?php wc_cart_totals_fee_html($sppcfw_fee); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if (wc_tax_enabled() && ! WC()->cart->display_prices_including_tax()) : ?>
			<?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
				<?php foreach (WC()->cart->get_tax_totals() as $sppcfw_code => $sppcfw_tax) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited 
				?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($sppcfw_code)); ?>">
						<th><?php echo esc_html($sppcfw_tax->label); ?></th>
						<td><?php echo wp_kses_post($sppcfw_tax->formatted_amount); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action('woocommerce_review_order_before_order_total'); ?>

		<tr class="order-total">
			<th><?php esc_html_e('Total', 'single-product-customizer'); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action('woocommerce_review_order_after_order_total'); ?>

	</tfoot>
</table>