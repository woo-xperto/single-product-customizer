<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('SPPCFW_backend_ui')):
	class SPPCFW_backend_ui
	{
		/**
		 * Sections array.
		 *
		 * @var   array
		 * @since 1.0.0
		 */
		private $sections_array = array();

		/**
		 * Fields array.
		 *
		 * @var   array
		 * @since 1.0.0
		 */
		private $fields_array = array();

		/**
		 * Constructor.
		 *
		 * @since  1.0.0
		 */
		public function __construct()
		{
			// Hook it up.
			add_action('admin_init', array($this, 'admin_init'));
			add_action('admin_menu', array($this, 'sppcfw_register_admin_menu'));
		}

		/**
		 * Set Sections.
		 *
		 * @param array $sections
		 * @since 1.0.0
		 */
		public function set_sections($sections)
		{
			// Bail if not array.
			if (!is_array($sections)) {
				return false;
			}

			// Assign to the sections array.
			$this->sections_array = $sections;

			return $this;
		}

		/**
		 * Add a single section.
		 *
		 * @param array $section
		 * @since 1.0.0
		 */
		public function add_section($section)
		{
			// echo $section['id'];
			// Bail if not array.
			if (!is_array($section)) {
				return false;
			}

			// Assign the section to sections array.
			$this->sections_array[] = $section;

			// print_r($this->sections_array);
			return $this;
		}

		/**
		 * Set Fields.
		 *
		 * @since 1.0.0
		 */
		public function set_fields($fields)
		{
			// Bail if not array.
			if (!is_array($fields)) {
				return false;
			}

			// Assign the fields.
			$this->fields_array = $fields;

			return $this;
		}

		/**
		 * Add a single field.
		 *
		 * @since 1.0.0
		 */
		public function add_field($section, $field_array)
		{
			// Set the defaults
			$defaults = array(
				'id' => '',
				'name' => '',
				'desc' => '',
				'type' => 'text',
				'class' => ''
			);

			// Combine the defaults with user's arguements.
			$arg = wp_parse_args($field_array, $defaults);
			// print_r($arg);
			// Each field is an array named against its section.
			$this->fields_array[$section][] = $arg;

			return $this;
		}

		/**
		 * Initialize API.
		 *
		 * Initializes and registers the settings sections and fields.
		 * Usually this should be called at `admin_init` hook.
		 *
		 * @since  1.0.0
		 */
		function admin_init()
		{
			/**
			 * Register the sections.
			 *
			 * Sections array is like this:
			 *
			 * $sections_array = array (
			 *   $section_array,
			 *   $section_array,
			 *   $section_array,
			 * );
			 *
			 * Section array is like this:
			 *
			 * $section_array = array (
			 *   'id'    => 'section_id',
			 *   'title' => 'Section Title'
			 * );
			 *
			 * @since 1.0.0
			 */
			// print_r($this->sections_array);
			foreach ($this->sections_array as $section) {
				if (false == get_option($section['id'])) {
					// Add a new field as section ID.
					add_option($section['id']);
				}

				// Deals with sections description.
				if (isset($section['desc']) && !empty($section['desc'])) {
					// Build HTML.
					$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';

					// Create the callback for description.
					$callback = function () use ($section) {
						echo esc_html(str_replace('"', '\"', $section['desc']));
					};
				} elseif (isset($section['callback'])) {
					$callback = $section['callback'];
				} else {
					$callback = null;
				}

				/**
				 * Add a new section to a settings page.
				 *
				 * @param string $id
				 * @param string $title
				 * @param callable $callback
				 * @param string $page | Page is same as section ID.
				 * @since 1.0.0
				 */
				add_settings_section($section['id'], $section['title'], $callback, $section['id']);
			}  // foreach ended.

			/**
			 * Register settings fields.
			 *
			 * Fields array is like this:
			 *
			 * $fields_array = array (
			 *   $section => $field_array,
			 *   $section => $field_array,
			 *   $section => $field_array,
			 * );
			 *
			 *
			 * Field array is like this:
			 *
			 * $field_array = array (
			 *   'id'   => 'id',
			 *   'name' => 'Name',
			 *   'type' => 'text',
			 * );
			 *
			 * @since 1.0.0
			 */
			foreach ($this->fields_array as $section => $field_array) {
				foreach ($field_array as $field) {
					// ID.
					$id = isset($field['id']) ? $field['id'] : false;
					$class = isset($field['class']) ? $field['class'] : '';

					// Type.
					$type = isset($field['type']) ? $field['type'] : 'text';

					// Name.
					$name = isset($field['name']) ? $field['name'] : __('No Name Added', 'single-product-customizer');

					// Label for.
					$label_for = "{$section}[{$field['id']}]";

					// Description.
					$description = isset($field['desc']) ? $field['desc'] : '';

					// Size.
					$size = isset($field['size']) ? $field['size'] : null;

					// Options.
					$options = isset($field['options']) ? $field['options'] : '';

					// Standard default value.
					$default = isset($field['default']) ? $field['default'] : '';

					// Standard default placeholder.
					$placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';

					// Sanitize Callback.
					$sanitize_callback = isset($field['sanitize_callback']) ? $field['sanitize_callback'] : '';

					$args = array(
						'id' => $id,
						'class' => $class,
						'help_link' => isset($field['help_link']) ? $field['help_link'] : '',
						'type' => $type,
						'name' => $name,
						'label_for' => $label_for,
						'desc' => $description,
						'section' => $section,
						'size' => $size,
						'options' => $options,
						'std' => $default,
						'placeholder' => $placeholder,
						'sanitize_callback' => $sanitize_callback,
					);

					/**
					 * Add a new field to a section of a settings page.
					 *
					 * @param string   $id
					 * @param string   $title
					 * @param callable $callback
					 * @param string   $page
					 * @param string   $section = 'default'
					 * @param array    $args = array()
					 * @since 1.0.0
					 */

					// @param string 	$id
					$field_id = $section . '[' . $field['id'] . ']';

					add_settings_field(
						$field_id,
						$name,
						array($this, 'callback_' . $type),
						$section,
						$section,
						$args
					);
				}  // foreach ended.
			}  // foreach ended.

			// Creates our settings in the fields table.
			foreach ($this->sections_array as $section) {
				/**
				 * Registers a setting and its sanitization callback.
				 *
				 * @param string $field_group   | A settings group name.
				 * @param string $field_name    | The name of an option to sanitize and save.
				 * @param callable  $sanitize_callback = ''
				 * @since 1.0.0
				 */
				// phpcs:ignore
				register_setting($section['id'], $section['id'], array($this, 'sanitize_fields'));
			}  // foreach ended.
		}  // admin_init() ended.

		/**
		 * Sanitize callback for Settings API fields.
		 *
		 * @since 1.0.0
		 */
		public function sanitize_fields($fields)
		{
			foreach ($fields as $field_slug => $field_value) {
				$sanitize_callback = $this->get_sanitize_callback($field_slug);

				// If callback is set, call it.
				if ($sanitize_callback) {
					$fields[$field_slug] = call_user_func($sanitize_callback, $field_value);
					continue;
				}
			}

			return $fields;
		}

		/**
		 * Get sanitization callback for given option slug
		 *
		 * @param string $slug option slug.
		 * @return mixed string | bool false
		 * @since  1.0.0
		 */
		function get_sanitize_callback($slug = '')
		{
			if (empty($slug)) {
				return false;
			}

			// Iterate over registered fields and see if we can find proper callback.
			foreach ($this->fields_array as $section => $field_array) {
				foreach ($field_array as $field) {
					if ($field['name'] != $slug) {
						continue;
					}

					// Return the callback name.
					return isset($field['sanitize_callback']) && is_callable($field['sanitize_callback']) ? $field['sanitize_callback'] : false;
				}
			}

			return false;
		}

		/**
		 * Get field description for display
		 *
		 * @param array $args settings field args
		 */
		public function get_field_description($args)
		{
			$desc_text = '';

			if (!empty($args['desc'])) {
				$desc_text = $args['desc'];
			}

			// Append help link/icon if provided via 'help_link' (or legacy 'link')
			$link = '';
			if (isset($args['help_link']) && !empty($args['help_link'])) {
				$link = $args['help_link'];
			} elseif (isset($args['link']) && !empty($args['link'])) {
				$link = $args['link'];
			}

			if (!empty($link)) {
				if (function_exists('sppcfw_help_youtube_link')) {
					ob_start();
					sppcfw_help_youtube_link($link);
					$icon = ob_get_clean();
					$desc_text .= ' ' . $icon;
				}
			}

			if (!empty($desc_text)) {
				return sprintf('<p >%s</p>', $desc_text);
			}

			return '';
		}

		/**
		 * Displays a title field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_title($args)
		{
			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			if ('' !== $args['name']) {
				$name = $args['name'];
			} else {
			};
			$type = isset($args['type']) ? $args['type'] : __('Title', 'single-product-customizer');

			$html = '';
			echo esc_html($html);
		}

		/**
		 * Displays a text field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_text($args)
		{
			// Retrieve the value from the database or set the default
			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));

			// Determine input size and type
			$size = isset($args['size']) ? esc_attr($args['size']) : 'regular';
			$type = isset($args['type']) ? esc_attr($args['type']) : 'text';

			$html = sprintf(
				'<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s" placeholder="%6$s" />',
				esc_attr($type),
				esc_attr($size),
				esc_attr($args['section']),
				esc_attr($args['id']),
				esc_attr($value),
				esc_attr($args['placeholder'])
			);

			$html .= $this->get_field_description($args);

			// Define allowed HTML tags and attributes
			$allowed_html = array(
				'input' => array(
					'type' => array(),
					'class' => array(),
					'id' => array(),
					'name' => array(),
					'value' => array(),
					'placeholder' => array(),
				),
				'p' => array(
					'class' => array(),
				),
				'a' => array(
					'href' => array(),
					'target' => array(),
					'rel' => array(),
					'class' => array(),
				),
				'span' => array(
					'class' => array(),
					'style' => array(),
				),
			);

			// Sanitize and output the HTML
			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays a url field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_url($args)
		{
			$this->callback_text($args);
		}

		/**
		 * Displays a number field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_number($args)
		{
			$this->callback_text($args);
		}

		/**
		 * Displays a checkbox for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_checkbox($args)
		{
			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));

			$html = '<fieldset>';
			$html .= sprintf('<label for="wxspc-%1$s[%2$s]">', $args['section'], $args['id']);
			$html .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id']);

			// Conditional Replacement for PRO fields when Pro is not active
			if (!sppcfw_is_pro_active() && $args['type'] === 'checkbox' && in_array($args['id'], array('enable_customizer_for_category', 'enable_customizer_for_product', 'enable_min_max_qty', 'enable_custom_tab', 'enable_additional_content', 'related_product_categories', 'move_image_section_to_right'), true)) {
				// Replace the input field with a notice for the PRO version
				$html .= sprintf('<p class="pro-notice">%s</p>', __('PRO Feature', 'single-product-customizer'));
			} else {
				// Render the checkbox if not the 'enable_min_max_qty' field
				$html .= sprintf(
					'<input type="checkbox" class="checkbox %1$s" id="wxspc-%2$s[%3$s]" name="%2$s[%3$s]" value="on" %4$s />',
					esc_attr($args['class']),
					esc_attr($args['section']),
					esc_attr($args['id']),
					checked($value, 'on', false)
				);
			}

			// Build label text and append YouTube help link/icon if provided
			$label_text = $args['desc'];
			if (isset($args['help_link']) && !empty($args['help_link'])) {
				if (function_exists('sppcfw_help_youtube_link')) {
					ob_start();
					sppcfw_help_youtube_link($args['help_link']);
					$label_text .= ob_get_clean();
				} else {
					$label_text .= ' <span class="wwodgc_youtube-link"><a href="' . esc_url($args['help_link']) . '" target="_blank" rel="noopener noreferrer" style="text-decoration: none;"><span class="dashicons dashicons-video-alt3" style="color: #FF0000;"></span></a></span>';
				}
			}
			$html .= sprintf('%1$s</label>', $label_text);
			$html .= '</fieldset>';

			// Define allowed HTML tags and attributes
			$allowed_html = array(
				'fieldset' => array(),
				'label' => array(
					'for' => array(),
				),
				'input' => array(
					'type' => array('hidden', 'checkbox'),
					'class' => array(),
					'id' => array(),
					'name' => array(),
					'value' => array(),
					'checked' => array(),
				),
				'a' => array(
					'href' => array(),
					'target' => array(),
					'rel' => array(),
					'style' => array(),
					'class' => array(),
				),
				'span' => array(
					'class' => array(),
					'style' => array(),
				),
				'p' => array(
					'class' => array(),
				),
			);

			// Sanitize and output the HTML
			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays a multicheckbox a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_multicheck($args)
		{
			$value = $this->get_option($args['id'], $args['section'], $args['std']);

			$html = '<fieldset>';
			foreach ($args['options'] as $key => $label) {
				$checked = isset($value[$key]) ? $value[$key] : '0';
				$html .= sprintf('<label for="wxspc-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key);
				$html .= sprintf('<input type="checkbox" class="checkbox" id="wxspc-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked($checked, $key, false));
				$html .= sprintf('%1$s</label><br>', $label);
			}
			$html .= $this->get_field_description($args);
			$html .= '</fieldset>';

			// Define allowed HTML tags and attributes
			$allowed_html = array(
				'fieldset' => array(),
				'label' => array(
					'for' => array(),
				),
				'input' => array(
					'type' => array(),
					'class' => array(),
					'id' => array(),
					'name' => array(),
					'value' => array(),
				),
			);

			// Sanitize and output the HTML
			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays a multicheckbox a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_radio($args)
		{
			$value = $this->get_option($args['id'], $args['section'], $args['std']);

			$html = '<fieldset>';
			foreach ($args['options'] as $key => $label) {
				$html .= sprintf('<label for="wxspc-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key);
				$html .= sprintf('<input type="radio" class="radio" id="wxspc-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked($value, $key, false));
				$html .= sprintf('%1$s</label><br>', $label);
			}
			$html .= $this->get_field_description($args);
			$html .= '</fieldset>';

			// Define allowed HTML tags and attributes
			$allowed_html = array(
				'fieldset' => array(),
				'label' => array(
					'for' => array(),
				),
				'input' => array(
					'type' => array(),
					'class' => array(),
					'id' => array(),
					'name' => array(),
					'value' => array(),
				),
			);

			// Sanitize and output the HTML
			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays a selectbox for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_select($args)
		{
			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

			$html = sprintf('<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id']);
			foreach ($args['options'] as $key => $label) {
				$html .= sprintf('<option value="%s"%s>%s</option>', $key, selected($value, $key, false), $label);
			}
			$html .= sprintf('</select>');
			$html .= $this->get_field_description($args);

			// Define allowed HTML tags and attributes
			$allowed_html = array(
				'select' => array(
					'class' => array(),
					'id' => array(),
					'name' => array(),
				),
				'option' => array(
					'value' => array(),
					'selected' => array(),
				),
				'p' => array(
					'class' => array(),
				),
			);
			// Sanitize and output the HTML
			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays a textarea for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_textarea($args)
		{
			$value = esc_textarea($this->get_option($args['id'], $args['section'], $args['std']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

			$html = sprintf('<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]">%4$s</textarea>', $size, $args['section'], $args['id'], $value);
			$html .= $this->get_field_description($args);

			// Define allowed HTML tags and attributes
			$allowed_html = array(
				'textarea' => array(
					'rows' => array(),
					'cols' => array(),
					'class' => array(),
					'id' => array(),
					'name' => array(),
				),
				'p' => array(
					'class' => array()
				)
			);

			// Sanitize and output the HTML
			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays a textarea for a settings field
		 *
		 * @param array $args settings field args.
		 * @return string
		 */
		function callback_html($args)
		{
			echo wp_kses_post($this->get_field_description($args));
		}

		/**
		 * Displays a rich text textarea for a settings field
		 *
		 * @param array $args settings field args.
		 */
		function callback_wysiwyg($args)
		{
			$value = $this->get_option($args['id'], $args['section'], $args['std']);
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : '500px';

			echo '<div style="max-width: ' . esc_attr($size) . ';">';

			$editor_settings = array(
				'teeny' => true,
				'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
				'textarea_rows' => 10,
			);
			if (isset($args['options']) && is_array($args['options'])) {
				$editor_settings = array_merge($editor_settings, $args['options']);
			}

			wp_editor($value, $args['section'] . '-' . $args['id'], $editor_settings);

			echo '</div>';

			echo wp_kses_post($this->get_field_description($args));
		}

		/**
		 * Displays a file upload field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		function callback_file($args)
		{
			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$id = $args['section'] . '[' . $args['id'] . ']';
			$label = isset($args['options']['button_label'])
				? $args['options']['button_label']
				: __('Choose File', 'single-product-customizer');

			$html = sprintf('<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value);
			$html .= '<input type="button" class="button wpsa-browse" value="' . $label . '" />';
			$html .= $this->get_field_description($args);

			// Define allowed HTML tags and attributes
			$allowed_html = array(
				'input' => array(
					'type' => array('text', 'button'),
					'class' => array(),
					'id' => array(),
					'name' => array(),
					'value' => array(),
				),
				'p' => array(
					'class' => array()
				)
			);

			// Sanitize and output the HTML
			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays an image upload field with a preview
		 *
		 * @param array $args settings field args.
		 */
		function callback_image($args)
		{
			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$id = $args['section'] . '[' . $args['id'] . ']';
			$label = isset($args['options']['button_label'])
				? $args['options']['button_label']
				: __('Choose Image', 'single-product-customizer');

			$html = sprintf('<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value);
			$html .= '<input type="button" class="button wpsa-browse" value="' . $label . '" />';
			$html .= $this->get_field_description($args);
			// phpcs:ignore
			$html .= '<p class="wpsa-image-preview"><img src=""/></p>';

			// Define allowed HTML tags and attributes
			$allowed_html = array(
				'input' => array(
					'type' => array('text', 'button'),
					'class' => array(),
					'id' => array(),
					'name' => array(),
					'value' => array(),
				),
				'p' => array(
					'class' => array(),
					'img' => array(
						'src' => array()
					)
				)
			);

			// Sanitize and output the HTML
			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays a password field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_password($args)
		{
			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

			$html = sprintf('<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value);
			$html .= $this->get_field_description($args);

			// Define allowed HTML tags and attributes
			$allowed_html = array(
				'input' => array(
					'type' => array('text', 'button'),
					'class' => array(),
					'id' => array(),
					'name' => array(),
					'value' => array(),
				),
				'p' => array(
					'class' => array()
				)
			);

			// Sanitize and output the HTML
			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays a color picker field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_color($args)
		{
			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std'], $args['placeholder']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

			$html = sprintf('<input type="text" class="%1$s-text sppcfw-color-picker" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" placeholder="%6$s" />', $size, $args['section'], $args['id'], $value, $args['std'], $args['placeholder']);
			$html .= $this->get_field_description($args);

			// Define allowed HTML tags and attributes
			$allowed_html = array(
				'input' => array(
					'type' => array('text'),
					'class' => array(),
					'id' => array(),
					'name' => array(),
					'value' => array(),
					'data-default-color' => array(),
					'placeholder' => array(),
				),
				'p' => array(
					'class' => array()
				)
			);

			// Sanitize and output the HTML
			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays a separator field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_separator($args)
		{
			$type = isset($args['type']) ? $args['type'] : 'separator';

			$html = '';
			$html .= '<div class="wpsa-settings-separator"></div>';
			echo esc_html($html);
		}

		/**
		 * Get the value of a settings field
		 *
		 * @param string $option  settings field name.
		 * @param string $section the section name this field belongs to.
		 * @param string $default default text if it's not found.
		 * @return string
		 */
		function get_option($option, $section, $default = '')
		{
			$options = get_option($section);

			if (isset($options[$option])) {
				return $options[$option];
			}

			return $default;
		}

		/**
		 * Add submenu page to the Settings main menu.
		 *
		 * @param string $page_title
		 * @param string $menu_title
		 * @param string $capability
		 * @param string $menu_slug
		 * @param callable $function = ''
		 * @author Ahmad Awais
		 * @since  [version]
		 */
		// register admin menu
		public function sppcfw_register_admin_menu()
		{
			$parent_slug = 'sppcfw-single-product-customizer';
			$menu_title = __('Single Product Customizer', 'single-product-customizer');
			$capability = 'manage_options';

			add_menu_page(
				$menu_title,
				$menu_title,
				$capability,
				$parent_slug,
				array($this, 'plugin_page'),
				'dashicons-admin-customizer',
				56
			);

			// Submenu: Dashboard_Settings
			add_submenu_page(
				$parent_slug,
				__('Dashboard_Settings', 'single-product-customizer'),
				__('Dashboard_Settings', 'single-product-customizer'),
				$capability,
				$parent_slug,
				array($this, 'plugin_page')
			);

			// Submenu: Single Page Builder
            add_submenu_page(
                $parent_slug,
                __( 'Single Page Builder', 'single-product-customizer' ),
                __( 'Single Page Builder', 'single-product-customizer' ),
                $capability,
                'sppcfw-single-page-builder',
                array( $this, 'builder_page' )
            );
		}

		public function templates_list_page() {
            require_once SPPCFW_DIR_PATH . 'backend/builder/templates-list-view.php';
        }

        public function builder_page() {
            $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
            if ( isset( $_GET['template_id'] ) && ( empty( $action ) || 'edit' === $action ) ) {
                require_once SPPCFW_DIR_PATH . 'backend/builder/builder-view.php';
            } else {
                require_once SPPCFW_DIR_PATH . 'backend/builder/templates-list-view.php';
            }
        }

		        /**
         * Format admin sidebar menu to display Dashboard Settings title.
         */
        public function sppcfw_format_admin_sidebar_menu() {
            global $submenu;
            $parent = 'sppcfw-single-product-customizer';

            if ( empty( $submenu[ $parent ] ) || ! is_array( $submenu[ $parent ] ) ) {
                return;
            }

            if ( isset( $submenu[ $parent ][0] ) && is_array( $submenu[ $parent ][0] ) ) {
                $submenu[ $parent ][0][0] = __( 'Dashboard Settings', 'single-product-customizer' );
            }
        }

        /**
         * Custom CSS to style admin sidebar menu to match design.
         */
        public function sppcfw_admin_menu_sidebar_styles() {
            ?>
            <style id="sppcfw-admin-sidebar-menu-css">
                #adminmenu .toplevel_page_sppcfw-single-product-customizer .wp-submenu li.sppcfw-menu-header-item a {
                    font-weight: 700 !important;
                    color: #ffffff !important;
                    font-size: 13px !important;
                    border-top: 1px solid rgba(255, 255, 255, 0.08);
                    margin-top: 4px;
                    padding-top: 8px !important;
                    padding-left: 12px !important;
                    opacity: 1 !important;
                }
                #adminmenu .toplevel_page_sppcfw-single-product-customizer .wp-submenu li.sppcfw-menu-header-item {
                    margin-top: 4px;
                }
            </style>
            <?php
        }

		public function plugin_page()
		{
			$current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : 'sppcfw-single-product-customizer'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$tab_param = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$active_tab = 'basic';
			if (!empty($tab_param)) {
				$active_tab = $tab_param;
			} elseif ('sppcfw-advance-settings' === $current_page) {
				$active_tab = 'advance';
			} elseif ('sppcfw-our-products' === $current_page) {
				$active_tab = 'our_products';
			} elseif ('sppcfw-quick-checkout' === $current_page) {
				$active_tab = 'quick_checkout';
			} elseif ('sppcfw-support' === $current_page) {
				$active_tab = 'support';
			}
			?>
            <div>
                <div class="sppcfw-dashboard">
                    <div class="sppcfw__panel">
                        <?php include SPPCFW_DIR_PATH . 'backend/templates/sidebar/sidebar-menu.php'; ?>
                        <div class="sppcfw__panel_content">
                            <div id="basic" class="tabcontent-sppcfw <?php echo 'basic' === $active_tab ? 'active' : ''; ?>">
                                <div class="metabox-holder">
                                    <div id="sppcfw_basic" class="sppcfw-group">
                                        <form method="post" action="options.php">
                                            <?php
											settings_fields('sppcfw_basic');
											do_settings_sections('sppcfw_basic');
											submit_button(null, 'primary', 'submit_sppcfw_basic');
											?>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div id="advance" class="tabcontent-sppcfw <?php echo 'advance' === $active_tab ? 'active' : ''; ?>">
                                <div class="metabox-holder">
                                    <div id="sppcfw_advanced" class="sppcfw-group">
                                        <form method="post" action="options.php">
                                            <?php
											settings_fields('sppcfw_advanced');
											do_settings_sections('sppcfw_advanced');
											submit_button(null, 'primary', 'submit_sppcfw_advanced');
											?>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div id="our_products" class="tabcontent-sppcfw <?php echo 'our_products' === $active_tab ? 'active' : ''; ?>">
                                <div class="metabox-holder">
                                    <div id="sppcfw_our_products" class="sppcfw-group">
                                        <?php
										// Include the Our Products admin page content.
										$our_products_file = dirname(__FILE__) . '/../Our-products/our-products.php';
										if (file_exists($our_products_file)) {
											include_once $our_products_file;
										} else {
											echo '<p>' . esc_html__('Our Products file not found.', 'single-product-customizer') . '</p>';
										}
										?>
                                    </div>
                                </div>
                            </div>

                            <div id="quick_checkout" class="tabcontent-sppcfw <?php echo 'quick_checkout' === $active_tab ? 'active' : ''; ?>">
                                <div class="metabox-holder">
                                    <div id="sppcfw_quick_checkout" class="sppcfw-group">
                                        <?php
										$quick_checkout_file = dirname(__FILE__) . '/../Enable-Quick-Checkout/quick-checkout.php';
										if (file_exists($quick_checkout_file)) {
											include_once $quick_checkout_file;
										} else {
											echo '<p>' . esc_html__('Quick Checkout file not found.', 'single-product-customizer') . '</p>';
										}
										?>
                                    </div>
                                </div>
                            </div>

                            <div id="support" class="tabcontent-sppcfw <?php echo 'support' === $active_tab ? 'active' : ''; ?>">
                                <div class="grid-support">
                                    <div class="support-item">
                                        <strong><span class="dashicons dashicons-admin-site-alt3"></span>
                                            <?php esc_html_e('Website:', 'single-product-customizer'); ?></strong>
                                        <a href="https://www.webcartisan.com/" target="_blank"><?php esc_html_e('webcartisan.com', 'single-product-customizer'); ?></a>
                                        <p><?php esc_html_e('Visit our official website for live chat and more information, tutorials, and resources.', 'single-product-customizer'); ?></p>
                                    </div>
                                    <div class="support-item">
                                        <strong><span class="dashicons dashicons-facebook-alt"></span><?php esc_html_e('Facebook:', 'single-product-customizer'); ?></strong>
                                        <a href="https://www.facebook.com/webcartisan" target="_blank"><?php esc_html_e('Follow us', 'single-product-customizer'); ?></a>
                                        <p><?php esc_html_e('Join our community on Facebook for support, updates, and discussions.', 'single-product-customizer'); ?></p>
                                    </div>
                                    <div class="support-item">
                                        <strong><span class="dashicons dashicons-whatsapp"></span> <?php esc_html_e('WhatsApp:', 'single-product-customizer'); ?></strong>
                                        <a href="https://wa.me/01926167151" target="_blank"><?php esc_html_e('Chat Now ', 'single-product-customizer'); ?></a>
                                        <p><?php esc_html_e('Get instant support by chatting with us on WhatsApp. We’re here to help!', 'single-product-customizer'); ?></p>
                                    </div>
                                    <div class="support-item">
                                        <strong><span class="dashicons dashicons-email-alt"></span> <?php esc_html_e('Email:', 'single-product-customizer'); ?></strong> <a href="mailto:hello@webcartisan.com"><?php esc_html_e('hello@webcartisan.com', 'single-product-customizer'); ?></a>
                                        <p><?php esc_html_e('Feel free to reach out to us via email for any inquiries or support requests.', 'single-product-customizer'); ?></p>
                                    </div>
                                    <div class="support-item">
                                        <strong><span class="dashicons dashicons-linkedin"></span> <?php esc_html_e('LinkedIn:', 'single-product-customizer'); ?></strong>
                                        <a href="https://www.linkedin.com/company/webcartisan" target="_blank"><?php esc_html_e('Connect on LinkedIn', 'single-product-customizer'); ?></a>
                                        <p><?php esc_html_e('Let’s connect on LinkedIn for networking, updates, and professional support.', 'single-product-customizer'); ?></p>
                                    </div>
                                    <div class="support-item">
                                        <strong><span class="dashicons dashicons-twitter"></span> <?php esc_html_e('Twitter:', 'single-product-customizer'); ?></strong> <a href="https://x.com/WebCartisan" target="_blank"><?php esc_html_e('Follow us', 'single-product-customizer'); ?></a>
                                        <p><?php esc_html_e('Stay updated with the latest news and announcements by following us on Twitter.', 'single-product-customizer'); ?></p>
                                    </div>
                                    <div class="support-item">
                                        <strong><span class="dashicons dashicons-youtube"></span> <?php esc_html_e('YouTube:', 'single-product-customizer'); ?></strong> <a href="https://www.youtube.com/@WebCartisan" target="_blank"><?php esc_html_e('Subscribe', 'single-product-customizer'); ?></a>
                                        <p><?php esc_html_e('Check out our YouTube channel for video tutorials and product showcases.', 'single-product-customizer'); ?></p>
                                    </div>
                                    <div class="support-item">
                                        <strong><span class="dashicons dashicons-instagram"></span> <?php esc_html_e('Instagram:', 'single-product-customizer'); ?></strong>
                                        <a href="https://www.instagram.com/webcartisan/" target="_blank"><?php esc_html_e('Follow us', 'single-product-customizer'); ?></a>
                                        <p><?php esc_html_e('See behind-the-scenes content and our latest updates on Instagram.', 'single-product-customizer'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
		}

		/**
		 * Show navigations as tab
		 *
		 * Shows all the settings section labels as tab
		 */
		function show_navigation()
		{
			$html = '<h2 class="nav-tab-wrapper">';

			foreach ($this->sections_array as $tab) {
				if ($tab['id'] === 'sppcfw_advanced_get_pro') {
					$html .= '<button type="button" class="nav-tab custom-button" onclick="generate_custom_link(this)" data-url="https://www.webcartisan.com/single-product-page-customizer/">'
						. esc_html($tab['title'])
						. '<span style="color: red;"> *</span></button>';
				} elseif ($tab['id'] === 'sppcfw_advanced_get_support') {
					$html .= '<button type="button" class="nav-tab custom-button" onclick="generate_custom_link(this)" data-url="https://www.webcartisan.com/">'
						. esc_html($tab['title'])
						. '</button>';
				} else {
					$html .= sprintf(
						'<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>',
						esc_attr($tab['id']),
						esc_html($tab['title'])
					);
				}
			}

			$html .= '</h2>';

			// Define allowed HTML tags and attributes
			$allowed_html = array(
				'h2' => array(
					'class' => array(),
				),
				'a' => array(
					'href' => array(),
					'class' => array(),
					'id' => array(),
				),
				'button' => array(
					'type' => array(),
					'class' => array(),
					'onclick' => array(),
					'data-url' => array(),
				),
				'span' => array('style' => array()),
			);

			// Sanitize and output the HTML
			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Show the section settings forms
		 *
		 * This function displays every sections in a different form
		 */
		function show_forms()
		{
			?>
			<div class="metabox-holder">
				<?php foreach ($this->sections_array as $form) { ?>
					<!-- style="display: none;" -->
					<div id="<?php echo esc_attr($form['id']); ?>" class="sppcfw-group" >
						<form method="post" action="options.php">
							<?php
							do_action('sppcfw_wsa_form_top_' . $form['id'], $form);
							settings_fields($form['id']);
							do_settings_sections($form['id']);
							do_action('sppcfw_wsa_form_bottom_' . $form['id'], $form);
							?>
							<div style="padding-left: 10px">
								<?php submit_button(null, 'primary', 'submit_' . $form['id']); ?>
							</div>
						</form>
					</div>
				<?php } ?>
			</div>
			<?php
		}
	}
endif;
