<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists("Sppcfw_Frontend_Upsell_Product_Title")){
    class Sppcfw_Frontend_Upsell_Product_Title{
        public function __construct(){
            add_filter("woocommerce_product_upsells_products_heading",[ $this, "sppcfw_display_upsell_product_title"],110,1);
        }
    
        public function sppcfw_display_upsell_product_title($sppcfw_upsell_prodct_text){
            if($this->is_enabled()===1){
                $upsell_product_title=$this->sppcfw_get_upsell_product_title();
                if(!empty($upsell_product_title))$sppcfw_upsell_prodct_text=$upsell_product_title;
            }
            return $sppcfw_upsell_prodct_text;
        
        }

        public function sppcfw_get_upsell_product_title(){
            $upsell_products_title='';

            if(isset(SPPCFW_ADVANCED['upsell_products_title'])){
                if(!empty(SPPCFW_ADVANCED['upsell_products_title'])){
                    $upsell_products_title=SPPCFW_ADVANCED['upsell_products_title'];
                }
            }

            if(sppcfw_is_pro_active()){
                if(sppcfw_if_category_based_customization_enabled()===1){
                    $product_cat=sppcfw_get_product_category_id();    
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['upsell_products_title'])){
                            if(!empty($sppcfw_cat['upsell_products_title'])){
                                $upsell_products_title=$sppcfw_cat['upsell_products_title'];
                            }
                        }
                    }
                }

                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['upsell_products_title'])){
                        if(!empty($SPPCFW_INDIVIDUAL['upsell_products_title'])){
                            $upsell_products_title=$SPPCFW_INDIVIDUAL['upsell_products_title'];
                        }                      
                    }
                }
            }

            return $upsell_products_title;
        }
        public function is_enabled(){
            $enabled=0;
            if(!empty($this->sppcfw_get_upsell_product_title())){
                $enabled=1;
            }
            return $enabled;
        }
    }

    new Sppcfw_Frontend_Upsell_Product_Title();
}



