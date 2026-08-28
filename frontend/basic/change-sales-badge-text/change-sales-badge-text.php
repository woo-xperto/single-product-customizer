<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( ! class_exists('Sppcfw_Frontend_Sales_Badge_Text')){
    class Sppcfw_Frontend_Sales_Badge_Text{

        public function __construct(){
            add_filter("woocommerce_sale_flash",[ $this, "sppcfw_display_sale_badge_text"], 99,3);
        }
    
        public function sppcfw_display_sale_badge_text( $badge_text, $post, $product ){
            // Only for single product page
            if( ! is_product() ) {
                return $badge_text;
            }
            
            $sales_badge_text = $this->sppcfw_get_sales_badge_text();
            if(!empty($sales_badge_text)){
                return "<span class='sppcfw_sales_badge_text onsale'>". $sales_badge_text ."</span>";
            }else{
                return $badge_text;
            }
            
        }

        public function sppcfw_get_sales_badge_text(){
            $sale_badge_text = '';
            if(isset(SPPCFW_BASIC['sale_badge_text'])){
                if(!empty(SPPCFW_BASIC['sale_badge_text'])){
                    $sale_badge_text = SPPCFW_BASIC['sale_badge_text'];
                }
            }

            if(SPPCFW_PRO_ACTIVE){

                if(sppcfw_if_category_based_customization_enabled()===1){
                    $product_cat = sppcfw_get_product_category_id();    
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['sale_badge_text'])){
                            if(!empty($sppcfw_cat['sale_badge_text'])){
                                $sale_badge_text = $sppcfw_cat['sale_badge_text'];
                            }
                        }
                    }
                }

                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['sale_badge_text'])){
                        if(!empty($SPPCFW_INDIVIDUAL['sale_badge_text'])){
                            $sale_badge_text = $SPPCFW_INDIVIDUAL['sale_badge_text'];
                        }                      
                    }
                }
            }

            return $sale_badge_text;
        }
    }

    new Sppcfw_Frontend_Sales_Badge_Text();
}