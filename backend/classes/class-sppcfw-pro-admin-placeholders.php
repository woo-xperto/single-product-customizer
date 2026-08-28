<?php
/**
 * Admin-only Pro feature placeholders (discovery UI — no Pro functionality in free plugin).
 *
 * @package Single_Product_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SPPCFW_Pro_Admin_Placeholders' ) ) {

	/**
	 * Renders disabled “Pro” controls and placeholder submenus in the free plugin admin when Pro is not active.
	 */
	class SPPCFW_Pro_Admin_Placeholders {

		/**
		 * Bootstrap hooks.
		 */
		public static function init() {
			if ( ! is_admin() ) {
				return;
			}

			add_action( 'admin_menu', array( __CLASS__, 'sppcfw_register_wp_admin_submenus' ), 25 );
			add_action( 'admin_menu', array( __CLASS__, 'sppcfw_style_wp_admin_submenu_labels' ), 999 );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'sppcfw_enqueue_styles' ) );
		}

		/**
		 * Pro plugin installed, active, and licensed for this site.
		 *
		 * @return bool
		 */
		public static function sppcfw_is_pro_available() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( ! is_plugin_active( 'single-product-customizer-pro/single-product-customizer-pro.php' ) ) {
				return false;
			}

			return function_exists( 'sppcfw_pro_license_is_active' ) && sppcfw_pro_license_is_active();
		}

		/**
		 * @return bool
		 */
		public static function sppcfw_should_show_placeholders() {
			return ! self::sppcfw_is_pro_available();
		}

		/**
		 * @return string
		 */
		public static function sppcfw_get_purchase_url() {
			return apply_filters(
				'sppcfw_pro_purchase_url',
				'http://webcartisan.com/single-product-page-customizer/'
			);
		}

		/**
		 * CTA URL when Pro is not available on this site.
		 *
		 * @return string
		 */
		public static function sppcfw_get_cta_url() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( is_plugin_active( 'single-product-customizer-pro/single-product-customizer-pro.php' ) ) {
				return admin_url( 'admin.php?page=sppcfw-pro-license' );
			}

			return self::sppcfw_get_purchase_url();
		}

		/**
		 * @return string
		 */
		public static function sppcfw_get_cta_link_html() {
			$url = esc_url( self::sppcfw_get_cta_url() );

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( is_plugin_active( 'single-product-customizer-pro/single-product-customizer-pro.php' ) ) {
				$label = __( 'Activate your Pro license', 'single-product-customizer' );
			} else {
				$label = __( 'Get Product Page Customizer Pro', 'single-product-customizer' );
			}

			return '<a href="' . $url . '">' . esc_html( $label ) . '</a>';
		}

		/**
		 * Submenu entries when Pro is unavailable.
		 *
		 * @return array<int, array{slug:string,menu_title:string,page_title:string,description:string}>
		 */
		public static function sppcfw_get_wp_admin_submenu_items() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$items = array();

			if ( ! is_plugin_active( 'single-product-customizer-pro/single-product-customizer-pro.php' ) ) {
				$items[] = array(
					'slug'        => 'sppcfw-pro-license',
					'menu_title'  => __( 'Pro license', 'single-product-customizer' ),
					'page_title'  => __( 'Single Product Customizer Pro — License', 'single-product-customizer' ),
					'description' => __( 'Enter your license key to unlock Pro features on this site.', 'single-product-customizer' ),
				);
			}

			return apply_filters( 'sppcfw_pro_admin_placeholder_wp_submenu_items', $items );
		}

		/**
		 * Register placeholder items on the admin menu.
		 */
		public static function sppcfw_register_wp_admin_submenus() {
			if ( ! self::sppcfw_should_show_placeholders() ) {
				return;
			}

			$parent = 'sppcfw-single-product-customizer';

			foreach ( self::sppcfw_get_wp_admin_submenu_items() as $item ) {
				add_submenu_page(
					$parent,
					$item['page_title'],
					$item['menu_title'],
					'manage_options',
					$item['slug'],
					array( __CLASS__, 'sppcfw_render_placeholder_admin_page' )
				);
			}
		}

		/**
		 * Append purple Pro badge to placeholder submenu labels in the left admin menu.
		 */
		public static function sppcfw_style_wp_admin_submenu_labels() {
			if ( ! self::sppcfw_should_show_placeholders() ) {
				return;
			}

			global $submenu;

			$parent = 'sppcfw-single-product-customizer';

			if ( empty( $submenu[ $parent ] ) || ! is_array( $submenu[ $parent ] ) ) {
				return;
			}

			$slugs = array();
			foreach ( self::sppcfw_get_wp_admin_submenu_items() as $item ) {
				$slugs[] = $item['slug'];
			}

			$badge = ' <span class="sppcfw-pro-menu-badge">' . esc_html__( 'Pro', 'single-product-customizer' ) . '</span>';

			foreach ( $submenu[ $parent ] as $idx => $entry ) {
				if ( ! is_array( $entry ) || empty( $entry[2] ) ) {
					continue;
				}
				if ( in_array( (string) $entry[2], $slugs, true ) && false === strpos( (string) $entry[0], 'sppcfw-pro-menu-badge' ) ) {
					$submenu[ $parent ][ $idx ][0] .= $badge;
				}
			}
		}

		/**
		 * Render placeholder screen for a Pro submenu slug.
		 */
		public static function sppcfw_render_placeholder_admin_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'single-product-customizer' ) );
			}

			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$sppcfw_pro_placeholder_title       = __( 'Single Product Customizer Pro', 'single-product-customizer' );
			$sppcfw_pro_placeholder_description = '';

			foreach ( self::sppcfw_get_wp_admin_submenu_items() as $item ) {
				if ( $item['slug'] === $page ) {
					$sppcfw_pro_placeholder_title       = $item['page_title'];
					$sppcfw_pro_placeholder_description = $item['description'];
					break;
				}
			}

			require SPPCFW_DIR_PATH . 'backend/templates/pro/placeholder-page.php';
		}

		/**
		 * Enqueue placeholder styles.
		 */
		public static function sppcfw_enqueue_styles( $hook ) {
			if ( ! self::sppcfw_should_show_placeholders() ) {
				return;
			}
			wp_enqueue_style(
				'sppcfw_pro_admin_placeholders',
				SPPCFW_DIR_URL . 'backend/assets/css/pro-admin-placeholders.css',
				array(),
				SPPCFW_VERSION
			);
		}
	}
}
