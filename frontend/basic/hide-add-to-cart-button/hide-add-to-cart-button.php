<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists('Sppcfw_Frontend_Hide_Add_To_Cart_Button')){
    class Sppcfw_Frontend_Hide_Add_To_Cart_Button{

        public function __construct(){
            // For traditional themes - single product
            add_action("woocommerce_before_single_product_summary", [$this, "sppcfw_hide_add_to_cart_button_single"], 1);
            
            // For traditional themes - shop/archive pages
            add_action("woocommerce_before_shop_loop_item", [$this, "sppcfw_hide_add_to_cart_button_loop"], 1);
            
            // For block themes
            add_action("render_block", [$this, "sppcfw_remove_add_to_cart_form_in_block_themes"], 99, 2);
            
            // Hide quantity input as well
            add_filter('woocommerce_is_sold_individually', [$this, 'sppcfw_remove_quantity_fields'], 999, 2);
            
            // Disable AJAX add to cart
            add_filter('woocommerce_loop_add_to_cart_link', [$this, 'sppcfw_remove_add_to_cart_link'], 10, 2);
            
            // Add custom CSS class
            add_filter('body_class', [$this, 'sppcfw_add_body_class_for_hide_cart']);
        }

        public function sppcfw_hide_add_to_cart_button_single(){
            if($this->is_enabled() === 1){
                // Remove add to cart from single product
                remove_action("woocommerce_single_product_summary", "woocommerce_template_single_add_to_cart", 30);
                
                // Also remove from other possible locations
                remove_action('woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30);
                remove_action('woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30);
                remove_action('woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30);
                remove_action('woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30);
                
                // Remove variations form
                remove_action('woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30);
            }
        }

        public function sppcfw_hide_add_to_cart_button_loop(){
            if($this->is_enabled() === 1){
                // Remove add to cart from shop/archive pages
                remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
            }
        }

        // Remove the add to cart form block for block themes
        public function sppcfw_remove_add_to_cart_form_in_block_themes($block_content, $block) {
            if ($this->is_enabled() === 1) {
                if (isset($block['blockName']) && in_array($block['blockName'], [
                    'woocommerce/add-to-cart-form',
                    'woocommerce/add-to-cart-button',
                    'woocommerce/product-button',
                    'woocommerce/single-product'
                ])) {
                    // Check if it's specifically the add to cart block
                    if (in_array($block['blockName'], ['woocommerce/add-to-cart-form', 'woocommerce/add-to-cart-button'])) {
                        return ''; 
                    }
                    
                    // For single product block, we need to filter its content
                    if ($block['blockName'] === 'woocommerce/single-product') {
                        // Remove add to cart form from single product block
                        $block_content = preg_replace('/<form[^>]*class="[^"]*cart[^"]*"[^>]*>.*?<\/form>/s', '', $block_content);
                    }
                }
            }
            return $block_content;
        }

        // Remove quantity fields when add to cart is hidden
        public function sppcfw_remove_quantity_fields($return, $product){
            if($this->is_enabled() === 1 && !is_admin()){
                return true; // Force sold individually to hide quantity
            }
            return $return;
        }

        // Remove add to cart links from shop loop
        public function sppcfw_remove_add_to_cart_link($button, $product){
            if($this->is_enabled() === 1 && !is_admin()){
                return '';
            }
            return $button;
        }

        // Add custom body class
        public function sppcfw_add_body_class_for_hide_cart($classes){
            if($this->is_enabled() === 1){
                $classes[] = 'sppcfw-hide-add-to-cart';
            }
            return $classes;
        }

        public function is_enabled(){
            // Early return for non-product pages
            if(!is_product() && !is_shop() && !is_product_category() && !is_product_tag() && !is_product_taxonomy()){
                return 0;
            }
            
            $enabled = 0;
            
            if(sppcfw_is_pro_active()){
                // check in product level
                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['hide_add_to_cart_button'])){
                        if($SPPCFW_INDIVIDUAL['hide_add_to_cart_button']==='on'){
                           $enabled = 1;
                        }                       
                    }
                    return $enabled;
                }

                // check in category level
                if(sppcfw_if_category_based_customization_enabled()===1){
                    $product_cat = sppcfw_get_product_category_id();
                    if($product_cat > 0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['hide_add_to_cart_button'])){
                            if($sppcfw_cat['hide_add_to_cart_button']==='on'){
                                $enabled = 1;
                            }
                        }
                    }
                    return $enabled;
                }
            }

            // Global settings
            if(isset(SPPCFW_BASIC['hide_add_to_cart_button'])){
                if(SPPCFW_BASIC['hide_add_to_cart_button']==='on'){
                    $enabled = 1;
                }
            }

            return $enabled;
        }
        
        // Optional: Display custom message instead of add to cart
        public function sppcfw_display_custom_message(){
            if($this->is_enabled() === 1){
                $message = '';
                
                if(sppcfw_is_pro_active()){
                    // Check for custom message in product level
                    if(sppcfw_if_product_based_customization_enabled() === 1){
                        global $SPPCFW_INDIVIDUAL;
                        if(isset($SPPCFW_INDIVIDUAL['add_to_cart_custom_message']) && !empty($SPPCFW_INDIVIDUAL['add_to_cart_custom_message'])){
                            $message = $SPPCFW_INDIVIDUAL['add_to_cart_custom_message'];
                        }
                    }
                    
                    // Check for custom message in category level
                    if(empty($message) && sppcfw_if_category_based_customization_enabled() === 1){
                        $product_cat = sppcfw_get_product_category_id();
                        if($product_cat > 0){
                            $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                            if(isset($sppcfw_cat['add_to_cart_custom_message']) && !empty($sppcfw_cat['add_to_cart_custom_message'])){
                                $message = $sppcfw_cat['add_to_cart_custom_message'];
                            }
                        }
                    }
                }
                
                // Check global message
                if(empty($message) && isset(SPPCFW_BASIC['add_to_cart_custom_message']) && !empty(SPPCFW_BASIC['add_to_cart_custom_message'])){
                    $message = SPPCFW_BASIC['add_to_cart_custom_message'];
                }
                
                if(!empty($message)){
                    echo '<div class="sppcfw-custom-message">' . wp_kses_post($message) . '</div>';
                }
            }
        }
    }

    new Sppcfw_Frontend_Hide_Add_To_Cart_Button();
    
    // Add custom message hook if needed
    add_action('woocommerce_single_product_summary', function(){
        $instance = new Sppcfw_Frontend_Hide_Add_To_Cart_Button();
        $instance->sppcfw_display_custom_message();
    }, 35);
}