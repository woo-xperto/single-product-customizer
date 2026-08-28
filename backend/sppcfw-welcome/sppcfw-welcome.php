<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sppcfw_register_welcome_page() {
	add_submenu_page(
		null,
		__( 'Single Product Customizer', 'single-product-customizer' ),
		__( 'Welcome', 'single-product-customizer' ),
		'manage_options',
		'sppcfw-welcome',
		'sppcfw_welcome_page_callback'
	);
}
add_action( 'admin_menu', 'sppcfw_register_welcome_page', 99 );

add_action( 'current_screen', 'sppcfw_set_welcome_page_title' );
add_action( 'admin_head', 'sppcfw_set_welcome_page_title', 1 );
function sppcfw_set_welcome_page_title() {
	if ( isset( $_GET['page'] ) && 'sppcfw-welcome' === sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		global $title;
		if ( empty( $title ) ) {
			$title = __( 'Single Product Customizer Welcome', 'single-product-customizer' );
		}
	}
}

/**
 * Disable all admin notices on the Single Product Customizer Welcome page.
 */
function sppcfw_disable_welcome_page_notices() {

	if ( isset( $_GET['page'] ) && 'sppcfw-welcome' === sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
	}
}
add_action( 'admin_head', 'sppcfw_disable_welcome_page_notices', 1 );

function sppcfw_welcome_page_callback() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'single-product-customizer' ) );
	}

	echo '<div>';
	sppcfw_welcome_content();
	echo '</div>';
}

function sppcfw_enqueue_welcome_page_assets( $hook_suffix ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || false === strpos( $screen->id, 'sppcfw-welcome' ) ) {
		return;
	}

	wp_enqueue_style(
		'sppcfw_welcome_page_style',
		SPPCFW_DIR_URL . 'backend/assets/css/sppcfw-welcome.css',
		array(),
		SPPCFW_VERSION,
		'all'
	);
	wp_enqueue_style(
		'sppcfw-setup-notice-style',
		SPPCFW_DIR_URL . 'backend/assets/css/sppcfw-setup-notice.css',
		array(),
		SPPCFW_VERSION
	);

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script(
		'sppcfw_welcome_page_script',
		SPPCFW_DIR_URL . 'backend/assets/js/sppcfw-welcome.js',
		array( 'jquery' ),
		SPPCFW_VERSION,
		true
	);

	wp_localize_script(
		'sppcfw_welcome_page_script',
		'sppcfw_welcome_page',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'sppcfw_welcome_page_nonce' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'sppcfw_enqueue_welcome_page_assets' );

function sppcfw_welcome_content() {
	?>
	<div class="sppcfw-welcome">

		<div class="sppcfw-header">
			<h1 style="color: #ffffff">🎉 <?php esc_html_e( 'Welcome to Single Product Customizer for WooCommerce!', 'single-product-customizer' ); ?></h1>
			<p><?php esc_html_e( 'Your plugin has been successfully activated!', 'single-product-customizer' ); ?></p>
		</div>

		<div class="sppcfw-content">
			<h2 class="sppcfw-section-title"><?php esc_html_e( 'Stay updated with the latest features, security updates, tips and tricks.', 'single-product-customizer' ); ?></h2>

			<form method="post">
				<div class="sppcfw-form-group">
					<label for="sppcfw_admin_email"><?php esc_html_e( 'Email Address', 'single-product-customizer' ); ?></label>
					<input type="email"
						id="sppcfw_admin_email"
						name="sppcfw_admin_email"
						value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
						class="sppcfw-input"
						required
						placeholder="<?php esc_attr_e( 'Enter your email address', 'single-product-customizer' ); ?>">
					<p style="font-size: 12px; color: #6c757d; margin-top: 5px;">
						<?php esc_html_e( 'This email will be used for sending notifications and product updates.', 'single-product-customizer' ); ?>
					</p>
				</div>

				<div class="sppcfw-buttons">
					<button type="button"
						name="sppcfw_welcome_subscribe"
						id="sppcfw_welcome_subscribe"
						value="subscribe"
						class="sppcfw-btn sppcfw-btn-primary">
						<?php esc_html_e( 'Subscribe & Continue Setup', 'single-product-customizer' ); ?>
					</button>
					<button type="button"
						name="sppcfw_welcome_no_thanks"
						id="sppcfw_welcome_no_thanks"
						value="no_thanks"
						class="sppcfw-btn sppcfw-btn-secondary">
						<?php esc_html_e( 'Skip & Continue Setup', 'single-product-customizer' ); ?>
					</button>
					<a href="http://webcartisan.com/single-product-page-customizer/" target="_blank" class="sppcfw-btn sppcfw-btn-dashboard">
						<?php esc_html_e( 'Get Pro', 'single-product-customizer' ); ?>
					</a>
				</div>
			</form>
		</div>
		<?php if ( class_exists( 'SPPCFW_Setup_Help_Notice' ) && ! SPPCFW_Setup_Help_Notice::is_dismissed() ) : ?>
			<div class="sppcfw-content sppcfw-notice-warning" style="margin-top: 30px;">
				<?php SPPCFW_Setup_Help_Notice::render_card( false ); ?>
			</div>
		<?php endif; ?>

		<div class="sppcfw-content webcartisan-another-plugins">
			<h2 class="sppcfw-section-title"><?php esc_html_e( 'Our Other Products', 'single-product-customizer' ); ?></h2>
			<div class="sppcfw-features">
				<div class="sppcfw-feature">
					<h3><?php esc_html_e( 'Wallet Ready Gift Cards for WooCommerce', 'single-product-customizer' ); ?></h3>
					<p><?php esc_html_e( 'Deliver digital gift cards, PDF vouchers, and wallet passes with balance tracking and checkout redemption.', 'single-product-customizer' ); ?></p>
				</div>
				<div class="sppcfw-feature">
					<h3><?php esc_html_e( 'Variation Monster for WooCommerce', 'single-product-customizer' ); ?></h3>
					<p><?php esc_html_e( 'Manage all variation products including variation swatches, quick view, variation gallery, and variation table.', 'single-product-customizer' ); ?></p>
				</div>
				<div class="sppcfw-feature">
					<h3><?php esc_html_e( 'Giveaway Lottery', 'single-product-customizer' ); ?></h3>
					<p><?php esc_html_e( 'Ultimate giveaway lottery plugin for WooCommerce to run contests, raffles & campaigns.', 'single-product-customizer' ); ?></p>
				</div>
			</div>
		</div>
	</div>
	<?php
}

add_action( 'wp_ajax_sppcfw_welcome_api_call', 'sppcfw_welcome_api_call' );
function sppcfw_welcome_api_call() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'single-product-customizer' ) ) );
	}

	check_ajax_referer( 'sppcfw_welcome_page_nonce', 'nonce' );

	$admin_email = isset( $_POST['admin_email'] ) ? sanitize_email( wp_unslash( $_POST['admin_email'] ) ) : get_option( 'admin_email' );
	$site_url    = get_site_url();
	$type        = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
	$api_url     = 'https://www.webcartisan.com/wp-json/giftrocket/v1/welcome-notice';

	$data = array(
		'site_url'    => $site_url,
		'admin_email' => $admin_email,
		'type'        => $type,
		'plugin'      => 'single-product-customizer',
	);

	wp_remote_post(
		$api_url,
		array(
			'method'   => 'POST',
			'timeout'  => 5,
			'blocking' => false,
			'headers'  => array(
				'Content-Type' => 'application/json',
			),
			'body'     => wp_json_encode( $data ),
		)
	);

	update_option( 'sppcfw_welcome_page_seen', '1' );

	wp_send_json_success(
		array(
			'url' => admin_url( 'admin.php?page=sppcfw-single-product-customizer' ),
		)
	);
}
