<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( ! class_exists('Sppcfw_Frontend_Related_Product_Title')){
    class Sppcfw_Frontend_Related_Product_Title{

        public function __construct(){
            add_filter("woocommerce_product_related_products_heading",[$this, "sppcfw_display_related_products_title"]);
        }
    
        function sppcfw_display_related_products_title($related_prducts_title){

            if($this->is_enabled()===1){
                $sppcfw_product_title=$this->sppcfw_get_related_products_title();
                if(!empty($sppcfw_product_title)){
                    $related_prducts_title=$sppcfw_product_title;
                }
            }
            
            return $related_prducts_title;
        }

        public function sppcfw_get_related_products_title(){
            $related_prducts_title='';
            

            if(SPPCFW_PRO_ACTIVE){
                if(isset(SPPCFW_ADVANCED['related_products_title'])){
                    if(!empty(SPPCFW_ADVANCED['related_products_title'])){
                        $related_prducts_title=SPPCFW_ADVANCED['related_products_title'];
                    }
                }

                if(sppcfw_if_category_based_customization_enabled()===1){
                    $product_cat=sppcfw_get_product_category_id();    
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['related_products_title'])){
                            if(!empty($sppcfw_cat['related_products_title'])){
                                $related_prducts_title=$sppcfw_cat['related_products_title'];
                            }
                        }
                    }
                }

                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['related_products_title'])){
                        if(!empty($SPPCFW_INDIVIDUAL['related_products_title'])){
                            $related_prducts_title=$SPPCFW_INDIVIDUAL['related_products_title'];
                        }                      
                    }
                }
            }

            return $related_prducts_title;
        }
        public function is_enabled(){
            $enabled=0;
            if(SPPCFW_PRO_ACTIVE){
                $enabled=1;
            }
            return $enabled;
        }
    }

    new Sppcfw_Frontend_Related_Product_Title();
}







