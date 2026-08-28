<?php
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists( 'Sppcfw_Frontend_Ajax_Add_To_Cart' )){
    class Sppcfw_Frontend_Ajax_Add_To_Cart {
        public function __construct(){
            add_action("wp_enqueue_scripts",[$this,"sppcfw_add_assets"]);
        }

        /* Register assets*/
        public function sppcfw_add_assets(){

            if($this->is_enabled()===1 && sppcfw_is_singular()){
                // sppcfw-ajax-add-to-cart-js.
                wp_enqueue_script(
                    'sppcfw-ajax-add-to-cart-js',
                    plugin_dir_url(__FILE__).'ajax-add-to-cart.js',
                    array( 'jquery'),
                    true,
                    SPPCFW_VERSION
                );
                wp_localize_script( 'sppcfw-ajax-add-to-cart-js', 'sppcfw_ajax_add_to_cart',
                    array( 
                        'ajaxurl' => admin_url( 'admin-ajax.php' )
                    )
                );
            }
            
        }

        public function is_enabled(){
            $enabled=0;
            if(sppcfw_is_pro_active()){
                // check in product level
                if(sppcfw_if_product_based_customization_enabled()===1){
                    global $SPPCFW_INDIVIDUAL;
                    if(isset($SPPCFW_INDIVIDUAL['enable_ajax_add_to_cart'])){
                        if($SPPCFW_INDIVIDUAL['enable_ajax_add_to_cart']==='on'){
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
                        
                        if(isset($sppcfw_cat['enable_ajax_add_to_cart'])){
                            if($sppcfw_cat['enable_ajax_add_to_cart']==='on'){
                                return 1;
                            }
                        }
                    }
                    return $enabled;
                }
            }

            if(isset(SPPCFW_ADVANCED['enable_ajax_add_to_cart'])){
                if(SPPCFW_ADVANCED['enable_ajax_add_to_cart']==='on'){
                    $enabled=1;
                }
            }

            return $enabled;
        }


    } // Sppcfw_frontend_ajax_add_to_cart class end

    new Sppcfw_Frontend_Ajax_Add_To_Cart();
} // Sppcfw_frontend_ajax_add_to_cart class checking end




function sppcfw_ajax_add_to_cart(){
    // Ensure WooCommerce cart is available.
    if ( ! function_exists( 'WC' ) ) {
        wp_die();
    }

    WC()->initialize_cart();

    // Support the default WooCommerce form field names as well as this plugin's JS modifications.
    $product_id = 0;
    if ( isset( $_POST['product_id'] ) ) {
        $product_id = absint( $_POST['product_id'] );
    } elseif ( isset( $_POST['add-to-cart'] ) ) {
        // WooCommerce uses `add-to-cart` as the product id for single add-to-cart forms.
        $product_id = absint( $_POST['add-to-cart'] );
    }

    $variation_id = 0;
    if ( isset( $_POST['variation_id'] ) ) {
        $variation_id = absint( $_POST['variation_id'] );
    }

    $quantity = 1;
    if ( isset( $_POST['quantity'] ) ) {
        $quantity = max( 1, absint( $_POST['quantity'] ) );
    }

    if ( empty( $product_id ) ) {
        if ( function_exists( 'wc_add_notice' ) ) {
            wc_add_notice( __( 'Please select product options and try again.', 'single-product-customizer' ), 'error' );
        }
        wc_print_notices();
        wp_die();
    }

    WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );

    wc_print_notices();
    wp_die();
}

add_action( 'wp_ajax_sppcfw_ajax_add_to_cart', 'sppcfw_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_sppcfw_ajax_add_to_cart','sppcfw_ajax_add_to_cart');


add_filter('option_woocommerce_cart_redirect_after_add', function($option_value){
    if(isset($_POST['action'])){
        if($_POST['action']==='sppcfw_ajax_add_to_cart'){
            return 'no';
        }
    }

    return $option_value;
});