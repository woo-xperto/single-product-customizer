<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists("Sppcfw_Frontend_Hide_Price")){
    class Sppcfw_Frontend_Hide_Price{
        public function __construct(){
            // For traditional themes
            add_action("woocommerce_before_single_product_summary", [$this, "sppcfw_hide_price_section"], 1);
            
            // For block themes - two approaches
            add_action("render_block", [$this, "sppcfw_remove_product_price_in_block_themes"], 99, 2);
            
            // Additional hook for block themes to remove price from product template
            add_filter('woocommerce_get_price_html', [$this, 'sppcfw_hide_price_in_all_locations'], 10, 2);
            
            // Hide price from shop/archive pages
            add_action('woocommerce_before_shop_loop_item', [$this, 'sppcfw_hide_price_in_loop'], 1);
            add_action('woocommerce_after_shop_loop_item', [$this, 'sppcfw_hide_price_in_loop'], 999);
        }
    
        public function sppcfw_hide_price_section(){
            if($this->is_enabled()===1){
                remove_action("woocommerce_single_product_summary", "woocommerce_template_single_price", 10);
                
                // Also remove from other possible locations
                remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
            }
        }

        // Hide price in shop/archive loops
        public function sppcfw_hide_price_in_loop(){
            if($this->is_enabled() === 1){
                remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
            }
        }

        // Remove the product price block for block themes
        public function sppcfw_remove_product_price_in_block_themes($block_content, $block) {
            if ($this->is_enabled() === 1) {
                if (isset($block['blockName']) && in_array($block['blockName'], [
                    'woocommerce/product-price',
                    'core/post-title',
                    'woocommerce/product-sale-badge',
                    'woocommerce/product-rating'
                ])) {
                    // Check if it's specifically the price block
                    if ($block['blockName'] === 'woocommerce/product-price') {
                        return ''; 
                    }
                }
            }
            return $block_content;
        }

        // Hide price in all locations using filter
        public function sppcfw_hide_price_in_all_locations($price, $product){
            if($this->is_enabled() === 1 && !is_admin()){
                return '';
            }
            return $price;
        }

        public function is_enabled(){
            // Add global product check for better performance
            global $product;
            
            if(!is_object($product) && function_exists('wc_get_product')){
                $product = wc_get_product();
            }
            
            $enabled = 0;
            
            // Check if we're on a product page or shop page
            if(!is_product() && !is_shop() && !is_product_category() && !is_product_tag() && !is_product_taxonomy()){
                return $enabled;
            }
            
            if(SPPCFW_PRO_ACTIVE){
                // check in product level
                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['hide_product_price'])){
                        if($SPPCFW_INDIVIDUAL['hide_product_price']==='on'){
                           $enabled=1;
                        }                       
                    }
                    return $enabled;
                }

                // check in category level
                if(sppcfw_if_category_based_customization_enabled()===1){
                    $product_cat = sppcfw_get_product_category_id();
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['hide_product_price'])){
                            if($sppcfw_cat['hide_product_price']==='on'){
                                $enabled=1;
                            }
                        }
                    }
                    return $enabled;
                }
            }

            // Global settings
            if(isset(SPPCFW_BASIC['hide_product_price'])){
                if(SPPCFW_BASIC['hide_product_price']==='on'){
                    $enabled=1;
                }
            }

            return $enabled;
        }
    }

    new Sppcfw_Frontend_Hide_Price();
}