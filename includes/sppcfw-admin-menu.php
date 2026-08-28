<?php
/**
 * Admin menu slug and URLs for Single Product Customizer screens.
 *
 * @package Single_Product_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'sppcfw_parent_menu_slug' ) ) {
	/**
	 * Top-level admin menu slug.
	 *
	 * @return string
	 */
	function sppcfw_parent_menu_slug() {
		return 'sppcfw-single-product-customizer';
	}
}

if ( ! function_exists( 'sppcfw_admin_url' ) ) {
	/**
	 * Build an admin.php URL for Single Product Customizer screen.
	 *
	 * @param array<string, string> $query_args Merged into the query string.
	 * @return string
	 */
	function sppcfw_admin_url( array $query_args = [] ) {
		if ( ! isset( $query_args['page'] ) ) {
			$query_args['page'] = sppcfw_parent_menu_slug();
		}

		return add_query_arg( $query_args, admin_url( 'admin.php' ) );
	}
}

if ( ! function_exists( 'sppcfw_admin_capability' ) ) {
	/**
	 * Capability required for Single Product Customizer admin screens.
	 *
	 * Uses manage_woocommerce when WooCommerce is active; otherwise manage_options.
	 *
	 * @return string
	 */
	function sppcfw_admin_capability() {
		if ( function_exists( 'sppcfw_is_woocommerce_active' ) && sppcfw_is_woocommerce_active() ) {
			return 'manage_woocommerce';
		}

		return 'manage_options';
	}
}

if ( ! function_exists( 'sppcfw_redirect_legacy_admin_url' ) ) {
	/**
	 * Redirect legacy URLs to the top-level menu URL.
	 */
	function sppcfw_redirect_legacy_admin_url() {
		if ( ! is_admin() || ! isset( $_GET['post_type'], $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$sppcfw_post_type = sanitize_key( wp_unslash( $_GET['post_type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$sppcfw_page      = sanitize_key( wp_unslash( $_GET['page'] ) );      // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'product' !== $sppcfw_post_type || sppcfw_parent_menu_slug() !== $sppcfw_page ) {
			return;
		}

		$target = sppcfw_admin_url();
		wp_safe_redirect( $target );
		exit;
	}
	add_action( 'admin_init', 'sppcfw_redirect_legacy_admin_url', 1 );
}

if ( ! function_exists( 'sppcfw_register_admin_menu_without_woocommerce' ) ) {
	/**
	 * Register admin menu when WooCommerce is not active.
	 */
	function sppcfw_register_admin_menu_without_woocommerce() {
		if ( ! function_exists( 'sppcfw_is_woocommerce_active' ) || sppcfw_is_woocommerce_active() ) {
			return;
		}

		$parent_slug = sppcfw_parent_menu_slug();
		$menu_title  = __( 'Single Product Customizer', 'single-product-customizer' );
		$capability  = sppcfw_admin_capability();

		add_menu_page(
			$menu_title,
			$menu_title,
			$capability,
			$parent_slug,
			'sppcfw_render_woocommerce_required_admin_page',
			'dashicons-admin-customizer',
			56
		);

		add_submenu_page(
			$parent_slug,
			__( 'Single Product Customizer', 'single-product-customizer' ),
			__( 'Single Product Customizer', 'single-product-customizer' ),
			$capability,
			$parent_slug,
			'sppcfw_render_woocommerce_required_admin_page'
		);
	}
	add_action( 'admin_menu', 'sppcfw_register_admin_menu_without_woocommerce' );
}

if ( ! function_exists( 'sppcfw_render_woocommerce_required_admin_page' ) ) {
	/**
	 * Admin page shown when WooCommerce is missing.
	 */
	function sppcfw_render_woocommerce_required_admin_page() {
		if ( ! current_user_can( sppcfw_admin_capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'single-product-customizer' ) );
		}

		$install_url = admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Single Product Customizer', 'single-product-customizer' ); ?></h1>
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
}
