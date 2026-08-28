<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists("Sppcfw_Frontend_Change_Add_To_Cart_Button_Text")){

    class Sppcfw_Frontend_Change_Add_To_Cart_Button_Text{
        public function __construct(){
            add_action("woocommerce_product_single_add_to_cart_text",[$this,"sppcfw_change_add_to_cart_button_text"],10,1);
        }
    
    
        public function sppcfw_change_add_to_cart_button_text($add_to_cart_text){
            $add_to_cart_button_text=$this->sppcfw_get_add_to_cart_button_text();
            if(!empty($add_to_cart_button_text)){
                return $add_to_cart_button_text;
            }else{
                return $add_to_cart_text;
            }
        }

        public function sppcfw_get_add_to_cart_button_text(){
            $add_to_cart_button_text='';
            if(isset(SPPCFW_BASIC['add_to_cart_button_text'])){
                if(!empty(SPPCFW_BASIC['add_to_cart_button_text'])){
                    $add_to_cart_button_text=SPPCFW_BASIC['add_to_cart_button_text'];
                }
            }

            if(sppcfw_is_pro_active()){

                if(sppcfw_if_category_based_customization_enabled()===1){
                    $product_cat=sppcfw_get_product_category_id();    
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['add_to_cart_button_text'])){
                            if(!empty($sppcfw_cat['add_to_cart_button_text'])){
                                $add_to_cart_button_text=$sppcfw_cat['add_to_cart_button_text'];
                            }
                        }
                    }
                }

                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['add_to_cart_button_text'])){
                        if(!empty($SPPCFW_INDIVIDUAL['add_to_cart_button_text'])){
                            $add_to_cart_button_text=$SPPCFW_INDIVIDUAL['add_to_cart_button_text'];
                        }                      
                    }
                }
            }

            return $add_to_cart_button_text;
        }
    
    } 

    new Sppcfw_Frontend_Change_Add_To_Cart_Button_Text();
    
}


