<?php
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists("Sppcfw_Variation_Switcher")){
    
    class Sppcfw_Variation_Switcher{

        public function __construct(){
            add_action("wp_enqueue_scripts",[$this, "sppcfw_variation_switcher_assets"]);
            add_filter("woocommerce_dropdown_variation_attribute_options_html",[$this,"sppcfw_display_variation_switcher"],10,2);
        }

        public function sppcfw_variation_switcher_assets(){
            if($this->is_enabled()===1){
                wp_enqueue_script(
                    'sppcfw-variation-switcher-js',
                    plugin_dir_url(__FILE__).'variation-switcher.js',
                    array( 'jquery'),
                    SPPCFW_VERSION,
                    true
                );

                wp_enqueue_style(
                    'variation-switcher-css',
                    plugin_dir_url(__FILE__).'variation-switcher.css',
                    null,
                    SPPCFW_VERSION,
                    'all'
                );

                // ... rest of your CSS enqueues remain the same
            }           
        }

        public function is_enabled(){
            $enabled=0;
            if(SPPCFW_PRO_ACTIVE){
                if(isset(SPPCFW_ADVANCED['enable_variation_switcher'])){
                    if(SPPCFW_ADVANCED['enable_variation_switcher']==='on'){
                        $enabled=1;
                    }
                }
            }
            return $enabled;
        }

        public function sppcfw_get_attribute_type($attribute_name){
            // Check if it's a taxonomy attribute
            if (taxonomy_exists('pa_' . $attribute_name)) {
                global $wpdb;
                $table_name = $wpdb->prefix."woocommerce_attribute_taxonomies";
                // phpcs:ignore
                $result = $wpdb->get_results($wpdb->prepare("SELECT attribute_type FROM $table_name where attribute_name=%s", $attribute_name));
                
                if($result && count($result)>0 && isset($result[0]->attribute_type)){
                    return $result[0]->attribute_type;
                }
            }
            
            // For custom attributes, return default 'button' type or get from product meta
            return 'button'; // Default type for custom attributes
        }

        public function sppcfw_get_attribute_type_meta_value($slug, $taxonomy, $product_id = null){
            // For taxonomy attributes
            if (taxonomy_exists($taxonomy)) {
                $term = get_term_by('slug', $slug, $taxonomy, 'ARRAY_A');
                if(is_array($term) && isset($term["term_id"])){      
                    return get_term_meta($term["term_id"], 'webcfwc_variation_meta', true);
                }
            }
            
            // For custom attributes, try to get from product meta
            if ($product_id) {
                // You might need to adjust this based on how you store custom attribute meta
                $product = wc_get_product($product_id);
                if ($product) {
                    $attributes = $product->get_attributes();
                    $taxonomy = wc_attribute_taxonomy_name($taxonomy);
                    
                    if (isset($attributes[$taxonomy])) {
                        $attribute = $attributes[$taxonomy];
                        if ($attribute && !$attribute->is_taxonomy()) {
                            // Custom attribute - get options
                            $options = $attribute->get_options();
                            foreach ($options as $option) {
                                if (sanitize_title($option) === $slug) {
                                    // Return option name as label for custom attributes
                                    return $option;
                                }
                            }
                        }
                    }
                }
            }
            
            return '';
        }

        public function sppcfw_display_variation_switcher($html, $args)
        {
            if ($this->is_enabled() === 1) {
                $options = $args['options'];
                $product = $args['product'];
                $attribute = $args['attribute']; // pa_size, pa_color, or custom attribute slug
                
                // Get product ID for custom attribute handling
                $product_id = $product ? $product->get_id() : null;

                // Check if options array is empty
                if (empty($options) && $product && taxonomy_exists($attribute)) {
                    $terms = wc_get_product_terms($product->get_id(), $attribute, array('fields' => 'slugs'));
                    $options = $terms;
                }
                
                // Handle custom attributes
                if (empty($options) && $product && !taxonomy_exists($attribute)) {
                    $attributes = $product->get_attributes();
                    if (isset($attributes[$attribute])) {
                        $attr_obj = $attributes[$attribute];
                        if ($attr_obj && !$attr_obj->is_taxonomy()) {
                            $options = $attr_obj->get_options();
                        }
                    }
                }

                // Return original HTML if no options
                if (empty($options)) {
                    return $html;
                }

                // Determine if this is a taxonomy attribute
                $is_taxonomy = taxonomy_exists($attribute);
                $attribute_name = $is_taxonomy ? ltrim($attribute, 'pa_') : $attribute;
                $attributes_type = $this->sppcfw_get_attribute_type($attribute_name);

                $attribute_name_field = 'attribute_' . sanitize_title($attribute);
                $select = '<select id="' . esc_attr($attribute) . '" class="vairation_select" data-attribute_name="' . esc_attr($attribute_name_field) . '" name="' . esc_attr($attribute_name_field) . '">';
                $select .= '<option value="">' . esc_html__("Choose one", "single-product-customizer") . '</option>';
                $button = '';

                foreach ($options as $option) {
                    // Get option slug and label
                    $option_slug = '';
                    $option_label = '';
                    
                    if ($is_taxonomy) {
                        // Taxonomy attribute
                        $term = is_object($option) ? $option : get_term_by('slug', $option, $attribute);
                        $option_slug = is_object($term) ? $term->slug : $option;
                        $option_label = is_object($term) ? $term->name : $option;
                    } else {
                        // Custom attribute: use raw option value to match WooCommerce default
                        $option_slug = $option;
                        $option_label = $option;
                    }
                    
                    if (empty($option_slug)) continue;

                    $select .= '<option value="' . esc_attr($option_slug) . '">' . esc_html($option_label) . '</option>';
                    
                    // Get meta value for the attribute type
                    $option_meta = $this->sppcfw_get_attribute_type_meta_value($option_slug, $attribute, $product_id);

                    switch ($attributes_type) {
                        case 'color':
                            $button .= '<button data-val="' . esc_attr($option_slug) . '" class="webfwc_variation_button color" data-bg-color="' . esc_attr($option_meta) . '" type="button" data-attr="' . esc_attr($attribute) . '" title="' . esc_attr($option_label) . '"></button>';
                            break;

                        case 'icon':
                            $button .= '<button data-val="' . esc_attr($option_slug) . '" class="webfwc_variation_button icon" type="button" data-attr="' . esc_attr($attribute) . '" title="' . esc_attr($option_label) . '">
                                <i class="' . esc_attr($option_meta) . '"></i>
                            </button>';
                            break;
                        case 'image':
                            $img = '';
                            if ($option_meta > 0) {
                                $img = wp_get_attachment_image($option_meta, 'thumbnail');
                            }
                            $button .= '<button data-val="' . esc_attr($option_slug) . '" class="webfwc_variation_button image" type="button" data-attr="' . esc_attr($attribute) . '" title="' . esc_attr($option_label) . '">
                                ' . $img . '
                            </button>';
                            break;
                        default:
                            $button .= '<button data-val="' . esc_attr($option_slug) . '" class="webfwc_variation_button button" type="button" data-attr="' . esc_attr($attribute) . '">' . esc_html($option_label) . '</button>';
                    }
                }

                $select .= '</select>';

                return '<div class="cu_button_el" data-attribute="' . esc_attr($attribute) . '" data-is-taxonomy="' . ($is_taxonomy ? '1' : '0') . '">' . $select . $button . '</div>';
            }

            return $html;
        }
    }

    new Sppcfw_Variation_Switcher();
}