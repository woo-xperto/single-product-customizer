<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Sppcfw_Frontend_Quick_Checkout')) {
    class Sppcfw_Frontend_Quick_Checkout
    {
        /**
         * Prevent recursive fallback rendering in block themes.
         *
         * @var bool
         */
        private $sppcfw_rendering_fallback = false;

        /**
         * Prevent re-registering summary hooks multiple times in one request.
         *
         * @var bool
         */
        private $sppcfw_single_hooks_prepared = false;

        /**
         * Track whether Quick Checkout template has already rendered on the page.
         *
         * @var bool
         */
        private static $sppcfw_template_rendered = false;

        public function __construct()
        {
            // Check if Quick Checkout is enabled and remove hooks
            if ($this->sppcfw_is_quick_checkout_enabled()) {
                // $this->sppcfw_remove_default_product_hooks();
            }

            // Register WooCommerce template directory
            add_filter('woocommerce_locate_template', [$this, 'sppcfw_locate_custom_template'], 10, 3);

            add_action('wp_enqueue_scripts', [$this, 'sppcfw_enqueue_quick_checkout_assets']);
            // Ensure removal runs after WooCommerce has registered its hooks
            add_action('wp', [$this, 'sppcfw_remove_shared_hooks_on_single_product'], 20);
            // Astra/OceanWP and some optimizers can re-attach Woo hooks late,
            // so remove again right before single product rendering.
            add_action('woocommerce_before_single_product', [$this, 'sppcfw_remove_shared_hooks_on_single_product'], 0);
            // Hard guard for themes that rebuild Woo hooks (Astra/OceanWP).
            add_action('woocommerce_before_single_product', [$this, 'sppcfw_prepare_single_product_hooks_for_quick_checkout'], -999);

            // For both block and classic themes, use these hooks
            // Display template-1 and template-2-free on woocommerce_single_product_summary
            add_action('woocommerce_single_product_summary', [$this, 'sppcfw_display_quick_checkout_template'], 5);
            add_action('woocommerce_single_product_summary', [$this, 'sppcfw_auto_add_product_to_cart'], 4);

            // Display template-3 on woocommerce_after_single_product_summary
            add_action('woocommerce_after_single_product_summary', [$this, 'sppcfw_display_template_3'], 5);
            add_action('woocommerce_after_single_product_summary', [$this, 'sppcfw_auto_add_product_to_cart_template_3'], 4);

            // Fallback hook for block themes that may not fire the above hooks
            add_action('woocommerce_before_add_to_cart_form', [$this, 'sppcfw_render_quick_checkout_fallback'], 20);

            // Allow checkout form to display even when cart is empty (for quick checkout)
            add_filter('woocommerce_checkout_redirect_empty_cart', array($this, 'sppcfw_allow_empty_cart_checkout'), 10, 1);
            add_filter('woocommerce_checkout_update_order_review_expired', array($this, 'sppcfw_allow_empty_cart_order_review'), 10, 1);

            // Hook into update_order_review to handle empty cart gracefully
            add_action('woocommerce_checkout_update_order_review', array($this, 'sppcfw_handle_empty_cart_order_review'), 5, 1);

            // Render quick checkout product details from a dedicated template.
            add_action('woocommerce_checkout_before_customer_details', array($this, 'sppcfw_render_product_details_before_customer_details'));

            // Filter Gutenberg single product summary blocks in block themes
            add_filter('render_block', array($this, 'sppcfw_hide_block_theme_summary_blocks'), 10, 2);
        }

        /**
         * Filter Gutenberg single product summary blocks in block themes when Quick Checkout is active.
         *
         * @param string $block_content Rendered block HTML.
         * @param array  $block         Block data.
         * @return string
         */
        public function sppcfw_hide_block_theme_summary_blocks($block_content, $block)
        {
            if (!$this->sppcfw_is_quick_checkout_active() || !is_product()) {
                return $block_content;
            }

            $blocks_to_hide = array(
                'core/post-title',
                'core/post-excerpt',
                'woocommerce/product-rating',
                'woocommerce/product-price',
                'woocommerce/add-to-cart-form',
                'woocommerce/product-meta',
                'woocommerce/product-sku',
            );

            if (isset($block['blockName']) && in_array($block['blockName'], $blocks_to_hide, true)) {
                return '';
            }

            return $block_content;
        }

        /* Check if current theme is a block theme */
        public function sppcfw_is_block_theme()
        {
            $theme = wp_get_theme();
            return $theme->is_block_theme();
        }

        /* Fallback method to render quick checkout in block themes */
        public function sppcfw_render_quick_checkout_fallback()
        {
            // Guard against recursive rendering (e.g. variable/grouped add-to-cart templates
            // triggering woocommerce_before_add_to_cart_form again while already rendering fallback).
            if ($this->sppcfw_rendering_fallback || self::$sppcfw_template_rendered) {
                return;
            }

            // Only run on product pages
            if (!is_product()) {
                return;
            }

            // Check if Quick Checkout is enabled
            if (!$this->sppcfw_is_quick_checkout_active()) {
                return;
            }

            // Only run this fallback in block themes
            if (!$this->sppcfw_is_block_theme()) {
                return;
            }

            // Get the selected template
            $selected_template = get_option('sppcfw_enable_qc', 'default');
            if (!sppcfw_is_pro_active() && $selected_template !== 'default') {
                $selected_template = 'template-1';
            }

            // Get template path
            $template_path = $this->sppcfw_get_template_path($selected_template);

            // Load the template
            $this->sppcfw_rendering_fallback = true;
            try {
                if (file_exists($template_path)) {
                    self::$sppcfw_template_rendered = true;
                    echo '<div class="sppcfw-quick-checkout-wrapper" style="margin: 20px 0;">';
                    include $template_path;
                    echo '</div>';
                } else {
                    // Fallback to default if template doesn't exist
                    $default_path = $this->sppcfw_get_template_path('default');
                    if (file_exists($default_path)) {
                        self::$sppcfw_template_rendered = true;
                        echo '<div class="sppcfw-quick-checkout-wrapper" style="margin: 20px 0;">';
                        include $default_path;
                        echo '</div>';
                    }
                }
            } finally {
                $this->sppcfw_rendering_fallback = false;
            }
        }

        /* Register custom templates directory with WooCommerce */
        public function sppcfw_locate_custom_template($template, $template_name, $template_path)
        {
            // Only apply custom templates on single product pages
            if (is_product()) {
                // Override review-order template for quick checkout
                if ('checkout/review-order.php' === $template_name) {
                    $quick_checkout_template = plugin_dir_path(__FILE__) . 'templates/checkout/review-order.php';
                    if (file_exists($quick_checkout_template) && $this->sppcfw_is_quick_checkout_active()) {
                        return $quick_checkout_template;
                    }
                }
            }
            if ($this->sppcfw_is_quick_checkout_active()) {
                // Check if the template file exists in our custom directory
                $custom_template_path = plugin_dir_path(__FILE__) . 'templates/' . $template_name;

                if (file_exists($custom_template_path)) {
                    return $custom_template_path;
                }
            }

            // For checkout templates in subdirectories
            $is_quick_checkout_request = $this->sppcfw_is_quick_checkout_active() &&
                (
                    is_product() ||
                    (function_exists('sppcfw_is_valid_single_product_referer') && sppcfw_is_valid_single_product_referer())
                );

            if ($is_quick_checkout_request) {
                $custom_checkout_template = plugin_dir_path(__FILE__) . 'templates/checkout/' . $template_name;
                if (file_exists($custom_checkout_template)) {
                    return $custom_checkout_template;
                }
            }

            return $template;
        }

        /* Check if Quick Checkout is enabled */
        public function sppcfw_is_quick_checkout_enabled()
        {
            $sppcfw_enable_quick_checkout = get_option('sppcfw_enable_quick_checkout', 0);
            return !empty($sppcfw_enable_quick_checkout) && sppcfw_is_singular();
        }

        /* Check if Quick Checkout is currently active */
        public function sppcfw_is_quick_checkout_active()
        {
            $sppcfw_enable_quick_checkout = get_option('sppcfw_enable_quick_checkout', 0);
            return !empty($sppcfw_enable_quick_checkout);
        }

        /**
         * Auto-add product to cart on initial page load for quick checkout (template-1, template-2-free)
         */
        public function sppcfw_auto_add_product_to_cart()
        {
            // Only run on quick checkout pages
            if (!$this->sppcfw_is_quick_checkout_active()) {
                return;
            }

            // Skip for template-3 (handled separately)
            $selected_template = get_option('sppcfw_enable_qc', 'default');
            if ($selected_template === 'template-3') {
                return;
            }

            global $product;

            if (!$product || !$product->get_id()) {
                return;
            }

            $product_id = $product->get_id();

            // Check if product is not already in cart
            $in_cart = false;
            if (function_exists('WC') && WC()->cart) {
                foreach (WC()->cart->get_cart() as $cart_item) {
                    if ($cart_item['product_id'] == $product_id || $cart_item['variation_id'] == $product_id) {
                        $in_cart = true;
                        break;
                    }
                }
            }

            // Add to cart if not already present
            if (!$in_cart && !isset($_GET['added-to-cart'])) {
                WC()->cart->add_to_cart($product_id, 1);
                wp_safe_remote_post(add_query_arg('added-to-cart', $product_id, home_url()), [
                    'blocking' => false,
                    'sslverify' => apply_filters('https_local_ssl_verify', false),
                ]);
            }
        }

        /**
         * Auto-add product to cart for template-3 on woocommerce_after_single_product_summary
         */
        public function sppcfw_auto_add_product_to_cart_template_3()
        {
            // Only run on quick checkout pages
            if (!$this->sppcfw_is_quick_checkout_active()) {
                return;
            }

            // Only run for template-3
            $selected_template = get_option('sppcfw_enable_qc', 'default');
            if ($selected_template !== 'template-3') {
                return;
            }

            global $product;

            if (!$product || !$product->get_id()) {
                return;
            }

            $product_id = $product->get_id();

            // Check if product is not already in cart
            $in_cart = false;
            if (function_exists('WC') && WC()->cart) {
                foreach (WC()->cart->get_cart() as $cart_item) {
                    if ($cart_item['product_id'] == $product_id || $cart_item['variation_id'] == $product_id) {
                        $in_cart = true;
                        break;
                    }
                }
            }

            // Add to cart if not already present
            if (!$in_cart && !isset($_GET['added-to-cart'])) {
                WC()->cart->add_to_cart($product_id, 1);
                wp_safe_remote_post(add_query_arg('added-to-cart', $product_id, home_url()), [
                    'blocking' => false,
                    'sslverify' => apply_filters('https_local_ssl_verify', false),
                ]);
            }
        }

        /* Register frontend assets for Quick Checkout */
        public function sppcfw_enqueue_quick_checkout_assets()
        {
            if (!is_product()) {
                return;
            }
            // Check if Quick Checkout is enabled
            $sppcfw_enable_quick_checkout = get_option('sppcfw_enable_quick_checkout', 0);

            if (!empty($sppcfw_enable_quick_checkout)) {
                wp_enqueue_script(
                    'sppcfw-quick-checkout-frontend-js',
                    plugin_dir_url(__FILE__) . 'assets/js/quick-checkout-frontend.js',
                    array('jquery'),
                    (defined('SPPCFW_DEV') && SPPCFW_DEV ? time() : SPPCFW_VERSION),
                    true
                );

                wp_localize_script(
                    'sppcfw-quick-checkout-frontend-js',
                    'sppcfw',
                    array(
                        'ajaxUrl' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('sppcfw-quick-checkout'),
                        'checkoutUrl' => wc_get_checkout_url(),
                        'strings' => array(
                            'processing' => __('Processing...', 'single-product-customizer'),
                            'error' => __('An error occurred. Please try again.', 'single-product-customizer'),
                            'confirmRemove' => __('Are you sure you want to remove this item?', 'single-product-customizer'),
                        ),
                    )
                );
                // Enqueue Quick Checkout styles
                wp_enqueue_style(
                    'sppcfw-quick-checkout-frontend-css',
                    plugin_dir_url(__FILE__) . 'assets/css/quick-checkout-frontend.css',
                    array(),
                    (defined('SPPCFW_DEV') && SPPCFW_DEV ? time() : SPPCFW_VERSION),
                    'all'
                );

                if ($this->sppcfw_is_block_theme()) {
                    wp_add_inline_style(
                        'sppcfw-quick-checkout-frontend-css',
                        '.single-product .wp-block-post-title, .single-product .wp-block-woocommerce-product-rating, .single-product .wp-block-woocommerce-product-price, .single-product .wp-block-post-excerpt, .single-product .wp-block-woocommerce-add-to-cart-form, .single-product .wp-block-woocommerce-product-meta, .single-product .wc-block-grid__product-rating, .single-product .wc-block-grid__product-price, .single-product div.product form.cart:not(.checkout) { display: none !important; }'
                    );
                }

                // Enqueue Quick Checkout template styles
                wp_enqueue_style(
                    'sppcfw-quick-checkout-template-css',
                    plugin_dir_url(__FILE__) . 'assets/css/quick-checkout.css',
                    array('sppcfw-quick-checkout-frontend-css'),
                    (defined('SPPCFW_DEV') && SPPCFW_DEV ? time() : SPPCFW_VERSION),
                    'all'
                );
                // Load Font Awesome locally for quick checkout template icons.
                wp_enqueue_style(
                    'sppcfw-font-awesome',
                    plugins_url('../../backend/backend-variable-switcher/fontawesome.min.css', __FILE__),
                    array(),
                    (defined('SPPCFW_DEV') && SPPCFW_DEV ? time() : SPPCFW_VERSION),
                    'all'
                );

                wp_enqueue_script('wc-checkout');
                wp_enqueue_script('wc-country-select');
                wp_enqueue_script('wc-address-i18n');
                wp_enqueue_script('wc-add-to-cart-variation');

                // Temporarily set is_checkout to true so payment gateways load their scripts
                // This is safe because we're only on product pages with quick checkout enabled
                add_filter('woocommerce_is_checkout', '__return_true', 999);

                // Get all available payment gateways and let them enqueue their scripts
                $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                foreach ($available_gateways as $gateway_id => $gateway) {
                    if ($gateway->enabled === 'yes' && method_exists($gateway, 'payment_scripts')) {
                        $gateway->payment_scripts();
                    }
                }

                // Remove the filter after scripts are loaded
                remove_filter('woocommerce_is_checkout', '__return_true', 999);
            }
        }

        /* Display Quick Checkout Template on Single Product Page (template-1, template-2-free) */
        public function sppcfw_display_quick_checkout_template()
        {
            if (self::$sppcfw_template_rendered) {
                return;
            }

            // Check if Quick Checkout is enabled
            $sppcfw_enable_quick_checkout = get_option('sppcfw_enable_quick_checkout', 0);

            if (!empty($sppcfw_enable_quick_checkout) && sppcfw_is_singular()) {
                // Get selected template
                $selected_template = get_option('sppcfw_enable_qc', 'default');
                if (!sppcfw_is_pro_active()) {
                    $selected_template = 'template-1';
                }

                // Skip template-3 here (it will be displayed via woocommerce_after_single_product_summary)
                if ($selected_template === 'template-3') {
                    return;
                }

                // Get template path based on selection
                $template_path = $this->sppcfw_get_template_path($selected_template);

                // Load the template
                if (file_exists($template_path)) {
                    self::$sppcfw_template_rendered = true;
                    // Wrap template in a container
                    echo '<div class="sppcfw-quick-checkout-wrapper">';
                    include $template_path;
                    echo '</div>';
                } else {
                    // Fallback to default if template doesn't exist
                    $default_path = $this->sppcfw_get_template_path('default');
                    if (file_exists($default_path)) {
                        self::$sppcfw_template_rendered = true;
                        echo '<div class="sppcfw-quick-checkout-wrapper">';
                        include $default_path;
                        echo '</div>';
                    }
                }
            }
        }

        /* Display template-3 on woocommerce_after_single_product_summary hook */
        public function sppcfw_display_template_3()
        {
            if (self::$sppcfw_template_rendered) {
                return;
            }

            // Check if Quick Checkout is enabled
            $sppcfw_enable_quick_checkout = get_option('sppcfw_enable_quick_checkout', 0);

            if (!empty($sppcfw_enable_quick_checkout) && sppcfw_is_singular()) {
                // Get selected template
                $selected_template = get_option('sppcfw_enable_qc', 'default');

                // Only display template-3 here
                if ($selected_template !== 'template-3') {
                    return;
                }

                if (!sppcfw_is_pro_active()) {
                    return;
                }

                // Get template path for template-3
                $template_path = $this->sppcfw_get_template_path('template-3');

                // Load the template
                if (file_exists($template_path)) {
                    self::$sppcfw_template_rendered = true;
                    // Wrap template in a container
                    echo '<div class="sppcfw-quick-checkout-wrapper">';
                    include $template_path;
                    echo '</div>';
                }
            }
        }

        /* Get the template file path based on selected template */
        public function sppcfw_get_template_path($template_name)
        {
            $template_dir = plugin_dir_path(__FILE__) . 'templates/';

            // Map template names to file names
            $template_map = array(
                'default' => 'quick-checkout-form.php',
                'template-1' => 'template-1.php',
                'template-2' => 'template-2.php',
                'template-3' => 'template-3.php',
            );

            $template_file = isset($template_map[$template_name]) ? $template_map[$template_name] : 'quick-checkout-form.php';
            $full_path = $template_dir . $template_file;

            if (!file_exists($full_path) && defined('SPPCFW_PRO_DIR_PATH')) {
                $pro_path = SPPCFW_PRO_DIR_PATH . 'frontend/Enable-Quick-Checkout/templates/' . $template_file;
                if (file_exists($pro_path)) {
                    return $pro_path;
                }
            }

            return $full_path;
        }

        /**
         * Allow empty cart checkout for quick checkout
         *
         * @param bool $redirect Whether to redirect
         * @return bool
         */
        public function sppcfw_allow_empty_cart_checkout($redirect)
        {
            if ($this->sppcfw_is_quick_checkout_active()) {
                return false;  // Don't redirect, allow checkout form to show
            }
            return $redirect;
        }

        /**
         * Allow empty cart order review update for quick checkout
         *
         * @param bool $expired Whether order review is expired
         * @return bool
         */
        public function sppcfw_allow_empty_cart_order_review($expired)
        {
            if ($this->sppcfw_is_quick_checkout_active()) {
                return false;  // Don't show expired message, allow empty cart
            }
            return $expired;
        }

        /**
         * Handle empty cart in order review update
         *
         * @param string $post_data Posted data
         */
        public function sppcfw_handle_empty_cart_order_review($post_data)
        {
            // If cart is empty and quick checkout is active, clear any error notices
            if (WC()->cart->is_empty() && $this->sppcfw_is_quick_checkout_active()) {
                // Remove "session expired" notices
                $notices = wc_get_notices('error');
                foreach ($notices as $key => $notice) {
                    if (strpos($notice['notice'], 'session has expired') !== false) {
                        unset(WC()->notices['error'][$key]);
                    }
                }
            }
        }

        /**
         * Remove shared loop and single product summary hooks
         * when Quick Checkout is enabled.
         */
        public function sppcfw_remove_shared_hooks_on_single_product()
        {
            // Only run if Quick Checkout is active and we are on a product page
            if (!$this->sppcfw_is_quick_checkout_active() || !is_product()) {
                return;
            }

            // Remove single product summary hooks (category/meta, title, rating, short description)
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);

            // Remove after single product summary hooks (related products, upsell products)
            remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
            remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);

            // Some themes output summary pieces through generic/title hooks.
            remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
            remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
            remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
            remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
        }

        /**
         * Rebuild single-product hooks for quick checkout to avoid theme re-hooking issues.
         *
         * @return void
         */
        public function sppcfw_prepare_single_product_hooks_for_quick_checkout()
        {
            if (!$this->sppcfw_is_quick_checkout_active() || !is_product() || $this->sppcfw_single_hooks_prepared) {
                return;
            }

            // Remove all default/theme callbacks from Woo single product sections.
            remove_all_actions('woocommerce_single_product_summary');
            remove_all_actions('woocommerce_after_single_product_summary');

            // Re-register only plugin-controlled rendering callbacks.
            add_action('woocommerce_single_product_summary', [$this, 'sppcfw_auto_add_product_to_cart'], 4);
            add_action('woocommerce_single_product_summary', [$this, 'sppcfw_display_quick_checkout_template'], 5);
            add_action('woocommerce_after_single_product_summary', [$this, 'sppcfw_auto_add_product_to_cart_template_3'], 4);
            add_action('woocommerce_after_single_product_summary', [$this, 'sppcfw_display_template_3'], 5);
            add_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);

            $this->sppcfw_single_hooks_prepared = true;
        }

        /**
         * Render product details block before customer details section.
         *
         * The template is loaded from a dedicated file so markup stays reusable
         * and is injected through the WooCommerce checkout hook.
         *
         * @return void
         */
        public function sppcfw_render_product_details_before_customer_details()
        {
            if (!$this->sppcfw_is_quick_checkout_active() || !is_product()) {
                return;
            }

            $template_path = plugin_dir_path(__FILE__) . 'templates/checkout/quick-product-details.php';
            if (file_exists($template_path)) {
                include $template_path;
            }
        }
    }  // Sppcfw_Frontend_Quick_Checkout class end

    new Sppcfw_Frontend_Quick_Checkout();
}  // Sppcfw_Frontend_Quick_Checkout class checking end
