<?php
if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists("Sppcfw_Frontend_Enable_Custom_Message")){
    class Sppcfw_Frontend_Enable_Custom_Message{
        private $sppcfw_custom_message;
        public function __construct(){

            add_action('wp',[$this,'initialization']);
            
        }

        public function initialization(){
            $this->sppcfw_custom_message = $this->sppcfw_get_custom_message();
            if(count($this->sppcfw_custom_message)>0){
                if(!empty($this->sppcfw_custom_message['hook'])){
                    add_action($this->sppcfw_custom_message['hook'] ,[$this, "sppcfw_display_custom_message"],10);
                }
            }
        }

        public function sppcfw_display_custom_message(){
            $sppcfw_custom_meassage = sprintf("<div id='sppcfw_custom_msg' class='sppcfw_custom_message'> %s </div>", $this->sppcfw_custom_message['message']);

            $allowed_html = array(
				'div' => array(
					'id' => array(),
					'class' => array(),
				),
			);

            echo wp_kses($sppcfw_custom_meassage, $allowed_html);        
        }

        public function sppcfw_get_custom_message(){
            $sppcfw_custom_message=array();
            if(SPPCFW_PRO_ACTIVE){
                // check in product level
                if(sppcfw_if_product_based_customization_enabled()===1){
                    
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['enable_custom_message'])){
                        if($SPPCFW_INDIVIDUAL['enable_custom_message']==='on'){
                            $sppcfw_custom_message['hook']=$SPPCFW_INDIVIDUAL['custom_message_display_hook'];
                            $sppcfw_custom_message['message']=$SPPCFW_INDIVIDUAL['custom_message_text'];

                            return $sppcfw_custom_message;
                        }                      
                    }
                }

                // check in category level
                
                if(sppcfw_if_category_based_customization_enabled()===1){

                    $product_cat=sppcfw_get_product_category_id();
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['enable_custom_message'])){
                            if($sppcfw_cat['enable_custom_message']==='on'){
                                $sppcfw_custom_message['hook']=$sppcfw_cat['custom_message_display_hook'];
                                $sppcfw_custom_message['message']=$sppcfw_cat['custom_message_text'];

                                return $sppcfw_custom_message;
                            }
                        }
                    }
                }

                if(isset(SPPCFW_ADVANCED['enable_custom_message'])){
                    if(SPPCFW_ADVANCED['enable_custom_message']==='on'){
                        
                        $sppcfw_custom_message['hook']=SPPCFW_ADVANCED['custom_message_display_hook'];
                        $sppcfw_custom_message['message']=SPPCFW_ADVANCED['custom_message_text'];

                        return $sppcfw_custom_message;
                    }
                }

            }
            return $sppcfw_custom_message;
        }

        public function is_enabled(){
            $enabled=0;
            if(SPPCFW_PRO_ACTIVE){
                // check in product level
                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['enable_custom_message'])){
                        if($SPPCFW_INDIVIDUAL['enable_custom_message']==='on'){
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
                        
                        if(isset($sppcfw_cat['enable_custom_message'])){
                            if($sppcfw_cat['enable_custom_message']==='on'){
                                $enabled=1;
                            }
                        }
                    }

                    return $enabled;
                }

                if(isset(SPPCFW_ADVANCED['enable_custom_message'])){
                    if(SPPCFW_ADVANCED['enable_custom_message']==='on'){
                        $enabled=1;
                    }
                }

            }
            return $enabled;
        }
    }
    new Sppcfw_Frontend_Enable_Custom_Message();
}