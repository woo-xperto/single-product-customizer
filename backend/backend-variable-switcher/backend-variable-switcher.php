<?php 
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists("Sppcfw_Backend_Variation_Switcher")){
    class Sppcfw_Backend_Variation_Switcher{

        public function __construct(){

            $sppcfw_variation_check = (isset(SPPCFW_ADVANCED["enable_variation_switcher"])?SPPCFW_ADVANCED["enable_variation_switcher"]:'');

                if( $sppcfw_variation_check == "on" && SPPCFW_PRO_ACTIVE){

                    add_filter("product_attributes_type_selector",[$this,"sppcfw_custom_attributes_form"], 100,1);
                    add_action("admin_footer",[$this,"sppcfw_attributes_all_hooks"]);

                    add_action("woocommerce_product_option_terms",[$this,"sppcfw_load_custom_type_options_meta"],100,3);

                    /*enqueue backend variable switcher js file here */

                    add_action("admin_enqueue_scripts",[$this,"sppcfw_backend_variation_assets"]);
                }

            
        }

        public function sppcfw_backend_variation_assets(){

            wp_enqueue_style(
                'backend-fontawesome-css',
                plugin_dir_url(__FILE__).'fontawesome.min.css',
                null,
                SPPCFW_VERSION,
                'all'
            );

            wp_enqueue_style(
                'backend-iconsmind-css',
                plugin_dir_url(__FILE__).'./assets/css/iconsmind.css',
                null,
                SPPCFW_VERSION,
                'all'
            );

            wp_enqueue_style(
                'backend-linea-css',
                plugin_dir_url(__FILE__).'./assets/css/fonts/svg/font/style.css',
                null,
                SPPCFW_VERSION,
                'all'
            );
            wp_enqueue_style(
                'backend-linecon-css',
                plugin_dir_url(__FILE__).'./assets/css/linecon.css',
                null,
                SPPCFW_VERSION,
                'all'
            );
            wp_enqueue_style(
                'backend-steadysets-css',
                plugin_dir_url(__FILE__).'./assets/css/steadysets.css',
                null,
                SPPCFW_VERSION,
                'all'
            );

            wp_enqueue_style(
                'backend-variable-switcher-css',
                plugin_dir_url(__FILE__).'backend-variable-switcher.css',
                null,
                SPPCFW_VERSION,
                'all'
            );

            wp_enqueue_media();


            wp_enqueue_script(
                'sppcfw-backend-variation-switcher-js',
                plugin_dir_url(__FILE__).'backend-variable-switcher.js',
                array( 'jquery'),
                true,
                SPPCFW_VERSION
            );

            wp_enqueue_script(
                'backend-variable-switcher-modal-js',
                plugin_dir_url(__FILE__).'backend-variable-switcher-modal.js',
                array( 'jquery'),
                true,
                SPPCFW_VERSION
            );

            wp_localize_script('backend-variable-switcher-modal-js', 'iconDoxAjax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'fontawesome_jsonUrl' => plugins_url('assets/data/fortawesome.json', __FILE__),
                'iconsmind_json_url' => plugins_url('assets/data/iconsmind.json', __FILE__),
                'linea_json_url' => plugins_url('assets/data/linea.json', __FILE__),
                'linecon_json_url' => plugins_url('assets/data/linecon.json', __FILE__),
                'steadysets_json_url' => plugins_url('assets/data/steadysets.json', __FILE__),
            ]);

            


        }



        /*add custom type on Attributes from */
        public function sppcfw_custom_attributes_form($custom_types){

            $custom_types["icon"] = __("Icon","single-product-customizer");
            $custom_types["color"] = __("Color","single-product-customizer");
            $custom_types["button"] = __("Button","single-product-customizer");
            $custom_types["image"] = __("Image","single-product-customizer");

            return $custom_types;
        }

        /*add custom type on Attributes from end*/

        /*Attributes from fields all hooks */

    
        public function sppcfw_attributes_all_hooks(){

            $attribute_taxonomies = wc_get_attribute_taxonomies();

            if( !empty($attribute_taxonomies)){

                foreach ( $attribute_taxonomies as $attribute ) {

                    add_action( 'pa_' . $attribute->attribute_name . '_add_form_fields',[$this,"sppcfw_taxonomy_custom_field_add"],10,1 );
                    add_action( 'pa_' . $attribute->attribute_name . '_edit_form_fields',[$this, 'sppcfw_taxonomy_custom_field_edit'],200,2 );

                    add_action( "created_pa_{$attribute->attribute_name}", [$this, "sppcfw_save_term_meta"],100,2 );
                    add_action( "edited_pa_{$attribute->attribute_name}", [$this, "sppcfw_save_term_meta"],100,2 );

                    add_filter( "manage_edit-pa_{$attribute->attribute_name}_columns",[$this,"sppcfw_display_term_column"],10,1 );
                    add_filter( "manage_pa_{$attribute->attribute_name}_custom_column", [$this,"sppcfw_display_term_content"],10,3 );
                    add_filter("manage_edit-pa_{$attribute->attribute_name}_sortable_columns",[$this,"sppcfw_color_sortable_column"] ,10 ,1 );
                }
            }

        }

        /*Add field in custom taxonomy*/

        public function sppcfw_taxonomy_custom_field_add($taxonomy){

            $attribute_id=wc_attribute_taxonomy_id_by_name($taxonomy);
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            $index='id:'.$attribute_id;
            $type='';
            if(isset($attribute_taxonomies[$index])){
                if(isset($attribute_taxonomies[$index]->attribute_type)){
                    $type=$attribute_taxonomies[$index]->attribute_type;

                }
            }
            

                if ( $type === "color"  ) {

                    ?>
                    <div class="form-field">
                
                        <label for="webcfwc_pa_color"><?php esc_html_e("Please Select Color Below","single-product-customizer");?></label>
                        <input type="color" name="webcfwc_vt_color" id="webcfwc_pa_color" class="webcfwc_pa_color" value="" />

                    </div>
                    <?php 
            
                }else if( $type ==="icon" ){

                    ?>
                    <div id="icon-dox-modal" class="icon-dox-modal" style="display:none;">
                        <div class="icon-dox-modal-content">
                            <div class="icon-dox-header">
                                <h2><?php esc_html_e("Icon Library", "single-product-customizer"); ?></h2>
                                <span class="icon-dox-close">&times;</span>
                            </div>
                            <div class="icon-dox-body">
                                <div class="icon-dox-sidebar">
                                <ul class="icon-dox-tabs">
                                    <li data-style="all" class="active">All Icons</li>
                                    <li data-style="regular">Font Awesome - Regular</li>
                                    <li data-style="solid">Font Awesome - Solid</li>
                                    <li data-style="brands">Font Awesome - Brands</li>
                                </ul>
                                </div>
                                <div class="icon-dox-main">
                                    <input type="text" class="icon-dox-search" placeholder="<?php esc_html_e("Search Icon By Name","single-product-customizer") ?>">
                                    <div class="icon-dox-icons" id="icon-dox-icons">
                            
                                    </div>
                                </div>
                            </div>
                            <div class="icon-dox-footer">
                                <button type="button" class="insert-icon-button"><?php esc_html_e("Insert", "single-product-customizer"); ?></button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <select name="webcfwc_variation_icon" id="webcfwc_variation_icon">

                            <option value=""><?php esc_html_e("Select Icon Below","single-product-customizer");?></option>

                            <option value="fontawesome"><?php esc_html_e("Fontawsome Icon","single-product-customizer");?></option>

                            <option value="iconsmind"><?php esc_html_e("Iconsmind Icon","single-product-customizer");?></option>

                            <option value="linea"><?php esc_html_e("Linea Icon","single-product-customizer");?></option>

                            <option value="linecon"><?php esc_html_e("Linecon Icon","single-product-customizer");?></option>

                            <option value="steadysets"><?php esc_html_e("Steadysets Icon","single-product-customizer");?></option>
                        </select>
                        <div id="webcfwc_icon_box">
                            <input type="text" id="webcfwc_variation_add_icon" name="webcfwc_variation_add_icon" value="" hidden="hidden"><br>
                        </div>
                    </div>

        
                    <?php 

                }else if( $type ==="image" ){

                    ?>
                        <div class="form-field term-group">
                        
                            <input type="hidden" id="webcfwc_tax_image_id" name="webcfwc_tax_image_id" class="webcfwc_custom_media_url" value="">
                            <div id="webcfwc_image_wrapper"></div>
                            <p>
                            <input type="button" class="button button-secondary webcfwc_tax_media_button" id="webcfwc_tax_media_button" name="webcfwc_tax_media_button" value="<?php esc_html_e( 'Add Image', 'single-product-customizer' ); ?>" />
                            </p>
                        </div>



                    <?php 

                }

        }

        /*edit field in custom taxonomy*/
        public  function sppcfw_taxonomy_custom_field_edit( $term, $taxonomy ){

            $attribute_id=wc_attribute_taxonomy_id_by_name($taxonomy);
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            $index='id:'.$attribute_id;
            $type='';
            if(isset($attribute_taxonomies[$index])){
                if(isset($attribute_taxonomies[$index]->attribute_type)){
                    $type=$attribute_taxonomies[$index]->attribute_type;

                }
            }

            
            /*color section */

            $webcfwc_variation_meta = get_term_meta($term->term_id, "webcfwc_variation_meta", true);
        
            if( $type === "color"){
                
                ?>
                    <tr class="form-field">
                        <th scope="row">
                            <label for="color"><?php esc_html_e("Color","single-product-customizer");?></label>
                        </th>
                        <td>
                            <input type="color" name="webcfwc_vt_color" id="webcfwc_pa_color" class="webcfwc_pa_color" value="<?php echo esc_attr($webcfwc_variation_meta); ?>" />
                        </td>
                    </tr>
                
                <?php 

            } 

            /*color section end*/

            /*icon section*/
            if($type === "icon"){

                ?>

            <div id="icon-dox-modal" class="icon-dox-modal" style="display:none;">
                    <div class="icon-dox-modal-content">
                        <div class="icon-dox-header">
                            <h2><?php esc_html_e("Icon Library", "single-product-customizer"); ?></h2>
                            <span class="icon-dox-close">&times;</span>
                        </div>
                        <div class="icon-dox-body">
                            <div class="icon-dox-sidebar">
                            <ul class="icon-dox-tabs">
                                <li data-style="all" class="active">All Icons</li>
                                <li data-style="regular">Font Awesome - Regular</li>
                                <li data-style="solid">Font Awesome - Solid</li>
                                <li data-style="brands">Font Awesome - Brands</li>
                            </ul>
                            </div>
                            <div class="icon-dox-main">
                                <input type="text" class="icon-dox-search" placeholder="<?php esc_html_e("Search Icon By Name","single-product-customizer") ?>">
                                <div class="icon-dox-icons" id="icon-dox-icons">
                        
                                </div>
                            </div>
                        </div>
                        <div class="icon-dox-footer">
                            <button type="button" class="insert-icon-button"><?php esc_html_e("Insert", "single-product-customizer"); ?></button>
                        </div>
                    </div>
                </div>

                <tr class="form-field">
                    <th scope="row">
                        <label for="color"><?php esc_html_e("Select Icon Libaray","single-product-customizer");?></label>
                    </th>
                    <td>
                        <div name="icon_dox_library" id="icon_dox_library">
                            <select name="webcfwc_variation_icon" id="webcfwc_variation_icon">

                                <option value=""><?php esc_html_e("Select Icon Below","single-product-customizer");?></option>

                                <option value="fontawesome"><?php esc_html_e("Fontawsome Icon","single-product-customizer");?></option>

                                <option value="iconsmind"><?php esc_html_e("Iconsmind Icon","single-product-customizer");?></option>

                                <option value="linea"><?php esc_html_e("Linea Icon","single-product-customizer");?></option>

                                <option value="linecon"><?php esc_html_e("Linecon Icon","single-product-customizer");?></option>

                                <option value="steadysets"><?php esc_html_e("Steadysets Icon","single-product-customizer");?></option>
                            </select>
                
                        </div>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="color"><?php esc_html_e(" Icon Class","single-product-customizer");?></label>
                    </th>
                    <td>
                    <input type="text" id="webcfwc_variation_add_icon" name="webcfwc_variation_add_icon" value="<?php echo esc_attr( $webcfwc_variation_meta );?>"><br>
                    </td>
                </tr>
            
            <?php 
            }
            /*icon section end*/

            /*image section */

            
            if( $type === "image" ){


                ?>

                <tr class="form-field">
                    <th scope="row"><?php echo esc_html("Edit Image: ","single-product-customizer");?></th>
                    <td>
                        <div class="form-field term-group">
                        
                            <input type="hidden" id="webcfwc_tax_image_id" name="webcfwc_tax_image_id" class="webcfwc_custom_media_url" value="<?php echo esc_attr( $webcfwc_variation_meta );?>">
                            <div id="webcfwc_image_wrapper">
                                <?php
                                if( $webcfwc_variation_meta ){
                                    echo wp_get_attachment_image($webcfwc_variation_meta,'thumbnail');
                                }
                                ?>
                            </div>
                            <p>
                                <input type="button" class="button button-secondary webcfwc_tax_media_button" id="webcfwc_tax_media_button" name="webcfwc_tax_media_button" value="<?php esc_html_e( 'Add Image', 'single-product-customizer' ); ?>" />
                                <!-- <input type="button" class="button button-secondary webcfwc_tax_media_remove" id="webcfwc_tax_media_remove" name="webcfwc_tax_media_remove" value="<?php // _e( 'Remove Image', 'webcfwc' ); ?>" /> -->
                            </p>
                        </div>
                    </td>
                </tr>

                <?php


            }
            /*image section */

        }

        /*save term meta */
        public function sppcfw_save_term_meta($term_id, $tt_id){
                
            /*Color Section */
            // phpcs:ignore
            if(isset( $_POST['webcfwc_vt_color']) ){
                // phpcs:ignore
                $webcfwc_variation_color = sanitize_text_field($_POST['webcfwc_vt_color']);
            
                if ($webcfwc_variation_color) {
                    $previous_color_val = get_term_meta($term_id,"webcfwc_variation_meta", true);
                    update_term_meta($term_id, "webcfwc_variation_meta", $webcfwc_variation_color, $previous_color_val, true);
                }
            }
            /*Color Section  end*/
        
            
            /*icon Section */
            // phpcs:ignore
            if(isset(  $_POST["webcfwc_variation_add_icon"] ) ){
                // phpcs:ignore
                $webcfwc_variation_add_icon = sanitize_text_field($_POST["webcfwc_variation_add_icon"]);
                if (!empty($webcfwc_variation_add_icon)) {
                    $previous_icon_class = get_term_meta( $term_id, "webcfwc_variation_meta", true);
                    update_term_meta($term_id, "webcfwc_variation_meta", $webcfwc_variation_add_icon, $previous_icon_class, true );
                }
            }

            /*icon Section */

            /*variation image section*/
            // phpcs:ignore
            if( isset( $_POST["webcfwc_tax_image_id"]  )){
                // phpcs:ignore
                $add_vaeation_img = absint($_POST["webcfwc_tax_image_id"]);
                if ($add_vaeation_img > 0) {
                    $previous_img = get_term_meta( $term_id, "webcfwc_variation_meta", true);
                    update_term_meta( $term_id,"webcfwc_variation_meta",$add_vaeation_img ,$previous_img, true);
                }
            }
            /*variation image section*/

            
            
        }

        /*display column*/

        public function sppcfw_display_term_column($columns){
            // phpcs:ignore
            $taxonomy=(isset($_POST["taxonomy"])? sanitize_text_field($_POST["taxonomy"]):(isset($_GET["taxonomy"])? sanitize_text_field($_GET["taxonomy"]):''));

            $attribute_id=wc_attribute_taxonomy_id_by_name($taxonomy);
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            $index='id:'.$attribute_id;
            $type='';
            if(isset($attribute_taxonomies[$index])){
                if(isset($attribute_taxonomies[$index]->attribute_type)){
                    $type=$attribute_taxonomies[$index]->attribute_type;

                }
            }

            if(  $type === "color" ){

                $columns["color"] = __("Color","single-product-customizer");

            }else if(  $type === "icon" ){

                $columns["icon"] = __("Icon","single-product-customizer");

            }else if( $type === "image" ){

                $columns["image"] = __("Image","single-product-customizer");
            }
    
        
            return $columns;
            
        }

        /*display term Content*/

        public function sppcfw_display_term_content($content, $column_name, $term_id ){
                
            if( $column_name === "color"){
                    
            $col_variation_meta = get_term_meta( $term_id, "webcfwc_variation_meta", true); 

                // var_dump( $col_variation_meta );

                $color_type = "color";
                $color_name = "color_taxonomy";
                $color_field_id = "color_tax";

                $color = sprintf(" <input type='%s' name='%s' value='%s' id='%s'/>" ,$color_type ,$color_name, esc_attr($col_variation_meta), $color_field_id );

                $content .= $color;
            
            }//end color  

            if( $column_name === "icon" ){

                $col_v_icon_meta = get_term_meta( $term_id, "webcfwc_variation_meta", true);

                $webcfwc_icon = sprintf("<i class='%s'></i>", $col_v_icon_meta );

                $content .= $webcfwc_icon;
            }

            if( $column_name === "image" ){
                $col_v_icon_meta = get_term_meta( $term_id, "webcfwc_variation_meta", true);

                $variation_img_display = wp_get_attachment_image( $col_v_icon_meta, "thumbnail");

                $content .= $variation_img_display;
            }


        return $content;

        }

        /*Sortable column*/

        public function sppcfw_color_sortable_column($sortable){
            // phpcs:ignore
            $taxonomy=(isset($_POST["taxonomy"])? sanitize_text_field($_POST["taxonomy"]):(isset($_GET["taxonomy"])? sanitize_text_field($_GET["taxonomy"]):''));

            $attribute_id=wc_attribute_taxonomy_id_by_name($taxonomy);
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            $index='id:'.$attribute_id;
            $type='';
            if(isset($attribute_taxonomies[$index])){
                if(isset($attribute_taxonomies[$index]->attribute_type)){
                    $type=$attribute_taxonomies[$index]->attribute_type;

                }
            }

            if($type === "color"){

                $sortable["color"] = "Color";

            }else if( $type === "icon" ){

                $sortable["icon"] = "icon";

            }else if($type === "image" ){

                $sortable["image"] = "Image"; 
            }
        
            return $sortable;
        }


        /*load custom type option meta*/

        public function sppcfw_load_custom_type_options_meta( $attribute_taxonomy, $i, $attribute){
            if ( 'select' !== $attribute_taxonomy->attribute_type && in_array( $attribute_taxonomy->attribute_type,['select','color','icon','image','button'])) {
                $attribute_orderby = ! empty( $attribute_taxonomy->attribute_orderby ) ? $attribute_taxonomy->attribute_orderby : 'name';
                ?>
                <select multiple="multiple"
                        data-minimum_input_length="0"
                        data-limit="50" data-return_id="id"
                        data-placeholder="<?php esc_attr_e( 'Select terms', 'single-product-customizer' ); ?>"
                        data-orderby="<?php echo esc_attr( $attribute_orderby ); ?>"
                        class="multiselect attribute_values wc-taxonomy-term-search"
                        name="attribute_values[<?php echo esc_attr( $i ); ?>][]"
                        data-taxonomy="<?php echo esc_attr( $attribute->get_taxonomy() ); ?>">
                    <?php
                    $selected_terms = $attribute->get_terms();
                    if ( $selected_terms ) {
                        foreach ( $selected_terms as $selected_term ) {
                            /**
                             * Filter the selected attribute term name.
                             *
                             * @since 3.4.0
                             * @param string  $name Name of selected term.
                             * @param array   $term The selected term object.
                             */
                            echo '<option value="' . esc_attr( $selected_term->term_id ) . '" selected="selected">' . esc_html( apply_filters( 'sppcfw_woocommerce_product_attribute_term_name', $selected_term->name, $selected_term ) ) . '</option>';
                        }
                    }
                    ?>
                </select>
                <button class="button plus select_all_attributes"><?php esc_html_e( 'Select all', 'single-product-customizer' ); ?></button>
                <button class="button minus select_no_attributes"><?php esc_html_e( 'Select none', 'single-product-customizer' ); ?></button>
                <button class="button fr plus add_new_attribute"><?php esc_html_e( 'Create value', 'single-product-customizer' ); ?></button>
                <?php
            }
        }


    }


    new Sppcfw_Backend_Variation_Switcher();
}
