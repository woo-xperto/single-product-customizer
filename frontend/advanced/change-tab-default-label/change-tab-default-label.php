<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists("Sppcfw_Frontend_Change_Tab_Default_Label")){
    
    class Sppcfw_Frontend_Change_Tab_Default_Label{

        public function __construct(){
            add_filter("woocommerce_product_tabs",[$this,"sppcfw_change_tab_default_label"], 144, 1 );
        }

        public function sppcfw_change_tab_default_label( $tabs ){

            if($this->is_enabled()===1){
               
                $sppcfw_tab_labels = $this->sppcfw_get_default_tab_titles();
                if(count($sppcfw_tab_labels)>0){
                    // phpcs:ignore
                    $tabs['description']['title'] = __( "{$sppcfw_tab_labels['discription']}","single-product-customizer");
                    // phpcs:ignore
                    $tabs['additional_information']['title'] = __( "{$sppcfw_tab_labels['additional_information']}","single-product-customizer");
                    // phpcs:ignore
                    // Only modify reviews tab if reviews are enabled
                    if ('yes' === get_option('woocommerce_enable_reviews')) {
                        // phpcs:ignore
                        $tabs['reviews']['title'] = __("{$sppcfw_tab_labels['reviews']}", "single-product-customizer");
                    }
                }
            }
            
            
            
            return $tabs;
        }

        public function sppcfw_get_default_tab_titles(){
            $default_tab_titles=array();
            

            if($this->is_enabled()===1){
                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['enable_change_tab_default_label'])){
                        if($SPPCFW_INDIVIDUAL['enable_change_tab_default_label']==='on'){
                            $default_tab_titles=array(
                                'discription'=>$SPPCFW_INDIVIDUAL['description_tab_label'],
                                'additional_information'=>$SPPCFW_INDIVIDUAL['additional_information_tab_label'],
                                'reviews'=>$SPPCFW_INDIVIDUAL['review_tab_label']
                            );

                            return $default_tab_titles;
                        }                      
                    }
                }

                if(sppcfw_if_category_based_customization_enabled()===1){
                    $product_cat=sppcfw_get_product_category_id();    
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['enable_change_tab_default_label'])){
                            if($sppcfw_cat['enable_change_tab_default_label']==='on'){
                                $default_tab_titles=array(
                                    'discription'=>$sppcfw_cat['description_tab_label'],
                                    'additional_information'=>$sppcfw_cat['additional_information_tab_label'],
                                    'reviews'=>$sppcfw_cat['review_tab_label']
                                );
    
                                return $default_tab_titles;
                            }
                        }
                    }
                }

                if(isset(SPPCFW_ADVANCED['enable_change_tab_default_label'])){
                    if(SPPCFW_ADVANCED['enable_change_tab_default_label']==='on'){
                        $default_tab_titles=array(
                            'discription'=>SPPCFW_ADVANCED['description_tab_label'],
                            'additional_information'=>SPPCFW_ADVANCED['additional_information_tab_label'],
                            'reviews'=>SPPCFW_ADVANCED['review_tab_label']
                        );

                        return $default_tab_titles;
                    }
                }
                

            }

            return $default_tab_titles;
        }

        public function is_enabled(){
            $enabled=0;
            if(sppcfw_is_pro_active()){
                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['enable_change_tab_default_label'])){
                        if($SPPCFW_INDIVIDUAL['enable_change_tab_default_label']==='on'){
                            return 1;
                        }else{
                            return 0;
                        }                       
                    }
                }

                if(sppcfw_if_category_based_customization_enabled()===1){
                    $product_cat=sppcfw_get_product_category_id();    
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        if(isset($sppcfw_cat['enable_change_tab_default_label'])){
                            if($sppcfw_cat['enable_change_tab_default_label']==='on'){
                                return 1;
                            }
                        }
                    }
                    return $enabled;
                }
            }

            if(isset(SPPCFW_ADVANCED['enable_change_tab_default_label'])){
                if(SPPCFW_ADVANCED['enable_change_tab_default_label']==='on'){
                    $enabled=1;
                }
            }

            return $enabled;
        }
    }

    new Sppcfw_Frontend_Change_Tab_Default_Label();
}




