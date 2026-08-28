<?php
if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('Sppcfw_Frontend_Remove_Product_Meta_Section')){
    class Sppcfw_Frontend_Remove_Product_Meta_Section{
        public function __construct(){
            add_action('template_redirect', [$this, 'sppcfw_init_meta_removal'], 999);
            add_filter('render_block', [$this, 'sppcfw_remove_block_meta'], 99, 2);
            add_action('wp_head', [$this, 'sppcfw_add_css']);
        }

        public function sppcfw_init_meta_removal(){
            if(!$this->is_enabled()) return;
            
            // Remove meta hooks
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
            remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_meta', 10);
            add_filter('wc_product_sku_enabled', '__return_false');
            
            // Filter content for direct HTML
            add_filter('the_content', [$this, 'sppcfw_filter_content'], 99);
        }

        public function sppcfw_filter_content($content){
            if($this->is_enabled()){
                // Remove all product meta elements
                $content = preg_replace('/<div[^>]*class="[^"]*product_meta[^"]*"[^>]*>.*?<\/div>/is', '', $content);
            }
            return $content;
        }

        public function sppcfw_add_css(){
            if($this->is_enabled()){
                echo '<style>.product_meta{display:none!important;}</style>';
            }
        }

        public function sppcfw_remove_block_meta($content, $block){
            if($this->is_enabled() && isset($block['blockName'])){
                if(strpos($block['blockName'], 'woocommerce/product-meta') !== false ||
                   strpos($block['blockName'], 'woocommerce/product-sku') !== false){
                    return '';
                }
            }
            return $content;
        }

        public function is_enabled(){
            if(!is_product() && !is_shop() && !is_product_category()) return false;
            
            if(sppcfw_is_pro_active() && sppcfw_if_product_based_customization_enabled() === 1){
                global $SPPCFW_INDIVIDUAL;
                return isset($SPPCFW_INDIVIDUAL['remove_product_meta']) && 
                       $SPPCFW_INDIVIDUAL['remove_product_meta'] === 'on';
            }

            return isset(SPPCFW_BASIC['remove_product_meta']) && 
                   SPPCFW_BASIC['remove_product_meta'] === 'on';
        }
    }
    new Sppcfw_Frontend_Remove_Product_Meta_Section();
}