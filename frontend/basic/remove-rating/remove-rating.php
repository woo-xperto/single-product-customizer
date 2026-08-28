<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists('Sppcfw_Frontend_Remove_Rating')){
    class Sppcfw_Frontend_Remove_Rating{
        public function __construct(){
            // For traditional themes (classic PHP templates)
            add_action("woocommerce_single_product_summary",[$this, "sppcwf_remove_rating"],8); 

            // For block themes and WooCommerce blocks in any theme
            add_filter('render_block', [$this, 'sppcfw_remove_reviews_in_block_themes'], 99, 2); 
            
            // Additional fix: Remove rating from WooCommerce product template parts
            add_action('wp', [$this, 'sppcfw_remove_rating_globally']);
            // Hide leftover review link elements via CSS for themes that still render them
            add_action('wp_head', [$this, 'sppcfw_hide_review_link_css']);
            // Ensure comment count shows zero on product pages to prevent "0 customer reviews" text
            add_filter('get_comments_number', [$this, 'sppcfw_zero_comments_number'], 10, 2);
        }

        public function sppcwf_remove_rating(){
            if($this->is_enabled()===1){
                remove_action("woocommerce_single_product_summary","woocommerce_template_single_rating", 10); 
            }
        }

        // Remove the reviews block for block themes AND WooCommerce blocks in any theme
        public function sppcfw_remove_reviews_in_block_themes($block_content, $block) {
            if ($this->is_enabled() === 1) {
                // Remove both product-rating and reviews blocks
                if (isset($block['blockName']) && 
                    ($block['blockName'] === 'woocommerce/product-rating' || 
                     $block['blockName'] === 'woocommerce/reviews-by-product')) {
                    return ''; 
                }
            }
            return $block_content;
        }

        // Additional method to handle WooCommerce blocks globally
        public function sppcfw_remove_rating_globally() {
            if ($this->is_enabled() === 1 && is_product()) {
                // Remove WooCommerce block-based ratings
                remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
                
                // Also filter the product data
                add_filter('woocommerce_product_get_rating_html', [$this, 'sppcfw_remove_rating_html'], 10, 3);
                
                // Remove from product data
                add_filter('woocommerce_product_get_average_rating', '__return_zero');
                add_filter('woocommerce_product_get_rating_count', '__return_zero');
                add_filter('woocommerce_product_get_review_count', '__return_zero');
            }
        }
        
        // Remove rating HTML output
        public function sppcfw_remove_rating_html($rating_html, $rating, $count) {
            if ($this->is_enabled() === 1) {
                return '';
            }
            return $rating_html;
        }

        // Output CSS in head to hide review link markup on product pages
        public function sppcfw_hide_review_link_css() {
            if ( $this->is_enabled() === 1 && is_product() ) {
                echo "<style type=\"text/css\">.woocommerce-review-link, .woocommerce-product-rating, a.woocommerce-review-link {display:none !important;} .woocommerce-review-link *{display:none !important;}</style>\n";
            }
        }

        // Force comments number to zero for products to avoid "0 customer reviews" strings
        public function sppcfw_zero_comments_number( $count, $post_id ) {
            if ( $this->is_enabled() === 1 && get_post_type( $post_id ) === 'product' ) {
                return 0;
            }
            return $count;
        }

        public function is_enabled(){
            $enabled=0;
            if(sppcfw_is_pro_active()){
                // check in product level
                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['remove_product_rating'])){
                        if($SPPCFW_INDIVIDUAL['remove_product_rating']==='on'){
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
                        
                        if(isset($sppcfw_cat['remove_product_rating'])){
                            if($sppcfw_cat['remove_product_rating']==='on'){
                                $enabled=1;
                            }
                        }
                    }
                    return $enabled;
                }
            }

            if(isset(SPPCFW_BASIC['remove_product_rating'])){
                if(SPPCFW_BASIC['remove_product_rating']==='on'){
                    $enabled=1;
                }
            }

            return $enabled;
        }
    }

    new Sppcfw_Frontend_Remove_Rating();
}