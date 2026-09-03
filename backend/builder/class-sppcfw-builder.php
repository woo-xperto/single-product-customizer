<?php

/**
 * Single Product Page Builder Engine
 *
 * @package Single_Product_Customizer
 */
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('SPPCFW_Builder')) {
	class SPPCFW_Builder
	{
		/**
		 * Constructor.
		 */
		public function __construct()
		{
			add_action('admin_enqueue_scripts', array($this, 'sppcfw_enqueue_builder_assets'));
			add_action('in_admin_header', array($this, 'sppcfw_suppress_builder_admin_notices'), 1);

			// AJAX actions
			add_action('wp_ajax_sppcfw_get_builder_products_and_categories', array($this, 'sppcfw_ajax_get_products_and_categories'));
			add_action('wp_ajax_sppcfw_get_builder_product_data', array($this, 'sppcfw_ajax_get_product_data'));
			add_action('wp_ajax_sppcfw_save_builder_template', array($this, 'sppcfw_ajax_save_builder_template'));
			add_action('wp_ajax_sppcfw_load_builder_template', array($this, 'sppcfw_ajax_load_builder_template'));
			add_action('wp_ajax_sppcfw_get_builder_templates', array($this, 'sppcfw_ajax_get_builder_templates'));
			add_action('wp_ajax_sppcfw_toggle_builder_status', array($this, 'sppcfw_ajax_toggle_builder_status'));
			add_action('wp_ajax_sppcfw_update_builder_basic_setting', array($this, 'sppcfw_ajax_update_builder_basic_setting'));
		}

		/**
		 * Suppress admin notices on builder screen.
		 *
		 * @return void
		 */
		public function sppcfw_suppress_builder_admin_notices()
		{
			$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
			if (isset($_GET['page']) && 'sppcfw-single-page-builder' === $_GET['page'] && isset($_GET['template_id']) && (empty($action) || 'edit' === $action)) {
				remove_all_actions('admin_notices');
				remove_all_actions('all_admin_notices');
				remove_all_actions('user_admin_notices');
				remove_all_actions('network_admin_notices');
			}
		}

		/**
		 * Render templates list view screen.
		 *
		 * @return void
		 */
		public function sppcfw_render_templates_list_view()
		{
			if (!current_user_can(function_exists('sppcfw_admin_capability') ? sppcfw_admin_capability() : 'manage_options')) {
				wp_die(esc_html__('You do not have permission to access this page.', 'single-product-customizer'));
			}

			require_once __DIR__ . '/templates-list-view.php';
		}

		/**
		 * Render builder view HTML container.
		 *
		 * @return void
		 */
		public function sppcfw_render_builder_view()
		{
			if (!current_user_can(function_exists('sppcfw_admin_capability') ? sppcfw_admin_capability() : 'manage_options')) {
				wp_die(esc_html__('You do not have permission to access this page.', 'single-product-customizer'));
			}

			require_once __DIR__ . '/builder-view.php';
		}

		/**
		 * Enqueue assets for builder screen and template list.
		 *
		 * @param string $hook Page hook string.
		 * @return void
		 */
		public function sppcfw_enqueue_builder_assets($hook)
		{
			if (isset($_GET['page']) && ('sppcfw-single-page-builder' === $_GET['page'] || 'sppcfw-single-page-builder' === $_GET['page'])) {
				// Enqueue Builder Tailwind CSS
				wp_enqueue_style(
					'sppcfw-builder-tailwind',
					SPPCFW_DIR_URL . 'backend/assets/css/builder-tailwind.css',
					array(),
					SPPCFW_VERSION
				);
			}

			$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
			if (isset($_GET['page']) && 'sppcfw-single-page-builder' === $_GET['page'] && isset($_GET['template_id']) && (empty($action) || 'edit' === $action)) {
				// Enqueue React / WP Element
				wp_enqueue_script('wp-element');

				// Enqueue Google Fonts & Icons
				wp_enqueue_style(
					'sppcfw-builder-fonts',
					'https://fonts.googleapis.com/css2?family=Geist:wght@400;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap',
					array(),
					null
				);

				wp_enqueue_style(
					'sppcfw-builder-material-icons',
					'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap',
					array(),
					null
				);

				wp_enqueue_script(
					'sppcfw-builder-app',
					SPPCFW_DIR_URL . 'backend/assets/js/builder-app.js',
					array('wp-element', 'jquery'),
					SPPCFW_VERSION,
					true
				);

				$template_id = isset($_GET['template_id']) ? sanitize_text_field($_GET['template_id']) : 'template_default';
				$sppcfw_basic = get_option('sppcfw_basic', array());
				$enable_pm = (is_array($sppcfw_basic) && isset($sppcfw_basic['enable_plus_minus_button']) && 'on' === $sppcfw_basic['enable_plus_minus_button']) ? 'on' : '';
				$btn_text = (is_array($sppcfw_basic) && isset($sppcfw_basic['add_to_cart_button_text'])) ? $sppcfw_basic['add_to_cart_button_text'] : 'Add to cart';
				$hide_price = (is_array($sppcfw_basic) && isset($sppcfw_basic['hide_product_price']) && 'on' === $sppcfw_basic['hide_product_price']) ? 'on' : '';

				wp_localize_script(
					'sppcfw-builder-app',
					'SPPCFWBuilderConfig',
					array(
						'ajax_url' => admin_url('admin-ajax.php'),
						'nonce' => wp_create_nonce('sppcfw_builder_nonce'),
						'plugin_url' => SPPCFW_DIR_URL,
						'template_id' => $template_id,
						'basic_settings' => array(
							'enable_plus_minus_button' => $enable_pm,
							'add_to_cart_button_text' => $btn_text,
							'hide_product_price' => $hide_price,
						),
					)
				);
			}
		}

		/**
		 * AJAX: Get products and categories list for dropdowns.
		 *
		 * @return void
		 */
		public function sppcfw_ajax_get_products_and_categories()
		{
			check_ajax_referer('sppcfw_builder_nonce', 'nonce');

			$products = array();
			$categories = array();

			// Fetch products
			$query_args = array(
				'post_type' => 'product',
				'posts_per_page' => 100,
				'post_status' => 'publish',
			);
			$query = new WP_Query($query_args);

			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					$img_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
					$products[] = array(
						'id' => get_the_ID(),
						'title' => get_the_title(),
						'url' => get_permalink(get_the_ID()),
						'image_url' => $img_url ? $img_url : '',
					);
				}
				wp_reset_postdata();
			}

			// Fetch product categories
			$terms = get_terms(
				array(
					'taxonomy' => 'product_cat',
					'hide_empty' => false,
				)
			);

			if (!is_wp_error($terms) && !empty($terms)) {
				foreach ($terms as $term) {
					$categories[] = array(
						'id' => $term->term_id,
						'name' => $term->name,
						'slug' => $term->slug,
					);
				}
			}

			wp_send_json_success(
				array(
					'products' => $products,
					'categories' => $categories,
				)
			);
		}

		/**
		 * AJAX: Get real-time single product data & meta.
		 *
		 * @return void
		 */
		public function sppcfw_ajax_get_product_data()
		{
			check_ajax_referer('sppcfw_builder_nonce', 'nonce');

			$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

			if (!$product_id || !function_exists('wc_get_product')) {
				// Fallback dummy data if no product ID or WooCommerce inactive
				$dummy_data = array(
					'id' => 0,
					'title' => __('Sample WooCommerce Product', 'single-product-customizer'),
					'price' => '$49.99',
					'regular_price' => '$59.99',
					'sale_price' => '$49.99',
					'on_sale' => true,
					'sku' => 'SAMPLE-SKU-123',
					'stock_status' => 'instock',
					'stock_text' => __('In Stock (25 available)', 'single-product-customizer'),
					'weight' => '0.5 kg',
					'dimensions' => '10 × 10 × 5 cm',
					'total_sales' => '142',
					'rating_html' => '<div class="star-rating"><span style="width:100%">★★★★★</span></div>',
					'average_rating' => '5.00',
					'rating_count' => 12,
					'image_url' => SPPCFW_DIR_URL . 'backend/resources/images/features-img.webp',
					'gallery_urls' => array(
						SPPCFW_DIR_URL . 'backend/resources/images/features-img.webp',
					),
					'short_description' => __('This is a sample product short description detailing key features and benefits of your WooCommerce product.', 'single-product-customizer'),
					'description' => __('Full product description going into extensive technical detail about specifications, usage guidelines, and warranty information.', 'single-product-customizer'),
					'categories' => __('Clothing, Featured', 'single-product-customizer'),
					'tags' => __('Customizer, Premium', 'single-product-customizer'),
					'meta_groups' => array(
						array(
							'title' => __('General & Inventory Meta', 'single-product-customizer'),
							'items' => array(
								array('key' => '_sku', 'label' => __('SKU', 'single-product-customizer'), 'value' => 'SAMPLE-SKU-123'),
								array('key' => '_stock_status', 'label' => __('Stock Status', 'single-product-customizer'), 'value' => 'In Stock'),
								array('key' => '_stock', 'label' => __('Stock Quantity', 'single-product-customizer'), 'value' => '25'),
								array('key' => '_weight', 'label' => __('Weight', 'single-product-customizer'), 'value' => '0.5 kg'),
								array('key' => '_dimensions', 'label' => __('Dimensions', 'single-product-customizer'), 'value' => '10 × 10 × 5 cm'),
								array('key' => 'total_sales', 'label' => __('Total Sales', 'single-product-customizer'), 'value' => '142 units sold'),
							),
						),
						array(
							'title' => __('Taxonomies & Attributes', 'single-product-customizer'),
							'items' => array(
								array('key' => 'product_cat', 'label' => __('Categories', 'single-product-customizer'), 'value' => 'Clothing, Featured'),
								array('key' => 'product_tag', 'label' => __('Tags', 'single-product-customizer'), 'value' => 'Customizer, Premium'),
								array('key' => 'pa_color', 'label' => __('Color Attribute', 'single-product-customizer'), 'value' => 'Black, Blue, Red'),
								array('key' => 'pa_size', 'label' => __('Size Attribute', 'single-product-customizer'), 'value' => 'S, M, L, XL'),
							),
						),
						array(
							'title' => __('Custom Post Meta', 'single-product-customizer'),
							'items' => array(
								array('key' => 'custom_warranty', 'label' => __('Warranty Info', 'single-product-customizer'), 'value' => '2 Years Limited Warranty'),
								array('key' => 'custom_material', 'label' => __('Material', 'single-product-customizer'), 'value' => '100% Organic Cotton'),
							),
						),
					),
				);

				wp_send_json_success(array('product' => $dummy_data));
				return;
			}

			$product = wc_get_product($product_id);

			if (!$product) {
				wp_send_json_error(array('message' => __('Product not found', 'single-product-customizer')));
				return;
			}

			$image_id = $product->get_image_id();
			$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : wc_placeholder_img_src();

			$gallery_ids = $product->get_gallery_image_ids();
			$gallery_urls = array();
			if ($image_url) {
				$gallery_urls[] = $image_url;
			}
			if (!empty($gallery_ids)) {
				foreach ($gallery_ids as $g_id) {
					$g_url = wp_get_attachment_image_url($g_id, 'full');
					if ($g_url) {
						$gallery_urls[] = $g_url;
					}
				}
			}

			$variation_images = array();
			if ($product->is_type('variable')) {
				$available_variations = $product->get_available_variations();
				if (!empty($available_variations)) {
					foreach ($available_variations as $var) {
						if (!empty($var['image']['url'])) {
							$var_img_url = $var['image']['url'];
							if (!in_array($var_img_url, $gallery_urls, true)) {
								$gallery_urls[] = $var_img_url;
							}
							$variation_images[] = array(
								'variation_id' => $var['variation_id'],
								'image_url'    => $var_img_url,
								'attributes'   => isset($var['attributes']) ? $var['attributes'] : array(),
							);
						}
					}
				}
			}

			$cat_names = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names'));
			$tag_names = wp_get_post_terms($product_id, 'product_tag', array('fields' => 'names'));

			// Format dimensions properly as string
			$raw_dimensions = $product->get_dimensions(false);
			$formatted_dimensions = function_exists('wc_format_dimensions') ? wc_format_dimensions($raw_dimensions) : '';
			if (empty($formatted_dimensions) || 'N/A' === $formatted_dimensions) {
				$formatted_dimensions = 'N/A';
			}

			// Build Meta Groups
			$meta_groups = array();

			// 1. General & Inventory
			$general_meta = array(
				array('key' => '_sku', 'label' => __('SKU', 'single-product-customizer'), 'value' => $product->get_sku() ? $product->get_sku() : 'N/A'),
				array('key' => '_stock_status', 'label' => __('Stock Status', 'single-product-customizer'), 'value' => $product->is_in_stock() ? __('In Stock', 'single-product-customizer') : __('Out of Stock', 'single-product-customizer')),
				array('key' => '_stock', 'label' => __('Stock Quantity', 'single-product-customizer'), 'value' => $product->get_stock_quantity() !== null ? (string) $product->get_stock_quantity() : 'N/A'),
				array('key' => '_weight', 'label' => __('Weight', 'single-product-customizer'), 'value' => $product->get_weight() ? $product->get_weight() . ' ' . get_option('woocommerce_weight_unit', 'kg') : 'N/A'),
				array('key' => '_dimensions', 'label' => __('Dimensions', 'single-product-customizer'), 'value' => $formatted_dimensions),
				array('key' => 'total_sales', 'label' => __('Total Sales', 'single-product-customizer'), 'value' => (string) $product->get_total_sales()),
			);

			$meta_groups[] = array(
				'title' => __('General & Inventory Meta', 'single-product-customizer'),
				'items' => $general_meta,
			);

			// 2. Taxonomies & Product Attributes
			$tax_meta = array(
				array('key' => 'product_cat', 'label' => __('Categories', 'single-product-customizer'), 'value' => !empty($cat_names) && !is_wp_error($cat_names) ? implode(', ', $cat_names) : 'N/A'),
				array('key' => 'product_tag', 'label' => __('Tags', 'single-product-customizer'), 'value' => !empty($tag_names) && !is_wp_error($tag_names) ? implode(', ', $tag_names) : 'N/A'),
			);

			$attributes = $product->get_attributes();
			if (!empty($attributes)) {
				foreach ($attributes as $attr_slug => $attribute) {
					$attr_name = wc_attribute_label($attribute->get_name());
					$attr_terms = array();

					if ($attribute->is_taxonomy()) {
						$terms = wc_get_product_terms($product_id, $attribute->get_name(), array('fields' => 'names'));
						if (!is_wp_error($terms)) {
							$attr_terms = $terms;
						}
					} else {
						$attr_terms = $attribute->get_options();
					}

					$formatted_attr = !empty($attr_terms) && is_array($attr_terms) ? implode(', ', array_map('strval', $attr_terms)) : 'N/A';

					$tax_meta[] = array(
						'key' => 'attr_' . sanitize_key($attr_slug),
						'label' => $attr_name,
						'value' => $formatted_attr,
					);
				}
			}

			$meta_groups[] = array(
				'title' => __('Taxonomies & Attributes', 'single-product-customizer'),
				'items' => $tax_meta,
			);

			// 3. Custom Post Meta
			$raw_meta = get_post_meta($product_id);
			$custom_meta = array();
			if (is_array($raw_meta)) {
				foreach ($raw_meta as $key => $values) {
					if (0 === strpos($key, '_')) {
						continue;  // skip WP/WC hidden meta
					}
					$val = is_array($values) && isset($values[0]) ? $values[0] : '';
					$val = maybe_unserialize($val);
					if (is_array($val)) {
						$val = implode(', ', array_map('strval', $val));
					}
					if (is_string($val) && '' !== trim($val)) {
						$custom_meta[] = array(
							'key' => $key,
							'label' => ucwords(str_replace(array('_', '-'), ' ', $key)),
							'value' => esc_html($val),
						);
					}
				}
			}

			if (!empty($custom_meta)) {
				$meta_groups[] = array(
					'title' => __('Custom Post Meta', 'single-product-customizer'),
					'items' => $custom_meta,
				);
			}

			$data = array(
				'id' => $product->get_id(),
				'title' => $product->get_name(),
				'price' => wc_price($product->get_price()),
				'regular_price' => wc_price($product->get_regular_price()),
				'sale_price' => $product->get_sale_price() ? wc_price($product->get_sale_price()) : '',
				'on_sale' => $product->is_on_sale(),
				'sku' => $product->get_sku() ? $product->get_sku() : 'N/A',
				'stock_status' => $product->get_stock_status(),
				'stock_text' => $product->is_in_stock() ? __('In Stock', 'single-product-customizer') : __('Out of Stock', 'single-product-customizer'),
				'weight' => $product->get_weight() ? $product->get_weight() . ' ' . get_option('woocommerce_weight_unit', 'kg') : '',
				'dimensions' => 'N/A' !== $formatted_dimensions ? $formatted_dimensions : '',
				'total_sales' => (string) $product->get_total_sales(),
				'rating_html' => wc_get_rating_html($product->get_average_rating(), $product->get_rating_count()),
				'average_rating' => $product->get_average_rating(),
				'rating_count' => $product->get_rating_count(),
				'image_url' => $image_url,
				'gallery_urls' => $gallery_urls,
				'variation_images' => $variation_images,
				'short_description' => $product->get_short_description(),
				'description' => $product->get_description(),
				'categories' => !empty($cat_names) && !is_wp_error($cat_names) ? implode(', ', $cat_names) : '',
				'tags' => !empty($tag_names) && !is_wp_error($tag_names) ? implode(', ', $tag_names) : '',
				'meta_groups' => $meta_groups,
			);

			wp_send_json_success(array('product' => $data));
		}

		/**
		 * AJAX: Save Builder Template & Display Conditions (Multi-Template Registry).
		 *
		 * @return void
		 */
		public function sppcfw_ajax_save_builder_template()
		{
			check_ajax_referer('sppcfw_builder_nonce', 'nonce');

			if (!current_user_can(function_exists('sppcfw_admin_capability') ? sppcfw_admin_capability() : 'manage_options')) {
				wp_send_json_error(array('message' => __('Permission denied', 'single-product-customizer')));
			}

			$template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : '';
			$template_title = isset($_POST['template_title']) ? sanitize_text_field($_POST['template_title']) : '';
			$status = isset($_POST['status']) ? strtolower(sanitize_text_field($_POST['status'])) : 'published';
			$page_settings = isset($_POST['page_settings']) ? json_decode(wp_unslash($_POST['page_settings']), true) : array();
			$layout = isset($_POST['layout']) ? wp_unslash($_POST['layout']) : '';
			$conditions = isset($_POST['conditions']) ? wp_unslash($_POST['conditions']) : '';

			if (isset($_POST['enable_plus_minus_button'])) {
				$basic = get_option('sppcfw_basic', array());
				if (!is_array($basic)) {
					$basic = array();
				}
				$basic['enable_plus_minus_button'] = ('on' === sanitize_text_field($_POST['enable_plus_minus_button'])) ? 'on' : '';
				update_option('sppcfw_basic', $basic);
			}

			if (isset($_POST['add_to_cart_button_text'])) {
				$basic = get_option('sppcfw_basic', array());
				if (!is_array($basic)) {
					$basic = array();
				}
				$basic['add_to_cart_button_text'] = sanitize_text_field($_POST['add_to_cart_button_text']);
				update_option('sppcfw_basic', $basic);
			}

			if (isset($_POST['hide_product_price'])) {
				$basic = get_option('sppcfw_basic', array());
				if (!is_array($basic)) {
					$basic = array();
				}
				$basic['hide_product_price'] = ('on' === sanitize_text_field($_POST['hide_product_price'])) ? 'on' : '';
				update_option('sppcfw_basic', $basic);
			}

			$selected_product_id = isset($_POST['selected_product_id']) ? sanitize_text_field($_POST['selected_product_id']) : (isset($page_settings['selected_product_id']) ? sanitize_text_field($page_settings['selected_product_id']) : '');

			if (empty($template_id) || 'new' === $template_id) {
				$template_id = 'template_' . time();
			}

			if (empty($template_title)) {
				$template_title = __('Single Product Template', 'single-product-customizer');
			}

			$templates = get_option('sppcfw_builder_templates', array());

			$templates[$template_id] = array(
				'id' => $template_id,
				'title' => $template_title,
				'status' => $status,
				'selected_product_id' => $selected_product_id,
				'page_settings' => $page_settings,
				'layout' => json_decode($layout, true),
				'conditions' => json_decode($conditions, true),
				'updated_at' => current_time('mysql'),
			);

			update_option('sppcfw_builder_templates', $templates);

			// Also update legacy single option for fallback
			update_option('sppcfw_builder_template', $templates[$template_id]);

			wp_send_json_success(
				array(
					'message' => __('Single Page Builder layout published successfully!', 'single-product-customizer'),
					'template_id' => $template_id,
				)
			);
		}

		/**
		 * AJAX: Load Builder Template from Registry.
		 *
		 * @return void
		 */
		public function sppcfw_ajax_load_builder_template()
		{
			check_ajax_referer('sppcfw_builder_nonce', 'nonce');

			$template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : 'template_default';

			if ('new' === $template_id) {
				$blank_template = array(
					'id' => 'new',
					'title' => __('New Product Template', 'single-product-customizer'),
					'layout' => array(),
					'conditions' => array('scope' => 'entire'),
					'status' => 'draft',
				);
				wp_send_json_success(array('template' => $blank_template));
				return;
			}

			$templates = get_option('sppcfw_builder_templates', array());
			$sppcfw_basic = get_option('sppcfw_basic', array());
			$enable_pm = (is_array($sppcfw_basic) && isset($sppcfw_basic['enable_plus_minus_button']) && 'on' === $sppcfw_basic['enable_plus_minus_button']) ? 'on' : '';
			$btn_text = (is_array($sppcfw_basic) && isset($sppcfw_basic['add_to_cart_button_text'])) ? $sppcfw_basic['add_to_cart_button_text'] : 'Add to cart';
			$hide_price = (is_array($sppcfw_basic) && isset($sppcfw_basic['hide_product_price']) && 'on' === $sppcfw_basic['hide_product_price']) ? 'on' : '';

			if (isset($templates[$template_id])) {
				wp_send_json_success(array(
					'template' => $templates[$template_id],
					'basic_settings' => array(
						'enable_plus_minus_button' => $enable_pm,
						'add_to_cart_button_text' => $btn_text,
						'hide_product_price' => $hide_price,
					),
				));
				return;
			}

			// Fallback: check legacy single option or first item in templates array
			if (!empty($templates)) {
				$first = reset($templates);
				wp_send_json_success(array('template' => $first));
				return;
			}

			$legacy = get_option('sppcfw_builder_template', array());
			if (!empty($legacy)) {
				if (empty($legacy['id'])) {
					$legacy['id'] = 'template_default';
				}
				if (empty($legacy['title'])) {
					$legacy['title'] = __('Default Single Product Template', 'single-product-customizer');
				}
				wp_send_json_success(array('template' => $legacy));
				return;
			}

			// Complete blank template fallback
			$blank = array(
				'id' => 'template_default',
				'title' => __('Default Single Product Template', 'single-product-customizer'),
				'layout' => array(),
				'conditions' => array('scope' => 'entire'),
			);
			wp_send_json_success(array('template' => $blank));
		}

		/**
		 * AJAX: Get List of All Builder Templates.
		 *
		 * @return void
		 */
		public function sppcfw_ajax_get_builder_templates()
		{
			check_ajax_referer('sppcfw_builder_nonce', 'nonce');

			$templates_option = get_option('sppcfw_builder_templates', array());
			$list = array();

			if (!empty($templates_option) && is_array($templates_option)) {
				foreach ($templates_option as $id => $tpl) {
					$list[] = array(
						'id' => isset($tpl['id']) ? $tpl['id'] : $id,
						'title' => !empty($tpl['title']) ? $tpl['title'] : __('Untitled Template', 'single-product-customizer'),
						'status' => !empty($tpl['status']) ? strtolower($tpl['status']) : 'published',
						'updated_at' => !empty($tpl['updated_at']) ? $tpl['updated_at'] : '',
					);
				}
			} else {
				$legacy = get_option('sppcfw_builder_template', array());
				if (!empty($legacy)) {
					$list[] = array(
						'id' => isset($legacy['id']) ? $legacy['id'] : 'template_default',
						'title' => !empty($legacy['title']) ? $legacy['title'] : __('Default Single Product Template', 'single-product-customizer'),
						'status' => 'published',
						'updated_at' => isset($legacy['updated_at']) ? $legacy['updated_at'] : '',
					);
				}
			}

			wp_send_json_success(array('templates' => $list));
		}

		/**
		 * AJAX: Toggle Single Product Builder active status.
		 *
		 * @return void
		 */
		/**
		 * AJAX: Update basic setting directly from builder.
		 *
		 * @return void
		 */
		public function sppcfw_ajax_update_builder_basic_setting()
		{
			check_ajax_referer('sppcfw_builder_nonce', 'nonce');

			if (!current_user_can(function_exists('sppcfw_admin_capability') ? sppcfw_admin_capability() : 'manage_options')) {
				wp_send_json_error(array('message' => __('Permission denied', 'single-product-customizer')));
			}

			$key = isset($_POST['key']) ? sanitize_key($_POST['key']) : '';
			$value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '';

			if ('enable_plus_minus_button' === $key) {
				$basic = get_option('sppcfw_basic', array());
				if (!is_array($basic)) {
					$basic = array();
				}
				$basic['enable_plus_minus_button'] = ('on' === $value) ? 'on' : '';
				update_option('sppcfw_basic', $basic);
				wp_send_json_success(array('message' => __('Basic setting updated', 'single-product-customizer'), 'value' => $basic['enable_plus_minus_button']));
				return;
			}

			if ('add_to_cart_button_text' === $key) {
				$basic = get_option('sppcfw_basic', array());
				if (!is_array($basic)) {
					$basic = array();
				}
				$basic['add_to_cart_button_text'] = sanitize_text_field($value);
				update_option('sppcfw_basic', $basic);
				wp_send_json_success(array('message' => __('Basic setting updated', 'single-product-customizer'), 'value' => $basic['add_to_cart_button_text']));
				return;
			}

			if ('hide_product_price' === $key) {
				$basic = get_option('sppcfw_basic', array());
				if (!is_array($basic)) {
					$basic = array();
				}
				$basic['hide_product_price'] = ('on' === $value) ? 'on' : '';
				update_option('sppcfw_basic', $basic);
				wp_send_json_success(array('message' => __('Basic setting updated', 'single-product-customizer'), 'value' => $basic['hide_product_price']));
				return;
			}

			wp_send_json_error(array('message' => __('Invalid setting key', 'single-product-customizer')));
		}

		public function sppcfw_ajax_toggle_builder_status()
		{
			check_ajax_referer('sppcfw_builder_nonce', 'nonce');

			$cap = function_exists('sppcfw_admin_capability') ? sppcfw_admin_capability() : 'manage_options';
			if (!current_user_can($cap)) {
				wp_send_json_error(array('message' => esc_html__('Permission denied.', 'single-product-customizer')));
			}

			// If Quick Checkout is active, builder cannot be enabled
			$is_qc_enabled = (int) get_option('sppcfw_enable_quick_checkout', 0);
			if ($is_qc_enabled) {
				wp_send_json_error(array(
					'message'   => esc_html__('Cannot enable Builder while Quick Checkout is active. Please checkout in Quick Checkout Options.', 'single-product-customizer'),
					'qc_active' => true,
				));
			}

			$enabled = isset($_POST['enabled']) && ('1' === $_POST['enabled'] || 'true' === $_POST['enabled'] || 1 === $_POST['enabled'] || true === $_POST['enabled']) ? 1 : 0;
			update_option('sppcfw_enable_single_product_builder', $enabled);

			wp_send_json_success(array(
				'enabled' => $enabled,
				'message' => $enabled
					? esc_html__('Single Product Builder enabled successfully.', 'single-product-customizer')
					: esc_html__('Single Product Builder disabled successfully.', 'single-product-customizer'),
			));
		}
	}

	new SPPCFW_Builder();
}
