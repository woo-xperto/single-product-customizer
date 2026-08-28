<?php
/**
 * Setup Help Admin Notice for Single Product Customizer for WooCommerce.
 *
 * @package Single_Product_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SPPCFW_Setup_Help_Notice' ) ) {

	class SPPCFW_Setup_Help_Notice {

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_notices', array( $this, 'show_admin_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_ajax_sppcfw_dismiss_setup_notice', array( $this, 'ajax_dismiss_notice' ) );
		}

		/**
		 * Check if setup notice was dismissed within the last hour.
		 *
		 * @return bool
		 */
		public static function is_dismissed(): bool {
			$dismissed_until = (int) get_option( 'sppcfw_setup_notice_dismissed_until', 0 );
			return ( time() < $dismissed_until );
		}

		/**
		 * Check if current user has permission to see the setup notice.
		 *
		 * @return bool
		 */
		private function should_show_notice(): bool {
			$capability = function_exists( 'sppcfw_admin_capability' ) ? sppcfw_admin_capability() : 'manage_options';
			if ( ! current_user_can( $capability ) ) {
				return false;
			}

			if ( self::is_dismissed() ) {
				return false;
			}

			return true;
		}

		/**
		 * Enqueue notice styles and dismissal JS on admin pages.
		 *
		 * @return void
		 */
		public function enqueue_scripts(): void {
			if ( ! $this->should_show_notice() ) {
				return;
			}

			wp_enqueue_style(
				'sppcfw-setup-notice-style',
				SPPCFW_DIR_URL . 'backend/assets/css/sppcfw-setup-notice.css',
				array(),
				SPPCFW_VERSION
			);

			wp_enqueue_script( 'jquery' );
			wp_add_inline_script(
				'jquery',
				'jQuery(document).ready(function($){
					$(document).on("click", ".sppcfw-setup-dismiss-btn", function(e){
						e.preventDefault();
						e.stopPropagation();
						var $btn = $(this);
						var $card = $btn.closest(".sppcfw-setup-notice-card, .sppcfw-content.sppcfw-notice-warning");
						var nonce = $btn.data("nonce");
						$card.fadeOut(250, function(){ $(this).remove(); });
						$.post(ajaxurl, {
							action: "sppcfw_dismiss_setup_notice",
							nonce: nonce
						});
					});
				});'
			);
		}

		/**
		 * AJAX handler to dismiss notice for 1 hour.
		 *
		 * @return void
		 */
		public function ajax_dismiss_notice(): void {
			check_ajax_referer( 'sppcfw_dismiss_setup_notice_nonce', 'nonce' );

			$capability = function_exists( 'sppcfw_admin_capability' ) ? sppcfw_admin_capability() : 'manage_options';
			if ( ! current_user_can( $capability ) ) {
				wp_send_json_error( array( 'message' => __( 'Permission denied', 'single-product-customizer' ) ) );
			}

			$dismissed_until = time() + HOUR_IN_SECONDS;
			update_option( 'sppcfw_setup_notice_dismissed_until', $dismissed_until );

			wp_send_json_success(
				array(
					'dismissed_until' => $dismissed_until,
					'message'         => __( 'Notice dismissed for 1 hour', 'single-product-customizer' ),
				)
			);
		}

		/**
		 * Render setup help notice HTML card.
		 *
		 * @param bool $with_wrapper Whether to wrap in WP admin notice container.
		 * @return void
		 */
		public static function render_card( bool $with_wrapper = true ): void {
			// Enqueue CSS when rendering card directly (e.g. inside tab-sppcfw).
			wp_enqueue_style(
				'sppcfw-setup-notice-style',
				SPPCFW_DIR_URL . 'backend/assets/css/sppcfw-setup-notice.css',
				array(),
				SPPCFW_VERSION
			);

			$first_activated = get_option( 'sppcfw_first_activated_time' );
			if ( ! $first_activated ) {
				$first_activated = time();
				add_option( 'sppcfw_first_activated_time', $first_activated );
			}

			$total_free_days = 30;
			$days_passed     = (int) floor( ( time() - (int) $first_activated ) / DAY_IN_SECONDS );
			$days_left       = max( 0, $total_free_days - $days_passed );

			if ( $days_left > 0 ) {
				/* translators: %d: number of days left */
				$days_text = sprintf( esc_html__( '%d days left', 'single-product-customizer' ), $days_left );
			} else {
				$days_text = esc_html__( '0 days left', 'single-product-customizer' );
			}

			$whatsapp_url = 'https://www.webcartisan.com/onboard-chat-single-product-page-customizer';
			$nonce        = wp_create_nonce( 'sppcfw_dismiss_setup_notice_nonce' );

			if ( $with_wrapper ) {
				echo '<div class="notice sppcfw-setup-notice-card">';
			}
			?>
			<button type="button" class="sppcfw-setup-dismiss-btn" title="<?php esc_attr_e( 'Dismiss for 1 hour', 'single-product-customizer' ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" aria-label="<?php esc_attr_e( 'Dismiss for 1 hour', 'single-product-customizer' ); ?>">&times;</button>
			<div class="sppcfw-setup-left">
				<div class="sppcfw-setup-avatar-wrapper">
					<svg width="48" height="48" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="sppcfw-setup-avatar-svg">
						<circle cx="40" cy="40" r="40" fill="#E2E8F0"/>
						<path d="M16 68C16 56 26 48 40 48C54 48 64 56 64 68" fill="#475569"/>
						<path d="M32 48L40 58L48 48" fill="#FFFFFF"/>
						<path d="M34 40V46C34 49.3 36.7 52 40 52C43.3 52 46 49.3 46 46V40" fill="#FDBA74"/>
						<circle cx="40" cy="32" r="14" fill="#FDBA74"/>
						<path d="M26 28C26 20 32 16 40 16C48 16 54 20 54 28C54 28 50 24 40 24C30 24 26 28 26 28Z" fill="#334155"/>
						<rect x="29" y="29" width="9" height="7" rx="2" fill="none" stroke="#1E293B" stroke-width="2"/>
						<rect x="42" y="29" width="9" height="7" rx="2" fill="none" stroke="#1E293B" stroke-width="2"/>
						<line x1="38" y1="32" x2="42" y2="32" stroke="#1E293B" stroke-width="2"/>
						<path d="M36 38C36 40 38 41 40 41C42 41 44 40 44 38" stroke="#C2410C" stroke-width="1.5" stroke-linecap="round"/>
						<path d="M24 32C24 23 31 16 40 16C49 16 56 23 56 32" stroke="#0F172A" stroke-width="3" stroke-linecap="round" fill="none"/>
						<rect x="22" y="28" width="4" height="8" rx="2" fill="#0F172A"/>
						<rect x="54" y="28" width="4" height="8" rx="2" fill="#0F172A"/>
						<path d="M24 34C24 40 30 42 34 42" stroke="#0F172A" stroke-width="2" stroke-linecap="round" fill="none"/>
						<circle cx="35" cy="42" r="2.5" fill="#22C55E"/>
					</svg>
					<span class="sppcfw-setup-status-dot"></span>
				</div>
				<div class="sppcfw-setup-info">
					<div class="sppcfw-setup-badge-wrap">
						<div class="sppcfw-setup-time-badge">
							<p><?php esc_html_e( 'Expert Help', 'single-product-customizer' ); ?></p>
						</div>
						<div class="sppcfw-setup-time-badge">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 16 14"></polyline></svg>
							<span><?php echo esc_html( $days_text ); ?></span>
						</div>
					</div>
					<h3 class="sppcfw-setup-title"><?php esc_html_e( 'Get free setup help For Single Product Page Customizer', 'single-product-customizer' ); ?></h3>
					<p class="sppcfw-setup-desc"><?php esc_html_e( 'Get expert help at no cost — Single Product Customizer for WooCommerce plugin team is here to support you!', 'single-product-customizer' ); ?></p>
				</div>
			</div>
			<div class="sppcfw-setup-right">
				<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="sppcfw-setup-claim-btn">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2H3z"></path></svg>
					<span><?php esc_html_e( 'Claim Free Setup', 'single-product-customizer' ); ?></span>
				</a>
			</div>
			<?php
			if ( $with_wrapper ) {
				echo '</div>';
			}
		}

		/**
		 * Render setup help notice across backend.
		 *
		 * @return void
		 */
		public function show_admin_notice(): void {
			if ( ! $this->should_show_notice() ) {
				return;
			}

			if ( isset( $_GET['page'] ) && 'sppcfw-welcome' === sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( $screen && false !== strpos( (string) $screen->id, 'sppcfw-welcome' ) ) {
				return;
			}

			self::render_card( true );
		}
	}

	new SPPCFW_Setup_Help_Notice();
}
