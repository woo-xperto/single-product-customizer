<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if( !class_exists( 'Sppcfw_backend_master' )){
    class Sppcfw_backend_master {
        public function __construct(){
            
            add_action("admin_enqueue_scripts",[$this,"sppcfw_add_admin_assets"],10,1);
			add_action( 'wp_enqueue_scripts', array( $this, 'sppcfw_quick_checkout_frontend_assets' ) );
        }

        /* Register Admin assets*/
        public function sppcfw_add_admin_assets($hook){
			global $sppcfw_available_hooks;
            wp_enqueue_script( 'jquery' );

            // phpcs:ignore
			wp_enqueue_script('iris',admin_url( 'js/iris.min.js' ),array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch','jquery' ),false,1);
			wp_enqueue_script(
				'sppcfw_backend-deparam-js',
				plugin_dir_url(__FILE__).'resources/js/jquery-deparam.js',
				array('jquery' ),
				SPPCFW_VERSION,
				1
			);
			wp_enqueue_script(
				'sppcfw_backend-resource-js',
				plugin_dir_url(__FILE__).'resources/js/backend.js',
				array('jquery' ),
				(SPPCFW_DEV?time():SPPCFW_VERSION),
				1
			);
            $sppcfw_custom_message_saved = isset(SPPCFW_ADVANCED['custom_message_display_hook'])
                ? trim(SPPCFW_ADVANCED['custom_message_display_hook'])
                : '';
            $sppcfw_variation_table_saved = isset(SPPCFW_ADVANCED['variation_table_display_hook'])
                ? trim(SPPCFW_ADVANCED['variation_table_display_hook'])
                : '';
			wp_localize_script( 'sppcfw_backend-resource-js', 'sppcfw_settings',
				array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'sppcfw_basic' => SPPCFW_BASIC,
					'sppcfw_advanced' => SPPCFW_ADVANCED,
					'sppcfw_wc_action_hooks'=>$sppcfw_available_hooks,
                    'custom_message_display_hook_dashboard' => $sppcfw_custom_message_saved,
                    'variation_table_display_hook_dashboard' => $sppcfw_variation_table_saved,
                    'logoUrl' => plugin_dir_url(__FILE__) . 'resources/images/logo.png',
                    'is_pro_active' => sppcfw_is_pro_active()
				)
			);
			// Media Uploader.
			wp_enqueue_media();

			wp_enqueue_style(
				'sppcfw_backend-resource-css',
				plugin_dir_url(__FILE__).'resources/css/backend.css',
				null,
				(SPPCFW_DEV?time():SPPCFW_VERSION),
				'all'
			);
        }

		public function sppcfw_quick_checkout_frontend_assets (){

			wp_enqueue_script(
				'sppcfw_template_quick_checkout_js',
				SPPCFW_DIR_URL.'frontend/Enable-Quick-Checkout/assets/js/quick-checkout.js',
				array('jquery' ),
				(SPPCFW_DEV?time():SPPCFW_VERSION),
				1
			);

		}

    } // Sppcfw_backend_master class end
} // Sppcfw_backend_master class checking end



new Sppcfw_backend_master();

include __DIR__ . '/backend-settings-page.php';
include __DIR__ . '/backend-variable-switcher/backend-variable-switcher.php';
include __DIR__ . '/ajax-handlers.php';
