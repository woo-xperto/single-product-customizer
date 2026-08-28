<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( ! class_exists('Sppcfw_Frontend_Variation_Table')){
    class Sppcfw_Frontend_Variation_Table{
        private $hook;
        public function __construct(){
            add_action('wp',[$this,'initialization']);
        }

        public function initialization(){
            // Only for single product page
            if( ! is_product() ) {
                return;
            }
            
            if($this->is_enabled()===1){
                
                $this->hook=$this->sppcfw_get_variation_table_show_hook();
                if(!empty($this->hook)){
                    add_action($this->hook,[$this,"sppcfw_show_variation_table"]);
                }
            }
        }

        public function sppcfw_get_variation_table_show_hook(){
            $hook='';
            if(sppcfw_is_pro_active()){
                // check in product level
                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;

                    if(isset($SPPCFW_INDIVIDUAL['variation_table_display_hook'])){
                        if(!empty($SPPCFW_INDIVIDUAL['enable_varition_table'])){
                            $hook=$SPPCFW_INDIVIDUAL['variation_table_display_hook'];  
                        }                       
                    }
                   return $hook;
                }

                // check in category level
                if(sppcfw_if_category_based_customization_enabled()===1){
                    $product_cat=sppcfw_get_product_category_id();
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['variation_table_display_hook'])){
                            if(!empty($sppcfw_cat['enable_varition_table'])){
                                $hook=$sppcfw_cat['variation_table_display_hook'];
                            }                       
                        }
                    }
                    return $hook;
                }
            }

            if(isset(SPPCFW_ADVANCED['variation_table_display_hook'])){
                if(!empty(SPPCFW_ADVANCED['enable_varition_table'])){
                    $hook=SPPCFW_ADVANCED['variation_table_display_hook'];
                }
            }

            return $hook;
        }


        public function sppcfw_entry_terms($taxonomy) {
            $terms = get_terms( array(
                'taxonomy'   => $taxonomy
            ) );
            $entry_terms='';
            if ( ! is_wp_error( $terms ) ) {
                    foreach ( $terms as $term ) {
                        $entry_terms .= $term->name . ', ';
                    }
                    $entry_terms = rtrim( $entry_terms, ', ' );
            }
            return $entry_terms;
        }

        public function sppcfw_show_variation_table(){
            global $post;

            $product = wc_get_product($post->ID);
            if( 'variable' === $product->get_type() ) {
                $variations=$product->get_children();
                $attrs=$product->get_attributes();
            
            ?>
            
            <table class="sppcfw_variation_table">
                <thead>
                    <tr>
                        <th><?php echo esc_html_e("SKU","single-product-customizer");?></th>
                        <?php
                            foreach($attrs as $attr){
                                echo '<th>'.wp_kses_post(wc_attribute_label( $attr->get_name() )).'</th>';
                            }
                        ?>
                        <th><?php echo esc_html_e("Price","single-product-customizer");?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($variations as $var){
                            $variation=wc_get_product($var);
                            $vattrs=$variation->get_attributes();
                            echo'<tr>
                            <td>'.wp_kses_post($variation->get_sku()).'</td>';
                            foreach($vattrs as $key=>$vattr){
                                if($vattr){
                                    $term=get_term_by('slug',$vattr,$key);
                                    if($term){
                                        $name=$term->name;
                                    }else{
                                        $name='';
                                    }
                                }else{
                                    $name=$this->sppcfw_entry_terms($key);
                                }
                                echo'<td>'.wp_kses_post($name).'</td>';
                            }
                            echo'<td>'.wp_kses_post($variation->get_price_html()).'</td>
                        </tr>';
                        }
                    ?>
                </tbody>
            </table>
            
            <?php
            }
        }

        public function is_enabled(){
            $enabled=0;
            if(sppcfw_is_pro_active()){
                // check in product level
                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['enable_varition_table'])){
                        if($SPPCFW_INDIVIDUAL['enable_varition_table']==='on'){
                            return 1;
                        }else{
                            return 0;
                        }                       
                    }
                }

                // check in category level
                if(sppcfw_if_category_based_customization_enabled()===1){
                    $product_cat=sppcfw_get_product_category_id();
                    if($product_cat>0){
                        $sppcfw_cat = get_term_meta($product_cat, 'sppcfw_category_based_settings', true);
                        
                        if(isset($sppcfw_cat['enable_varition_table'])){
                            if($sppcfw_cat['enable_varition_table']==='on'){
                                return 1;
                            }
                        }
                    }
                    return $enabled;
                }
            }

            if(isset(SPPCFW_ADVANCED['enable_varition_table'])){
                if(SPPCFW_ADVANCED['enable_varition_table']==='on'){
                    $enabled=1;
                }
            }

            return $enabled;
        }
    }

    new Sppcfw_Frontend_Variation_Table();
}