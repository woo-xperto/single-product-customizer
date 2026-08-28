<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists("Sppcfw_Frontend_Enable_Plus_Minus_Button")){
    
    class Sppcfw_Frontend_Enable_Plus_Minus_Button{
        
        public function __construct(){
              
              add_action("wp_enqueue_scripts",[$this,"sppcfw_plus_minus_button_assets"]);  
              add_action("woocommerce_before_quantity_input_field",[$this,"sppcfw_add_minus_button"]);  
              add_action("woocommerce_after_quantity_input_field",[$this,"sppcfw_add_plus_button"]);  
            
        }

        public function sppcfw_plus_minus_button_assets(){
            if(sppcfw_is_singular() && $this->is_enabled()===1){
                wp_enqueue_script(
                    'sppcfw-enable-plus-minus-button-js',
                    plugin_dir_url(__FILE__).'enable-plus-minus-button.js',
                    array('jquery'),
                    (defined('SPPCFW_VERSION') ? SPPCFW_VERSION : false),
                    true
                );

                // Add small inline style to ensure quantity wrapper displays as flex
                wp_register_style('sppcfw-plus-minus-inline', false);
                wp_enqueue_style('sppcfw-plus-minus-inline');
                $css = '.quantity{display:flex !important; align-items:center; gap:0.25rem;} .quantity .button, .sppcfw_minus_button, .sppcfw_plus_button{margin-bottom:0 !important;}';
                wp_add_inline_style('sppcfw-plus-minus-inline', $css);
            }
        }

        public function sppcfw_add_minus_button(){
            global $product;

            if ($product && $product->is_sold_individually()) {
                return true;
            }

            // Exit if stock quantity is exactly 1
            if ($product && $product->get_stock_quantity() === 1) {
                return true;
            }

            if(sppcfw_is_singular() && $this->is_enabled()===1){
                echo '<button type="button" class="button sppcfw_minus_button">-</button>';
            }
        }

        public function sppcfw_add_plus_button(){
            global $product;

            if ($product && $product->is_sold_individually()) {
                return true;
            }

            // Exit if stock quantity is exactly 1
            if ($product && $product->get_stock_quantity() === 1) {
                return true;
            }

            if(sppcfw_is_singular() && $this->is_enabled()===1){
                echo '<button type="button" class="button sppcfw_plus_button">+</button>';
            }
        }

        public function is_enabled(){
            $enabled=0;
            if(SPPCFW_PRO_ACTIVE){
                // check in product level
                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['enable_plus_minus_button'])){
                        if($SPPCFW_INDIVIDUAL['enable_plus_minus_button']==='on'){
                           $enabled=1;
                        }else{
                           $enabled=0;
                        }                       
                    }
            
                    return $enabled;
                }

                // check in category level
                
                if(sppcfw_if_category_based_customization_enabled()===1){

                    $product_cat=sppcfw_get_product_category_id();
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['enable_plus_minus_button'])){
                            if($sppcfw_cat['enable_plus_minus_button']==='on'){
                                $enabled=1;
                            }
                        }
                    }

                    return $enabled;
                }

            }

            if(isset(SPPCFW_BASIC['enable_plus_minus_button'])){
                if(SPPCFW_BASIC['enable_plus_minus_button']==='on'){
                    $enabled=1;
                }
            }

            return $enabled;
        }

    } // end class

    new Sppcfw_Frontend_Enable_Plus_Minus_Button();
}


