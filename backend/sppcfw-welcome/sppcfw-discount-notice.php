<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'SPPCFW_Campaign_Notice' ) ) {
	/**
	 * Single Product Customizer Campaign Admin Notice.
	 */
	class SPPCFW_Campaign_Notice {
		/**
		 * Notice start date.
		 *
		 * @var string
		 */
		private $notice_start_date = '2026-01-01';

		/**
		 * Notice end date.
		 *
		 * @var string
		 */
		private $notice_end_date = '2030-12-31';

		/**
		 * Option name for notice dismissal state.
		 *
		 * @var string
		 */
		private $option_name = 'sppcfw_campaign_notice';

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_notices', array( $this, 'show_admin_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_ajax_sppcfw_dismiss_notice', array( $this, 'ajax_dismiss_notice' ) );
		}

		/**
		 * Check if notice should be displayed.
		 *
		 * @return bool
		 */
		private function should_show_notice() {
			$pro_plugin_path = WP_PLUGIN_DIR . '/single-product-customizer-pro/single-product-customizer-pro.php';
			if ( file_exists( $pro_plugin_path ) && function_exists( 'is_plugin_active' ) && is_plugin_active( 'single-product-customizer-pro/single-product-customizer-pro.php' ) ) {
				return false;
			}

			$current_date     = current_time( 'Y-m-d' );
			$current_datetime = current_time( 'mysql' );

			if ( $current_date < $this->notice_start_date || $current_date > $this->notice_end_date ) {
				return false;
			}

			$notice_status = get_option( $this->option_name, array() );

			if ( isset( $notice_status['dismissed_until'] ) ) {
				if ( $current_datetime < $notice_status['dismissed_until'] ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Display admin notice.
		 *
		 * @return void
		 */
		public function show_admin_notice() {
			if ( ! $this->should_show_notice() ) {
				return;
			}

			$icon_url = SPPCFW_DIR_URL . 'backend/resources/images/single-product-icon.gif';
			$deal_url = 'http://webcartisan.com/single-product-page-customizer/';
			?>
			<div class="notice sppcfw-campaign-notice is-dismissible">
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'single-product-customizer' ); ?></span>
				</button>
				<div class="sppcfw-notice-glow"></div>
				<div class="sppcfw-notice-inner">
					<div class="sppcfw-notice-icon-container">
						<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php esc_attr_e( 'WebCartisan Logo', 'single-product-customizer' ); ?>" class="sppcfw-notice-icon" />
					</div>

					<div class="sppcfw-notice-body">
						<div class="sppcfw-notice-badge">
							<span class="sppcfw-badge-dot"></span>
							<span><?php esc_html_e( 'SALE BOOSTER DEAL', 'single-product-customizer' ); ?></span>
							<span class="sppcfw-badge-discount"><?php esc_html_e( '25% OFF', 'single-product-customizer' ); ?></span>
						</div>
						<h3 class="sppcfw-notice-title">
							<?php esc_html_e( 'Boost Product Sales with Single Product Customizer Pro!', 'single-product-customizer' ); ?>
						</h3>
						<p class="sppcfw-notice-slogan">
							<?php esc_html_e( 'Customize WooCommerce single product pages, variation tables, custom tabs, and min/max quantities with ease.', 'single-product-customizer' ); ?>
						</p>
					</div>

					<div class="sppcfw-notice-actions">
						<a href="<?php echo esc_url( $deal_url ); ?>" target="_blank" rel="noopener noreferrer" class="sppcfw-notice-cta-btn">
							<span><?php esc_html_e( 'Get 25% Discount', 'single-product-customizer' ); ?></span>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
								<line x1="5" y1="12" x2="19" y2="12"></line>
								<polyline points="12 5 19 12 12 19"></polyline>
							</svg>
						</a>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @return void
		 */
		public function enqueue_scripts() {
			if ( ! $this->should_show_notice() ) {
				return;
			}

			wp_enqueue_style(
				'sppcfw-campaign-notice',
				SPPCFW_DIR_URL . 'backend/assets/css/sales-campaign-notice.css',
				array(),
				SPPCFW_VERSION
			);

			wp_enqueue_script(
				'sppcfw-campaign-notice',
				SPPCFW_DIR_URL . 'backend/assets/js/sppcfw-admin-notice.js',
				array( 'jquery' ),
				SPPCFW_VERSION,
				true
			);

			wp_localize_script(
				'sppcfw-campaign-notice',
				'SPPCFWNotice',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'sppcfw_notice_nonce' ),
				)
			);
		}

		/**
		 * AJAX handler for dismissing notice.
		 *
		 * @return void
		 */
		public function ajax_dismiss_notice() {
			check_ajax_referer( 'sppcfw_notice_nonce', 'nonce' );

			$action = isset( $_POST['dismiss_action'] ) ? sanitize_text_field( wp_unslash( $_POST['dismiss_action'] ) ) : '';

			if ( 'later' === $action ) {
				$tomorrow = gmdate( 'Y-m-d H:i:s', current_time( 'timestamp' ) + DAY_IN_SECONDS );
				update_option(
					$this->option_name,
					array(
						'dismissed_until' => $tomorrow,
					)
				);

				wp_send_json_success(
					array(
						'message'         => 'Notice dismissed until tomorrow',
						'dismissed_until' => $tomorrow,
					)
				);
			}

			wp_send_json_error( array( 'message' => 'Invalid action' ) );
		}
	}

	new SPPCFW_Campaign_Notice();
}
