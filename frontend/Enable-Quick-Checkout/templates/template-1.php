<?php

/**
 * Template Name: Modern Horizontal Layout with Accordion
 * Description: WooCommerce checkout page with modern horizontal layout and accordion billing section
 */

if (! defined('ABSPATH')) {
    exit;
}

?>
<?php if (class_exists('WC_Checkout')) :
    $checkout = WC_Checkout::instance();
?>
    <?php if ($checkout->get_checkout_fields()) : ?>

        <?php do_action('woocommerce_checkout_before_customer_details'); ?>

        <form name="checkout" method="post" class="checkout woocommerce-checkout sppcfw-checkout-form" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">

            <div class="sppcfw-accordion" id="sppcfw-checkout-accordion">
                <!-- Cart Section -->
                <div class="sppcfw-accordion-item sppcfw-accordion-item-active" id="sppcfw-cart-section">
                    <div class="sppcfw-accordion-header" data-section="sppcfw-cart-section">
                        <div class="sppcfw-accordion-header-left">
                            <span class="sppcfw-accordion-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                            <span><?php esc_html_e(
                                        apply_filters('sppcfw_quick_checkout_cart_label', __('Cart', 'woocommerce'))
                                    );  ?>
                            </span>
                        </div>
                        <span class="sppcfw-accordion-toggle">▼</span>
                    </div>
                    <div class="sppcfw-accordion-content" id="sppcfw-order-review">
                        <?php do_action('sppcfw_before_quick_checkout_initial_cart'); ?>

                        <?php include plugin_dir_path(__FILE__) . 'checkout/quick-checkout-review-cart.php'; ?>

                        <?php do_action('sppcfw_after_quick_checkout_initial_cart'); ?>

                    </div>
                </div>

                <!-- Billing Section -->
                <div class="sppcfw-accordion-item" id="sppcfw-billing-section">
                    <div class="sppcfw-accordion-header" data-section="sppcfw-billing-section">
                        <div class="sppcfw-accordion-header-left">
                            <span class="sppcfw-accordion-icon"><i class="fa-solid fa-file-invoice"></i></span>
                            <span><?php esc_html_e(
                                        apply_filters('sppcfw_quick_checkout_billing_details_label', __('Billing Details', 'woocommerce'))
                                    );  ?>
                            </span>
                        </div>
                        <span class="sppcfw-accordion-toggle">▼</span>
                    </div>
                    <div class="sppcfw-accordion-content">
                        <?php do_action('sppcfw_before_quick_checkout_initial_billing'); ?>

                        <?php include plugin_dir_path(__FILE__) . 'checkout/quick-checkout-form-billing.php'; ?>

                        <?php do_action('sppcfw_after_quick_checkout_initial_billing'); ?>
                    </div>
                </div>

                <!-- Shipping Section -->
                <?php if (WC()->cart && true === WC()->cart->needs_shipping_address()) : ?>
                    <div class="sppcfw-accordion-item" id="sppcfw-shipping-section">
                        <div class="sppcfw-accordion-header" data-section="sppcfw-shipping-section">
                            <div class="sppcfw-accordion-header-left">
                                <span class="sppcfw-accordion-icon"><i class="fa-solid fa-truck-fast"></i></span>
                                <?php if (WC()->cart->needs_shipping()) : ?>
                                    <span><?php echo esc_html(
                                                apply_filters('sppcfw_quick_checkout_shipping_details_label', __('Shipping Details', 'woocommerce'))
                                            );  ?>
                                    </span>
                                <?php else : ?>
                                    <span><?php esc_html_e(
                                                apply_filters('sppcfw_quick_checkout_shipping_details_label', __('Shipping Details', 'woocommerce'))
                                            );  ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="sppcfw-accordion-toggle">▼</span>
                        </div>
                        <div class="sppcfw-accordion-content">
                            <?php do_action('sppcfw_before_quick_checkout_initial_shipping'); ?>

                            <?php include plugin_dir_path(__FILE__) . 'checkout/quick-checkout-form-shipping.php'; ?>

                            <?php do_action('sppcfw_after_quick_checkout_initial_shipping'); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Order Review & Payment Section -->
                <div class="sppcfw-accordion-item" id="sppcfw-order-section">
                    <div class="sppcfw-accordion-header" data-section="sppcfw-order-section">
                        <div class="sppcfw-accordion-header-left">
                            <span class="sppcfw-accordion-icon"><i class="fa-solid fa-credit-card"></i></span>
                            <span><?php esc_html_e(
                                        apply_filters('sppcfw_quick_checkout_order_payment_label', __('Order & Payment', 'woocommerce'))
                                    );  ?>
                            </span>
                        </div>
                        <span class="sppcfw-accordion-toggle">▼</span>
                    </div>
                    <div class="sppcfw-accordion-content">
                        <?php do_action('sppcfw_before_quick_checkout_initial_payment'); ?>

                        <?php woocommerce_checkout_payment(); ?>

                        <?php do_action('sppcfw_after_quick_checkout_initial_payment'); ?>
                    </div>
                </div>
            </div><!-- .sppcfw-accordion -->

            <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>

            <?php do_action('woocommerce_after_checkout_form', $checkout); ?>

        </form>

    <?php endif; ?>
<?php endif; ?>

<script>
    // Accordion functionality
    document.addEventListener('DOMContentLoaded', function() {
        const accordionHeaders = document.querySelectorAll('.sppcfw-accordion-header');

        accordionHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const sectionId = this.getAttribute('data-section');
                const accordionItem = this.closest('.sppcfw-accordion-item');

                // Close all accordion items
                document.querySelectorAll('.sppcfw-accordion-item').forEach(item => {
                    if (item !== accordionItem) {
                        item.classList.remove('sppcfw-accordion-item-active');
                    }
                });

                // Toggle current accordion item
                accordionItem.classList.toggle('sppcfw-accordion-item-active');
            });
        });
    });
</script>