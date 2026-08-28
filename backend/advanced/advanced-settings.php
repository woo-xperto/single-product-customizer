<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	// Section: Advanced Settings.
	$sppcfw_back_ui_obj->add_section(
		array(
			'id'    => 'sppcfw_advanced',
			'title' => __( 'Advanced Settings', 'single-product-customizer' ),
		)
	);
	// Section: Advanced Settings.
	$sppcfw_back_ui_obj->add_section(
		array(
			'id'    => 'sppcfw_advanced_get_pro',
			'title' => __( 'Get Pro', 'single-product-customizer' ),
		)
	);
	// Section: Advanced Settings.
	$sppcfw_back_ui_obj->add_section(
		array(
			'id'    => 'sppcfw_advanced_get_support',
			'title' => __( 'Get Support', 'single-product-customizer' ),
		)
	);

	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'enable_customizer_for_category',
			'type' => 'checkbox',
			'name' => __( 'Enable Category Based Customization', 'single-product-customizer' ),
			'desc' => '',
            'class'=>'sppcfw_advanced_enable_customizer_for_category'
		)
	);

	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'enable_customizer_for_product',
			'type' => 'checkbox',
			'name' => __( 'Enable Product Based Customization', 'single-product-customizer' ),
			'desc' => '',
            'class'=>'sppcfw_advanced_enable_customizer_for_product'
		)
	);

    // Field: Checkbox.
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'enable_ajax_add_to_cart',
			'type' => 'checkbox',
			'name' => __( 'Enable ajax add to cart', 'single-product-customizer' ),
			'desc' => '',
            'class'=>'sppcfw_advanced_enable_ajax_add_to_cart'
		)
	);

	/* Min max settup start */
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'enable_min_max_qty',
			'type' => 'checkbox',
			'name' => __( 'Enable min max quantity', 'single-product-customizer' ),
			'desc' => '',
            'class'=>'sppcfw_advanced_enable_min_max_qty'
		)
	);

	$sppcfw_settings_hide='sppcfw_admin_hidden';

	$sppcfw_min_max_enable = (isset($sppcfw_advanced['enable_min_max_qty'])?$sppcfw_advanced['enable_min_max_qty']:'');
	if($sppcfw_min_max_enable == "on"){
		$sppcfw_settings_hide='';
	}
	// Field: Number.
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'                => 'min_max_qty_global_min_value',
			'type'              => 'number',
			'name'              => __( 'Setup global min qty', 'single-product-customizer' ),
			'desc'              => __( 'Setup a global min qty for all products. You can override it from product setup.', 'single-product-customizer' ),
			'default'           => 1,
			'sanitize_callback' => 'intval',
            'class'=>'sppcfw_advanced_min_max_qty_global_min_value sppcfw_min_max '.$sppcfw_settings_hide
		)
	);


	// minimum quantity validation message text 
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'                => 'min_qty_validation_text',
			'type'              => 'text',
			'name'              => __( 'Setup min qty validation text', 'single-product-customizer' ),
			'desc'              => __( 'You can change minimum qty validation text from here.', 'single-product-customizer' ),
			'default'           => __('Minimum quantity should be','single-product-customizer'),
			'sanitize_callback' => 'intval',
			'class'=>'sppcfw_advanced_min_qty_validation_text sppcfw_min_max '.$sppcfw_settings_hide
		)
	);

	// Field: Number.
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'                => 'min_max_qty_global_max_value',
			'type'              => 'number',
			'name'              => __( 'Setup global max qty', 'single-product-customizer' ),
			'desc'              => __( 'Setup a global max qty for all products. You can override it from product setup.', 'single-product-customizer' ),
			'default'           => 1,
			'sanitize_callback' => 'intval',
            'class'=>'sppcfw_advanced_min_max_qty_global_max_value sppcfw_min_max '.$sppcfw_settings_hide
		)
	);


	// Maximum quantity validation message text 
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'                => 'max_qty_validation_text',
			'type'              => 'text',
			'name'              => __( 'Setup max qty validation text', 'single-product-customizer' ),
			'desc'              => __( 'You can change maximum qty validation text from here.', 'single-product-customizer' ),
			'default'           => __('Maximum quantity should be','single-product-customizer'),
			'sanitize_callback' => 'intval',
			'class'=>'sppcfw_advanced_min_qty_validation_text sppcfw_min_max '.$sppcfw_settings_hide
		)
	);



	// Field: Number.
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'                => 'plus_minus_button_qty_change_global_setp',
			'type'              => 'number',
			'name'              => __( 'Set quantity step', 'single-product-customizer' ),
			'desc'              => __( 'Setup quantity step. Can be override from product setup.', 'single-product-customizer' ),
			'default'           => 1,
			'sanitize_callback' => 'intval',
            'class'=>'sppcfw_advanced_plus_minus_button_qty_change_global_setp sppcfw_min_max '.$sppcfw_settings_hide
		)
	);

   /* min max settings end */



	/* custom message settings start */
	$sppcfw_settings_hide='sppcfw_admin_hidden';

	$sppcfw_custom_msg_enable = (isset($sppcfw_advanced['enable_custom_message'])?$sppcfw_advanced['enable_custom_message']:'');

	if($sppcfw_custom_msg_enable == "on"){
		$sppcfw_settings_hide='';
	}


	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'enable_custom_message',
			'type' => 'checkbox',
			'name' => __( 'Enable custom message', 'single-product-customizer' ),
			'desc' => '',
            'class'=>'sppcfw_advanced_enable_custom_message',
			'help_link' =>'https://youtu.be/0znk5Jpwpr0'
		)
	);
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'custom_message_text',
			'type' => 'textarea',
			'name' => __( 'Custom message', 'single-product-customizer' ),
			'desc' => '',
			'class'=>'sppcfw_advanced_custom_message_text sppcfw_custom_message '.$sppcfw_settings_hide
		)
	);
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'      => 'custom_message_display_hook',
			'type'    => 'select',
			'name'    => __( 'Select a location', 'single-product-customizer' ),
			'desc'    => __( 'You can choose a location where this message will be shown', 'single-product-customizer' ),
			'options' => $sppcfw_available_hooks,
			'class'=> 'sppcfw_custom_message '.$sppcfw_settings_hide
		)
	);

	/* custom message settings end*/

	/* variation table settings start*/
	$sppcfw_settings_hide='sppcfw_admin_hidden';

	$sppcfw_variation_table_enable = (isset($sppcfw_advanced['enable_varition_table'])?$sppcfw_advanced['enable_varition_table']:'');

	if($sppcfw_variation_table_enable == "on"){
		$sppcfw_settings_hide='';
	}
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'enable_varition_table',
			'type' => 'checkbox',
			'name' => __( 'Show variation table', 'single-product-customizer' ),
			'desc' => '',
            'class'=>'sppcfw_advanced_enable_varition_table',
			'help_link' =>'https://youtu.be/pwRVObgvxSI'
		)
	);

	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'      => 'variation_table_display_hook',
			'type'    => 'select',
			'name'    => __( 'Select a location', 'single-product-customizer' ),
			'desc'    => __( 'You can choose a location where variation table will be shown', 'single-product-customizer' ),
			'options' => $sppcfw_available_hooks,
			'class'=>'sppcfw_variation_table '.$sppcfw_settings_hide
		)
	);

	/* variation table settings end*/ 

	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'enable_custom_tab',
			'type' => 'checkbox',
			'name' => __( 'Enable custom tab', 'single-product-customizer' ),
			'desc' => '',
            'class'=>'sppcfw_advanced_enable_custom_tab'
		)
	);
	
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'enable_additional_content',
			'type' => 'checkbox',
			'name' => __( 'Enable additional content', 'single-product-customizer' ),
			'desc' => '',
            'class'=>'sppcfw_advanced_enable_additional_content'
		)
	);

	/* default tab label change settings start*/
	$sppcfw_settings_hide='sppcfw_admin_hidden';

	$sppcfw_change_default_label_enable = (isset($sppcfw_advanced['enable_change_tab_default_label'])?$sppcfw_advanced['enable_change_tab_default_label']:'');

	if($sppcfw_change_default_label_enable == "on"){
		$sppcfw_settings_hide='';
	}


	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'enable_change_tab_default_label',
			'type' => 'checkbox',
			'name' => __( 'Change tab default label', 'single-product-customizer' ),
			'desc' => '',
            'class'=>'sppcfw_advanced_enable_change_tab_deafult_label'
		)
	);
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'description_tab_label',
			'type' => 'text',
			'name' => __( 'Description label text', 'single-product-customizer' ),
			'desc'=>'',
			'default' => __( 'Description', 'single-product-customizer' ),
            'class'=>'sppcfw_advanced_description_label_text sppcfw_tab_label '.$sppcfw_settings_hide
		)
	);

	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'additional_information_tab_label',
			'type' => 'text',
			'name' => __( 'Additional information label text', 'single-product-customizer' ),
			'desc'=>'',
			'default' => __( 'Additional information', 'single-product-customizer' ),
            'class'=>'sppcfw_advanced_additional_information_label_text sppcfw_tab_label '.$sppcfw_settings_hide
		)
	);

	
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'review_tab_label',
			'type' => 'text',
			'name' => __( 'Review label text', 'single-product-customizer' ),
			'desc'=>'',
			'default' => __( 'Reviews', 'single-product-customizer' ),
            'class'=>'sppcfw_advanced_riview_label_text sppcfw_tab_label '.$sppcfw_settings_hide
		)
	);

	/* default tab label change settings end*/

	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'enable_variation_switcher',
			'type' => 'checkbox',
			'name' => __( 'Enable variation switcher', 'single-product-customizer' ),
			'desc' => '',
            'class'=>'sppcfw_advanced_enable_variation_switcher',
			'help_link' =>'https://youtu.be/PpKSdsaOMeg'
		)
	);

    $sppcfw_is_block_theme = sppcfw_is_block_theme_active();
    $sppcfw_related_products_disabled = $sppcfw_is_block_theme ? 'disabled' : '';
    $sppcfw_related_products_desc = $sppcfw_is_block_theme ? '<span style="color: red; font-weight: bold;">' . __( 'This feature is not available in block theme', 'single-product-customizer' ) . '</span>' : '';
    
    $sppcfw_back_ui_obj->add_field(
        'sppcfw_advanced',
        array(
            'id'                => 'related_products_title',
            'type'              => 'text',
            'name'              => __( 'Related products title', 'single-product-customizer' ),
            'desc'              => $sppcfw_related_products_desc,
            'default'           => __( 'Related products', 'single-product-customizer' ),
			'help_link' 		=>'https://youtu.be/eOsk7buqgmI',
            'disabled'          => $sppcfw_related_products_disabled,
            'class'             => $sppcfw_related_products_disabled ? 'sppcfw_disabled_field' : ''
        )
    );
    
    $sppcfw_back_ui_obj->add_field(
        'sppcfw_advanced',
        array(
            'id'                => 'upsell_products_title',
            'type'              => 'text',
            'name'              => __( 'Upsell products title', 'single-product-customizer' ),
            'desc'              => '',
            'default'           => __( 'You may also like…', 'single-product-customizer' ),
			'help_link' 		=>'https://youtu.be/lrXgTynjQ9E'
        )
    );

    $sppcfw_back_ui_obj->add_field(
        'sppcfw_advanced',
        array(
            'id'   => 'change_clear_text',
            'type' => 'text',
            'name' => __( 'Variation reset text', 'single-product-customizer' ),
            'desc'=>'',
            'default' => __( 'Clear', 'single-product-customizer' ),
            'class'=>'sppcfw_global_change_clear_text',
			'help_link' =>'https://youtu.be/JLfqhSXl70I'
        )
    );
    $sppcfw_back_ui_obj->add_field(
        'sppcfw_advanced',
        array(
            'id'   		=> 'change_backorder_text',
            'type' 		=> 'text',
            'name' 		=> __( 'On backorder text', 'single-product-customizer' ),
            'desc'		=>'',
            'default' 	=> __( 'Available on backorder', 'single-product-customizer' ),
            'class'		=>'sppcfw_global_change_backorder_text',
			'help_link' =>'https://youtu.be/CXFJX_3RNXk'
        )
    );


	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'enable_quick_cart',
			'type' => 'checkbox',
			'name' => __( 'Enable quick cart', 'single-product-customizer' ),
			'desc' => '',
            'class'=>'sppcfw_advanced_enable_quick_cart'
		)
	);

	
	$sppcfw_back_ui_obj->add_field(
		'sppcfw_advanced',
		array(
			'id'   => 'related_product_categories',
			'type' => 'checkbox',
			'name' => __( 'Display related product categories grid', 'single-product-customizer' ),
			'desc' => '',
            'class'=>'sppcfw_advanced_enable_related_product_categories'
		)
	);