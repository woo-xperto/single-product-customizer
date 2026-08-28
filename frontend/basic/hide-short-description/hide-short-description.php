<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists("Sppcfw_Frontend_Hide_Short_Description")){
    class Sppcfw_Frontend_Hide_Short_Description{
        public function __construct(){
            // For traditional themes - single product
            add_action("woocommerce_before_single_product_summary", [$this, "sppcfw_hide_short_description_single"], 1);
            
            // For traditional themes - shop/archive pages
            add_action("woocommerce_before_shop_loop_item_title", [$this, "sppcfw_hide_short_description_loop"], 1);
            
            // For block themes
            add_action("render_block", [$this, "sppcfw_remove_short_description_in_block_themes"], 99, 2);
            
            // Filter to remove short description content
            add_filter('woocommerce_short_description', [$this, 'sppcfw_filter_short_description'], 10, 1);
            
            // Add CSS for fallback
            add_action('wp_footer', [$this, 'sppcfw_hide_woocommerce_short_description_css'], 99);
            
            // Add body class for CSS targeting
            add_filter('body_class', [$this, 'sppcfw_add_body_class_for_hide_short_description']);
        }
        
        public function sppcfw_hide_short_description_single(){
            if($this->is_enabled() === 1){
                // Remove from single product summary
                remove_action("woocommerce_single_product_summary", "woocommerce_template_single_excerpt", 20);
                
                // Also try to remove from other possible locations
                add_filter('woocommerce_product_short_description', '__return_empty_string', 999);
            }
        }
        
        public function sppcfw_hide_short_description_loop(){
            if($this->is_enabled() === 1){
                // Remove from shop/archive pages if any theme adds it there
                remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_single_excerpt', 5);
            }
        }

        // Remove the short description block for block themes
        public function sppcfw_remove_short_description_in_block_themes($block_content, $block) {
            if ($this->is_enabled() === 1) {
                if (isset($block['blockName']) && in_array($block['blockName'], [
                    'woocommerce/product-summary',
                    'core/post-excerpt',
                    'woocommerce/product-short-description',
                    'core/paragraph' // Sometimes short description is in paragraph block
                ])) {
                    // For WooCommerce product summary block
                    if ($block['blockName'] === 'woocommerce/product-summary') {
                        // Check if this block contains short description
                        if (strpos($block_content, 'woocommerce-product-details__short-description') !== false) {
                            // Remove short description from product summary block
                            $block_content = preg_replace('/<div[^>]*class="[^"]*woocommerce-product-details__short-description[^"]*"[^>]*>.*?<\/div>/s', '', $block_content);
                        }
                        return $block_content;
                    }
                    
                    // For core post excerpt block
                    if ($block['blockName'] === 'core/post-excerpt') {
                        return ''; 
                    }
                    
                    // For WooCommerce short description block
                    if ($block['blockName'] === 'woocommerce/product-short-description') {
                        return '';
                    }
                    
                    // For paragraph blocks that might contain short description
                    if ($block['blockName'] === 'core/paragraph') {
                        // Check if this is in product context
                        global $product;
                        if (is_a($product, 'WC_Product')) {
                            $short_description = $product->get_short_description();
                            if (!empty($short_description) && strpos($block_content, $short_description) !== false) {
                                return '';
                            }
                        }
                    }
                }
            }
            return $block_content;
        }
        
        // Filter to remove short description content
        public function sppcfw_filter_short_description($description) {
            if ($this->is_enabled() === 1 && !is_admin()) {
                return '';
            }
            return $description;
        }

        // Add CSS to hide the block as a fallback
        public function sppcfw_hide_woocommerce_short_description_css() {
            if($this->is_enabled() === 1){
                ?>
                <style id="sppcfw-hide-short-description-css">
                    /* Hide short description in various locations */
                    .sppcfw-hide-short-desc .woocommerce-product-details__short-description,
                    .sppcfw-hide-short-desc .woocommerce-product-details__short-description *,
                    .sppcfw-hide-short-desc .wp-block-post-excerpt,
                    .sppcfw-hide-short-desc .wp-block-woocommerce-product-summary .woocommerce-product-details__short-description,
                    .sppcfw-hide-short-desc .woocommerce-product-short-description,
                    .sppcfw-hide-short-desc .product_meta + .description,
                    .sppcfw-hide-short-desc .product .summary > .woocommerce-product-details__short-description,
                    .sppcfw-hide-short-desc .entry-summary .woocommerce-product-details__short-description,
                    .sppcfw-hide-short-desc .woocommerce-variation-description {
                        display: none !important;
                        visibility: hidden !important;
                        height: 0 !important;
                        width: 0 !important;
                        overflow: hidden !important;
                        opacity: 0 !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        border: 0 !important;
                    }
                    
                    /* Adjust layout when short description is hidden */
                    .sppcfw-hide-short-desc .woocommerce-product-details__short-description:empty {
                        display: none !important;
                    }
                </style>
                <?php
            }
        }
        
        // Add custom body class
        public function sppcfw_add_body_class_for_hide_short_description($classes) {
            if($this->is_enabled() === 1){
                $classes[] = 'sppcfw-hide-short-desc';
            }
            return $classes;
        }

        public function is_enabled(){
            // Early return for non-product pages
            if(!is_product() && !is_shop() && !is_product_category() && !is_product_tag() && !is_product_taxonomy()){
                return 0;
            }
            
            $enabled = 0;
            
            if(SPPCFW_PRO_ACTIVE){
                // check in product level
                if(sppcfw_if_product_based_customization_enabled() === 1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['hide_short_description'])){
                        if($SPPCFW_INDIVIDUAL['hide_short_description'] === 'on'){
                           $enabled = 1;
                        }                       
                    }
                    return $enabled;
                }

                // check in category level
                if(sppcfw_if_category_based_customization_enabled() === 1){
                    $product_cat = sppcfw_get_product_category_id();
                    if($product_cat > 0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['hide_short_description'])){
                            if($sppcfw_cat['hide_short_description'] === 'on'){
                                $enabled = 1;
                            }
                        }
                    }
                    return $enabled;
                }
            }

            // Global settings
            if(isset(SPPCFW_BASIC['hide_short_description'])){
                if(SPPCFW_BASIC['hide_short_description'] === 'on'){
                    $enabled = 1;
                }
            }

            return $enabled;
        }
        
        // Optional: Display custom message instead of short description
        public function sppcfw_display_custom_message() {
            if($this->is_enabled() === 1){
                $message = '';
                
                if(SPPCFW_PRO_ACTIVE){
                    // Check for custom message in product level
                    if(sppcfw_if_product_based_customization_enabled() === 1){
                        global $SPPCFW_INDIVIDUAL;
                        if(isset($SPPCFW_INDIVIDUAL['short_description_custom_message']) && !empty($SPPCFW_INDIVIDUAL['short_description_custom_message'])){
                            $message = $SPPCFW_INDIVIDUAL['short_description_custom_message'];
                        }
                    }
                    
                    // Check for custom message in category level
                    if(empty($message) && sppcfw_if_category_based_customization_enabled() === 1){
                        $product_cat = sppcfw_get_product_category_id();
                        if($product_cat > 0){
                            $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                            if(isset($sppcfw_cat['short_description_custom_message']) && !empty($sppcfw_cat['short_description_custom_message'])){
                                $message = $sppcfw_cat['short_description_custom_message'];
                            }
                        }
                    }
                }
                
                // Check global message
                if(empty($message) && isset(SPPCFW_BASIC['short_description_custom_message']) && !empty(SPPCFW_BASIC['short_description_custom_message'])){
                    $message = SPPCFW_BASIC['short_description_custom_message'];
                }
                
                if(!empty($message)){
                    echo '<div class="sppcfw-short-desc-custom-message">' . wp_kses_post($message) . '</div>';
                }
            }
        }
    }

    new Sppcfw_Frontend_Hide_Short_Description();
    
    // Add custom message hook if needed
    add_action('woocommerce_single_product_summary', function(){
        $instance = new Sppcfw_Frontend_Hide_Short_Description();
        $instance->sppcfw_display_custom_message();
    }, 21); // After where short description would normally appear
}