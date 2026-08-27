<?php 
/**
* Plugin Name:       Product Page Customizer for WooCommerce
* Plugin URI:        http://webcartisan.com/single-product-page-customizer/
* Description:       An esential helper tool for woocommerce single product page. Borderless freedom to customize single product page. 
* Requires at least:  6.5
* Requires PHP:       8.1
* Tested up to:       7.1
* Version:           1.0.8
* Author:            WebCartisan
* Author URI:        http://webcartisan.com/
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       single-product-customizer
* Domain Path:       /languages
*/


if( ! defined( 'ABSPATH' ) ){
    exit();
} 

/**
 * Load translations on init to avoid early textdomain loading warnings.
 */
add_action( 'init', 'sppcfw_load_textdomain' );
function sppcfw_load_textdomain() {
    load_plugin_textdomain( 'single-product-customizer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}


add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'sppcfw_plugin_action_links' );
function sppcfw_plugin_action_links( $links ) {
    $action_links = array(
        'settings' => '<a href="' . admin_url( 'admin.php?page=sppcfw-single-product-customizer' ) . '" aria-label="' . esc_attr__( 'View Single Product Customizer Settings', 'single-product-customizer' ) . '">' . esc_html__( 'Settings', 'single-product-customizer' ) . '</a>',
    );

    return array_merge( $action_links, $links );
}



add_filter('plugin_row_meta', 'sppcfw_plugin_support_link', 10, 2);

/**
 * Add a support link to the plugin details.
 *
 * @param $links, $file
 * @since 1.0.0
 */
function sppcfw_plugin_support_link($links, $file) {
    if ($file === plugin_basename(__FILE__)) {
        $support_link = '<a href="https://wa.me/01926167151" target="_blank" style="color: #0073aa;">' . __('Support', 'single-product-customizer') . '</a>';
        $dock_link    = '<a href="https://webcartisan.com/docs/single-product-customizer-for-woocommerce/" target="_blank" style="color: #0073aa;">' . __('Docs', 'single-product-customizer') . '</a>';
        $links[] = $support_link;
        $links[] = $dock_link;
    }
    return $links;
}

if ( ! defined( 'SPPCFW_PLUGIN_FILE' ) ) {
	define( 'SPPCFW_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'SPPCFW_DEV' ) ) {
	define( 'SPPCFW_DEV', 1 );
}

if ( ! defined( 'SPPCFW_VERSION' ) ) {
	define( 'SPPCFW_VERSION', '1.0.8' );
}

if ( ! defined( 'SPPCFW_DIR_URL' ) ) {
	define( 'SPPCFW_DIR_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'SPPCFW_DIR_PATH' ) ) {
	define( 'SPPCFW_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

$sppcfw_basic = get_option( 'sppcfw_basic' );
if ( ! defined( 'SPPCFW_BASIC' ) ) {
	define( 'SPPCFW_BASIC', $sppcfw_basic ); // basic settings
}

$sppcfw_advanced = get_option( 'sppcfw_advanced' );
if ( ! defined( 'SPPCFW_ADVANCED' ) ) {
	define( 'SPPCFW_ADVANCED', $sppcfw_advanced ); // advanced settings
}

if ( ! defined( 'SPPCFW_PRO_ACTIVE' ) ) {
	define( 'SPPCFW_PRO_ACTIVE', false );
}

/**
 * Helper function to check if Pro version is active.
 *
 * @return bool
 */
function sppcfw_is_pro_active() {
	return ( defined( 'SPPCFW_PRO_ACTIVE' ) && SPPCFW_PRO_ACTIVE ) || ( function_exists( 'sppcfw_pro_license_is_active' ) && sppcfw_pro_license_is_active() );
}

$SPPCFW_INDIVIDUAL=array();// global var
add_action('wp',function(){
    if(!is_admin()){
        if(is_singular( 'product' )){
            global $post;
            $product_id=$post->ID;
            global $SPPCFW_INDIVIDUAL;
            $sppcfw_individual_product_settings=get_post_meta($product_id,'sppcfw_product',true);
            $SPPCFW_INDIVIDUAL=$sppcfw_individual_product_settings;
        }
    }
});


require_once plugin_dir_path( __FILE__ ) . 'includes/sppcfw-admin-menu.php';
require_once plugin_dir_path( __FILE__ ) . 'backend/sppcfw-welcome/sppcfw-welcome.php';
require_once plugin_dir_path( __FILE__ ) . 'backend/sppcfw-welcome/sppcfw-discount-notice.php';
require_once plugin_dir_path( __FILE__ ) . 'backend/sppcfw-welcome/sppcfw-setup-notice.php';

/**
 * Check if WooCommerce is active (single-site + multisite).
 *
 * @return bool
 */
function sppcfw_is_woocommerce_active() {
    if ( class_exists( 'WooCommerce' ) ) {
        return true;
    }
    $active_plugins = (array) get_option( 'active_plugins', array() );
    if ( in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) ) {
        return true;
    }
    if ( is_multisite() ) {
        $network_active_plugins = array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) );
        if ( in_array( 'woocommerce/woocommerce.php', $network_active_plugins, true ) ) {
            return true;
        }
    }
    return false;
}

/**
 * Show admin notice when WooCommerce is missing.
 */
function sppcfw_missing_woocommerce_notice() {
    if ( sppcfw_is_woocommerce_active() ) {
        return;
    }
    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }
    echo '<div class="notice notice-warning"><p><strong>'
        . esc_html__( 'Single Product Customizer requires WooCommerce to be installed and activated to use the plugin\'s full functionality.', 'single-product-customizer' )
        . '</strong></p></div>';
}
add_action( 'admin_notices', 'sppcfw_missing_woocommerce_notice' );

/**
 * Register admin menu shell when WooCommerce is not active.
 */
function sppcfw_register_admin_menu_without_woocommerce() {
    if ( sppcfw_is_woocommerce_active() ) {
        return;
    }

    $parent_slug = 'sppcfw-single-product-customizer';
    $menu_title  = __( 'Single Product Customizer', 'single-product-customizer' );

    add_menu_page(
        $menu_title,
        $menu_title,
        'manage_options',
        $parent_slug,
        'sppcfw_render_woocommerce_required_admin_page',
        'dashicons-admin-customizer',
        56
    );
}
add_action( 'admin_menu', 'sppcfw_register_admin_menu_without_woocommerce' );

/**
 * Admin page shown when WooCommerce is missing.
 */
function sppcfw_render_woocommerce_required_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to access this page.', 'single-product-customizer' ) );
    }

    $install_url = admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( __( 'Single Product Customizer', 'single-product-customizer' ) ); ?></h1>
        <div class="notice notice-warning">
            <p>
                <strong>
                    <?php
                    esc_html_e(
                        'Single Product Customizer requires WooCommerce for full functionality. Please install and activate WooCommerce to customize single product pages.',
                        'single-product-customizer'
                    );
                    ?>
                </strong>
            </p>
            <?php if ( current_user_can( 'install_plugins' ) ) : ?>
                <p>
                    <a class="button button-primary" href="<?php echo esc_url( $install_url ); ?>">
                        <?php esc_html_e( 'Install WooCommerce', 'single-product-customizer' ); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

require_once plugin_dir_path( __FILE__ ) . 'backend/classes/class-sppcfw-pro-admin-placeholders.php';
SPPCFW_Pro_Admin_Placeholders::init();

if ( sppcfw_is_woocommerce_active() ) {
    /**
     * Include plugin files after translations are loaded to avoid early __() calls.
     */
    add_action( 'init', 'sppcfw_load_includes', 20 );
    function sppcfw_load_includes() {
        include_once SPPCFW_DIR_PATH . 'backend/resources/hook-list.php';
        include_once SPPCFW_DIR_PATH . 'common/util.php';
        include_once SPPCFW_DIR_PATH . 'backend/backend-master.php';
        include_once SPPCFW_DIR_PATH . 'frontend/frontend-master.php';
    }
}

register_activation_hook( __FILE__, 'sppcfw_plugin_activate' );
function sppcfw_plugin_activate() {
    $now = strtotime( 'now' );
    if ( ! get_option( 'sppcfw_myplugin_activation_date' ) ) {
        add_option( 'sppcfw_myplugin_activation_date', $now );
    }

    if ( get_option( 'sppcfw_welcome_page_seen' ) ) {
        set_transient( 'sppcfw_activation_redirect_target', 'dashboard', 60 );
    } else {
        update_option( 'sppcfw_welcome_page_seen', '1' );
        set_transient( 'sppcfw_activation_redirect_target', 'welcome', 60 );
    }
}

add_action( 'admin_init', 'sppcfw_maybe_redirect_after_activation' );
function sppcfw_maybe_redirect_after_activation() {
    $redirect_target = get_transient( 'sppcfw_activation_redirect_target' );
    if ( ! $redirect_target ) {
        return;
    }

    delete_transient( 'sppcfw_activation_redirect_target' );

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $redirect_url = 'welcome' === $redirect_target
        ? add_query_arg( array( 'page' => 'sppcfw-welcome' ), admin_url( 'admin.php' ) )
        : admin_url( 'admin.php?page=sppcfw-single-product-customizer' );

    wp_safe_redirect( $redirect_url );
    exit;
}

/**
* Check date on admin initiation and add to admin notice if it was over 10 days ago.
*
* @link   https://www.winwar.co.uk/2014/10/ask-wordpress-plugin-reviews-week/?utm_source=codesnippet
*
* @return void
*/
function sppcfw_check_installation_date() {
 
    $install_date     = get_option( 'sppcfw_myplugin_activation_date' );
    $review_dismissed = get_option( 'sppcfw_review_dismissed' );
    $past_date        = strtotime( '-7 days' );

    if ( $past_date == $install_date && !$review_dismissed ) {

        add_action( 'admin_notices', 'sppcfw_display_admin_notice' );
 
    }
 
}
add_action( 'admin_init', 'sppcfw_check_installation_date' );
 
/**
* Display Admin Notice, asking for a review
*
* @return null
*/


function sppcfw_display_admin_notice() {

    // Review URL - Change to the URL of your plugin on WordPress.org
    $review_url = esc_url('https://wordpress.org/plugins/single-product-customizer/');
    $dismiss_url = esc_url('https://www.webcartisan.com/single-product-page-customizer/');
    // Plugin image URL
    $logo_url = esc_url(plugin_dir_url(__FILE__) . 'backend/resources/images/logo.png');

    // Escaping message for proper display with a line break
    $message = esc_html__('Hello! Seems like you have used Single Product Customizer for this website — Thanks a lot!', 'single-product-customizer') .
        esc_html__(' Could you please do us a big favor and give it a 5-star rating on WordPress? This would boost our motivation and help other users make a comfortable decision while choosing the Single Product Customizer.', 'single-product-customizer');

    echo '<div id="sppcfw-review-notice" class="updated sppcfw_sreview_notices">';
    // phpcs:ignore
    printf('<span class="logo"><img src="%s" alt="%s"/></span> <ul class="right_contes"><li>%s</li> <li class="button_wrap">
        <a href="%s" id="sppcfw-dismiss-btn" target="_blank">%s</a> 
        <button type="button" id="sppcfw-dismiss-btn-already-did"><i class="fas fa-check-circle"></i> %s</button> 
        <a href="%s" target="_blank"><i class="fas fa-life-ring"></i> %s</a>
        <button type="button" id="sppcfw-not-good-enough-btn"><i class="fas fa-thumbs-down"></i> %s</button>',
        esc_attr($logo_url),
        esc_attr__('Plugin Logo', 'single-product-customizer'),
        esc_attr($message),
        esc_attr($review_url),
        esc_html__('Ok, you deserved it', 'single-product-customizer'),
        esc_html__('I already did', 'single-product-customizer'),
        esc_attr($dismiss_url),
        esc_html__('I need support', 'single-product-customizer'),
        esc_html__('No, not good enough', 'single-product-customizer')
    );

    echo '</div>';
}


/**
* Set the plugin to no longer bug users if user asks not to be.
*
* @return null
*/
function sppcfw_set_no_review() {

    $sppcfw_review_dismissed  = "";
    // phpcs:ignore
    if ( isset( $_GET['dismiss-review'] ) ) {
        // phpcs:ignore
        $sppcfw_review_dismissed = sanitize_text_field( $_GET['dismiss-review'] );
    }

    if ( intval($sppcfw_review_dismissed) === 1 ) {

        add_option( 'sppcfw_review_dismissed', TRUE );

    }

} add_action( 'admin_init', 'sppcfw_set_no_review', 5 );


function sppcfw_enqueue_scripts() {
    wp_enqueue_script(
        'sppcfw_backend-notices-js',
        plugin_dir_url(__FILE__) . 'backend/resources/js/admin-notices.js',
        ['jquery'],
        '1.0',
        true
    );

    // Localize script with nonce and AJAX URL
    wp_localize_script( 'sppcfw_backend-notices-js', 'sppcfw_obj', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'sppcfw_nonce' )
    ));
}
add_action( 'admin_enqueue_scripts', 'sppcfw_enqueue_scripts' );

// Handle AJAX request dismiss review notice
add_action('wp_ajax_sppcfw_dismiss_review_notice', 'sppcfw_dismiss_review_notice_callback');
function sppcfw_dismiss_review_notice_callback() {

    check_ajax_referer('sppcfw_nonce', 'nonce');

    update_option('sppcfw_review_dismissed', true);

    wp_send_json_success(['message' => 'Notice dismissed successfully.']);
}

// Handle AJAX request and send notification to the admin email
add_action('wp_ajax_sppcfw_send_admin_notification', 'sppcfw_send_admin_notification_callback');

function sppcfw_send_admin_notification_callback() {
    // Check nonce for security
    check_ajax_referer('sppcfw_nonce', 'nonce');

    // phpcs:ignore
    $feedback_message = sanitize_text_field($_POST['message'] ?? '');

    if (empty($feedback_message)) {
        wp_send_json_error(['message' => 'Message is empty.']);
    }

    // Set email recipient, subject, and message
    $admin_email = get_option('admin_email');
    $to_email = 'hello@webcartisan.com';
    $subject = 'Plugin Feedback: Single Product Customizer';
    $email_message = 'A user provided the following feedback: ' . $feedback_message;

    // Set headers for "From" to be the admin email
    $headers = [
        'From: ' . get_bloginfo('name') . ' <' . $admin_email . '>',
        'Content-Type: text/html; charset=UTF-8'
    ];

    // Send the email
    $mail_sent = wp_mail($to_email, $subject, $email_message, $headers);

    if ($mail_sent) {
        wp_send_json_success(['message' => 'Feedback sent successfully.']);
    } else {
        wp_send_json_error(['message' => 'Failed to send feedback email.']);
    }

    wp_die();
}


/*******************
 * Start Add YouTube help link Function
 *******************/

function wodgc_help_youtube_link($link){
    ?>
    <span class="wwodgc_youtube-link">
        <a href="<?php echo esc_attr($link); ?>"
           target="_blank"
           style="text-decoration: none;">
            <span class="dashicons dashicons-youtube" style="color: #FF0000;"></span>
        </a>
    </span>
    <?php
}

/*******************
 * End Add YouTube help link Function
 *******************/


if ( ! class_exists( 'Appsero\Client' ) ) {
    require_once __DIR__ . '/vendor/appsero/client/src/Client.php';
}


/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_single_product_customizer() {

    if ( ! class_exists( 'Appsero\Client' ) ) {
      require_once __DIR__ . '/appsero/src/Client.php';
    }

    $client = new Appsero\Client( '62476434-948c-4350-9373-3de33a87152b', 'Single Product Page Customizer with Variation Swatches for WooCommerce', __FILE__ );

    // Active insights
    $client->insights()->init();

}

appsero_init_tracker_single_product_customizer();
