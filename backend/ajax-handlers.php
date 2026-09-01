<?php

/**
 * Quick Checkout AJAX Handlers
 *
 * @package Single Product Customizer
 */
if (!defined('ABSPATH')) {
    exit;
}

class Sppcfw_Quick_Checkout
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // After sppcfw_init (init priority 10) loads this file; must run later on the same init pass.
        add_action('init', array(__CLASS__, 'maybe_disable_quick_checkout_for_block_theme'), 11);

        // Settings save handlers
        add_action('wp_ajax_sppcfw_save_settings', array($this, 'handle_save_settings'));
        add_action('wp_ajax_sppcfw_save_quick_checkout', array($this, 'handle_save_quick_checkout'));

        // Cart actions
        add_action('wp_ajax_sppcfw_update_cart_quantity', array($this, 'update_cart_quantity'));
        add_action('wp_ajax_nopriv_sppcfw_update_cart_quantity', array($this, 'update_cart_quantity'));

        add_action('wp_ajax_sppcfw_remove_cart_item', array($this, 'remove_cart_item'));
        add_action('wp_ajax_nopriv_sppcfw_remove_cart_item', array($this, 'remove_cart_item'));
    }

    /**
     * Persist Quick Checkout on/off and mirror related WooCommerce / advanced options.
     *
     * @param bool $enabled Whether Quick Checkout is enabled.
     */
    public static function sync_quick_checkout_enabled_store($enabled)
    {
        $enabled = (bool) $enabled;

        update_option('sppcfw_enable_quick_checkout', $enabled ? 1 : 0);
        update_option('woocommerce_enable_ajax_add_to_cart', $enabled ? 'yes' : 'no');

        $sppcfw_advanced = get_option('sppcfw_advanced', array());
        if (!is_array($sppcfw_advanced)) {
            $sppcfw_advanced = array();
        }

        if ($enabled) {
            $sppcfw_advanced['enable_ajax_add_to_cart'] = 'on';
        } else {
            unset($sppcfw_advanced['enable_ajax_add_to_cart']);
        }

        update_option('sppcfw_advanced', $sppcfw_advanced);
    }

    /**
     * When a block theme is active, force Quick Checkout off in the database (same as unchecked).
     */
    public static function maybe_disable_quick_checkout_for_block_theme()
    {
        $theme = wp_get_theme();
        if (!$theme->exists() || !method_exists($theme, 'is_block_theme') || !$theme->is_block_theme()) {
            return;
        }

        if (!(int) get_option('sppcfw_enable_quick_checkout', 0)) {
            return;
        }

        self::sync_quick_checkout_enabled_store(false);
    }

    /**
     * Save settings (Basic and Advanced)
     */
    public function handle_save_settings()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'sppcfw_settings_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'single-product-customizer')));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission', 'single-product-customizer')));
        }

        $section_id = isset($_POST['section']) ? sanitize_text_field(wp_unslash($_POST['section'])) : '';
        if (empty($section_id)) {
            wp_send_json_error(array('message' => __('Invalid section', 'single-product-customizer')));
        }

        $form_data = isset($_POST['form_data']) ? sanitize_text_field(wp_unslash($_POST['form_data'])) : '';
        if (empty($form_data) || !is_string($form_data)) {
            wp_send_json_error(array('message' => __('No data provided', 'single-product-customizer')));
        }

        parse_str($form_data, $parsed_data);
        $settings_data = isset($parsed_data[$section_id]) && is_array($parsed_data[$section_id]) ? $parsed_data[$section_id] : array();

        $sanitized_settings = array();
        foreach ($settings_data as $key => $value) {
            $sanitized_settings[sanitize_key($key)] = ($value === 'on') ? 'on' : sanitize_text_field($value);
        }

        update_option(sanitize_key($section_id), $sanitized_settings);

        wp_send_json_success(array(
            'message' => __('Settings saved successfully!', 'single-product-customizer'),
        ));
    }

    /**
     * Save Quick Checkout settings
     */
    public function handle_save_quick_checkout()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'sppcfw_qc_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'single-product-customizer')));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission', 'single-product-customizer')));
        }

        $sppcfw_enable_quick_checkout = isset($_POST['sppcfw_enable_quick_checkout']) ? 1 : 0;

        $is_builder_active = (int) get_option('sppcfw_enable_single_product_builder', 0);
        if ($is_builder_active && $sppcfw_enable_quick_checkout) {
            wp_send_json_error(array(
                'message' => __('Cannot enable Quick Checkout while Single Product Builder is active. Please checkout in single page builder Options', 'single-product-customizer')
            ));
        }

        self::sync_quick_checkout_enabled_store((bool) $sppcfw_enable_quick_checkout);

        $is_pro_template_attempt = false;
        if ($sppcfw_enable_quick_checkout && isset($_POST['sppcfw_enable_qc'])) {
            $selected_template = sanitize_text_field(wp_unslash($_POST['sppcfw_enable_qc']));
            if (!sppcfw_is_pro_active() && $selected_template !== 'template-1') {
                $is_pro_template_attempt = true;
                $selected_template = 'template-1';
            }
            update_option('sppcfw_enable_qc', $selected_template);
        }

        if (sppcfw_is_pro_active()) {
            $sppcfw_show_product_title     = isset($_POST['sppcfw_show_product_title']) ? 1 : 0;
            $sppcfw_show_review            = isset($_POST['sppcfw_show_review']) ? 1 : 0;
            $sppcfw_show_short_description = isset($_POST['sppcfw_show_short_description']) ? 1 : 0;

            update_option('sppcfw_show_product_title', $sppcfw_show_product_title);
            update_option('sppcfw_show_review', $sppcfw_show_review);
            update_option('sppcfw_show_short_description', $sppcfw_show_short_description);
        }

        // Keep conflicting basic settings disabled when the quick checkout display options are enabled.
        $sppcfw_basic = get_option('sppcfw_basic', array());
        if (!is_array($sppcfw_basic)) {
            $sppcfw_basic = array();
        }

        update_option('sppcfw_basic', $sppcfw_basic);

        wp_send_json_success(array(
            'message' => $is_pro_template_attempt ? __('This is a Pro Template', 'single-product-customizer') : __('Quick Checkout settings saved successfully!', 'single-product-customizer'),
            'is_pro_template' => $is_pro_template_attempt,
        ));
    }

    /**
     * Update cart quantity via AJAX
     */
    public function update_cart_quantity()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'sppcfw-quick-checkout')) {
            wp_send_json_error(array('message' => __('Security check failed', 'single-product-customizer')));
        }

        $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';
        $quantity = isset($_POST['quantity']) ? absint(wp_unslash($_POST['quantity'])) : 1;

        if (empty($cart_item_key)) {
            wp_send_json_error(array('message' => __('Invalid cart item.', 'single-product-customizer')));
        }

        $cart_item = WC()->cart->get_cart_item($cart_item_key);
        if (!$cart_item) {
            wp_send_json_error(array('message' => __('Cart item not found.', 'single-product-customizer')));
        }

        $product = $cart_item['data'];
        $min_quantity = $product->get_min_purchase_quantity();
        $max_quantity = $product->get_max_purchase_quantity();

        if ($quantity < $min_quantity) {
            $quantity = $min_quantity;
        }
        if ($max_quantity > 0 && $quantity > $max_quantity) {
            $quantity = $max_quantity;
        }

        $updated = WC()->cart->set_quantity($cart_item_key, $quantity, true);
        if (!$updated) {
            wp_send_json_error(array('message' => __('Failed to update quantity.', 'single-product-customizer')));
        }

        WC()->cart->calculate_totals();

        wp_send_json_success(array(
            'message' => __('Quantity updated.', 'single-product-customizer'),
            'quantity' => $quantity,
            'cart_total' => WC()->cart->get_total(),
            'trigger_order_review_update' => true,
        ));
    }

    /**
     * Remove cart item via AJAX
     */
    public function remove_cart_item()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'sppcfw-quick-checkout')) {
            wp_send_json_error(array('message' => __('Security check failed', 'single-product-customizer')));
        }

        $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';

        if (empty($cart_item_key)) {
            wp_send_json_error(array('message' => __('Invalid cart item.', 'single-product-customizer')));
        }

        $removed = WC()->cart->remove_cart_item($cart_item_key);
        if (!$removed) {
            wp_send_json_error(array('message' => __('Failed to remove item.', 'single-product-customizer')));
        }

        WC()->cart->calculate_totals();

        wp_send_json_success(array(
            'message' => __('Item removed.', 'single-product-customizer'),
            'cart_total' => WC()->cart->get_total(),
            'trigger_order_review_update' => true,
        ));
    }
}

// Initialize the class
new Sppcfw_Quick_Checkout();
