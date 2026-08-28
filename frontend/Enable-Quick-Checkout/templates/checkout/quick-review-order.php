<?php

/**
 * Review order table - Custom version for quick checkout
 * Adds editable quantity input in order review table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined('ABSPATH') || exit;
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


        if (WC()->cart->is_empty()) {
        ?>
            <tr class="cart-empty">
                <td colspan="2" class="empty-cart-message">
                    <?php
                    do_action('sppcfw_before_quick_checkout_initial_empty_cart');
                    global $product;

                    if (! $product) {
                        $referer = $_SERVER['HTTP_REFERER'] ?? '';
                        $referer = esc_url_raw($referer);

                        // Get post ID from URL
                        $post_id = url_to_postid($referer);
                        if (! $post_id) {
                            return false;
                        }
                        $product = wc_get_product($post_id);
                    }

                    if ($product && is_a($product, 'WC_Product')) {

                        if ($product->is_type('variable') || $product->is_type('grouped')) {
                            include __DIR__ . '/quick-variable-add-to-cart.php';
                        } else {
                            $empty_cart_message = apply_filters(
                                'sppcfw_quick_checkout_empty_cart_message',
                                __('Your cart is empty. Please add a product to continue.', 'single-product-customizer')
                            );

                            echo esc_html($empty_cart_message);
                        }
                    }
                    do_action('sppcfw_after_quick_checkout_initial_empty_cart');
                    ?>
                </td>
            </tr>
            <?php
        } else {
            $sppcfw_has_rendered_quick_add_to_cart_form = false;
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

                if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
                    // This custom template is only loaded when quick checkout is active
                    // So we can always show the quantity input (unless product is sold individually)
                    $can_edit_quantity = ! $_product->is_sold_individually();
            ?>
                    <?php if (! $sppcfw_has_rendered_quick_add_to_cart_form) : ?>
                        <tr>
                            <td colspan="2">
                                <?php
                                do_action('sppcfw_before_quick_checkout_cart');
                                // Render quick add-to-cart controls only once, even with multiple cart items.
                                include __DIR__ . '/quick-variable-add-to-cart.php';

                                $sppcfw_has_rendered_quick_add_to_cart_form = true;

                                do_action('sppcfw_after_quick_checkout_cart');
                                ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                        <td class="product-name">
                            <!-- Remove item button - at the beginning -->
                            <a href="#" class="sppcfw-remove-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" title="<?php esc_attr_e('Remove this item', 'single-product-customizer'); ?>">
                                ×
                            </a>

                            <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)) . '&nbsp;'; ?>

                            <?php if ($can_edit_quantity) : ?>
                                <?php
                                // Editable quantity input for quick checkout
                                $min_value = apply_filters('woocommerce_quantity_input_min', $_product->get_min_purchase_quantity(), $_product);
                                $max_value = apply_filters('woocommerce_quantity_input_max', $_product->get_max_purchase_quantity(), $_product);
                                $step = apply_filters('woocommerce_quantity_input_step', 1, $_product);
                                ?>
                                <span class="product-quantity">
                                    &times;&nbsp;
                                    <input
                                        type="number"
                                        class="sppcfw-checkout-quantity"
                                        data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                        data-product-id="<?php echo esc_attr($_product->get_id()); ?>"
                                        min="<?php echo esc_attr($min_value); ?>"
                                        <?php if ($max_value > 0) : ?>
                                        max="<?php echo esc_attr($max_value); ?>"
                                        <?php endif; ?>
                                        step="<?php echo esc_attr($step); ?>"
                                        value="<?php echo esc_attr($cart_item['quantity']); ?>"
                                        size="4"
                                        style="width: 60px; display: inline-block; text-align: center;" />
                                </span>
                            <?php else : ?>
                                <?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                                ?>
                            <?php endif; ?>

                            <?php echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                            ?>
                        </td>
                        <td class="product-total">
                            <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                            ?>
                        </td>
                    </tr>
        <?php
                }
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

        <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
            <tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                <th><?php wc_cart_totals_coupon_label($coupon); ?></th>
                <td><?php wc_cart_totals_coupon_html($coupon); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>

            <?php do_action('woocommerce_review_order_before_shipping'); ?>

            <?php wc_cart_totals_shipping_html(); ?>

            <?php do_action('woocommerce_review_order_after_shipping'); ?>

        <?php endif; ?>

        <?php foreach (WC()->cart->get_fees() as $fee) : ?>
            <tr class="fee">
                <th><?php echo esc_html($fee->name); ?></th>
                <td><?php wc_cart_totals_fee_html($fee); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if (wc_tax_enabled() && ! WC()->cart->display_prices_including_tax()) : ?>
            <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
                <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited 
                ?>
                    <tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                        <th><?php echo esc_html($tax->label); ?></th>
                        <td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
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