<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( ! class_exists('Sppcfw_Frontend_Out_Of_Stock')){
    class Sppcfw_Frontend_Out_Of_Stock{

        public function __construct(){
            add_filter("woocommerce_get_availability_text",[$this, "sppcfw_display_out_of_stock_text"],100,2);
        }
    
    
        public function sppcfw_display_out_of_stock_text($sppcfw_stock_text, $product ){
                
            if(  !$product->is_in_stock()  ){

                if(isset(SPPCFW_BASIC['out_of_stock_text'])){
                    if(!empty(SPPCFW_BASIC['out_of_stock_text'])){
                        $sppcfw_stock_text=SPPCFW_BASIC['out_of_stock_text'];
                    }
                }

                if(sppcfw_is_pro_active()){

                    if(sppcfw_if_category_based_customization_enabled()===1){
                        $product_cat=sppcfw_get_product_category_id();    
                        if($product_cat>0){
                            $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                            
                            if(isset($sppcfw_cat['out_of_stock_text'])){
                                if(!empty($sppcfw_cat['out_of_stock_text'])){
                                    $sppcfw_stock_text=$sppcfw_cat['out_of_stock_text'];
                                }
                            }
                        }
                    }

                    if(sppcfw_if_product_based_customization_enabled()===1){
                        global $SPPCFW_INDIVIDUAL;
                        if(isset($SPPCFW_INDIVIDUAL['out_of_stock_text'])){
                            if(!empty($SPPCFW_INDIVIDUAL['out_of_stock_text'])){
                                $sppcfw_stock_text=$SPPCFW_INDIVIDUAL['out_of_stock_text'];
                            }                      
                        }
                    }
                }
                
            }
    
           return $sppcfw_stock_text; 
    
        }

    
    }

    new Sppcfw_Frontend_Out_Of_Stock();
}


