<?php

/**
 * Single Product Page Builder Frontend Renderer
 *
 * @package Single_Product_Customizer
 */
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('SPPCFW_Builder_Renderer')) {
	class SPPCFW_Builder_Renderer
	{
		/**
		 * Constructor.
		 */
		public function __construct()
		{
			add_action('wp', array($this, 'sppcfw_maybe_init_frontend_override'));
		}

		/**
		 * Active matching template instance.
		 *
		 * @var array
		 */
		private $matched_template = array();

		/**
		 * Check conditions and initialize template override.
		 *
		 * @return void
		 */
		public function sppcfw_maybe_init_frontend_override()
		{
			if (is_admin() || !is_singular('product')) {
				return;
			}

			$is_preview = isset($_GET['sppcfw_preview']) && isset($_GET['template_id']) && current_user_can('manage_options');

			if (!$is_preview) {
				// Suppress builder designs if Quick Checkout is enabled
				$sppcfw_enable_quick_checkout = (int) get_option('sppcfw_enable_quick_checkout', 0);
				if (!empty($sppcfw_enable_quick_checkout)) {
					return;
				}

				// Suppress builder designs if Single Product Builder toggle is disabled
				$sppcfw_enable_builder = (int) get_option('sppcfw_enable_single_product_builder', 0);
				if (empty($sppcfw_enable_builder)) {
					return;
				}
			}

			$matched = $this->sppcfw_get_matching_template(get_the_ID());

			if (empty($matched) || empty($matched['layout'])) {
				return;
			}

			$this->matched_template = $matched;

			add_action('wp_enqueue_scripts', array($this, 'sppcfw_enqueue_frontend_builder_styles'));

			// Hook into WooCommerce single product summary to render builder layout
			add_action('woocommerce_before_single_product_summary', array($this, 'sppcfw_render_builder_template'), 5);
			// Remove default WooCommerce single product hooks to avoid duplication when custom template is active
			remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);
			remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
			remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
			remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
		}

		/**
		 * Find best matching template from registry for product ID.
		 *
		 * @param int $product_id Product ID.
		 * @return array Template array.
		 */
		private function sppcfw_get_matching_template($product_id)
		{
			$templates = get_option('sppcfw_builder_templates', array());

			if (isset($_GET['sppcfw_preview']) && isset($_GET['template_id']) && current_user_can('manage_options')) {
				$preview_id = sanitize_text_field($_GET['template_id']);
				if (isset($templates[$preview_id]) && !empty($templates[$preview_id]['layout'])) {
					return $templates[$preview_id];
				}
			}

			if (empty($templates)) {
				$legacy = get_option('sppcfw_builder_template', array());
				if (!empty($legacy) && !empty($legacy['layout'])) {
					return $legacy;
				}
				return array();
			}

			$product_cats = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));

			$product_match = array();
			$category_match = array();
			$entire_match = array();

			foreach ($templates as $tpl) {
				if (empty($tpl['layout']) || (isset($tpl['status']) && in_array(strtolower($tpl['status']), array('draft', 'trash'), true))) {
					continue;
				}

				$conditions = isset($tpl['conditions']) ? $tpl['conditions'] : array();
				$scope = isset($conditions['scope']) ? $conditions['scope'] : 'entire';

				$tpl_prod_id = isset($tpl['selected_product_id']) ? $tpl['selected_product_id'] : (isset($tpl['page_settings']['selected_product_id']) ? $tpl['page_settings']['selected_product_id'] : '');
				if (!empty($tpl_prod_id) && (int) $tpl_prod_id === (int) $product_id) {
					$product_match = $tpl;
					break;
				}

				if ('product' === $scope) {
					$selected_prods = isset($conditions['product_ids']) ? (array) $conditions['product_ids'] : array();
					if (in_array($product_id, $selected_prods, true) || in_array((string) $product_id, $selected_prods, true)) {
						$product_match = $tpl;
						break;
					}
				} elseif ('category' === $scope) {
					$selected_cats = isset($conditions['category_ids']) ? (array) $conditions['category_ids'] : array();
					if (!empty(array_intersect($selected_cats, $product_cats))) {
						$category_match = $tpl;
					}
				} elseif ('entire' === $scope) {
					$entire_match = $tpl;
				}
			}

			if (!empty($product_match)) {
				return $product_match;
			}
			if (!empty($category_match)) {
				return $category_match;
			}
			if (!empty($entire_match)) {
				return $entire_match;
			}

			return array();
		}

		/**
		 * Enqueue frontend dynamic styles for builder widgets & containers.
		 *
		 * @return void
		 */
		public function sppcfw_enqueue_frontend_builder_styles()
		{
			$layout = isset($this->matched_template['layout']) ? $this->matched_template['layout'] : array();

			$custom_css = '
				.sppcfw-builder-section { width: 100%; box-sizing: border-box; }
				.sppcfw-container-boxed { margin-left: auto !important; margin-right: auto !important; }
				.sppcfw-container-full { width: 100% !important; }
				.sppcfw-flex-row { display: flex; flex-wrap: wrap; }
				.sppcfw-column { box-sizing: border-box; }
			';

			if (!empty($layout) && is_array($layout)) {
				$custom_css .= $this->sppcfw_generate_recursive_styles($layout, 'desktop');
				$tablet_css = $this->sppcfw_generate_recursive_styles($layout, 'tablet');
				if (!empty($tablet_css)) {
					$custom_css .= " @media (max-width: 1024px) { {$tablet_css} }";
				}
				$mobile_css = $this->sppcfw_generate_recursive_styles($layout, 'mobile');
				if (!empty($mobile_css)) {
					$custom_css .= " @media (max-width: 767px) { {$mobile_css} }";
				}
			}

			wp_register_style('sppcfw-builder-frontend-inline', false);
			wp_enqueue_style('sppcfw-builder-frontend-inline');
			wp_add_inline_style('sppcfw-builder-frontend-inline', $custom_css);
		}

		/**
		 * Helper to get responsive property.
		 */
		private function sppcfw_get_device_prop($array, $key, $device = 'desktop', $default = '')
		{
			if (!is_array($array)) {
				return $default;
			}
			if ('mobile' === $device) {
				if (isset($array[$key . '_mobile']) && '' !== $array[$key . '_mobile']) {
					return $array[$key . '_mobile'];
				}
				if (isset($array[$key . '_tablet']) && '' !== $array[$key . '_tablet']) {
					return $array[$key . '_tablet'];
				}
				return isset($array[$key]) ? $array[$key] : $default;
			}
			if ('tablet' === $device) {
				if (isset($array[$key . '_tablet']) && '' !== $array[$key . '_tablet']) {
					return $array[$key . '_tablet'];
				}
				return isset($array[$key]) ? $array[$key] : $default;
			}
			return isset($array[$key]) ? $array[$key] : $default;
		}

		/**
		 * Generate dynamic CSS recursively.
		 *
		 * @param array $elements Elements array.
		 * @param string $device Device mode.
		 * @return string CSS string.
		 */
		private function sppcfw_generate_recursive_styles($elements, $device = 'desktop')
		{
			$css = '';
			foreach ($elements as $el) {
				$type = isset($el['type']) ? $el['type'] : '';
				$id = isset($el['id']) ? esc_attr($el['id']) : '';
				$settings = isset($el['settings']) ? $el['settings'] : array();
				$styles = isset($el['styles']) ? $el['styles'] : array();

				if ($id) {
					$css .= ".sppcfw-el-{$id} {";
					if ('container' === $type) {
						$width_mode = $this->sppcfw_get_device_prop($settings, 'width_mode', $device, 'boxed');
						$boxed_width = esc_attr($this->sppcfw_get_device_prop($settings, 'boxed_width', $device, '1140px'));
						if ('boxed' === $width_mode) {
							$css .= "max-width: {$boxed_width} !important; margin: 0 auto 24px auto !important;";
						} else {
							$css .= 'width: 100% !important; margin-bottom: 24px !important;';
						}
					} elseif ('column' === $type) {
						$flex_width = esc_attr($this->sppcfw_get_device_prop($settings, 'flex_width', $device, '100%'));
						$flex_direction = esc_attr($this->sppcfw_get_device_prop($settings, 'flex_direction', $device, 'column'));
						$justify_content = esc_attr($this->sppcfw_get_device_prop($settings, 'justify_content', $device, 'flex-start'));
						$align_items = esc_attr($this->sppcfw_get_device_prop($settings, 'align_items', $device, 'stretch'));
						$gap = esc_attr($this->sppcfw_get_device_prop($settings, 'gap', $device, '12px'));
						$min_height = esc_attr($this->sppcfw_get_device_prop($settings, 'min_height', $device, '0px'));

						$css .= "flex: 1 1 calc({$flex_width} - 16px) !important; min-width: 200px !important; display: flex !important; flex-direction: {$flex_direction} !important; justify-content: {$justify_content} !important; align-items: {$align_items} !important; gap: {$gap} !important; min-height: {$min_height} !important;";
					}

					$text_color = $this->sppcfw_get_device_prop($styles, 'text_color', $device, '');
					if (!empty($text_color)) {
						$css .= 'color: ' . esc_attr($text_color) . ' !important;';
					}

					$bg_color = $this->sppcfw_get_device_prop($styles, 'bg_color', $device, '');
					if (!empty($bg_color)) {
						$css .= 'background-color: ' . esc_attr($bg_color) . ' !important;';
					}

					$font_size = $this->sppcfw_get_device_prop($styles, 'font_size', $device, '');
					if (!empty($font_size)) {
						$css .= 'font-size: ' . esc_attr($font_size) . ' !important;';
					}

					$font_family = $this->sppcfw_get_device_prop($styles, 'font_family', $device, '');
					if (!empty($font_family)) {
						$css .= 'font-family: ' . esc_attr($font_family) . ', sans-serif !important;';
					}

					$border_color = $this->sppcfw_get_device_prop($styles, 'border_color', $device, '');
					if (!empty($border_color)) {
						$css .= 'border-color: ' . esc_attr($border_color) . ' !important;';
					}

					$border_width = $this->sppcfw_get_device_prop($styles, 'border_width', $device, '');
					if (!empty($border_width)) {
						$css .= 'border-style: solid; border-width: ' . esc_attr($border_width) . ' !important;';
					}

					$border_radius = $this->sppcfw_get_device_prop($styles, 'border_radius', $device, '');
					if (!empty($border_radius)) {
						$css .= 'border-radius: ' . esc_attr($border_radius) . ' !important;';
					}

					$padding_top = $this->sppcfw_get_device_prop($styles, 'padding_top', $device, '');
					if (!empty($padding_top)) {
						$css .= 'padding-top: ' . esc_attr($padding_top) . ' !important;';
					}

					$padding_right = $this->sppcfw_get_device_prop($styles, 'padding_right', $device, '');
					if (!empty($padding_right)) {
						$css .= 'padding-right: ' . esc_attr($padding_right) . ' !important;';
					}

					$padding_bottom = $this->sppcfw_get_device_prop($styles, 'padding_bottom', $device, '');
					if (!empty($padding_bottom)) {
						$css .= 'padding-bottom: ' . esc_attr($padding_bottom) . ' !important;';
					}

					$padding_left = $this->sppcfw_get_device_prop($styles, 'padding_left', $device, '');
					if (!empty($padding_left)) {
						$css .= 'padding-left: ' . esc_attr($padding_left) . ' !important;';
					}

					$margin_top = $this->sppcfw_get_device_prop($styles, 'margin_top', $device, '');
					if (!empty($margin_top)) {
						$css .= 'margin-top: ' . esc_attr($margin_top) . ' !important;';
					}

					$margin_right = $this->sppcfw_get_device_prop($styles, 'margin_right', $device, '');
					if (!empty($margin_right)) {
						$css .= 'margin-right: ' . esc_attr($margin_right) . ' !important;';
					}

					$margin_bottom = $this->sppcfw_get_device_prop($styles, 'margin_bottom', $device, '');
					if (!empty($margin_bottom)) {
						$css .= 'margin-bottom: ' . esc_attr($margin_bottom) . ' !important;';
					}

					$margin_left = $this->sppcfw_get_device_prop($styles, 'margin_left', $device, '');
					if (!empty($margin_left)) {
						$css .= 'margin-left: ' . esc_attr($margin_left) . ' !important;';
					}

					$width = $this->sppcfw_get_device_prop($styles, 'width', $device, '');
					if (!empty($width)) {
						$css .= 'width: ' . esc_attr($width) . ' !important;';
					}

					$max_width = $this->sppcfw_get_device_prop($styles, 'max_width', $device, '');
					if (!empty($max_width)) {
						$css .= 'max-width: ' . esc_attr($max_width) . ' !important;';
					}

					$height = $this->sppcfw_get_device_prop($styles, 'height', $device, '');
					if (!empty($height)) {
						$css .= 'height: ' . esc_attr($height) . ' !important;';
					}

					$opacity = $this->sppcfw_get_device_prop($styles, 'opacity', $device, '');
					if ('' !== $opacity && null !== $opacity) {
						$css .= 'opacity: ' . esc_attr($opacity) . ' !important;';
					}

					$alignment = $this->sppcfw_get_device_prop($styles, 'alignment', $device, '');
					if (!empty($alignment)) {
						$css .= 'text-align: ' . esc_attr($alignment) . ' !important;';
					}
					$css .= '}';

					if ('container' === $type) {
						$gap = esc_attr($this->sppcfw_get_device_prop($settings, 'gap', $device, '16px'));
						$flex_direction = esc_attr($this->sppcfw_get_device_prop($settings, 'flex_direction', $device, 'row'));
						$css .= ".sppcfw-el-{$id} > .sppcfw-flex-row { gap: {$gap} !important; flex-direction: {$flex_direction} !important; }";
					}
				}

				if (!empty($el['children']) && is_array($el['children'])) {
					$css .= $this->sppcfw_generate_recursive_styles($el['children'], $device);
				}
			}
			return $css;
		}

		/**
		 * Render frontend builder layout output.
		 *
		 * @return void
		 */
		public function sppcfw_render_builder_template()
		{
			$elements = isset($this->matched_template['layout']) ? $this->matched_template['layout'] : array();

			if (empty($elements)) {
				return;
			}

			echo '<div class="sppcfw-builder-frontend-wrapper">';
			$this->sppcfw_render_elements_recursive($elements);
			echo '</div>';
		}

		/**
		 * Render elements tree recursively.
		 *
		 * @param array $elements List of elements.
		 * @return void
		 */
		private function sppcfw_render_elements_recursive($elements)
		{
			if (empty($elements) || !is_array($elements)) {
				return;
			}

			foreach ($elements as $el) {
				$type = isset($el['type']) ? $el['type'] : '';
				$id = isset($el['id']) ? esc_attr($el['id']) : '';
				$settings = isset($el['settings']) ? $el['settings'] : array();
				$advanced = isset($el['advanced']) ? $el['advanced'] : array();
				$css_class = !empty($advanced['custom_class']) ? esc_attr($advanced['custom_class']) : '';

				if ('container' === $type) {
					$width_mode = isset($settings['width_mode']) && 'full' === $settings['width_mode'] ? 'full' : 'boxed';

					echo '<div class="sppcfw-builder-section sppcfw-container-' . esc_attr($width_mode) . ' sppcfw-el-' . $id . ' ' . $css_class . '">';
					echo '<div class="sppcfw-flex-row">';
					if (!empty($el['children'])) {
						$this->sppcfw_render_elements_recursive($el['children']);
					}
					echo '</div>';
					echo '</div>';
				} elseif ('column' === $type) {
					echo '<div class="sppcfw-column sppcfw-el-' . $id . ' ' . $css_class . '">';
					if (!empty($el['children'])) {
						$this->sppcfw_render_elements_recursive($el['children']);
					}
					echo '</div>';
				} else {
					// Render Widget
					echo '<div class="sppcfw-widget-item sppcfw-el-' . $id . ' ' . $css_class . '">';
					$this->sppcfw_render_single_widget($el);
					echo '</div>';
				}
			}
		}

		/**
		 * Render individual widget output.
		 *
		 * @param array $el Widget element data.
		 * @return void
		 */
		private function sppcfw_render_single_widget($el)
		{
			global $product;

			if (!$product) {
				$product = wc_get_product(get_the_ID());
			}

			if (!$product) {
				return;
			}

			$type = isset($el['type']) ? $el['type'] : '';

			switch ($type) {
				case 'product_title':
					$settings = isset($el['settings']) ? $el['settings'] : array();
					$tag = !empty($settings['html_tag']) ? sanitize_key($settings['html_tag']) : 'h1';
					$valid_tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p');
					if (!in_array($tag, $valid_tags, true)) {
						$tag = 'h1';
					}
					if ('h1' === $tag) {
						woocommerce_template_single_title();
					} else {
						the_title('<' . $tag . ' class="product_title entry-title">', '</' . $tag . '>');
					}
					break;
				case 'heading':
					$settings = isset($el['settings']) ? $el['settings'] : array();
					$tag = !empty($settings['html_tag']) ? sanitize_key($settings['html_tag']) : 'h2';
					$valid_tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p');
					if (!in_array($tag, $valid_tags, true)) {
						$tag = 'h2';
					}
					$text = isset($settings['text']) && '' !== $settings['text'] ? esc_html($settings['text']) : esc_html__('Add Your Heading Text Here', 'single-product-customizer');
					$link_url = !empty($settings['link_url']) ? esc_url($settings['link_url']) : '';
					$target = !empty($settings['link_target_blank']) ? ' target="_blank" rel="noopener noreferrer"' : '';

					echo '<' . $tag . ' class="sppcfw-custom-heading">';
					if (!empty($link_url)) {
						echo '<a href="' . $link_url . '"' . $target . '>' . $text . '</a>';
					} else {
						echo $text;
					}
					echo '</' . $tag . '>';
					break;
				case 'text_editor':
					$settings = isset($el['settings']) ? $el['settings'] : array();
					$tag = !empty($settings['html_tag']) ? sanitize_key($settings['html_tag']) : 'div';
					$valid_tags = array('div', 'p', 'span');
					if (!in_array($tag, $valid_tags, true)) {
						$tag = 'div';
					}
					$content = isset($settings['text_content']) && '' !== $settings['text_content'] ? wp_kses_post($settings['text_content']) : esc_html__('Add your custom description or paragraph content here...', 'single-product-customizer');
					echo '<' . $tag . ' class="sppcfw-custom-text-block">' . nl2br($content) . '</' . $tag . '>';
					break;
				case 'product_price':
					woocommerce_template_single_price();
					break;
				case 'product_gallery':
					woocommerce_show_product_images();
					break;
				case 'image':
					$settings = isset($el['settings']) ? $el['settings'] : array();
					$styles = isset($el['styles']) ? $el['styles'] : array();
					$img_src = !empty($settings['custom_image_url']) ? $settings['custom_image_url'] : (!empty($settings['image_url']) ? $settings['image_url'] : '');

					if (!empty($img_src)) {
						$alt = !empty($settings['alt_text']) ? esc_attr($settings['alt_text']) : esc_attr($product->get_name());
						$link_to = isset($settings['link_to']) ? $settings['link_to'] : 'none';
						$custom_link = isset($settings['custom_link']) ? esc_url($settings['custom_link']) : '';
						$target = !empty($settings['link_target_blank']) ? ' target="_blank" rel="noopener noreferrer"' : '';
						if (!empty($settings['link_rel_nofollow'])) {
							$target = !empty($target) ? ' target="_blank" rel="noopener noreferrer nofollow"' : ' rel="nofollow"';
						}
						$caption_type = isset($settings['caption_type']) ? $settings['caption_type'] : 'none';
						$custom_caption = isset($settings['custom_caption']) ? esc_html($settings['custom_caption']) : '';
						$alignment = isset($styles['alignment']) ? $styles['alignment'] : (isset($settings['alignment']) ? $settings['alignment'] : 'center');
						$align_class = 'text-center';
						if ('left' === $alignment) {
							$align_class = 'text-left';
						} elseif ('right' === $alignment) {
							$align_class = 'text-right';
						}

						echo '<div class="sppcfw-custom-image-wrapper ' . esc_attr($align_class) . '">';
						if ('file' === $link_to) {
							echo '<a href="' . esc_url($img_src) . '"' . $target . ' class="sppcfw-image-link inline-block">';
						} elseif ('custom' === $link_to && !empty($custom_link)) {
							echo '<a href="' . $custom_link . '"' . $target . ' class="sppcfw-image-link inline-block">';
						}

						echo '<img src="' . esc_url($img_src) . '" alt="' . $alt . '" class="sppcfw-custom-image-el inline-block" style="max-width:100%;height:auto;" />';

						if ('file' === $link_to || ('custom' === $link_to && !empty($custom_link))) {
							echo '</a>';
						}

						if ('custom' === $caption_type && !empty($custom_caption)) {
							echo '<figcaption class="sppcfw-image-caption text-xs text-gray-500 mt-1.5">' . $custom_caption . '</figcaption>';
						}
						echo '</div>';
					}
					break;
				case 'variation_swatches':
					if ($product->is_type('variable')) {
						woocommerce_variable_add_to_cart();
					}
					break;
				case 'product_add_to_cart':
					woocommerce_template_single_add_to_cart();
					break;
				case 'product_rating':
					woocommerce_template_single_rating();
					break;
				case 'product_short_desc':
					woocommerce_template_single_excerpt();
					break;
				case 'product_description':
					woocommerce_output_product_data_tabs();
					break;
				case 'product_meta':
					woocommerce_template_single_meta();
					break;
				case 'product_meta_item':
					$meta_key = isset($el['metaKey']) ? $el['metaKey'] : '';
					if ($meta_key) {
						$label = isset($el['label']) ? esc_html($el['label']) : $meta_key;
						$val = get_post_meta($product->get_id(), $meta_key, true);
						if (empty($val) && 0 === strpos($meta_key, '_')) {
							// Check WC getter methods if standard meta empty
							if ('_sku' === $meta_key) {
								$val = $product->get_sku();
							} elseif ('_stock_status' === $meta_key) {
								$val = $product->get_stock_status();
							} elseif ('_weight' === $meta_key) {
								$val = $product->get_weight();
							} elseif ('_dimensions' === $meta_key) {
								$val = function_exists('wc_format_dimensions') ? wc_format_dimensions($product->get_dimensions(false)) : '';
							}
						}
						if (!empty($val)) {
							echo '<div class="sppcfw-custom-meta-field p-2 bg-gray-50 border rounded my-2">';
							echo '<strong>' . esc_html($label) . ': </strong>';
							echo '<span>' . esc_html(is_array($val) ? implode(', ', $val) : $val) . '</span>';
							echo '</div>';
						}
					}
					break;
				case 'custom_message':
					echo '<div class="sppcfw-custom-message-banner p-3 bg-indigo-100 text-indigo-800 rounded font-semibold my-2">';
					echo esc_html__('Special Offer: Free Shipping on all orders!', 'single-product-customizer');
					echo '</div>';
					break;
				case 'plus_minus_buttons':
					echo '<div class="sppcfw-stepper-widget my-2">';
					woocommerce_quantity_input(array('input_value' => 1));
					echo '</div>';
					break;
				case 'related_products':
					woocommerce_output_related_products();
					break;
				case 'upsell_products':
					woocommerce_upsell_display();
					break;
				default:
					break;
			}
		}
	}

	new SPPCFW_Builder_Renderer();
}
