<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists("Sppcfw_Frontend_Backorder_Text")){
    class Sppcfw_Frontend_Backorder_Text{
        public function __construct(){
            add_filter("woocommerce_get_availability_text",[$this,"sppcfw_change_backorder_text"],10,2);
        }

        public function sppcfw_change_backorder_text( $availability, $product ){
            if( $product->is_on_backorder() && $this->is_enabled()===1){

                $sppcfw_backorder_text_val = $this->sppcfw_get_backorder_text();
                
                if( $sppcfw_backorder_text_val ){
                    // phpcs:ignore
                    $availability = esc_html($sppcfw_backorder_text_val);
                }

            }

            return $availability;
        }
        public function sppcfw_get_backorder_text(){
            $change_backorder_text='';

            if(isset(SPPCFW_ADVANCED['change_backorder_text'])){
                if(!empty(SPPCFW_ADVANCED['change_backorder_text'])){
                    $change_backorder_text=SPPCFW_ADVANCED['change_backorder_text'];
                }
            }

            if(sppcfw_is_pro_active()){
                if(sppcfw_if_category_based_customization_enabled()===1){
                    $product_cat=sppcfw_get_product_category_id();    
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['change_backorder_text'])){
                            if(!empty($sppcfw_cat['change_backorder_text'])){
                                $change_backorder_text=$sppcfw_cat['change_backorder_text'];
                            }
                        }
                    }
                }

                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['change_backorder_text'])){
                        if(!empty($SPPCFW_INDIVIDUAL['change_backorder_text'])){
                            $change_backorder_text=$SPPCFW_INDIVIDUAL['change_backorder_text'];
                        }                      
                    }
                }
            }

            return $change_backorder_text;
        }
        public function is_enabled(){
            $enabled=0;
            if(!empty($this->sppcfw_get_backorder_text())){
                $enabled=1;
            }
            return $enabled;
        }
    }

    new Sppcfw_Frontend_Backorder_Text();
}

