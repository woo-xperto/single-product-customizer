<?php 
if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists("Sppcfw_Frontend_Variation_Reset_Text")){
    class Sppcfw_Frontend_Variation_Reset_Text{
        public function __construct(){
            add_filter("woocommerce_reset_variations_link",[$this, "sppcfw_reset_variation_clear_text"],10,1);
        }

        public function sppcfw_reset_variation_clear_text($sppcfw_variation_rest_text){
            if($this->is_enabled()===1){
                $clear_text=$this->sppcfw_variation_rest_text();
                if(!empty($clear_text)){
                    // phpcs:ignore
                    $sppcfw_variation_rest_text = '<a class="reset_variations" href="#">' . esc_html($clear_text) . '</a>';
                }
            }
            return $sppcfw_variation_rest_text;
        }

        public function sppcfw_variation_rest_text(){
            $change_clear_text='';
            

            if(SPPCFW_PRO_ACTIVE){
                if(isset(SPPCFW_ADVANCED['change_clear_text'])){
                    if(!empty(SPPCFW_ADVANCED['change_clear_text'])){
                        $change_clear_text=SPPCFW_ADVANCED['change_clear_text'];
                    }
                }

                if(sppcfw_if_category_based_customization_enabled()===1){
                    $product_cat=sppcfw_get_product_category_id();    
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['change_clear_text'])){
                            if(!empty($sppcfw_cat['change_clear_text'])){
                                $change_clear_text=$sppcfw_cat['change_clear_text'];
                            }
                        }
                    }
                }

                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['change_clear_text'])){
                        if(!empty($SPPCFW_INDIVIDUAL['change_clear_text'])){
                            $change_clear_text=$SPPCFW_INDIVIDUAL['change_clear_text'];
                        }                      
                    }
                }
            }

            return $change_clear_text;
        }
        public function is_enabled(){
            $enabled=0;
            if(SPPCFW_PRO_ACTIVE){
                $enabled=1;
            }
            return $enabled;
        }
    }
    new Sppcfw_Frontend_Variation_Reset_Text();
}

