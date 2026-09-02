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
			remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
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
				.sppcfw-builder-frontend-wrapper { width: 100% !important; max-width: 100% !important; float: none !important; clear: both !important; box-sizing: border-box !important; }
				.sppcfw-builder-section { width: 100%; box-sizing: border-box; }
				.sppcfw-container-boxed { margin-left: auto !important; margin-right: auto !important; }
				.sppcfw-container-full { width: 100% !important; }
				.sppcfw-flex-row { display: flex; flex-wrap: wrap; width: 100%; box-sizing: border-box; }
				.sppcfw-column { box-sizing: border-box; }
				.sppcfw-widget-item { width: 100% !important; box-sizing: border-box !important; }

				/* Hide empty summary container from native WooCommerce template */
				.woocommerce div.product > .summary.entry-summary:empty,
				.woocommerce-page div.product > .summary.entry-summary:empty,
				div.product > .summary:empty { display: none !important; width: 0 !important; float: none !important; margin: 0 !important; padding: 0 !important; }

				/* Full width product gallery override matching builder edit canvas */
				.woocommerce div.product .sppcfw-builder-frontend-wrapper div.images,
				.woocommerce-page div.product .sppcfw-builder-frontend-wrapper div.images,
				.woocommerce div.product .sppcfw-builder-frontend-wrapper .woocommerce-product-gallery,
				.woocommerce-page div.product .sppcfw-builder-frontend-wrapper .woocommerce-product-gallery,
				.sppcfw-builder-frontend-wrapper .woocommerce-product-gallery,
				.sppcfw-builder-frontend-wrapper div.product div.images,
				.sppcfw-builder-frontend-wrapper div.images,
				.sppcfw-builder-frontend-wrapper .images,
				.sppcfw-product-gallery-frontend-wrapper,
				.sppcfw-product-gallery-frontend-wrapper .woocommerce-product-gallery,
				.sppcfw-product-gallery-frontend-wrapper div.images {
					float: none !important;
					width: 100% !important;
					max-width: 100% !important;
					margin-left: 0 !important;
					margin-right: 0 !important;
					margin-bottom: 0 !important;
					opacity: 1 !important;
					box-sizing: border-box !important;
				}

				/* Gallery Component Styles */
				.sppcfw-product-gallery-frontend-wrapper {
					position: relative !important;
					width: 100% !important;
					max-width: 100% !important;
					box-sizing: border-box !important;
				}

				.sppcfw-gallery-main-container {
					position: relative !important;
					width: 100% !important;
					display: block !important;
				}

				.sppcfw-gallery-main-frame {
					position: relative !important;
					overflow: hidden !important;
					border-radius: inherit !important;
					width: 100% !important;
					cursor: default;
				}

				.sppcfw-gallery-main-frame img.sppcfw-gallery-main-img {
					width: 100% !important;
					height: auto !important;
					display: block !important;
					border-radius: inherit !important;
					transition: opacity 0.2s ease, transform 0.25s ease !important;
				}

				.sppcfw-gallery-sale-badge {
					position: absolute !important;
					top: 10px !important;
					left: 10px !important;
					z-index: 10 !important;
					background: #ef4444 !important;
					color: #ffffff !important;
					padding: 4px 10px !important;
					font-size: 11px !important;
					font-weight: 700 !important;
					border-radius: 9999px !important;
					text-transform: uppercase !important;
					box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important;
					line-height: 1 !important;
				}

				.sppcfw-gallery-lightbox-btn {
					position: absolute !important;
					top: 10px !important;
					right: 10px !important;
					z-index: 10 !important;
					background: rgba(255,255,255,0.9) !important;
					border: 1px solid rgba(0,0,0,0.1) !important;
					border-radius: 50% !important;
					width: 32px !important;
					height: 32px !important;
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
					cursor: pointer !important;
					color: #374151 !important;
					backdrop-filter: blur(4px) !important;
					box-shadow: 0 2px 6px rgba(0,0,0,0.1) !important;
					transition: all 0.2s ease !important;
					padding: 0 !important;
				}

				.sppcfw-gallery-lightbox-btn:hover {
					background: #ffffff !important;
					color: #111827 !important;
					transform: scale(1.05) !important;
				}

				.sppcfw-gallery-carousel-wrapper {
					position: relative !important;
					display: flex !important;
					align-items: center !important;
					gap: 6px !important;
					margin-top: 10px !important;
					width: 100% !important;
					user-select: none !important;
					box-sizing: border-box !important;
				}

				.sppcfw-gallery-carousel-track {
					display: flex !important;
					gap: 8px !important;
					overflow-x: auto !important;
					scroll-behavior: smooth !important;
					scrollbar-width: none !important;
					-ms-overflow-style: none !important;
					width: 100% !important;
					padding: 4px 1px !important;
					box-sizing: border-box !important;
				}

				.sppcfw-gallery-carousel-track::-webkit-scrollbar {
					display: none !important;
				}

				.sppcfw-carousel-nav {
					background: #ffffff !important;
					border: 1px solid #e5e7eb !important;
					color: #374151 !important;
					width: 28px !important;
					height: 28px !important;
					min-width: 28px !important;
					border-radius: 50% !important;
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
					cursor: pointer !important;
					box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
					transition: all 0.15s ease !important;
					z-index: 5 !important;
					font-size: 13px !important;
					line-height: 1 !important;
					padding: 0 !important;
				}

				.sppcfw-carousel-nav:hover {
					background: #f3f4f6 !important;
					color: #111827 !important;
					border-color: #d1d5db !important;
				}

				.sppcfw-gallery-carousel-slide,
				.sppcfw-gallery-grid-thumb {
					border: 2px solid #e5e7eb !important;
					border-radius: 6px !important;
					overflow: hidden !important;
					cursor: pointer !important;
					transition: all 0.2s ease !important;
					padding: 2px !important;
					background: #ffffff !important;
					box-sizing: border-box !important;
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
				}

				.sppcfw-gallery-carousel-slide:hover,
				.sppcfw-gallery-grid-thumb:hover {
					border-color: #9333ea !important;
				}

				.sppcfw-gallery-carousel-slide.is-active,
				.sppcfw-gallery-grid-thumb.is-active {
					border-color: #9333ea !important;
					box-shadow: 0 0 0 1px #9333ea !important;
				}

				.sppcfw-gallery-carousel-slide img,
				.sppcfw-gallery-grid-thumb img {
					width: 100% !important;
					height: auto !important;
					max-height: 70px !important;
					object-fit: contain !important;
					display: block !important;
					border-radius: 4px !important;
					pointer-events: none !important;
				}

				/* Lightbox Modal */
				.sppcfw-lightbox-modal {
					position: fixed !important;
					inset: 0 !important;
					z-index: 999999 !important;
					background: rgba(0, 0, 0, 0.85) !important;
					backdrop-filter: blur(8px) !important;
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
					padding: 24px !important;
					opacity: 0;
					visibility: hidden;
					transition: opacity 0.25s ease, visibility 0.25s ease !important;
				}

				.sppcfw-lightbox-modal.is-open {
					opacity: 1 !important;
					visibility: visible !important;
				}

				.sppcfw-lightbox-content {
					position: relative !important;
					max-width: 90vw !important;
					max-height: 90vh !important;
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
				}

				.sppcfw-lightbox-content img {
					max-width: 90vw !important;
					max-height: 90vh !important;
					object-fit: contain !important;
					border-radius: 8px !important;
					box-shadow: 0 20px 40px rgba(0,0,0,0.4) !important;
				}

				.sppcfw-lightbox-close {
					position: absolute !important;
					top: -40px !important;
					right: 0 !important;
					background: transparent !important;
					border: none !important;
					color: #ffffff !important;
					font-size: 28px !important;
					cursor: pointer !important;
					padding: 4px 8px !important;
					line-height: 1 !important;
				}
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

			$gallery_js = "
				document.addEventListener('DOMContentLoaded', function() {
					function initSppcfwGalleries() {
						document.querySelectorAll('.sppcfw-gallery-container').forEach(function(gallery) {
							var mainImg = gallery.querySelector('.sppcfw-gallery-main-img');
							var mainFrame = gallery.querySelector('.sppcfw-gallery-main-frame');
							var thumbs = gallery.querySelectorAll('.sppcfw-gallery-carousel-slide, .sppcfw-gallery-grid-thumb');
							var track = gallery.querySelector('.sppcfw-gallery-carousel-track');
							var prevBtn = gallery.querySelector('.sppcfw-carousel-prev');
							var nextBtn = gallery.querySelector('.sppcfw-carousel-next');
							var lightboxBtn = gallery.querySelector('.sppcfw-gallery-lightbox-btn');
							var zoomEnabled = gallery.getAttribute('data-zoom') === 'true';

							thumbs.forEach(function(thumb) {
								thumb.addEventListener('click', function(e) {
									e.preventDefault();
									thumbs.forEach(function(t) { t.classList.remove('is-active'); });
									thumb.classList.add('is-active');

									var newMain = thumb.getAttribute('data-main-src');
									var newFull = thumb.getAttribute('data-full-src');
									if (mainImg && newMain) {
										mainImg.style.opacity = '0.4';
										setTimeout(function() {
											mainImg.src = newMain;
											if (newFull) {
												mainImg.setAttribute('data-zoom-src', newFull);
												mainImg.setAttribute('data-full-src', newFull);
											}
											mainImg.style.opacity = '1';
										}, 100);
									}
								});
							});

							if (track && prevBtn && nextBtn) {
								prevBtn.addEventListener('click', function(e) {
									e.preventDefault();
									var scrollAmount = track.clientWidth * 0.75;
									track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
								});
								nextBtn.addEventListener('click', function(e) {
									e.preventDefault();
									var scrollAmount = track.clientWidth * 0.75;
									track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
								});
							}

							if (zoomEnabled && mainFrame && mainImg) {
								mainFrame.addEventListener('mousemove', function(e) {
									var rect = mainFrame.getBoundingClientRect();
									var x = ((e.clientX - rect.left) / rect.width) * 100;
									var y = ((e.clientY - rect.top) / rect.height) * 100;
									mainImg.style.transformOrigin = x + '% ' + y + '%';
									mainImg.style.transform = 'scale(1.5)';
								});
								mainFrame.addEventListener('mouseleave', function() {
									mainImg.style.transformOrigin = 'center center';
									mainImg.style.transform = 'scale(1)';
								});
							}

							if (lightboxBtn && mainImg) {
								lightboxBtn.addEventListener('click', function(e) {
									e.preventDefault();
									var fullSrc = mainImg.getAttribute('data-full-src') || mainImg.src;
									var modal = document.querySelector('.sppcfw-lightbox-modal');
									if (!modal) {
										modal = document.createElement('div');
										modal.className = 'sppcfw-lightbox-modal';
										modal.innerHTML = '<div class=\"sppcfw-lightbox-content\"><button type=\"button\" class=\"sppcfw-lightbox-close\" aria-label=\"Close\">&times;</button><img src=\"\" alt=\"Full size image\" /></div>';
										document.body.appendChild(modal);

										modal.querySelector('.sppcfw-lightbox-close').addEventListener('click', function() {
											modal.classList.remove('is-open');
										});
										modal.addEventListener('click', function(evt) {
											if (evt.target === modal) {
												modal.classList.remove('is-open');
											}
										});
										document.addEventListener('keydown', function(evt) {
											if (evt.key === 'Escape' && modal.classList.contains('is-open')) {
												modal.classList.remove('is-open');
											}
										});
									}
									modal.querySelector('img').src = fullSrc;
									modal.classList.add('is-open');
								});
							}
						});
					}
					initSppcfwGalleries();
				});
			";
			wp_register_script('sppcfw-builder-frontend-gallery', '', array(), false, true);
			wp_enqueue_script('sppcfw-builder-frontend-gallery');
			wp_add_inline_script('sppcfw-builder-frontend-gallery', $gallery_js);
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
						$css .= ".sppcfw-el-{$id} h1, .sppcfw-el-{$id} h2, .sppcfw-el-{$id} h3, .sppcfw-el-{$id} h4, .sppcfw-el-{$id} h5, .sppcfw-el-{$id} h6, .sppcfw-el-{$id} .product_title, .sppcfw-el-{$id} .sppcfw-custom-heading, .sppcfw-el-{$id} a { color: " . esc_attr($text_color) . ' !important; }';
					}

					$bg_color = $this->sppcfw_get_device_prop($styles, 'bg_color', $device, '');
					if (!empty($bg_color)) {
						$css .= 'background-color: ' . esc_attr($bg_color) . ' !important;';
					}

					$font_size = $this->sppcfw_get_device_prop($styles, 'font_size', $device, '');
					if (!empty($font_size)) {
						$css .= 'font-size: ' . esc_attr($font_size) . ' !important;';
						$css .= ".sppcfw-el-{$id} h1, .sppcfw-el-{$id} h2, .sppcfw-el-{$id} h3, .sppcfw-el-{$id} h4, .sppcfw-el-{$id} h5, .sppcfw-el-{$id} h6, .sppcfw-el-{$id} .product_title, .sppcfw-el-{$id} .sppcfw-custom-heading { font-size: " . esc_attr($font_size) . ' !important; }';
					}

					$font_family = $this->sppcfw_get_device_prop($styles, 'font_family', $device, '');
					if (!empty($font_family) && 'Inherit' !== $font_family) {
						$css .= 'font-family: ' . esc_attr($font_family) . ', sans-serif !important;';
						$css .= ".sppcfw-el-{$id} h1, .sppcfw-el-{$id} h2, .sppcfw-el-{$id} h3, .sppcfw-el-{$id} h4, .sppcfw-el-{$id} h5, .sppcfw-el-{$id} h6, .sppcfw-el-{$id} .product_title, .sppcfw-el-{$id} .sppcfw-custom-heading { font-family: " . esc_attr($font_family) . ', sans-serif !important; }';
					}

					$font_weight = $this->sppcfw_get_device_prop($styles, 'font_weight', $device, '');
					if (!empty($font_weight) && 'Default' !== $font_weight) {
						$css .= 'font-weight: ' . esc_attr($font_weight) . ' !important;';
						$css .= ".sppcfw-el-{$id} h1, .sppcfw-el-{$id} h2, .sppcfw-el-{$id} h3, .sppcfw-el-{$id} h4, .sppcfw-el-{$id} h5, .sppcfw-el-{$id} h6, .sppcfw-el-{$id} .product_title, .sppcfw-el-{$id} .sppcfw-custom-heading, .sppcfw-el-{$id} a { font-weight: " . esc_attr($font_weight) . ' !important; }';
					}

					$line_height = $this->sppcfw_get_device_prop($styles, 'line_height', $device, '');
					if (!empty($line_height)) {
						$css .= 'line-height: ' . esc_attr($line_height) . ' !important;';
						$css .= ".sppcfw-el-{$id} h1, .sppcfw-el-{$id} h2, .sppcfw-el-{$id} h3, .sppcfw-el-{$id} h4, .sppcfw-el-{$id} h5, .sppcfw-el-{$id} h6, .sppcfw-el-{$id} .product_title, .sppcfw-el-{$id} .sppcfw-custom-heading { line-height: " . esc_attr($line_height) . ' !important; }';
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
					} else {
						$rad_t = $this->sppcfw_get_device_prop($styles, 'border_radius_top', $device, '');
						$rad_r = $this->sppcfw_get_device_prop($styles, 'border_radius_right', $device, '');
						$rad_b = $this->sppcfw_get_device_prop($styles, 'border_radius_bottom', $device, '');
						$rad_l = $this->sppcfw_get_device_prop($styles, 'border_radius_left', $device, '');
						if (!empty($rad_t) || !empty($rad_r) || !empty($rad_b) || !empty($rad_l)) {
							$t = !empty($rad_t) ? $rad_t : '0px';
							$r = !empty($rad_r) ? $rad_r : '0px';
							$b = !empty($rad_b) ? $rad_b : '0px';
							$l = !empty($rad_l) ? $rad_l : '0px';
							$css .= "border-radius: {$t} {$r} {$b} {$l} !important;";
						}
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

					$opacity = $this->sppcfw_get_device_prop($styles, 'opacity', $device, '');
					if ('' !== $opacity && null !== $opacity) {
						$css .= 'opacity: ' . esc_attr($opacity) . ' !important;';
					}

					$alignment = $this->sppcfw_get_device_prop($styles, 'alignment', $device, '');
					if (!empty($alignment)) {
						$css .= 'text-align: ' . esc_attr($alignment) . ' !important;';
					}
					$css .= '}';

					// Specific element component style rules
					$btn_bg = $this->sppcfw_get_device_prop($styles, 'btn_bg_color', $device, '');
					$btn_color = $this->sppcfw_get_device_prop($styles, 'btn_text_color', $device, '');
					$btn_radius = $this->sppcfw_get_device_prop($styles, 'btn_border_radius', $device, '');
					if (!empty($btn_bg) || !empty($btn_color) || !empty($btn_radius)) {
						$css .= ".sppcfw-el-{$id} button, .sppcfw-el-{$id} .button, .sppcfw-el-{$id} .single_add_to_cart_button {";
						if (!empty($btn_bg)) {
							$css .= 'background-color: ' . esc_attr($btn_bg) . ' !important;';
						}
						if (!empty($btn_color)) {
							$css .= 'color: ' . esc_attr($btn_color) . ' !important;';
						}
						if (!empty($btn_radius)) {
							$css .= 'border-radius: ' . esc_attr($btn_radius) . ' !important;';
						}
						$css .= '}';
					}

					$price_color = $this->sppcfw_get_device_prop($styles, 'price_color', $device, '');
					$sale_price_color = $this->sppcfw_get_device_prop($styles, 'sale_price_color', $device, '');
					if (!empty($price_color)) {
						$css .= ".sppcfw-el-{$id} .price, .sppcfw-el-{$id} .amount { color: " . esc_attr($price_color) . ' !important; }';
					}
					if (!empty($sale_price_color)) {
						$css .= ".sppcfw-el-{$id} ins, .sppcfw-el-{$id} ins .amount { color: " . esc_attr($sale_price_color) . ' !important; }';
					}

					$star_color = $this->sppcfw_get_device_prop($styles, 'star_color', $device, '');
					$star_size = $this->sppcfw_get_device_prop($styles, 'star_size', $device, '');
					$review_count_color = $this->sppcfw_get_device_prop($styles, 'review_count_color', $device, '');
					if (!empty($star_color)) {
						$css .= ".sppcfw-el-{$id} .star-rating span::before, .sppcfw-el-{$id} .star-rating::before { color: " . esc_attr($star_color) . ' !important; }';
					}
					if (!empty($star_size)) {
						$css .= ".sppcfw-el-{$id} .star-rating { font-size: " . esc_attr($star_size) . ' !important; }';
					}
					if (!empty($review_count_color)) {
						$css .= ".sppcfw-el-{$id} .woocommerce-review-link { color: " . esc_attr($review_count_color) . ' !important; }';
					}

					$active_tab_color = $this->sppcfw_get_device_prop($styles, 'active_tab_color', $device, '');
					if (!empty($active_tab_color)) {
						$css .= ".sppcfw-el-{$id} .woocommerce-tabs ul.tabs li.active a { color: " . esc_attr($active_tab_color) . ' !important; border-bottom-color: ' . esc_attr($active_tab_color) . ' !important; }';
					}

					$label_color = $this->sppcfw_get_device_prop($styles, 'label_color', $device, '');
					if (!empty($label_color)) {
						$css .= ".sppcfw-el-{$id} strong, .sppcfw-el-{$id} .meta-label { color: " . esc_attr($label_color) . ' !important; }';
					}

					if ('container' === $type) {
						$gap = esc_attr($this->sppcfw_get_device_prop($settings, 'gap', $device, '16px'));
						$flex_direction = esc_attr($this->sppcfw_get_device_prop($settings, 'flex_direction', $device, 'row'));
						$css .= ".sppcfw-el-{$id} > .sppcfw-flex-row { gap: {$gap} !important; flex-direction: {$flex_direction} !important; }";
					}

					if ('product_gallery' === $type) {
						$cols = !empty($settings['gallery_columns']) ? intval($settings['gallery_columns']) : 4;
						if ($cols < 2 || $cols > 8) {
							$cols = 4;
						}
						$align = $this->sppcfw_get_device_prop($styles, 'alignment', $device, (!empty($settings['alignment']) ? $settings['alignment'] : 'center'));
						$justify = 'center';
						if ('left' === $align) {
							$justify = 'flex-start';
						} elseif ('right' === $align) {
							$justify = 'flex-end';
						}
						$css .= ".sppcfw-el-{$id} .sppcfw-gallery-thumbs-grid { grid-template-columns: repeat({$cols}, minmax(0, 1fr)) !important; }";
						$css .= ".sppcfw-el-{$id} .sppcfw-gallery-carousel-slide { flex: 0 0 calc((100% - (" . ($cols - 1) . " * 8px)) / {$cols}) !important; }";
						$css .= ".sppcfw-el-{$id} .sppcfw-gallery-main-container { justify-content: {$justify} !important; }";
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
					echo '<div class="sppcfw-widget-item' . (!empty($css_class) ? ' ' . $css_class : '') . '">';
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
			$id = isset($el['id']) ? esc_attr($el['id']) : '';
			$el_class = !empty($id) ? ' sppcfw-el-' . $id : '';

			switch ($type) {
				case 'product_title':
					$settings = isset($el['settings']) ? $el['settings'] : array();
					$tag = !empty($settings['html_tag']) ? sanitize_key($settings['html_tag']) : 'h1';
					$valid_tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p');
					if (!in_array($tag, $valid_tags, true)) {
						$tag = 'h1';
					}
					$title_text = $product->get_name();
					$link_to_product = !empty($settings['link_to_product']);
					if ($link_to_product) {
						$title_html = '<a href="' . esc_url(get_permalink($product->get_id())) . '" class="sppcfw-product-title-link">' . esc_html($title_text) . '</a>';
					} else {
						$title_html = esc_html($title_text);
					}
					echo '<' . $tag . ' class="product_title entry-title' . $el_class . '">' . $title_html . '</' . $tag . '>';
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

					echo '<' . $tag . ' class="sppcfw-custom-heading' . $el_class . '">';
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
					echo '<' . $tag . ' class="sppcfw-custom-text-block' . $el_class . '">' . nl2br($content) . '</' . $tag . '>';
					break;
				case 'product_price':
					echo '<div class="sppcfw-price-wrapper' . $el_class . '">';
					woocommerce_template_single_price();
					echo '</div>';
					break;
				case 'product_gallery':
					$settings = isset($el['settings']) ? $el['settings'] : array();
					$styles = isset($el['styles']) ? $el['styles'] : array();

					$show_thumbnails = !isset($settings['show_thumbnails']) || true === $settings['show_thumbnails'] || 'true' === $settings['show_thumbnails'] || 1 === $settings['show_thumbnails'] || '1' === $settings['show_thumbnails'];
					if (isset($settings['show_thumbnails']) && (false === $settings['show_thumbnails'] || 'false' === $settings['show_thumbnails'] || 0 === $settings['show_thumbnails'] || '0' === $settings['show_thumbnails'])) {
						$show_thumbnails = false;
					}

					$thumbs_layout = !empty($settings['thumbs_layout']) ? sanitize_key($settings['thumbs_layout']) : 'grid';
					$cols = !empty($settings['gallery_columns']) ? intval($settings['gallery_columns']) : 4;
					if ($cols < 2 || $cols > 8) {
						$cols = 4;
					}

					$show_carousel_arrows = !isset($settings['show_carousel_arrows']) || false !== $settings['show_carousel_arrows'];
					if (isset($settings['show_carousel_arrows']) && (false === $settings['show_carousel_arrows'] || 'false' === $settings['show_carousel_arrows'] || 0 === $settings['show_carousel_arrows'] || '0' === $settings['show_carousel_arrows'])) {
						$show_carousel_arrows = false;
					}

					$enable_lightbox = !isset($settings['enable_lightbox']) || false !== $settings['enable_lightbox'];
					if (isset($settings['enable_lightbox']) && (false === $settings['enable_lightbox'] || 'false' === $settings['enable_lightbox'] || 0 === $settings['enable_lightbox'] || '0' === $settings['enable_lightbox'])) {
						$enable_lightbox = false;
					}

					$enable_zoom = !isset($settings['enable_zoom']) || false !== $settings['enable_zoom'];
					if (isset($settings['enable_zoom']) && (false === $settings['enable_zoom'] || 'false' === $settings['enable_zoom'] || 0 === $settings['enable_zoom'] || '0' === $settings['enable_zoom'])) {
						$enable_zoom = false;
					}

					$alignment = isset($styles['alignment']) ? $styles['alignment'] : (isset($settings['alignment']) ? $settings['alignment'] : 'center');
					$align_class = 'sppcfw-justify-center sppcfw-text-center';
					if ('left' === $alignment) {
						$align_class = 'sppcfw-justify-start sppcfw-text-left';
					} elseif ('right' === $alignment) {
						$align_class = 'sppcfw-justify-end sppcfw-text-right';
					}

					$post_thumbnail_id = $product->get_image_id();
					$attachment_ids = $product->get_gallery_image_ids();

					$all_images = array();
					if ($post_thumbnail_id) {
						$full_src = wp_get_attachment_image_url($post_thumbnail_id, 'full');
						$large_src = wp_get_attachment_image_url($post_thumbnail_id, 'woocommerce_single');
						if (!$large_src) {
							$large_src = $full_src;
						}
						$thumb_src = wp_get_attachment_image_url($post_thumbnail_id, 'woocommerce_gallery_thumbnail');
						if (!$thumb_src) {
							$thumb_src = wp_get_attachment_image_url($post_thumbnail_id, 'thumbnail');
						}
						if (!$thumb_src) {
							$thumb_src = $full_src;
						}
						$alt = get_post_meta($post_thumbnail_id, '_wp_attachment_image_alt', true);
						if (empty($alt)) {
							$alt = $product->get_name();
						}

						$all_images[] = array(
							'id' => $post_thumbnail_id,
							'full' => $full_src,
							'main' => $large_src,
							'thumb' => $thumb_src,
							'alt' => $alt,
						);
					}

					if (!empty($attachment_ids)) {
						foreach ($attachment_ids as $att_id) {
							$full_src = wp_get_attachment_image_url($att_id, 'full');
							$large_src = wp_get_attachment_image_url($att_id, 'woocommerce_single');
							if (!$large_src) {
								$large_src = $full_src;
							}
							$thumb_src = wp_get_attachment_image_url($att_id, 'woocommerce_gallery_thumbnail');
							if (!$thumb_src) {
								$thumb_src = wp_get_attachment_image_url($att_id, 'thumbnail');
							}
							if (!$thumb_src) {
								$thumb_src = $full_src;
							}
							$alt = get_post_meta($att_id, '_wp_attachment_image_alt', true);
							if (empty($alt)) {
								$alt = $product->get_name();
							}

							$all_images[] = array(
								'id' => $att_id,
								'full' => $full_src,
								'main' => $large_src,
								'thumb' => $thumb_src,
								'alt' => $alt,
							);
						}
					}

					if (empty($all_images)) {
						$placeholder = wc_placeholder_img_src('woocommerce_single');
						$all_images[] = array(
							'id' => 0,
							'full' => $placeholder,
							'main' => $placeholder,
							'thumb' => $placeholder,
							'alt' => $product->get_name(),
						);
					}

					$first_image = $all_images[0];
					$is_on_sale = $product->is_on_sale();

					echo '<div class="sppcfw-product-gallery-frontend-wrapper' . $el_class . ' sppcfw-gallery-container ' . esc_attr($align_class) . '" data-zoom="' . ($enable_zoom ? 'true' : 'false') . '" data-lightbox="' . ($enable_lightbox ? 'true' : 'false') . '">';

					// Main Featured Image Container
					echo '<div class="sppcfw-gallery-main-container" style="position:relative; display:inline-block; max-width:100%;">';

					if ($is_on_sale) {
						echo '<span class="onsale sppcfw-gallery-sale-badge">' . esc_html__('Sale!', 'woocommerce') . '</span>';
					}

					echo '<div class="sppcfw-gallery-main-frame' . ($enable_zoom ? ' sppcfw-zoom-enabled' : '') . '">';
					echo '<img src="' . esc_url($first_image['main']) . '" data-zoom-src="' . esc_url($first_image['full']) . '" data-full-src="' . esc_url($first_image['full']) . '" alt="' . esc_attr($first_image['alt']) . '" class="sppcfw-gallery-main-img" style="max-width:100%;height:auto;display:block;" />';

					if ($enable_lightbox) {
						echo '<button type="button" class="sppcfw-gallery-lightbox-btn" title="' . esc_attr__('View full image', 'single-product-customizer') . '"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></button>';
					}

					echo '</div>'; // close sppcfw-gallery-main-frame
					echo '</div>'; // close sppcfw-gallery-main-container

					// Thumbnails Row
					if ($show_thumbnails && count($all_images) > 1) {
						if ('carousel' === $thumbs_layout) {
							echo '<div class="sppcfw-gallery-carousel-wrapper" data-cols="' . esc_attr($cols) . '">';
							if ($show_carousel_arrows) {
								echo '<button type="button" class="sppcfw-carousel-nav sppcfw-carousel-prev" aria-label="Previous thumbnails">&#10094;</button>';
							}
							echo '<div class="sppcfw-gallery-carousel-track">';
							foreach ($all_images as $idx => $img) {
								$active_class = (0 === $idx) ? ' is-active' : '';
								echo '<div class="sppcfw-gallery-carousel-slide' . $active_class . '" data-main-src="' . esc_url($img['main']) . '" data-full-src="' . esc_url($img['full']) . '" data-index="' . esc_attr($idx) . '" style="flex:0 0 calc((100% - (' . ($cols - 1) . ' * 8px)) / ' . $cols . '); min-width:40px; box-sizing:border-box;">';
								echo '<img src="' . esc_url($img['thumb']) . '" alt="' . esc_attr($img['alt']) . '" class="sppcfw-thumb-img" />';
								echo '</div>';
							}
							echo '</div>'; // close sppcfw-gallery-carousel-track
							if ($show_carousel_arrows) {
								echo '<button type="button" class="sppcfw-carousel-nav sppcfw-carousel-next" aria-label="Next thumbnails">&#10095;</button>';
							}
							echo '</div>'; // close sppcfw-gallery-carousel-wrapper
						} else {
							// Grid mode
							echo '<div class="sppcfw-gallery-thumbs-grid" style="display:grid; grid-template-columns:repeat(' . esc_attr($cols) . ', minmax(0, 1fr)); gap:8px; margin-top:10px;">';
							foreach ($all_images as $idx => $img) {
								$active_class = (0 === $idx) ? ' is-active' : '';
								echo '<div class="sppcfw-gallery-grid-thumb' . $active_class . '" data-main-src="' . esc_url($img['main']) . '" data-full-src="' . esc_url($img['full']) . '" data-index="' . esc_attr($idx) . '">';
								echo '<img src="' . esc_url($img['thumb']) . '" alt="' . esc_attr($img['alt']) . '" class="sppcfw-thumb-img" />';
								echo '</div>';
							}
							echo '</div>';
						}
					}

					echo '</div>'; // close sppcfw-product-gallery-frontend-wrapper
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

						echo '<div class="sppcfw-custom-image-wrapper' . $el_class . ' ' . esc_attr($align_class) . '">';
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
						echo '<div class="sppcfw-swatches-wrapper' . $el_class . '">';
						woocommerce_variable_add_to_cart();
						echo '</div>';
					}
					break;
				case 'product_add_to_cart':
					echo '<div class="sppcfw-add-to-cart-wrapper' . $el_class . '">';
					woocommerce_template_single_add_to_cart();
					echo '</div>';
					break;
				case 'product_rating':
					echo '<div class="sppcfw-rating-wrapper' . $el_class . '">';
					woocommerce_template_single_rating();
					echo '</div>';
					break;
				case 'product_short_desc':
					echo '<div class="sppcfw-short-desc-wrapper' . $el_class . '">';
					woocommerce_template_single_excerpt();
					echo '</div>';
					break;
				case 'product_description':
					echo '<div class="sppcfw-tabs-wrapper' . $el_class . '">';
					woocommerce_output_product_data_tabs();
					echo '</div>';
					break;
				case 'product_meta':
					echo '<div class="sppcfw-meta-wrapper' . $el_class . '">';
					woocommerce_template_single_meta();
					echo '</div>';
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
							echo '<div class="sppcfw-custom-meta-field' . $el_class . ' p-2 bg-gray-50 border rounded my-2">';
							echo '<strong>' . esc_html($label) . ': </strong>';
							echo '<span>' . esc_html(is_array($val) ? implode(', ', $val) : $val) . '</span>';
							echo '</div>';
						}
					}
					break;
				case 'custom_message':
					echo '<div class="sppcfw-custom-message-banner' . $el_class . ' p-3 bg-indigo-100 text-indigo-800 rounded font-semibold my-2">';
					echo esc_html__('Special Offer: Free Shipping on all orders!', 'single-product-customizer');
					echo '</div>';
					break;
				case 'plus_minus_buttons':
					echo '<div class="sppcfw-stepper-widget' . $el_class . ' my-2">';
					woocommerce_quantity_input(array('input_value' => 1));
					echo '</div>';
					break;
				case 'related_products':
					echo '<div class="sppcfw-related-wrapper' . $el_class . '">';
					woocommerce_output_related_products();
					echo '</div>';
					break;
				case 'upsell_products':
					echo '<div class="sppcfw-upsell-wrapper' . $el_class . '">';
					woocommerce_upsell_display();
					echo '</div>';
					break;
				default:
					break;
			}
		}
	}

	new SPPCFW_Builder_Renderer();
}
