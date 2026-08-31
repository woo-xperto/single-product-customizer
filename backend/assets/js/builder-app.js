/**
 * Single Product Page Builder React App
 * Built with React (wp.element) & Tailwind CSS
 * Features: Left-side Edit Container Inspector, Atomic Elements Drawer, Live Widget Search, Floating Structure Panel, Multi-template Management
 *
 * @package Single_Product_Customizer
 */

(function () {
	'use strict';

	const { createElement: h, useState, useEffect, useRef } = window.wp.element;

	// Helper for AJAX post
	function apiPost(action, data) {
		const config = window.SPPCFWBuilderConfig || {};
		const formData = new FormData();
		formData.append('action', action);
		formData.append('nonce', config.nonce || '');
		for (const key in data) {
			if (data.hasOwnProperty(key)) {
				formData.append(key, typeof data[key] === 'object' ? JSON.stringify(data[key]) : data[key]);
			}
		}
		return fetch(config.ajax_url || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			body: formData,
		}).then(res => res.json());
	}

	// Static Visual Sample Data for Edit Canvas
	const CANVAS_STATIC_DATA = {
		title: 'Feature image',
		price: '$49.99',
		sku: 'SAMPLE-SKU-123',
		stock_text: 'In Stock',
		rating_count: 5,
		image_url: window.SPPCFWBuilderConfig ? window.SPPCFWBuilderConfig.plugin_url + 'backend/resources/images/features-img.webp' : '',
		short_description: 'This is a product short description placeholder for designing your WooCommerce single product page layout.',
		description: 'Full product description placeholder detailing extensive technical specifications and features.',
		categories: 'Clothing, Featured',
		tags: 'Customizer, Premium',
	};

	// Atomic Elements Definitions (Image 1)
	const ATOMIC_ELEMENTS = [
		{ type: 'column', name: 'Column', preset: 'column', icon: 'view_column', desc: 'Add a new column into container' },
		{ type: 'div_block', name: 'Div block', preset: 'div_block', icon: 'crop_square', desc: 'Simple full width div container' },
		{ type: 'flexbox', name: 'Flexbox', preset: '1_container', icon: 'grid_view', desc: 'Flexbox layout container' },
		{ type: 'grid', name: 'Grid', preset: 'grid_2x2', icon: 'apps', desc: '2x2 grid container' },
		{ type: 'tabs', name: 'Tabs', preset: 'product_description', icon: 'folder', desc: 'Tabs container widget' },
	];

	// Core Single Product Widget Definitions
	const CORE_WIDGETS = [
		{ type: 'product_title', name: 'Product Title', icon: 'title' },
		{ type: 'product_price', name: 'Product Price', icon: 'payments' },
		{ type: 'product_gallery', name: 'Image Gallery', icon: 'image' },
		{ type: 'product_add_to_cart', name: 'Add to Cart', icon: 'shopping_cart' },
		{ type: 'product_rating', name: 'Rating Stars', icon: 'star' },
		{ type: 'product_short_desc', name: 'Short Description', icon: 'description' },
		{ type: 'product_description', name: 'Full Description & Tabs', icon: 'toc' },
		{ type: 'product_meta', name: 'Product Meta', icon: 'inventory_2' },
		{ type: 'variation_swatches', name: 'Variation Table', icon: 'grid_view' },
		{ type: 'custom_message', name: 'Custom Message', icon: 'campaign' },
		{ type: 'plus_minus_buttons', name: 'Plus/Minus Stepper', icon: 'exposure' },
		{ type: 'related_products', name: 'Related Products', icon: 'grid_on' },
		{ type: 'upsell_products', name: 'Upsell Products', icon: 'auto_awesome' },
	];

	// Preset Layout Generator
	function createContainerStructure(presetType) {
		const timestamp = Date.now();
		const containerId = 'container-' + timestamp;

		let children = [];
		let settings = {
			width_mode: 'boxed',
			boxed_width: '1140px',
			flex_direction: 'row',
			justify_content: 'flex-start',
			align_items: 'stretch',
			grid_columns: '2',
			gap: '16px',
			row_gap: '16px',
			gaps_linked: true,
			flex_wrap: 'nowrap',
			min_height: '0px',
			alignment: 'left',
		};

		switch (presetType) {
			case 'flex_col':
				settings.flex_direction = 'column';
				children = [
					{
						id: 'col-' + timestamp + '-1',
						type: 'column',
						label: 'Column 1 (100%)',
						settings: { flex_width: '100%' },
						children: [],
						styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' },
					},
				];
				break;
			case '1_container':
			case 'flexbox':
			case 'flex_row':
				settings.flex_direction = 'row';
				children = [
					{
						id: 'col-' + timestamp + '-1',
						type: 'column',
						label: 'Column 1 (100%)',
						settings: { flex_width: '100%' },
						children: [],
						styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' },
					},
				];
				break;
			case '2_col_50_50':
				children = [
					{ id: 'col-' + timestamp + '-1', type: 'column', label: 'Column 1 (50%)', settings: { flex_width: '50%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-2', type: 'column', label: 'Column 2 (50%)', settings: { flex_width: '50%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
				];
				break;
			case '2_col_33_66':
				children = [
					{ id: 'col-' + timestamp + '-1', type: 'column', label: 'Column 1 (33%)', settings: { flex_width: '33.33%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-2', type: 'column', label: 'Column 2 (67%)', settings: { flex_width: '66.66%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
				];
				break;
			case '2_col_66_33':
				children = [
					{ id: 'col-' + timestamp + '-1', type: 'column', label: 'Column 1 (67%)', settings: { flex_width: '66.66%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-2', type: 'column', label: 'Column 2 (33%)', settings: { flex_width: '33.33%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
				];
				break;
			case '3_col_33_33_33':
				children = [
					{ id: 'col-' + timestamp + '-1', type: 'column', label: 'Column 1 (33%)', settings: { flex_width: '33.33%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-2', type: 'column', label: 'Column 2 (33%)', settings: { flex_width: '33.33%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-3', type: 'column', label: 'Column 3 (33%)', settings: { flex_width: '33.33%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
				];
				break;
			case '3_col_25_50_25':
				children = [
					{ id: 'col-' + timestamp + '-1', type: 'column', label: 'Column 1 (25%)', settings: { flex_width: '25%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-2', type: 'column', label: 'Column 2 (50%)', settings: { flex_width: '50%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-3', type: 'column', label: 'Column 3 (25%)', settings: { flex_width: '25%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
				];
				break;
			case '4_col_25_25_25_25':
				children = [
					{ id: 'col-' + timestamp + '-1', type: 'column', label: 'Column 1 (25%)', settings: { flex_width: '25%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-2', type: 'column', label: 'Column 2 (25%)', settings: { flex_width: '25%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-3', type: 'column', label: 'Column 3 (25%)', settings: { flex_width: '25%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-4', type: 'column', label: 'Column 4 (25%)', settings: { flex_width: '25%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
				];
				break;
			case 'grid_2x2':
			case 'grid':
				settings.flex_direction = 'grid';
				settings.grid_columns = '2';
				children = [
					{ id: 'col-' + timestamp + '-1', type: 'column', label: 'Grid Box 1', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-2', type: 'column', label: 'Grid Box 2', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-3', type: 'column', label: 'Grid Box 3', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-4', type: 'column', label: 'Grid Box 4', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
				];
				break;
			case 'grid_3x3':
				settings.flex_direction = 'grid';
				settings.grid_columns = '3';
				children = [
					{ id: 'col-' + timestamp + '-1', type: 'column', label: 'Grid Box 1', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-2', type: 'column', label: 'Grid Box 2', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-3', type: 'column', label: 'Grid Box 3', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-4', type: 'column', label: 'Grid Box 4', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-5', type: 'column', label: 'Grid Box 5', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-6', type: 'column', label: 'Grid Box 6', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-7', type: 'column', label: 'Grid Box 7', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-8', type: 'column', label: 'Grid Box 8', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-9', type: 'column', label: 'Grid Box 9', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
				];
				break;
			case 'grid_1_2':
				settings.flex_direction = 'grid';
				settings.grid_columns = '2';
				children = [
					{ id: 'col-' + timestamp + '-1', type: 'column', label: 'Grid Box 1', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-2', type: 'column', label: 'Grid Box 2', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-3', type: 'column', label: 'Grid Box 3', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
				];
				break;
			case 'grid_2_1':
				settings.flex_direction = 'grid';
				settings.grid_columns = '2';
				children = [
					{ id: 'col-' + timestamp + '-1', type: 'column', label: 'Grid Box 1', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-2', type: 'column', label: 'Grid Box 2', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
					{ id: 'col-' + timestamp + '-3', type: 'column', label: 'Grid Box 3', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
				];
				break;
			case 'div_block':
				settings.width_mode = 'full';
				children = [
					{ id: 'col-' + timestamp + '-1', type: 'column', label: 'Div Content', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
				];
				break;
			default:
				children = [
					{ id: 'col-' + timestamp + '-1', type: 'column', label: 'Column 1', settings: { flex_width: '100%' }, children: [], styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' } },
				];
		}

		return {
			id: containerId,
			type: 'container',
			label: presetType === 'div_block' ? 'Div Container' : presetType.indexOf('grid') === 0 ? 'Grid Container' : 'Flexbox Container',
			settings: settings,
			children: children,
			styles: {
				bg_color: '#ffffff',
				border_color: '#e5e7eb',
				border_width: '1px',
				border_radius: '8px',
				padding_top: '20px',
				padding_right: '20px',
				padding_bottom: '20px',
				padding_left: '20px',
				margin_top: '0px',
				margin_right: '0px',
				margin_bottom: '24px',
				margin_left: '0px',
			},
			advanced: {
				custom_class: '',
				z_index: '1',
			},
		};
	}

	// Interactive Step-by-Step Layout Structure Selector Component (Matching Image 1 & 2)
	function LayoutStructureChooser({ onSelectPreset, onClose }) {
		const [step, setStep] = useState('type_selection'); // 'type_selection' | 'preset_selection'
		const [layoutType, setLayoutType] = useState('flexbox'); // 'flexbox' | 'grid'

		function handleTypeSelect(type) {
			setLayoutType(type);
			setStep('preset_selection');
		}

		function handlePresetSelect(presetKey) {
			onSelectPreset(presetKey);
		}

		return h(
			'div',
			{ className: 'sppcfw-w-full sppcfw-max-w-3xl sppcfw-mx-auto sppcfw-my-6 sppcfw-p-8 sppcfw-border sppcfw-border-dashed sppcfw-border-[#cbd5e1] sppcfw-rounded-xl sppcfw-bg-white sppcfw-text-[#334155] sppcfw-shadow-sm sppcfw-relative sppcfw-select-none sppcfw-animate-in sppcfw-fade-in sppcfw-duration-200' },

			// Top Action Navigation Bar
			h(
				'div',
				{ className: 'sppcfw-flex sppcfw-justify-between sppcfw-items-center sppcfw-mb-6' },
				step === 'preset_selection'
					? h(
							'button',
							{
								className: 'sppcfw-text-gray-400 hover:sppcfw-text-gray-700 sppcfw-transition-colors sppcfw-cursor-pointer sppcfw-text-xl sppcfw-p-1 sppcfw-font-bold',
								onClick: () => setStep('type_selection'),
								title: 'Back to layout type',
							},
							'‹'
					  )
					: h('div', { className: 'sppcfw-w-6' }),
				h(
					'h3',
					{ className: 'sppcfw-text-base sppcfw-font-semibold sppcfw-text-gray-700 sppcfw-text-center' },
					step === 'type_selection' ? 'Which layout would you like to use?' : 'Select your structure'
				),
				onClose
					? h(
							'button',
							{
								className: 'sppcfw-text-gray-400 hover:sppcfw-text-gray-700 sppcfw-transition-colors sppcfw-cursor-pointer sppcfw-text-base sppcfw-p-1 sppcfw-font-bold',
								onClick: onClose,
								title: 'Close',
							},
							'✕'
					  )
					: h('div', { className: 'sppcfw-w-6' })
			),

			// STEP 1: Layout Type Selection (Flexbox vs Grid - Image 1)
			step === 'type_selection' &&
				h(
					'div',
					{ className: 'sppcfw-flex sppcfw-justify-center sppcfw-items-center sppcfw-gap-8 sppcfw-py-6' },

					// Flexbox Card
					h(
						'div',
						{
							className: 'sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-gap-3 sppcfw-cursor-pointer sppcfw-tab-group',
							onClick: () => handleTypeSelect('flexbox'),
						},
						h(
							'div',
							{ className: 'sppcfw-w-24 sppcfw-h-24 sppcfw-bg-[#94a3b8]/20 group-hover:sppcfw-bg-[#9333ea]/10 sppcfw-border-2 sppcfw-border-transparent group-hover:sppcfw-border-[#9333ea] sppcfw-rounded-lg sppcfw-p-2.5 sppcfw-flex sppcfw-gap-1.5 sppcfw-transition-all sppcfw-shadow-sm' },
							h('div', { className: 'sppcfw-w-1/2 sppcfw-h-full sppcfw-bg-[#cbd5e1] group-hover:sppcfw-bg-[#a855f7] sppcfw-rounded-sm' }),
							h(
								'div',
								{ className: 'sppcfw-w-1/2 sppcfw-h-full sppcfw-flex sppcfw-flex-col sppcfw-gap-1.5' },
								h('div', { className: 'sppcfw-w-full sppcfw-h-1/2 sppcfw-bg-[#cbd5e1] group-hover:sppcfw-bg-[#a855f7] sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-w-full sppcfw-h-1/2 sppcfw-bg-[#cbd5e1] group-hover:sppcfw-bg-[#a855f7] sppcfw-rounded-sm' })
							)
						),
						h('span', { className: 'sppcfw-text-sm sppcfw-font-medium sppcfw-text-gray-600 group-hover:sppcfw-text-[#9333ea]' }, 'Flexbox')
					),

					// Grid Card
					h(
						'div',
						{
							className: 'sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-gap-3 sppcfw-cursor-pointer sppcfw-tab-group',
							onClick: () => handleTypeSelect('grid'),
						},
						h(
							'div',
							{ className: 'sppcfw-w-24 sppcfw-h-24 sppcfw-border-2 sppcfw-border-dashed sppcfw-border-[#94a3b8] group-hover:sppcfw-border-[#9333ea] sppcfw-rounded-lg sppcfw-p-2 sppcfw-grid sppcfw-grid-cols-2 sppcfw-gap-1.5 sppcfw-transition-all sppcfw-shadow-sm group-hover:sppcfw-bg-[#9333ea]/10' },
							h('div', { className: 'sppcfw-border sppcfw-border-dashed sppcfw-border-[#94a3b8] group-hover:sppcfw-border-[#a855f7] sppcfw-rounded-sm' }),
							h('div', { className: 'sppcfw-border sppcfw-border-dashed sppcfw-border-[#94a3b8] group-hover:sppcfw-border-[#a855f7] sppcfw-rounded-sm' }),
							h('div', { className: 'sppcfw-border sppcfw-border-dashed sppcfw-border-[#94a3b8] group-hover:sppcfw-border-[#a855f7] sppcfw-rounded-sm' }),
							h('div', { className: 'sppcfw-border sppcfw-border-dashed sppcfw-border-[#94a3b8] group-hover:sppcfw-border-[#a855f7] sppcfw-rounded-sm' })
						),
						h('span', { className: 'sppcfw-text-sm sppcfw-font-medium sppcfw-text-gray-600 group-hover:sppcfw-text-[#9333ea]' }, 'Grid')
					)
				),

			// STEP 2: Structure Preset Selection (Flexbox or Grid options - Image 2)
			step === 'preset_selection' &&
				(layoutType === 'flexbox'
					? h(
							'div',
							{ className: 'sppcfw-grid sppcfw-grid-cols-6 sppcfw-gap-4 sppcfw-py-4 sppcfw-max-w-2xl sppcfw-mx-auto' },

							// 1. Column ↓
							h(
								'div',
								{
									className: 'sppcfw-w-full sppcfw-h-16 sppcfw-bg-[#cbd5e1]/60 hover:sppcfw-bg-[#9333ea] hover:sppcfw-text-white sppcfw-text-gray-600 sppcfw-rounded-md sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-cursor-pointer sppcfw-transition-all sppcfw-shadow-sm sppcfw-font-bold sppcfw-text-lg',
									onClick: () => handlePresetSelect('flex_col'),
									title: 'Single Column (Vertical)',
								},
								'↓'
							),

							// 2. Row →
							h(
								'div',
								{
									className: 'sppcfw-w-full sppcfw-h-16 sppcfw-bg-[#cbd5e1]/60 hover:sppcfw-bg-[#9333ea] hover:sppcfw-text-white sppcfw-text-gray-600 sppcfw-rounded-md sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-cursor-pointer sppcfw-transition-all sppcfw-shadow-sm sppcfw-font-bold sppcfw-text-lg',
									onClick: () => handlePresetSelect('flex_row'),
									title: 'Single Row (Horizontal)',
								},
								'→'
							),

							// 3. 50 / 50
							h(
								'div',
								{
									className: 'sppcfw-w-full sppcfw-h-16 sppcfw-bg-[#cbd5e1]/60 hover:sppcfw-bg-[#9333ea] sppcfw-rounded-md sppcfw-p-1.5 sppcfw-flex sppcfw-gap-1 sppcfw-cursor-pointer sppcfw-transition-all sppcfw-shadow-sm sppcfw-tab-group',
									onClick: () => handlePresetSelect('2_col_50_50'),
									title: '50% / 50%',
								},
								h('div', { className: 'sppcfw-w-1/2 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-w-1/2 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' })
							),

							// 4. 33 / 67
							h(
								'div',
								{
									className: 'sppcfw-w-full sppcfw-h-16 sppcfw-bg-[#cbd5e1]/60 hover:sppcfw-bg-[#9333ea] sppcfw-rounded-md sppcfw-p-1.5 sppcfw-flex sppcfw-gap-1 sppcfw-cursor-pointer sppcfw-transition-all sppcfw-shadow-sm sppcfw-tab-group',
									onClick: () => handlePresetSelect('2_col_33_66'),
									title: '33% / 67%',
								},
								h('div', { className: 'sppcfw-w-1/3 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-w-2/3 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' })
							),

							// 5. 4 Columns (25/25/25/25)
							h(
								'div',
								{
									className: 'sppcfw-w-full sppcfw-h-16 sppcfw-bg-[#cbd5e1]/60 hover:sppcfw-bg-[#9333ea] sppcfw-rounded-md sppcfw-p-1.5 sppcfw-flex sppcfw-gap-1 sppcfw-cursor-pointer sppcfw-transition-all sppcfw-shadow-sm sppcfw-tab-group',
									onClick: () => handlePresetSelect('4_col_25_25_25_25'),
									title: '25% / 25% / 25% / 25%',
								},
								h('div', { className: 'sppcfw-w-1/4 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-w-1/4 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-w-1/4 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-w-1/4 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' })
							),

							// 6. 3 Columns (33/33/33)
							h(
								'div',
								{
									className: 'sppcfw-w-full sppcfw-h-16 sppcfw-bg-[#cbd5e1]/60 hover:sppcfw-bg-[#9333ea] sppcfw-rounded-md sppcfw-p-1.5 sppcfw-flex sppcfw-gap-1 sppcfw-cursor-pointer sppcfw-transition-all sppcfw-shadow-sm sppcfw-tab-group',
									onClick: () => handlePresetSelect('3_col_33_33_33'),
									title: '33% / 33% / 33%',
								},
								h('div', { className: 'sppcfw-w-1/3 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-w-1/3 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-w-1/3 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' })
							),

							// 7. 25 / 50 / 25
							h(
								'div',
								{
									className: 'sppcfw-w-full sppcfw-h-16 sppcfw-bg-[#cbd5e1]/60 hover:sppcfw-bg-[#9333ea] sppcfw-rounded-md sppcfw-p-1.5 sppcfw-flex sppcfw-gap-1 sppcfw-cursor-pointer sppcfw-transition-all sppcfw-shadow-sm sppcfw-tab-group',
									onClick: () => handlePresetSelect('3_col_25_50_25'),
									title: '25% / 50% / 25%',
								},
								h('div', { className: 'sppcfw-w-1/4 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-w-1/2 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-w-1/4 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' })
							),

							// 8. 67 / 33
							h(
								'div',
								{
									className: 'sppcfw-w-full sppcfw-h-16 sppcfw-bg-[#cbd5e1]/60 hover:sppcfw-bg-[#9333ea] sppcfw-rounded-md sppcfw-p-1.5 sppcfw-flex sppcfw-gap-1 sppcfw-cursor-pointer sppcfw-transition-all sppcfw-shadow-sm sppcfw-tab-group',
									onClick: () => handlePresetSelect('2_col_66_33'),
									title: '67% / 33%',
								},
								h('div', { className: 'sppcfw-w-2/3 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-w-1/3 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' })
							)
					  )
					: h(
							'div',
							{ className: 'sppcfw-grid sppcfw-grid-cols-4 sppcfw-gap-4 sppcfw-py-4 sppcfw-max-w-lg sppcfw-mx-auto' },

							// 1. Grid 2x2
							h(
								'div',
								{
									className: 'sppcfw-w-full sppcfw-h-16 sppcfw-bg-[#cbd5e1]/60 hover:sppcfw-bg-[#9333ea] sppcfw-rounded-md sppcfw-p-1.5 sppcfw-grid sppcfw-grid-cols-2 sppcfw-gap-1 sppcfw-cursor-pointer sppcfw-transition-all sppcfw-shadow-sm sppcfw-tab-group',
									onClick: () => handlePresetSelect('grid_2x2'),
									title: 'Grid 2x2',
								},
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' })
							),

							// 2. Grid 3x3
							h(
								'div',
								{
									className: 'sppcfw-w-full sppcfw-h-16 sppcfw-bg-[#cbd5e1]/60 hover:sppcfw-bg-[#9333ea] sppcfw-rounded-md sppcfw-p-1 sppcfw-grid sppcfw-grid-cols-3 sppcfw-gap-0.5 sppcfw-cursor-pointer sppcfw-transition-all sppcfw-shadow-sm sppcfw-tab-group',
									onClick: () => handlePresetSelect('grid_3x3'),
									title: 'Grid 3x3',
								},
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h('div', { className: 'sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' })
							),

							// 3. Grid 1 Top 2 Bottom
							h(
								'div',
								{
									className: 'sppcfw-w-full sppcfw-h-16 sppcfw-bg-[#cbd5e1]/60 hover:sppcfw-bg-[#9333ea] sppcfw-rounded-md sppcfw-p-1.5 sppcfw-flex sppcfw-flex-col sppcfw-gap-1 sppcfw-cursor-pointer sppcfw-transition-all sppcfw-shadow-sm sppcfw-tab-group',
									onClick: () => handlePresetSelect('grid_1_2'),
									title: 'Grid 1 Top, 2 Bottom',
								},
								h('div', { className: 'sppcfw-w-full sppcfw-h-1/2 sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
								h(
									'div',
									{ className: 'sppcfw-w-full sppcfw-h-1/2 sppcfw-flex sppcfw-gap-1' },
									h('div', { className: 'sppcfw-w-1/2 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
									h('div', { className: 'sppcfw-w-1/2 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' })
								)
							),

							// 4. Grid 2 Top 1 Bottom
							h(
								'div',
								{
									className: 'sppcfw-w-full sppcfw-h-16 sppcfw-bg-[#cbd5e1]/60 hover:sppcfw-bg-[#9333ea] sppcfw-rounded-md sppcfw-p-1.5 sppcfw-flex sppcfw-flex-col sppcfw-gap-1 sppcfw-cursor-pointer sppcfw-transition-all sppcfw-shadow-sm sppcfw-tab-group',
									onClick: () => handlePresetSelect('grid_2_1'),
									title: 'Grid 2 Top, 1 Bottom',
								},
								h(
									'div',
									{ className: 'sppcfw-w-full sppcfw-h-1/2 sppcfw-flex sppcfw-gap-1' },
									h('div', { className: 'sppcfw-w-1/2 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' }),
									h('div', { className: 'sppcfw-w-1/2 sppcfw-h-full sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' })
								),
								h('div', { className: 'sppcfw-w-full sppcfw-h-1/2 sppcfw-bg-[#94a3b8] group-hover:sppcfw-bg-white/80 sppcfw-rounded-sm' })
							)
					  ))
		);
	}

	// Responsive property helpers (desktop, tablet, mobile)
	function getDeviceKey(key, deviceView) {
		if (deviceView === 'tablet') return key + '_tablet';
		if (deviceView === 'mobile') return key + '_mobile';
		return key;
	}

	function getResponsiveProp(obj, key, deviceView) {
		if (!obj) return undefined;
		if (deviceView === 'mobile') {
			if (obj[key + '_mobile'] !== undefined && obj[key + '_mobile'] !== '') return obj[key + '_mobile'];
			if (obj[key + '_tablet'] !== undefined && obj[key + '_tablet'] !== '') return obj[key + '_tablet'];
			return obj[key];
		}
		if (deviceView === 'tablet') {
			if (obj[key + '_tablet'] !== undefined && obj[key + '_tablet'] !== '') return obj[key + '_tablet'];
			return obj[key];
		}
		return obj[key];
	}

	// Helpers for nested element tree
	function findElementInTree(tree, targetId) {
		for (const el of tree) {
			if (el.id === targetId) return el;
			if (el.children && Array.isArray(el.children)) {
				const found = findElementInTree(el.children, targetId);
				if (found) return found;
			}
		}
		return null;
	}

	function findParentInTree(tree, targetId, parent = null) {
		for (const el of tree) {
			if (el.id === targetId) return parent;
			if (el.children && Array.isArray(el.children)) {
				const found = findParentInTree(el.children, targetId, el);
				if (found) return found;
			}
		}
		return null;
	}

	function updateElementInTree(tree, targetId, updateFn) {
		return tree.map(el => {
			if (el.id === targetId) {
				return updateFn(el);
			}
			if (el.children && Array.isArray(el.children)) {
				return {
					...el,
					children: updateElementInTree(el.children, targetId, updateFn),
				};
			}
			return el;
		});
	}

	function removeElementFromTree(tree, targetId) {
		return tree
			.filter(el => el.id !== targetId)
			.map(el => {
				if (el.children && Array.isArray(el.children)) {
					return {
						...el,
						children: removeElementFromTree(el.children, targetId),
					};
				}
				return el;
			});
	}

	function insertChildInTree(tree, parentId, newChild, targetIndex) {
		if (!parentId) {
			const copy = [...tree];
			if (typeof targetIndex === 'number' && targetIndex >= 0) {
				copy.splice(targetIndex, 0, newChild);
			} else {
				copy.push(newChild);
			}
			return copy;
		}

		return tree.map(el => {
			if (el.id === parentId) {
				const children = el.children ? [...el.children] : [];
				if (typeof targetIndex === 'number' && targetIndex >= 0) {
					children.splice(targetIndex, 0, newChild);
				} else {
					children.push(newChild);
				}
				return { ...el, children };
			}
			if (el.children && Array.isArray(el.children)) {
				return {
					...el,
					children: insertChildInTree(el.children, parentId, newChild, targetIndex),
				};
			}
			return el;
		});
	}

	function moveElementInTree(tree, sourceId, targetParentId, targetIndex) {
		const elementToMove = findElementInTree(tree, sourceId);
		if (!elementToMove) return tree;

		const cleanedTree = removeElementFromTree(tree, sourceId);
		return insertChildInTree(cleanedTree, targetParentId, elementToMove, targetIndex);
	}

	// Searchable Product Select Dropdown Component
	function SearchableProductSelect({ products = [], selectedProductId, onChange, placeholder = 'Search products...' }) {
		const [isOpen, setIsOpen] = useState(false);
		const [searchTerm, setSearchTerm] = useState('');
		const containerRef = useRef(null);

		// Close dropdown on outside click
		useEffect(() => {
			function handleClickOutside(event) {
				if (containerRef.current && !containerRef.current.contains(event.target)) {
					setIsOpen(false);
				}
			}
			if (isOpen) {
				document.addEventListener('mousedown', handleClickOutside);
			}
			return () => {
				document.removeEventListener('mousedown', handleClickOutside);
			};
		}, [isOpen]);

		const selectedProduct = products.find(p => String(p.id) === String(selectedProductId));
		const displayLabel = selectedProduct ? selectedProduct.title : 'Default (All Products)';

		const filteredProducts = products.filter(p => {
			if (!searchTerm.trim()) return true;
			const term = searchTerm.toLowerCase();
			const titleMatch = (p.title || '').toLowerCase().includes(term);
			const idMatch = String(p.id).includes(term);
			return titleMatch || idMatch;
		});

		function handleSelect(id) {
			onChange({ target: { value: id } });
			setIsOpen(false);
			setSearchTerm('');
		}

		return h(
			'div',
			{ className: 'sppcfw-relative sppcfw-w-full', ref: containerRef },

			// Trigger Button
			h(
				'button',
				{
					type: 'button',
					className: `sppcfw-w-full sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-bg-[#111827] sppcfw-border sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-text-xs sppcfw-transition-all sppcfw-cursor-pointer ${
						isOpen ? 'sppcfw-border-[#9333ea] sppcfw-ring-1 sppcfw-ring-[#9333ea]' : 'sppcfw-border-[#374151] hover:sppcfw-border-gray-500'
					}`,
					onClick: () => setIsOpen(!isOpen),
				},
				h(
					'div',
					{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2 sppcfw-overflow-hidden sppcfw-pr-2' },
					h('span', { className: 'material-symbols-outlined sppcfw-text-sm sppcfw-text-[#9333ea]' }, selectedProductId ? 'shopping_bag' : 'auto_awesome'),
					h('span', { className: 'sppcfw-truncate sppcfw-text-white sppcfw-font-medium' }, displayLabel)
				),
				h('span', { className: `sppcfw-text-[10px] sppcfw-text-gray-400 sppcfw-transition-transform ${isOpen ? 'sppcfw-rotate-180' : ''}` }, '▼')
			),

			// Dropdown Panel
			isOpen &&
				h(
					'div',
					{
						className: 'sppcfw-absolute sppcfw-top-full sppcfw-left-0 sppcfw-w-full sppcfw-mt-1 sppcfw-bg-[#1f2937] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded-lg sppcfw-shadow-2xl sppcfw-z-50 sppcfw-overflow-hidden sppcfw-flex sppcfw-flex-col sppcfw-max-h-72',
					},

					// Search Input Header
					h(
						'div',
						{ className: 'sppcfw-p-2 sppcfw-border-b sppcfw-border-[#374151] sppcfw-bg-[#111827]' },
						h(
							'div',
							{ className: 'sppcfw-relative' },
							h('span', { className: 'material-symbols-outlined sppcfw-absolute sppcfw-right-2 sppcfw-top-1/2 sppcfw--translate-y-1/2 sppcfw-text-xs sppcfw-text-gray-400' }, 'search'),
							h('input', {
								type: 'text',
								autoFocus: true,
								value: searchTerm,
								placeholder: placeholder,
								onChange: e => setSearchTerm(e.target.value),
								className: 'sppcfw-w-full sppcfw-bg-[#1f2937] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-pl-7 sppcfw-pr-6 sppcfw-py-1 sppcfw-text-xs sppcfw-text-white sppcfw-placeholder-gray-400 focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
							}),
							searchTerm &&
								h(
									'button',
									{
										type: 'button',
										className: 'sppcfw-absolute sppcfw-right-1.5 sppcfw-top-1/2 sppcfw--translate-y-1/2 sppcfw-text-gray-400 hover:sppcfw-text-white sppcfw-text-xs sppcfw-cursor-pointer',
										onClick: () => setSearchTerm(''),
									},
									'✕'
								)
						)
					),

					// Options List
					h(
						'div',
						{ className: 'sppcfw-overflow-y-auto custom-scrollbar sppcfw-flex-1 sppcfw-p-1 sppcfw-space-y-0.5' },

						// "Default (All Products)" Option
						h(
							'button',
							{
								type: 'button',
								className: `sppcfw-w-full sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-p-2 sppcfw-rounded sppcfw-text-left sppcfw-transition-colors sppcfw-cursor-pointer ${
									!selectedProductId ? 'sppcfw-bg-[#9333ea] sppcfw-text-white' : 'hover:sppcfw-bg-[#111827] sppcfw-text-gray-200'
								}`,
								onClick: () => handleSelect(''),
							},
							h(
								'div',
								{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2.5' },
								h('span', { className: 'material-symbols-outlined sppcfw-text-sm sppcfw-opacity-80' }, 'auto_awesome'),
								h(
									'div',
									null,
									h('div', { className: 'sppcfw-text-xs sppcfw-font-semibold' }, 'Default (All Products)'),
									h('div', { className: 'sppcfw-text-[10px] sppcfw-opacity-70' }, 'Demo sample preview')
								)
							),
							!selectedProductId && h('span', { className: 'material-symbols-outlined sppcfw-text-xs sppcfw-font-bold' }, 'check')
						),

						// Products List
						filteredProducts.length > 0
							? filteredProducts.map(prod => {
									const isSelected = String(selectedProductId) === String(prod.id);
									return h(
										'button',
										{
											key: prod.id,
											type: 'button',
											className: `sppcfw-w-full sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-p-2 sppcfw-rounded sppcfw-text-left sppcfw-transition-colors sppcfw-cursor-pointer ${
												isSelected ? 'sppcfw-bg-[#9333ea] sppcfw-text-white' : 'hover:sppcfw-bg-[#111827] sppcfw-text-gray-200'
											}`,
											onClick: () => handleSelect(prod.id),
										},
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2.5 sppcfw-overflow-hidden' },
											prod.image_url
												? h('img', { src: prod.image_url, alt: '', className: 'sppcfw-w-6 sppcfw-h-6 sppcfw-rounded sppcfw-object-cover sppcfw-bg-[#111827]' })
												: h('span', { className: 'material-symbols-outlined sppcfw-text-sm sppcfw-opacity-60' }, 'image'),
											h(
												'div',
												{ className: 'sppcfw-overflow-hidden' },
												h('div', { className: 'sppcfw-text-xs sppcfw-font-medium sppcfw-truncate' }, prod.title),
												h('div', { className: 'sppcfw-text-[10px] sppcfw-opacity-70 font-mono' }, `ID: #${prod.id}`)
											)
										),
										isSelected && h('span', { className: 'material-symbols-outlined sppcfw-text-xs sppcfw-font-bold' }, 'check')
									);
							  })
							: h(
									'div',
									{ className: 'sppcfw-py-4 sppcfw-text-center sppcfw-text-gray-400 sppcfw-text-xs' },
									'No products found matching "',
									searchTerm,
									'"'
							  )
					)
				)
		);
	}

	// Main App Component
	function BuilderApp() {
		const initialTplId = window.SPPCFWBuilderConfig ? window.SPPCFWBuilderConfig.template_id || 'template_default' : 'template_default';
		const [templateId, setTemplateId] = useState(initialTplId);
		const [templateTitle, setTemplateTitle] = useState('Single Product Template');

		const [products, setProducts] = useState([]);
		const [categories, setCategories] = useState([]);
		const [selectedProductId, setSelectedProductId] = useState('');
		const [productData, setProductData] = useState(null);

		const [deviceView, setDeviceView] = useState('desktop'); // 'desktop' | 'tablet' | 'mobile'
		const [activeLeftTab, setActiveLeftTab] = useState('widgets'); // 'widgets' | 'structure'
		const [activeSubTab, setActiveSubTab] = useState('widgets'); // 'widgets' | 'components' | 'globals'
		const [searchQuery, setSearchQuery] = useState('');

		const [elements, setElements] = useState([]);
		const [selectedElementId, setSelectedElementId] = useState(null);

		const [isStructureOpen, setIsStructureOpen] = useState(true);
		const [isConditionsModalOpen, setIsConditionsModalOpen] = useState(false);
		const [displayConditions, setDisplayConditions] = useState({
			scope: 'entire',
			category_ids: [],
			product_ids: [],
		});

		const [pageSettings, setPageSettings] = useState({
			status: 'published',
			pageLayout: 'Default',
			bgColor: '#091421',
			customCss: '',
		});

		const [allTemplates, setAllTemplates] = useState([]);
		const [isSaving, setIsSaving] = useState(false);
		const [statusMessage, setStatusMessage] = useState('');

		function fetchTemplatesList() {
			apiPost('sppcfw_get_builder_templates', {}).then(res => {
				if (res && res.success && Array.isArray(res.data.templates)) {
					setAllTemplates(res.data.templates);
				}
			});
		}

		function applyLoadedTemplateData(tpl) {
			if (tpl.id) setTemplateId(tpl.id);
			if (tpl.title) setTemplateTitle(tpl.title);
			if (tpl.layout && Array.isArray(tpl.layout)) {
				setElements(tpl.layout);
			} else {
				setElements([]);
			}
			if (tpl.conditions) {
				setDisplayConditions(tpl.conditions);
			} else {
				setDisplayConditions({ scope: 'entire', category_ids: [], product_ids: [] });
			}

			const loadedProdId = (tpl.page_settings && tpl.page_settings.selected_product_id) || tpl.selected_product_id || '';
			setSelectedProductId(loadedProdId);
			fetchProductData(loadedProdId);

			if (tpl.page_settings) {
				const st = (tpl.page_settings.status || tpl.status || 'published').toLowerCase();
				setPageSettings({ ...tpl.page_settings, status: st, selected_product_id: loadedProdId });
			} else if (tpl.status) {
				setPageSettings(prev => ({ ...prev, status: tpl.status.toLowerCase(), selected_product_id: loadedProdId }));
			} else {
				setPageSettings({ status: 'published', pageLayout: 'Default', bgColor: '#091421', customCss: '', selected_product_id: loadedProdId });
			}
		}

		function switchTemplate(targetId) {
			apiPost('sppcfw_load_builder_template', { template_id: targetId }).then(res => {
				if (res && res.success && res.data && res.data.template) {
					const tpl = res.data.template;
					applyLoadedTemplateData(tpl);
					if (window.history && window.history.pushState) {
						const newUrl = new URL(window.location.href);
						newUrl.searchParams.set('template_id', tpl.id || targetId);
						window.history.pushState(null, '', newUrl.toString());
					}
				}
			});
		}

		// Initial Data Load
		useEffect(() => {
			fetchTemplatesList();

			apiPost('sppcfw_get_builder_products_and_categories', {}).then(res => {
				if (res && res.success) {
					setProducts(res.data.products || []);
					setCategories(res.data.categories || []);
				}
			});

			apiPost('sppcfw_load_builder_template', { template_id: initialTplId }).then(res => {
				if (res && res.success && res.data && res.data.template) {
					const tpl = res.data.template;
					applyLoadedTemplateData(tpl);
				} else {
					fetchProductData(0);
				}
			});
		}, []);

		function fetchProductData(productId) {
			if (!productId) {
				setProductData(null);
				return;
			}
			apiPost('sppcfw_get_builder_product_data', { product_id: productId }).then(res => {
				if (res && res.success && res.data && res.data.product) {
					setProductData(res.data.product);
				} else {
					setProductData(null);
				}
			});
		}

		function handleProductChange(e) {
			const id = e.target.value;
			setSelectedProductId(id);
			setPageSettings(prev => ({ ...prev, selected_product_id: id }));
			fetchProductData(id);
		}

		// Add Layout Structure to Canvas
		function addContainerPreset(presetType) {
			const newContainer = createContainerStructure(presetType);
			setElements(prev => [...prev, newContainer]);
			setSelectedElementId(newContainer.id);
		}

		// Add Column to specified Container
		function addColumnToContainer(targetContainerId, flexWidth) {
			const timestamp = Date.now();
			let resolvedContainerId = targetContainerId || selectedElementId;

			if (!resolvedContainerId && elements.length > 0) {
				resolvedContainerId = elements[elements.length - 1].id;
			}

			if (elements.length === 0) {
				const autoContainer = createContainerStructure('1_container');
				setElements([autoContainer]);
				setSelectedElementId(autoContainer.children[0].id);
				return;
			}

			const targetNode = findElementInTree(elements, resolvedContainerId);
			let containerId = targetNode && targetNode.type === 'container' ? targetNode.id : null;
			if (!containerId && targetNode && targetNode.type === 'column') {
				const parent = findParentInTree(elements, targetNode.id);
				if (parent) containerId = parent.id;
			}

			if (!containerId && elements.length > 0) {
				containerId = elements[0].id;
			}

			if (!containerId) return;

			const containerNode = findElementInTree(elements, containerId);
			const currentColsCount = containerNode && containerNode.children ? containerNode.children.length : 0;
			const newCount = currentColsCount + 1;

			let defaultWidth = '100%';
			if (newCount === 2) defaultWidth = '50%';
			else if (newCount === 3) defaultWidth = '33.33%';
			else if (newCount === 4) defaultWidth = '25%';
			else defaultWidth = flexWidth || (100 / newCount).toFixed(2) + '%';

			const newColId = 'col-' + timestamp + '-' + Math.floor(Math.random() * 1000);
			const newColumn = {
				id: newColId,
				type: 'column',
				label: 'Column ' + newCount + ' (' + (flexWidth || defaultWidth) + ')',
				settings: { flex_width: flexWidth || defaultWidth },
				children: [],
				styles: { padding_top: '12px', padding_right: '12px', padding_bottom: '12px', padding_left: '12px' },
			};

			setElements(prev => {
				return updateElementInTree(prev, containerId, container => {
					const children = container.children ? [...container.children] : [];
					return {
						...container,
						children: [...children, newColumn],
					};
				});
			});

			setSelectedElementId(newColId);
		}

		// Duplicate Column and its contents
		function duplicateColumn(columnId) {
			const targetId = columnId || selectedElementId;
			const colToDuplicate = findElementInTree(elements, targetId);
			if (!colToDuplicate || colToDuplicate.type !== 'column') return;

			const parentContainer = findParentInTree(elements, targetId);
			if (!parentContainer) return;

			const timestamp = Date.now();
			const newColId = 'col-' + timestamp + '-' + Math.floor(Math.random() * 1000);

			const duplicatedCol = JSON.parse(JSON.stringify(colToDuplicate));
			duplicatedCol.id = newColId;
			duplicatedCol.label = (colToDuplicate.label || 'Column') + ' (Copy)';

			function reassignIds(item) {
				item.id = 'el-' + item.type + '-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
				if (item.children && Array.isArray(item.children)) {
					item.children.forEach(reassignIds);
				}
			}
			if (duplicatedCol.children) {
				duplicatedCol.children.forEach(reassignIds);
			}

			setElements(prev => {
				return updateElementInTree(prev, parentContainer.id, container => {
					const children = container.children ? [...container.children] : [];
					const idx = children.findIndex(c => c.id === targetId);
					if (idx >= 0) {
						children.splice(idx + 1, 0, duplicatedCol);
					} else {
						children.push(duplicatedCol);
					}
					return { ...container, children };
				});
			});

			setSelectedElementId(newColId);
		}

		// Add Widget to specified Parent Column/Container
		function addWidgetToTarget(widgetType, name, metaKey, targetParentId, targetIndex) {
			if (['column'].includes(widgetType) || (name && name.toLowerCase() === 'column')) {
				addColumnToContainer(targetParentId);
				return;
			}

			// If adding preset layout directly from atomic cards
			if (['div_block', '1_container', 'flexbox', 'grid_2x2', 'grid'].includes(widgetType)) {
				addContainerPreset(widgetType);
				return;
			}

			// If canvas has no containers yet, automatically create a default flexbox container
			let currentElements = elements;
			let resolvedParentId = targetParentId;

			if (currentElements.length === 0 && !resolvedParentId) {
				const autoContainer = createContainerStructure('1_container');
				currentElements = [autoContainer];
				resolvedParentId = autoContainer.children[0].id;
			}

			const newId = 'el-' + widgetType + '-' + Date.now();
			const newElement = {
				id: newId,
				type: widgetType,
				label: name || widgetType,
				metaKey: metaKey || null,
				settings: {
					scope: 'global',
					alignment: 'left',
				},
				styles: {
					font_family: 'Inter',
					font_size: '16px',
					font_weight: '400',
					line_height: '1.5',
					text_color: '#111827',
					bg_color: 'transparent',
					border_color: '#e5e7eb',
					border_width: '0px',
					border_radius: '0px',
					padding_top: '0px',
					padding_right: '0px',
					padding_bottom: '0px',
					padding_left: '0px',
					margin_top: '0px',
					margin_right: '0px',
					margin_bottom: '16px',
					margin_left: '0px',
				},
				advanced: {
					custom_class: '',
					z_index: '1',
				},
			};

			setElements(() => {
				if (!resolvedParentId) {
					const lastContainer = currentElements[currentElements.length - 1];
					if (lastContainer && lastContainer.children && lastContainer.children[0]) {
						resolvedParentId = lastContainer.children[0].id;
					} else if (lastContainer) {
						resolvedParentId = lastContainer.id;
					}
				}
				return insertChildInTree(currentElements, resolvedParentId, newElement, targetIndex);
			});

			setSelectedElementId(newId);
		}

		function removeElement(id) {
			setElements(prev => removeElementFromTree(prev, id));
			if (selectedElementId === id) {
				setSelectedElementId(null);
			}
		}

		function saveTemplate() {
			setIsSaving(true);
			const currentStatus = (pageSettings && pageSettings.status) ? pageSettings.status.toLowerCase() : 'published';
			const isDraft = currentStatus === 'draft';
			setStatusMessage(isDraft ? 'Saving draft...' : 'Publishing template...');

			const updatedPageSettings = {
				...pageSettings,
				selected_product_id: selectedProductId || '',
			};

			apiPost('sppcfw_save_builder_template', {
				template_id: templateId,
				template_title: templateTitle,
				status: currentStatus,
				selected_product_id: selectedProductId || '',
				page_settings: JSON.stringify(updatedPageSettings),
				layout: JSON.stringify(elements),
				conditions: JSON.stringify(displayConditions),
			}).then(res => {
				setIsSaving(false);
				if (res && res.success) {
					if (res.data && res.data.template_id) {
						setTemplateId(res.data.template_id);
					}
					setStatusMessage(res.data.message || (isDraft ? 'Draft saved successfully!' : 'Published successfully!'));
					fetchTemplatesList();
					setTimeout(() => setStatusMessage(''), 4000);
				} else {
					setStatusMessage('Failed to save template.');
				}
			});
		}

		const selectedElement = findElementInTree(elements, selectedElementId);

		function updateElementProperties(updatedElement) {
			setElements(prev => updateElementInTree(prev, updatedElement.id, () => updatedElement));
		}

		// Helper to open Elements panel from header + icon button
		function openElementsTab() {
			setActiveLeftTab('widgets');
			setSelectedElementId(null);
			setSearchQuery('');
		}

		// Helper to open Page Settings (Post Settings) panel button
		function openPageSettings() {
			setActiveLeftTab('settings');
			setSelectedElementId(null);
		}

		// Helper to open live product preview in a new browser tab
		function handlePreview() {
			let targetProduct = null;
			if (selectedProductId && Array.isArray(products)) {
				targetProduct = products.find(p => String(p.id) === String(selectedProductId));
			}
			if (!targetProduct && Array.isArray(products) && products.length > 0) {
				targetProduct = products[0];
			}

			let previewUrl = targetProduct && targetProduct.url ? targetProduct.url : window.location.origin;
			const sep = previewUrl.includes('?') ? '&' : '?';
			previewUrl += sep + 'sppcfw_preview=1&template_id=' + encodeURIComponent(templateId);

			window.open(previewUrl, '_blank');
		}

		const effectiveSampleData = (selectedProductId && productData)
			? { ...CANVAS_STATIC_DATA, ...productData }
			: CANVAS_STATIC_DATA;

		return h(
			'div',
			{ className: 'sppcfw-builder-layout sppcfw-flex sppcfw-flex-col sppcfw-h-screen sppcfw-w-screen sppcfw-overflow-hidden sppcfw-text-[#d9e3f6]' },

			// Top Bar Navigation
			h(TopBar, {
				templateTitle,
				setTemplateTitle,
				deviceView,
				setDeviceView,
				saveTemplate,
				isSaving,
				statusMessage,
				openElementsTab,
				openPageSettings,
				activeLeftTab,
				pageSettings,
				isStructureOpen,
				setIsStructureOpen,
				openConditionsModal: () => setIsConditionsModalOpen(true),
				allTemplates,
				templateId,
				switchTemplate,
				handlePreview,
			}),

			// Main Workspace Grid
			h(
				'div',
				{ className: 'sppcfw-builder-workspace sppcfw-flex sppcfw-flex-1 sppcfw-overflow-hidden sppcfw-relative' },

				// Left Rail Navigation Icons
				h(LeftRail, {
					activeLeftTab,
					setActiveLeftTab,
					openElementsTab,
				}),

				// Left Sidebar Panel (Renders Inspector on left when element selected, Post Settings when settings active, or Elements Library)
				h(LeftPanel, {
					deviceView,
					selectedElement,
					updateElementProperties,
					activeLeftTab,
					activeSubTab,
					setActiveSubTab,
					searchQuery,
					setSearchQuery,
					products,
					categories,
					selectedProductId,
					handleProductChange,
					productData,
					sampleData: effectiveSampleData,
					addWidgetToTarget,
					addContainerPreset,
					addColumnToContainer,
					duplicateColumn,
					removeElement,
					elements,
					setElements,
					selectedElementId,
					setSelectedElementId,
					templateTitle,
					setTemplateTitle,
					pageSettings,
					setPageSettings,
					openElementsTab,
				}),

				// Central Canvas Workspace
				h(CentralCanvas, {
					deviceView,
					elements,
					setElements,
					selectedElementId,
					setSelectedElementId,
					sampleData: effectiveSampleData,
					pageSettings,
					removeElement,
					addWidgetToTarget,
					addColumnToContainer,
					duplicateColumn,
					openElementsTab,
					isStructureOpen,
					setIsStructureOpen,
				}),

				// Right Floating Dockable Structure Panel (Image 2 )
				isStructureOpen &&
					h(FloatingStructurePanel, {
						elements,
						setElements,
						selectedElementId,
						setSelectedElementId,
						removeElement,
						openElementsTab,
						closeStructure: () => setIsStructureOpen(false),
					})
			),

			// Display Conditions Modal
			isConditionsModalOpen &&
				h(DisplayConditionsModal, {
					displayConditions,
					setDisplayConditions,
					categories,
					products,
					closeModal: () => setIsConditionsModalOpen(false),
					saveTemplate,
				})
		);
	}

	// 1. Top Navigation Bar Component (Positioned: Left)
	function TopBar({ templateTitle, setTemplateTitle, deviceView, setDeviceView, saveTemplate, isSaving, statusMessage, openElementsTab, openPageSettings, activeLeftTab, pageSettings, isStructureOpen, setIsStructureOpen, openConditionsModal, allTemplates, templateId, switchTemplate, handlePreview }) {
		const [isMenuOpen, setIsMenuOpen] = useState(false);
		const menuRef = useRef(null);

		const [isTemplateMenuOpen, setIsTemplateMenuOpen] = useState(false);
		const templateMenuRef = useRef(null);

		// Close dropdowns when clicking outside
		useEffect(() => {
			function handleClickOutside(event) {
				if (menuRef.current && !menuRef.current.contains(event.target)) {
					setIsMenuOpen(false);
				}
				if (templateMenuRef.current && !templateMenuRef.current.contains(event.target)) {
					setIsTemplateMenuOpen(false);
				}
			}
			document.addEventListener('mousedown', handleClickOutside);
			return () => document.removeEventListener('mousedown', handleClickOutside);
		}, []);

		const currentStatus = (pageSettings && pageSettings.status) ? pageSettings.status.toLowerCase() : 'published';
		let publishLabel = 'Publish';
		if (isSaving) {
			publishLabel = currentStatus === 'draft' ? 'Saving...' : 'Publishing...';
		} else if (currentStatus === 'draft') {
			publishLabel = 'Save Draft';
		} else if (currentStatus === 'pending' || currentStatus === 'pending review' || currentStatus === 'private') {
			publishLabel = 'Save (' + currentStatus + ')';
		} else {
			publishLabel = 'Publish';
		}

		return h(
			'header',
			{ className: 'sppcfw-bg-[#111111] sppcfw-border-b sppcfw-border-[#262626] sppcfw-h-12 sppcfw-top-0 sppcfw-left-0 sppcfw-right-0 sppcfw-z-50 sppcfw-flex sppcfw-justify-between sppcfw-items-center sppcfw-px-3 sppcfw-select-none sppcfw-text-white sppcfw-text-xs font-sans sppcfw-relative' },

			// 1. LEFT SECTION: History
			h(
				'div',
				{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2 sppcfw-relative sppcfw-z-10', ref: menuRef },

				// History Button
				h(
					'button',
					{
						className: 'sppcfw-w-7 sppcfw-h-7 sppcfw-rounded-full sppcfw-bg-white sppcfw-text-black sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-shadow hover:sppcfw-bg-gray-100 sppcfw-transition-all sppcfw-font-bold focus:sppcfw-outline-none sppcfw-cursor-pointer',
						onClick: () => setIsMenuOpen(!isMenuOpen),
						title: 'Menu',
					},
					h('span', { className: 'material-symbols-outlined sppcfw-text-lg sppcfw-leading-none sppcfw-font-bold' }, 'menu')
				),

				//   Dropdown Menu (Image 2: Popover containing only "Exit to Builder")
				isMenuOpen &&
					h(
						'div',
						{ className: 'sppcfw-absolute sppcfw-top-9 sppcfw-left-0 sppcfw-w-60 sppcfw-bg-[#1e1e1e] sppcfw-border sppcfw-border-[#333333] sppcfw-rounded-lg sppcfw-shadow-2xl sppcfw-py-2 sppcfw-px-1 sppcfw-z-50 sppcfw-text-white sppcfw-animate-in sppcfw-fade-in sppcfw-slide-in-from-top-1 sppcfw-duration-150' },
						h(
							'button',
							{
								className: 'sppcfw-w-full sppcfw-flex sppcfw-items-center sppcfw-gap-3 sppcfw-px-3 sppcfw-py-2 sppcfw-text-left hover:sppcfw-bg-[#2d2d2d] sppcfw-rounded sppcfw-transition-colors sppcfw-text-xs sppcfw-font-medium sppcfw-text-gray-200 hover:sppcfw-text-white sppcfw-cursor-pointer',
								onClick: () => {
									setIsMenuOpen(false);
									window.location.href = 'admin.php?page=sppcfw-single-page-builder';
								},
							},
							h('span', { className: 'material-symbols-outlined sppcfw-text-base sppcfw-text-gray-300' }, 'logout'),
							'Exit to Builder'
						)
					),

				//Square Plus Button (+)
				h(
					'button',
					{
						className: 'sppcfw-w-7 sppcfw-h-7 sppcfw-bg-[#262626] hover:sppcfw-bg-[#333333] sppcfw-border sppcfw-border-[#3a3a3a] sppcfw-text-white sppcfw-rounded sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-transition-colors sppcfw-cursor-pointer',
						onClick: openElementsTab,
						title: 'Add Elements',
					},
					h('span', { className: 'material-symbols-outlined sppcfw-text-base' }, 'add')
				),

				// Document with Gear Icon Button (Post Settings)
				h(
					'button',
					{
						className: `sppcfw-w-7 sppcfw-h-7 sppcfw-bg-[#262626] hover:sppcfw-bg-[#333333] sppcfw-border sppcfw-rounded sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-transition-colors sppcfw-cursor-pointer ${
							activeLeftTab === 'settings' ? 'sppcfw-border-[#9333ea] sppcfw-text-white sppcfw-bg-[#333333]' : 'sppcfw-border-[#3a3a3a] sppcfw-text-white'
						}`,
						onClick: openPageSettings,
						title: 'Page Settings',
					},
					h('span', { className: 'material-symbols-outlined sppcfw-text-base' }, 'article')
				),

				// History Icon Button
				h(
					'button',
					{
						className: 'sppcfw-w-7 sppcfw-h-7 sppcfw-text-gray-300 hover:sppcfw-text-white sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-transition-colors sppcfw-cursor-pointer',
						title: 'Revision History',
					},
					h('span', { className: 'material-symbols-outlined sppcfw-text-base' }, 'history')
				),

			),

			// 2. CENTER SECTION:(Template Dropdown + Viewport Device Switcher)
			h(
				'div',
				{ className: 'sppcfw-absolute sppcfw-left-1/2 sppcfw--translate-x-1/2 sppcfw-flex sppcfw-items-center sppcfw-gap-3 sppcfw-z-10' },

				// Template Dropdown Selector ("home ∨")
				h(
					'div',
					{ className: 'sppcfw-relative', ref: templateMenuRef },
					h(
						'button',
						{
							type: 'button',
							className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1 sppcfw-cursor-pointer sppcfw-text-gray-300 hover:sppcfw-text-white sppcfw-font-medium sppcfw-text-xs sppcfw-bg-transparent sppcfw-border-none focus:sppcfw-outline-none sppcfw-py-1 sppcfw-px-2 sppcfw-rounded hover:sppcfw-bg-[#262626] sppcfw-transition-colors',
							onClick: () => setIsTemplateMenuOpen(!isTemplateMenuOpen),
							title: 'Switch Template',
						},
						h('span', { className: 'sppcfw-font-semibold sppcfw-max-w-[140px] sppcfw-truncate' }, templateTitle || 'home'),
						h('span', { className: 'material-symbols-outlined sppcfw-text-sm' }, 'expand_more')
					),

					// Template Selection Popover Dropdown
					isTemplateMenuOpen &&
						h(
							'div',
							{ className: 'sppcfw-absolute sppcfw-top-9 sppcfw-left-1/2 sppcfw--translate-x-1/2 sppcfw-w-64 sppcfw-bg-[#1e1e1e] sppcfw-border sppcfw-border-[#333333] sppcfw-rounded-lg sppcfw-shadow-2xl sppcfw-z-50 sppcfw-py-2 sppcfw-text-white sppcfw-animate-in sppcfw-fade-in sppcfw-slide-in-from-top-1 sppcfw-duration-150' },
							h('div', { className: 'sppcfw-px-3 sppcfw-py-1 sppcfw-text-[10px] sppcfw-uppercase sppcfw-font-bold sppcfw-text-gray-400 sppcfw-border-b sppcfw-border-[#2d2d2d] sppcfw-mb-1 sppcfw-flex sppcfw-justify-between sppcfw-items-center' },
								h('span', null, 'Page Templates'),
								h('span', { className: 'sppcfw-text-[9px] sppcfw-bg-[#2d2d2d] sppcfw-text-gray-300 sppcfw-px-1.5 sppcfw-py-0.5 sppcfw-rounded' }, (allTemplates ? allTemplates.length : 0) + ' Total')
							),
							h(
								'div',
								{ className: 'sppcfw-max-h-60 sppcfw-overflow-y-auto custom-scrollbar sppcfw-space-y-0.5 sppcfw-px-1' },
								allTemplates && allTemplates.length > 0
									? allTemplates.map(tpl => {
											const isActive = tpl.id === templateId;
											return h(
												'button',
												{
													key: tpl.id,
													className: `sppcfw-w-full sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-text-left sppcfw-rounded sppcfw-transition-colors sppcfw-text-xs sppcfw-font-medium sppcfw-cursor-pointer ${
														isActive ? 'sppcfw-bg-[#9333ea]/20 sppcfw-text-purple-200 sppcfw-font-bold sppcfw-border sppcfw-border-[#9333ea]/50' : 'hover:sppcfw-bg-[#2d2d2d] sppcfw-text-gray-200 hover:sppcfw-text-white'
													}`,
													onClick: () => {
														setIsTemplateMenuOpen(false);
														if (!isActive) {
															switchTemplate(tpl.id);
														}
													},
												},
												h(
													'div',
													{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2 sppcfw-overflow-hidden' },
													isActive && h('span', { className: 'sppcfw-text-purple-400 sppcfw-text-xs sppcfw-font-bold' }, '✓'),
													h('span', { className: 'sppcfw-truncate sppcfw-max-w-[140px]' }, tpl.title || 'Untitled Template')
												),
												h(
													'span',
													{
														className: `sppcfw-text-[9px] sppcfw-px-1.5 sppcfw-py-0.5 sppcfw-rounded sppcfw-uppercase font-mono ${
															(tpl.status || '').toLowerCase() === 'draft' ? 'sppcfw-bg-amber-900/60 sppcfw-text-amber-300 sppcfw-border sppcfw-border-amber-500/30' : 'sppcfw-bg-emerald-900/60 sppcfw-text-emerald-300 sppcfw-border sppcfw-border-emerald-500/30'
														}`,
													},
													tpl.status || 'Published'
												)
											);
									  })
									: h('div', { className: 'sppcfw-px-3 sppcfw-py-2 sppcfw-text-xs sppcfw-text-gray-400 sppcfw-italic sppcfw-text-center' }, 'No saved templates found')
							),
							h('div', { className: 'sppcfw-border-t sppcfw-border-[#2d2d2d] sppcfw-mt-1 sppcfw-pt-1 sppcfw-px-1' },
								h(
									'button',
									{
										className: 'sppcfw-w-full sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-gap-1.5 sppcfw-px-2 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-[#a855f7] hover:sppcfw-text-purple-300 hover:sppcfw-bg-[#2d2d2d] sppcfw-rounded sppcfw-transition-colors sppcfw-font-bold sppcfw-cursor-pointer',
										onClick: () => {
											setIsTemplateMenuOpen(false);
											switchTemplate('new');
										},
									},
									h('span', { className: 'material-symbols-outlined sppcfw-text-sm' }, 'add'),
									'Create New Template'
								)
							)
						)
				),

				// Viewport Switcher Icons (Desktop, Tablet, Mobile)
				h(
					'div',
					{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-3 sppcfw-text-gray-400' },
					h(
						'button',
						{
							className: `hover:sppcfw-text-white sppcfw-transition-colors sppcfw-py-0.5 sppcfw-cursor-pointer ${
								deviceView === 'desktop' ? 'sppcfw-text-white sppcfw-border-b-2 sppcfw-border-white' : ''
							}`,
							onClick: () => setDeviceView('desktop'),
							title: 'Desktop View',
						},
						h('span', { className: 'material-symbols-outlined sppcfw-text-base' }, 'desktop_windows')
					),
					h(
						'button',
						{
							className: `hover:sppcfw-text-white sppcfw-transition-colors sppcfw-py-0.5 sppcfw-cursor-pointer ${
								deviceView === 'tablet' ? 'sppcfw-text-white sppcfw-border-b-2 sppcfw-border-white' : ''
							}`,
							onClick: () => setDeviceView('tablet'),
							title: 'Tablet View',
						},
						h('span', { className: 'material-symbols-outlined sppcfw-text-base' }, 'tablet_mac')
					),
					h(
						'button',
						{
							className: `hover:sppcfw-text-white sppcfw-transition-colors sppcfw-py-0.5 sppcfw-cursor-pointer ${
								deviceView === 'mobile' ? 'sppcfw-text-white sppcfw-border-b-2 sppcfw-border-white' : ''
							}`,
							onClick: () => setDeviceView('mobile'),
							title: 'Mobile View',
						},
						h('span', { className: 'material-symbols-outlined sppcfw-text-base' }, 'smartphone')
					)
				)
			),

			// 3. RIGHT SECTION:(Publish & Canvas Actions Group) + Utilities
			h(
				'div',
				{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-3 sppcfw-z-10' },

				//Group (Layers, Eye, Badge, Publish Button)
				h(
					'div',
					{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2.5' },
					statusMessage && h('span', { className: 'sppcfw-text-xs sppcfw-text-[#10b981] sppcfw-font-medium' }, statusMessage),

					// Layers / Structure Icon Button
					h(
						'button',
						{
							className: `sppcfw-w-7 sppcfw-h-7 sppcfw-rounded sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-transition-colors sppcfw-cursor-pointer ${
								isStructureOpen ? 'sppcfw-bg-[#262626] sppcfw-text-white sppcfw-border sppcfw-border-[#3a3a3a]' : 'sppcfw-text-gray-400 hover:sppcfw-text-white'
							}`,
							onClick: () => setIsStructureOpen(!isStructureOpen),
							title: 'Structure Panel',
						},
						h('span', { className: 'material-symbols-outlined sppcfw-text-base' }, 'layers')
					),

					// Preview Eye Icon Button
					h(
						'button',
						{
							className: 'sppcfw-w-7 sppcfw-h-7 sppcfw-text-gray-400 hover:sppcfw-text-white sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-transition-colors sppcfw-cursor-pointer',
							onClick: handlePreview,
							title: 'Preview designed page in new tab',
						},
						h('span', { className: 'material-symbols-outlined sppcfw-text-base' }, 'visibility')
					),

					// Publish Button with Dropdown Chevron Arrow
					h(
						'div',
						{ className: 'sppcfw-relative sppcfw-flex sppcfw-items-center' },
						h(
							'button',
							{
								className: 'sppcfw-h-7 sppcfw-px-3 sppcfw-bg-[#1e1a29] sppcfw-border sppcfw-border-[#a855f7]/50 sppcfw-text-white hover:sppcfw-bg-[#2b1f3d] sppcfw-rounded sppcfw-text-xs sppcfw-font-semibold sppcfw-flex sppcfw-items-center sppcfw-gap-1.5 sppcfw-transition-all sppcfw-shadow-sm sppcfw-cursor-pointer',
								onClick: saveTemplate,
								disabled: isSaving,
							},
							publishLabel,
							h('span', { className: 'material-symbols-outlined sppcfw-text-sm sppcfw-text-purple-300', onClick: (e) => { e.stopPropagation(); openConditionsModal(); } }, 'expand_more')
						)
					)
				)
			)
		);
	}

	// 2. Left Rail Navigation Component
	function LeftRail({ activeLeftTab, setActiveLeftTab, openElementsTab }) {
		return h(
			'nav',
			{ className: 'sppcfw-bg-[#16202e] sppcfw-border-r sppcfw-border-[#4d4354] sppcfw-w-[64px] sppcfw-fixed sppcfw-left-0 sppcfw-top-12 sppcfw-bottom-0 sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-py-4 sppcfw-z-40 sppcfw-select-none' },
			h(
				'button',
				{
					className: `sppcfw-w-12 sppcfw-h-12 sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-justify-center sppcfw-gap-1 sppcfw-mb-4 sppcfw-rounded sppcfw-transition-all ${
						activeLeftTab === 'widgets' ? 'sppcfw-text-[#ddb8ff] sppcfw-border-l-2 sppcfw-border-[#9333ea] sppcfw-bg-[#2b3544]' : 'sppcfw-text-[#cfc2d7] hover:sppcfw-bg-[#212b39]'
					}`,
					onClick: openElementsTab,
					title: 'Elements Drawer',
				},
				h('span', { className: 'material-symbols-outlined sppcfw-text-xl' }, 'add_box'),
				h('span', { className: 'sppcfw-text-[9px] sppcfw-uppercase sppcfw-font-bold sppcfw-tracking-wider' }, 'Elements')
			)
		);
	}

	// 3. Left Panel (Renders LeftInspector when element selected, Post Settings when settings tab active, or Elements Library)
	function LeftPanel({
		deviceView = 'desktop',
		selectedElement,
		updateElementProperties,
		activeLeftTab,
		activeSubTab,
		setActiveSubTab,
		searchQuery,
		setSearchQuery,
		products,
		categories,
		selectedProductId,
		handleProductChange,
		productData,
		sampleData,
		addWidgetToTarget,
		addContainerPreset,
		addColumnToContainer,
		duplicateColumn,
		removeElement,
		elements,
		setElements,
		selectedElementId,
		setSelectedElementId,
		templateTitle,
		setTemplateTitle,
		pageSettings,
		setPageSettings,
		openElementsTab,
	}) {
		// If an element or container is selected, render the LEFT-SIDE "Edit Container" / "Edit Element" Inspector
		if (selectedElement) {
			return h(LeftInspector, {
				deviceView,
				selectedElement,
				updateElementProperties,
				closeInspector: () => setSelectedElementId(null),
				categories,
				products,
				sampleData,
				addColumnToContainer,
				duplicateColumn,
				removeElement,
			});
		}

		// If activeLeftTab is 'settings', render the Post Settings panel
		if (activeLeftTab === 'settings') {
			return h(PageSettingsPanel, {
				templateTitle,
				setTemplateTitle,
				pageSettings,
				setPageSettings,
				closePanel: openElementsTab,
				products,
				selectedProductId,
				handleProductChange,
			});
		}

		// Otherwise, render the Left Elements Panel
		return h(LeftElementsPanel, {
			activeSubTab,
			setActiveSubTab,
			searchQuery,
			setSearchQuery,
			products,
			selectedProductId,
			handleProductChange,
			productData,
			addWidgetToTarget,
			addContainerPreset,
			elements,
		});
	}

	// 3c. Page Settings (Post Settings) Panel Component
	function PageSettingsPanel({ templateTitle, setTemplateTitle, pageSettings, setPageSettings, closePanel, products = [], selectedProductId, handleProductChange }) {
		const [activeTab, setActiveTab] = useState('settings'); // 'settings' | 'style' | 'advanced'
		const [isGeneralOpen, setIsGeneralOpen] = useState(true);

		function handlePageSettingChange(key, value) {
			setPageSettings(prev => ({ ...prev, [key]: value }));
		}

		return h(
			'aside',
			{ className: 'sppcfw-w-[340px] sppcfw-bg-[#1f2937] sppcfw-border-r sppcfw-border-[#374151] sppcfw-flex sppcfw-flex-col sppcfw-ml-[64px] sppcfw-z-30 sppcfw-h-full sppcfw-overflow-hidden sppcfw-shadow-md sppcfw-select-none sppcfw-text-[#d9e3f6]' },

			// Header Title "Post Settings"
			h(
				'div',
				{ className: 'sppcfw-p-3 sppcfw-border-b sppcfw-border-[#374151] sppcfw-bg-[#121c2a] sppcfw-flex sppcfw-justify-between sppcfw-items-center' },
				h('h2', { className: 'sppcfw-text-sm sppcfw-font-extrabold sppcfw-text-white sppcfw-text-center sppcfw-flex-1' }, 'Post Settings'),
				h(
					'button',
					{
						className: 'sppcfw-text-gray-400 hover:sppcfw-text-white sppcfw-text-xs sppcfw-font-bold sppcfw-px-1.5 sppcfw-py-0.5 sppcfw-rounded hover:sppcfw-bg-[#212b39]',
						onClick: closePanel,
						title: 'Close',
					},
					'✕'
				)
			),

			// Top Sub-Tabs Bar: Settings (wrench) | Style (contrast) | Advanced (gear)
			h(
				'div',
				{ className: 'sppcfw-flex sppcfw-border-b sppcfw-border-[#374151] sppcfw-bg-[#16202e] sppcfw-text-xs sppcfw-font-semibold' },
				h(
					'button',
					{
						className: `sppcfw-flex-1 sppcfw-py-2.5 sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-gap-1 sppcfw-border-b-2 sppcfw-transition-colors ${
							activeTab === 'settings' ? 'sppcfw-border-white sppcfw-text-white sppcfw-bg-[#1f2937]' : 'sppcfw-border-transparent sppcfw-text-gray-400 hover:sppcfw-text-white'
						}`,
						onClick: () => setActiveTab('settings'),
					},
					h('span', { className: 'material-symbols-outlined sppcfw-text-base' }, 'build'),
					'Settings'
				),
				h(
					'button',
					{
						className: `sppcfw-flex-1 sppcfw-py-2.5 sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-gap-1 sppcfw-border-b-2 sppcfw-transition-colors ${
							activeTab === 'style' ? 'sppcfw-border-white sppcfw-text-white sppcfw-bg-[#1f2937]' : 'sppcfw-border-transparent sppcfw-text-gray-400 hover:sppcfw-text-white'
						}`,
						onClick: () => setActiveTab('style'),
					},
					h('span', { className: 'material-symbols-outlined sppcfw-text-base' }, 'contrast'),
					'Style'
				),
				h(
					'button',
					{
						className: `sppcfw-flex-1 sppcfw-py-2.5 sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-gap-1 sppcfw-border-b-2 sppcfw-transition-colors ${
							activeTab === 'advanced' ? 'sppcfw-border-white sppcfw-text-white sppcfw-bg-[#1f2937]' : 'sppcfw-border-transparent sppcfw-text-gray-400 hover:sppcfw-text-white'
						}`,
						onClick: () => setActiveTab('advanced'),
					},
					h('span', { className: 'material-symbols-outlined sppcfw-text-base' }, 'settings'),
					'Advanced'
				)
			),

			// Scrollable Panel Content
			h(
				'div',
				{ className: 'sppcfw-p-4 sppcfw-overflow-y-auto custom-scrollbar sppcfw-flex-1 sppcfw-space-y-4 sppcfw-text-xs' },

				// TAB 1: Settings
				activeTab === 'settings' &&
					h(
						'div',
						{ className: 'sppcfw-space-y-4' },

						// Accordion: General Settings
						h(
							'div',
							{ className: 'sppcfw-border-b sppcfw-border-[#374151] sppcfw-pb-4' },
							h(
								'div',
								{
									className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-cursor-pointer sppcfw-mb-3 sppcfw-select-none',
									onClick: () => setIsGeneralOpen(!isGeneralOpen),
								},
								h('h3', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white sppcfw-flex sppcfw-items-center sppcfw-gap-2' }, h('span', { className: 'sppcfw-text-[10px]' }, isGeneralOpen ? '▼' : '▶'), 'General Settings')
							),

							isGeneralOpen &&
								h(
									'div',
									{ className: 'sppcfw-space-y-4 sppcfw-pt-1' },

									// Title Field
									h(
										'div',
										{ className: 'sppcfw-space-y-1.5' },
										h('label', { className: 'sppcfw-font-semibold sppcfw-text-gray-200 sppcfw-block' }, 'Title'),
										h('input', {
											type: 'text',
											className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] focus:sppcfw-border-[#9333ea] sppcfw-rounded sppcfw-px-3 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-white sppcfw-placeholder-gray-500 focus:sppcfw-outline-none',
											value: templateTitle,
											onChange: e => setTemplateTitle(e.target.value),
											placeholder: 'Page title...',
										})
									),

									// Status Field
									h(
										'div',
										{ className: 'sppcfw-space-y-1.5' },
										h('label', { className: 'sppcfw-font-semibold sppcfw-text-gray-200 sppcfw-block' }, 'Status'),
										h(
											'select',
											{
												className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] focus:sppcfw-border-[#9333ea] sppcfw-rounded sppcfw-px-3 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-white focus:sppcfw-outline-none sppcfw-cursor-pointer',
												value: (pageSettings.status || 'published').toLowerCase(),
												onChange: e => handlePageSettingChange('status', e.target.value.toLowerCase()),
											},
											h('option', { value: 'published' }, 'Published'),
											h('option', { value: 'draft' }, 'Draft'),
											h('option', { value: 'private' }, 'Private'),
											h('option', { value: 'pending review' }, 'Pending Review')
										)
									),

									// Preview Product Data Field
									h(
										'div',
										{ className: 'sppcfw-space-y-1.5' },
										h('label', { className: 'sppcfw-font-semibold sppcfw-text-gray-200 sppcfw-block' }, 'Preview Product Data'),
										h(SearchableProductSelect, {
											products,
											selectedProductId,
											onChange: handleProductChange,
										})
									),

									// Page Layout Field
									h(
										'div',
										{ className: 'sppcfw-space-y-1.5 sppcfw-pt-1' },
										h('label', { className: 'sppcfw-font-semibold sppcfw-text-gray-200 sppcfw-block' }, 'Page Layout'),
										h(
											'select',
											{
												className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] focus:sppcfw-border-[#9333ea] sppcfw-rounded sppcfw-px-3 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-white focus:sppcfw-outline-none sppcfw-cursor-pointer',
												value: pageSettings.pageLayout || 'Default',
												onChange: e => handlePageSettingChange('pageLayout', e.target.value),
											},
											h('option', { value: 'Default' }, 'Default'),
											h('option', { value: 'Canvas' }, 'Canvas'),
											h('option', { value: 'Full Width' }, 'Full Width')
										),
										h('p', { className: 'sppcfw-text-[11px] sppcfw-text-gray-400 sppcfw-italic font-sans' }, 'The default page template as defined in Panel → Hamburger Menu → Site Settings.')
									)
								)
						)
					),

				// TAB 2: Style
				activeTab === 'style' &&
					h(
						'div',
						{ className: 'sppcfw-space-y-4' },
						h('h3', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white sppcfw-uppercase sppcfw-tracking-wider sppcfw-mb-2' }, 'Page Background & Style'),
						h(
							'div',
							{ className: 'sppcfw-space-y-1.5' },
							h('label', { className: 'sppcfw-font-semibold sppcfw-text-gray-200 sppcfw-block' }, 'Background Color'),
							h('input', {
								type: 'color',
								className: 'sppcfw-w-full sppcfw-h-8 sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-cursor-pointer',
								value: pageSettings.bgColor || '#091421',
								onChange: e => handlePageSettingChange('bgColor', e.target.value),
							})
						)
					),

				// TAB 3: Advanced
				activeTab === 'advanced' &&
					h(
						'div',
						{ className: 'sppcfw-space-y-4' },
						h('h3', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white sppcfw-uppercase sppcfw-tracking-wider sppcfw-mb-2' }, 'Custom CSS & Scripts'),
						h(
							'div',
							{ className: 'sppcfw-space-y-1.5' },
							h('label', { className: 'sppcfw-font-semibold sppcfw-text-gray-200 sppcfw-block' }, 'Custom CSS'),
							h('textarea', {
								className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-p-2 sppcfw-text-xs font-mono sppcfw-text-white sppcfw-h-32 focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
								placeholder: '/* Add custom CSS for this page template */',
								value: pageSettings.customCss || '',
								onChange: e => handlePageSettingChange('customCss', e.target.value),
							})
						)
					)
			)
		);
	}

	// 3a. Left Elements Panel Component (Search Box, Layout Section, Widgets)
	function LeftElementsPanel({
		activeSubTab,
		setActiveSubTab,
		searchQuery,
		setSearchQuery,
		products,
		selectedProductId,
		handleProductChange,
		productData,
		addWidgetToTarget,
		addContainerPreset,
		elements,
	}) {
		const [isLayoutOpen, setIsLayoutOpen] = useState(true);

		function handleDragStart(e, widgetType, name, metaKey) {
			e.dataTransfer.setData('application/json', JSON.stringify({ type: widgetType, name: name, metaKey: metaKey || null }));
		}

		// Filter elements based on Search Query
		const query = (searchQuery || '').trim().toLowerCase();

		const filteredCore = CORE_WIDGETS.filter(w => !query || w.name.toLowerCase().includes(query) || w.type.toLowerCase().includes(query));

		const filteredMetaGroups = productData && productData.meta_groups
			? productData.meta_groups.map(group => ({
					...group,
					items: group.items.filter(m => !query || m.label.toLowerCase().includes(query) || m.key.toLowerCase().includes(query)),
			  })).filter(g => g.items.length > 0)
			: [];

		return h(
			'aside',
			{ className: 'sppcfw-w-[340px] sppcfw-bg-[#1f2937] sppcfw-border-r sppcfw-border-[#374151] sppcfw-flex sppcfw-flex-col sppcfw-ml-[64px] sppcfw-z-30 sppcfw-h-full sppcfw-overflow-hidden sppcfw-shadow-md sppcfw-select-none' },

			// Header Title "Elements" + Preview Product Data
			h(
				'div',
				{ className: 'sppcfw-p-3 sppcfw-border-b sppcfw-border-[#374151] sppcfw-bg-[#121c2a] sppcfw-space-y-2' },
				h('div', { className: 'sppcfw-flex sppcfw-justify-between sppcfw-items-center' }, h('h2', { className: 'sppcfw-text-sm sppcfw-font-extrabold sppcfw-text-[#d9e3f6] sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' }, h('span', { className: 'material-symbols-outlined sppcfw-text-base sppcfw-text-[#9333ea]' }, 'widgets'), 'Elements'), h('span', { className: 'sppcfw-text-[10px] sppcfw-text-[#9ca3af] font-mono' }, `${elements.length} items on canvas`)),

				h('label', { className: 'inspector-label sppcfw-text-[10px]' }, 'Preview Product Data'),
				h(SearchableProductSelect, {
					products,
					selectedProductId,
					onChange: handleProductChange,
				})
			),

			// Sub-tabs Bar: Widgets | Components | Globals
			h(
				'div',
				{ className: 'sppcfw-flex sppcfw-border-b sppcfw-border-[#374151] sppcfw-bg-[#16202e] sppcfw-text-xs sppcfw-font-semibold' },
				h(
					'button',
					{
						className: `sppcfw-flex-1 sppcfw-py-2 sppcfw-text-center sppcfw-border-b-2 sppcfw-transition-colors ${
							activeSubTab === 'widgets' ? 'sppcfw-border-[#9333ea] sppcfw-text-[#ddb8ff] sppcfw-bg-[#1f2937]' : 'sppcfw-border-transparent sppcfw-text-[#9ca3af] hover:sppcfw-text-[#d9e3f6]'
						}`,
						onClick: () => setActiveSubTab('widgets'),
					},
					'Widgets'
				),
				h(
					'button',
					{
						className: `sppcfw-flex-1 sppcfw-py-2 sppcfw-text-center sppcfw-border-b-2 sppcfw-transition-colors ${
							activeSubTab === 'components' ? 'sppcfw-border-[#9333ea] sppcfw-text-[#ddb8ff] sppcfw-bg-[#1f2937]' : 'sppcfw-border-transparent sppcfw-text-[#9ca3af] hover:sppcfw-text-[#d9e3f6]'
						}`,
						onClick: () => setActiveSubTab('components'),
					},
					'Components'
				),
				h(
					'button',
					{
						className: `sppcfw-flex-1 sppcfw-py-2 sppcfw-text-center sppcfw-border-b-2 sppcfw-transition-colors ${
							activeSubTab === 'globals' ? 'sppcfw-border-[#9333ea] sppcfw-text-[#ddb8ff] sppcfw-bg-[#1f2937]' : 'sppcfw-border-transparent sppcfw-text-[#9ca3af] hover:sppcfw-text-[#d9e3f6]'
						}`,
						onClick: () => setActiveSubTab('globals'),
					},
					'Globals'
				)
			),

			// Search Widget Input
			h(
				'div',
				{ className: 'sppcfw-p-3 sppcfw-border-b sppcfw-border-[#374151] sppcfw-bg-[#111827]' },
				h(
					'div',
					{ className: 'sppcfw-relative sppcfw-flex sppcfw-items-center' },
					h('span', { className: 'material-symbols-outlined sppcfw-text-base sppcfw-absolute sppcfw-right-2.5 sppcfw-text-[#9ca3af]' }, 'search'),
					h('input', {
						type: 'text',
						className: 'sppcfw-w-full sppcfw-bg-[#091421] sppcfw-border sppcfw-border-[#374151] focus:sppcfw-border-[#9333ea] sppcfw-rounded sppcfw-pl-8 sppcfw-pr-7 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-[#d9e3f6] sppcfw-placeholder-[#6b7280] focus:sppcfw-outline-none',
						placeholder: 'Search Widget...',
						value: searchQuery,
						onChange: e => setSearchQuery(e.target.value),
					}),
					searchQuery &&
						h(
							'button',
							{
								className: 'sppcfw-absolute sppcfw-right-2 sppcfw-text-[#9ca3af] hover:sppcfw-text-white sppcfw-text-xs sppcfw-font-bold',
								onClick: () => setSearchQuery(''),
							},
							'✕'
						)
				)
			),

			// Scrollable Elements List
			h(
				'div',
				{ className: 'sppcfw-p-3 sppcfw-overflow-y-auto custom-scrollbar sppcfw-flex-1 sppcfw-space-y-4' },

				// Image 1: Layout Section (Container & Grid Cards)
				h(
					'div',
					{ className: 'sppcfw-border-b sppcfw-border-[#374151] sppcfw-pb-4' },
					h(
						'div',
						{
							className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-cursor-pointer sppcfw-mb-2 sppcfw-select-none',
							onClick: () => setIsLayoutOpen(!isLayoutOpen),
						},
						h('h3', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-[#d9e3f6] sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' }, h('span', { className: 'sppcfw-text-[10px]' }, isLayoutOpen ? '▼' : '▶'), 'Layout')
					),

					isLayoutOpen &&
						h(
							'div',
							{ className: 'sppcfw-grid sppcfw-grid-cols-2 sppcfw-gap-2.5 sppcfw-mt-2' },

							// 1. Container Card
							h(
								'div',
								{
									draggable: true,
									onDragStart: e => handleDragStart(e, 'flex_col', 'Container'),
									onClick: () => addContainerPreset('flex_col'),
									className: 'sppcfw-bg-[#181d24] sppcfw-border sppcfw-border-[#2d3748] hover:sppcfw-border-[#9333ea] hover:sppcfw-bg-[#202732] sppcfw-rounded-md sppcfw-p-4 sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-justify-center sppcfw-gap-2 sppcfw-cursor-pointer sppcfw-transition-all sppcfw-tab-group sppcfw-select-none sppcfw-relative sppcfw-shadow-sm',
								},
								h(
									'div',
									{ className: 'sppcfw-w-10 sppcfw-h-7 sppcfw-border-2 sppcfw-border-dashed sppcfw-border-[#9ca3af] group-hover:sppcfw-border-[#ddb8ff] sppcfw-rounded-sm sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-transition-colors' },
									h('div', { className: 'sppcfw-w-5 sppcfw-h-3 sppcfw-border sppcfw-border-dashed sppcfw-border-[#6b7280] group-hover:sppcfw-border-[#c084fc] sppcfw-rounded-[1px]' })
								),
								h('span', { className: 'sppcfw-text-xs sppcfw-font-medium sppcfw-text-center sppcfw-text-[#d9e3f6] group-hover:sppcfw-text-white' }, 'Container')
							),

							// 2. Grid Card
							h(
								'div',
								{
									draggable: true,
									onDragStart: e => handleDragStart(e, 'grid_2x2', 'Grid'),
									onClick: () => addContainerPreset('grid_2x2'),
									className: 'sppcfw-bg-[#181d24] sppcfw-border sppcfw-border-[#2d3748] hover:sppcfw-border-[#9333ea] hover:sppcfw-bg-[#202732] sppcfw-rounded-md sppcfw-p-4 sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-justify-center sppcfw-gap-2 sppcfw-cursor-pointer sppcfw-transition-all sppcfw-tab-group sppcfw-select-none sppcfw-relative sppcfw-shadow-sm',
								},
								h(
									'div',
									{ className: 'sppcfw-w-10 sppcfw-h-7 sppcfw-border sppcfw-border-[#4b5563] group-hover:sppcfw-border-[#ddb8ff] sppcfw-grid sppcfw-grid-cols-2 sppcfw-gap-0.5 sppcfw-p-0.5 sppcfw-rounded-sm sppcfw-transition-colors' },
									h('div', { className: 'sppcfw-bg-[#6b7280] group-hover:sppcfw-bg-[#c084fc] sppcfw-rounded-[1px]' }),
									h('div', { className: 'sppcfw-bg-[#6b7280] group-hover:sppcfw-bg-[#c084fc] sppcfw-rounded-[1px]' }),
									h('div', { className: 'sppcfw-bg-[#6b7280] group-hover:sppcfw-bg-[#c084fc] sppcfw-rounded-[1px]' }),
									h('div', { className: 'sppcfw-bg-[#6b7280] group-hover:sppcfw-bg-[#c084fc] sppcfw-rounded-[1px]' })
								),
								h('span', { className: 'sppcfw-text-xs sppcfw-font-medium sppcfw-text-center sppcfw-text-[#d9e3f6] group-hover:sppcfw-text-white' }, 'Grid')
							)
						)
				),

				// Single Product Widgets Category
				filteredCore.length > 0 &&
					h(
						'div',
						null,
						h('h3', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-[#cfc2d7] sppcfw-uppercase sppcfw-tracking-wider sppcfw-mb-2' }, 'Single Product Widgets'),
						h(
							'div',
							{ className: 'sppcfw-grid sppcfw-grid-cols-2 sppcfw-gap-2 sppcfw-mb-4' },
							filteredCore.map(w =>
								h(
									'div',
									{
										key: w.type,
										draggable: true,
										onDragStart: e => handleDragStart(e, w.type, w.name),
										onClick: () => addWidgetToTarget(w.type, w.name),
										className: 'sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-p-2.5 sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-gap-1.5 sppcfw-cursor-grab active:sppcfw-cursor-grabbing hover:sppcfw-border-[#9333ea] hover:sppcfw-bg-[#16202e] sppcfw-transition-colors sppcfw-tab-group sppcfw-select-none',
									},
									h('span', { className: 'material-symbols-outlined sppcfw-text-[#ddb8ff] group-hover:sppcfw-scale-110 sppcfw-transition-transform sppcfw-text-lg' }, w.icon),
									h('span', { className: 'sppcfw-text-[11px] sppcfw-font-semibold sppcfw-text-center' }, w.name)
								)
							)
						)
					),

				// Categorized Product Metadata Widgets
				filteredMetaGroups.length > 0 &&
					filteredMetaGroups.map((group, gIdx) =>
						h(
							'div',
							{ key: 'group-' + gIdx, className: 'sppcfw-mb-4 sppcfw-border-t sppcfw-border-[#374151] sppcfw-pt-3' },
							h('h3', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-[#ddb8ff] sppcfw-uppercase sppcfw-tracking-wider sppcfw-mb-2 sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' }, h('span', { className: 'material-symbols-outlined sppcfw-text-sm sppcfw-text-[#92ccff]' }, 'dataset'), group.title),
							h(
								'div',
								{ className: 'sppcfw-flex sppcfw-flex-col sppcfw-gap-1.5' },
								group.items.map(m =>
									h(
										'div',
										{
											key: m.key,
											draggable: true,
											onDragStart: e => handleDragStart(e, 'product_meta_item', m.label, m.key),
											onClick: () => addWidgetToTarget('product_meta_item', m.label, m.key),
											className: 'sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-p-2.5 sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-cursor-grab active:sppcfw-cursor-grabbing hover:sppcfw-border-[#9333ea] sppcfw-transition-colors sppcfw-tab-group',
										},
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2 sppcfw-overflow-hidden' },
											h('span', { className: 'material-symbols-outlined sppcfw-text-xs sppcfw-text-[#92ccff]' }, 'data_object'),
											h('div', { className: 'sppcfw-overflow-hidden' }, h('div', { className: 'sppcfw-text-xs sppcfw-font-medium sppcfw-truncate' }, m.label), h('div', { className: 'sppcfw-text-[10px] sppcfw-text-[#9ca3af] sppcfw-truncate font-mono' }, typeof m.value === 'object' ? JSON.stringify(m.value) : String(m.value || '')))
										),
										h('span', { className: 'sppcfw-text-[9px] sppcfw-bg-[#212b39] sppcfw-text-[#cfc2d7] sppcfw-px-1.5 sppcfw-py-0.5 sppcfw-rounded font-mono' }, 'Meta')
									)
								)
							)
						)
					)
			)
		);
	}

	// 3b. Left Inspector Component ("Edit Container" / "Edit Element")
	function LeftInspector({ deviceView = 'desktop', selectedElement, updateElementProperties, closeInspector, categories, products, sampleData, addColumnToContainer, duplicateColumn, removeElement }) {
		const [activeTab, setActiveTab] = useState('layout'); // 'layout'/'content' | 'style' | 'advanced'
		const [isContainerOpen, setIsContainerOpen] = useState(true);
		const [isItemsOpen, setIsItemsOpen] = useState(true);
		const [isImageOpen, setIsImageOpen] = useState(true);
		const [isLayoutAdvOpen, setIsLayoutAdvOpen] = useState(true);
		const [isBgOpen, setIsBgOpen] = useState(true);
		const [isOverlayOpen, setIsOverlayOpen] = useState(false);
		const [isBorderOpen, setIsBorderOpen] = useState(false);
		const [bgMode, setBgMode] = useState('normal'); // 'normal' | 'hover'
		const [bgType, setBgType] = useState('classic'); // 'classic' | 'gradient' | 'video' | 'image'
		const [styleMode, setStyleMode] = useState('normal'); // 'normal' | 'hover'
		const [scrollEffects, setScrollEffects] = useState(false);
		const [mouseEffects, setMouseEffects] = useState(false);
		const [widthUnit, setWidthUnit] = useState('px');
		const [heightUnit, setHeightUnit] = useState('px');
		const [imgWidthUnit, setImgWidthUnit] = useState('%');
		const [maxImgWidthUnit, setMaxImgWidthUnit] = useState('%');
		const [imgHeightUnit, setImgHeightUnit] = useState('px');
		const [radiusUnit, setRadiusUnit] = useState('px');
		const [marginUnit, setMarginUnit] = useState('px');
		const [paddingUnit, setPaddingUnit] = useState('px');
		const [gapsUnit, setGapsUnit] = useState('px');
		const [isGapsLinked, setIsGapsLinked] = useState(true);
		const [isRadiusLinked, setIsRadiusLinked] = useState(true);
		const [isMarginLinked, setIsMarginLinked] = useState(true);
		const [isPaddingLinked, setIsPaddingLinked] = useState(true);

		function getSetting(key) {
			return getResponsiveProp(selectedElement.settings, key, deviceView);
		}

		function getStyle(key) {
			return getResponsiveProp(selectedElement.styles, key, deviceView);
		}

		function getAdvanced(key) {
			return getResponsiveProp(selectedElement.advanced, key, deviceView);
		}

		function handleSettingChange(key, value) {
			const targetKey = getDeviceKey(key, deviceView);
			const updated = {
				...selectedElement,
				settings: { ...selectedElement.settings, [targetKey]: value },
			};
			updateElementProperties(updated);
		}

		function handleStyleChange(key, value) {
			const targetKey = getDeviceKey(key, deviceView);
			const updated = {
				...selectedElement,
				styles: { ...selectedElement.styles, [targetKey]: value },
			};
			updateElementProperties(updated);
		}

		function handleMultiStyleChange(keyValues) {
			const targetKeyValues = {};
			Object.keys(keyValues).forEach(k => {
				const tk = getDeviceKey(k, deviceView);
				targetKeyValues[tk] = keyValues[k];
			});
			const updated = {
				...selectedElement,
				styles: { ...selectedElement.styles, ...targetKeyValues },
			};
			updateElementProperties(updated);
		}

		function handleAdvancedChange(key, value) {
			const targetKey = getDeviceKey(key, deviceView);
			const updated = {
				...selectedElement,
				advanced: { ...selectedElement.advanced, [targetKey]: value },
			};
			updateElementProperties(updated);
		}

		function handleMultiAdvancedChange(keyValues) {
			const targetKeyValues = {};
			Object.keys(keyValues).forEach(k => {
				const tk = getDeviceKey(k, deviceView);
				targetKeyValues[tk] = keyValues[k];
			});
			const updated = {
				...selectedElement,
				advanced: { ...selectedElement.advanced, ...targetKeyValues },
			};
			updateElementProperties(updated);
		}

		const isContainer = selectedElement.type === 'container';
		const isColumn = selectedElement.type === 'column';
		const isImage = selectedElement.type === 'product_gallery' || selectedElement.type === 'image';
		const panelTitle = isContainer ? 'Edit Container' : isColumn ? 'Edit Column' : isImage ? 'Edit Image' : (selectedElement.label || 'Edit Element');
		const firstTabLabel = (isContainer || isColumn) ? 'Layout' : 'Content';
		const firstTabIcon = (isContainer || isColumn) ? 'view_column' : 'edit';

		function renderControlHeader(label, showDeviceIcon = true, unitValue = null, onUnitChange = null, units = null) {
			return h(
				'div',
				{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-text-xs sppcfw-text-[#e5e7eb] sppcfw-font-medium sppcfw-mb-1.5' },
				h(
					'div',
					{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' },
					label,
					showDeviceIcon && h('span', { className: 'material-symbols-outlined sppcfw-text-[13px] sppcfw-text-gray-400', title: deviceView }, 'desktop_windows')
				),
				units && onUnitChange && h(
					'div',
					{ className: 'sppcfw-relative sppcfw-inline-flex sppcfw-items-center' },
					h(
						'select',
						{
							className: 'sppcfw-select-measurement sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-text-[10px] sppcfw-text-gray-300 sppcfw-rounded sppcfw-px-1.5 sppcfw-py-0.5 sppcfw-pr-4 sppcfw-appearance-none focus:sppcfw-outline-none sppcfw-cursor-pointer',
							value: unitValue || units[0],
							onChange: e => onUnitChange(e.target.value),
						},
						units.map(u => h('option', { key: u, value: u }, u))
					),
				)
			);
		}

		function renderSliderInput(valStr, onChange, min = 0, max = 100, step = 1, unit = 'px', placeholder = '') {
			const rawNum = valStr !== '' && valStr !== undefined ? parseFloat(valStr) : '';
			const numVal = typeof rawNum === 'number' && !isNaN(rawNum) ? rawNum : '';
			return h(
				'div',
				{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2.5' },
				h('input', {
					type: 'range',
					min: min,
					max: max,
					step: step,
					className: 'sppcfw-flex-1 sppcfw-accent-white sppcfw-bg-[#374151] sppcfw-h-1.5 sppcfw-rounded-full sppcfw-cursor-pointer',
					value: numVal !== '' ? numVal : min,
					onChange: e => onChange(e.target.value ? e.target.value + unit : ''),
				}),
				h('input', {
					type: 'number',
					min: min,
					max: max,
					step: step,
					placeholder: placeholder,
					className: 'sppcfw-w-16 sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2 sppcfw-py-1 sppcfw-text-xs sppcfw-text-center sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
					value: numVal !== '' ? numVal : '',
					onChange: e => onChange(e.target.value !== '' ? e.target.value + unit : ''),
				})
			);
		}

		function renderFourBoxInput(sourceObj, prefix, onChangeFour, unit = 'px', isLinked, setIsLinked) {
			const sides = ['top', 'right', 'bottom', 'left'];

			function handleSingleChange(side, rawVal) {
				const val = rawVal !== '' ? rawVal + unit : '';
				if (isLinked) {
					onChangeFour({
						[`${prefix}_top`]: val,
						[`${prefix}_right`]: val,
						[`${prefix}_bottom`]: val,
						[`${prefix}_left`]: val,
					});
				} else {
					onChangeFour({ [`${prefix}_${side}`]: val });
				}
			}

			return h(
				'div',
				{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' },
				h(
					'ul',
					{ className: 'sppcfw-grid sppcfw-grid-cols-5 sppcfw-flex-1' },
					sides.map(side => {
						const sideVal = (sourceObj && sourceObj[`${prefix}_${side}`]) || '';
						const num = sideVal !== '' ? parseFloat(sideVal) : '';
						return h(
							'li',
							{ key: side, className: 'sppcfw-flex sppcfw-flex-col sppcfw-items-center' },
							h('input', {
								type: 'number',
								className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-1.5 sppcfw-py-1 sppcfw-text-xs sppcfw-text-center sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
								value: num,
								onChange: e => handleSingleChange(side, e.target.value),
							}),
							h('span', { className: 'sppcfw-text-[9px] sppcfw-text-gray-400 sppcfw-capitalize sppcfw-mt-0.5' }, side),
						);
					}),
					h(
						'button',
						{
							type: 'button',
							className: `sppcfw-h-10 sppcfw-p-2 sppcfw-rounded sppcfw-border sppcfw-transition-colors sppcfw-cursor-pointer ${
								isLinked ? 'sppcfw-bg-[#9333ea]/30 sppcfw-border-[#9333ea] sppcfw-text-purple-200' : 'sppcfw-bg-[#111827] sppcfw-border-[#374151] sppcfw-text-gray-400 hover:sppcfw-text-white'
							}`,
							onClick: () => setIsLinked(!isLinked),
							title: isLinked ? 'Unlink values' : 'Link values',
						},
						h('span', { className: 'material-symbols-outlined sppcfw-text-xs' }, isLinked ? 'link' : 'link_off')
					)
				),
			);
		}

		function renderButtonGroup(options, currentVal, onChange) {
			return h(
				'div',
				{ className: 'sppcfw-flex sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-bg-[#111827] sppcfw-overflow-hidden' },
				options.map(opt => {
					const isActive = currentVal === opt.value;
					return h(
						'button',
						{
							key: opt.value,
							type: 'button',
							className: `sppcfw-flex-1 sppcfw-py-1.5 sppcfw-px-2 sppcfw-text-xs sppcfw-font-semibold sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-gap-1 sppcfw-transition-colors sppcfw-cursor-pointer ${
								isActive ? 'sppcfw-bg-[#374151] sppcfw-text-white sppcfw-font-bold' : 'sppcfw-text-gray-400 hover:sppcfw-text-white hover:sppcfw-bg-[#1f2937]'
							}`,
							onClick: () => onChange(opt.value),
							title: opt.title || opt.label,
						},
						opt.icon ? h('span', { className: 'material-symbols-outlined sppcfw-text-sm' }, opt.icon) : opt.label
					);
				})
			);
		}

		return h(
			'aside',
			{ className: 'sppcfw-w-[340px] sppcfw-bg-[#1f2937] sppcfw-border-r sppcfw-border-[#374151] sppcfw-flex sppcfw-flex-col sppcfw-ml-[64px] sppcfw-z-30 sppcfw-h-full sppcfw-overflow-hidden sppcfw-shadow-xl sppcfw-select-none' },

			// Inspector Header: "Edit Container" (Images 2 & 3 Header)
			h(
				'div',
				{ className: 'sppcfw-border-b sppcfw-border-[#374151] sppcfw-bg-[#121c2a]' },
				h(
					'div',
					{ className: 'sppcfw-p-3 sppcfw-flex sppcfw-justify-between sppcfw-items-center sppcfw-border-b sppcfw-border-[#212b39]' },
					h(
						'div',
						{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2 sppcfw-overflow-hidden' },
						h(
							'button',
							{
								className: 'sppcfw-text-[#9ca3af] hover:sppcfw-text-white sppcfw-p-1 sppcfw-rounded hover:sppcfw-bg-[#212b39] sppcfw-transition-colors',
								onClick: closeInspector,
								title: 'Back to Elements',
							},
							'←'
						),
						h('h2', { className: 'sppcfw-font-bold sppcfw-text-xs sppcfw-text-[#ffffff] sppcfw-truncate sppcfw-max-w-[130px]' }, panelTitle),
						h(
							'span',
							{
								className: `sppcfw-text-[10px] sppcfw-px-2 sppcfw-py-0.5 sppcfw-rounded sppcfw-font-bold sppcfw-uppercase sppcfw-border sppcfw-flex sppcfw-items-center sppcfw-gap-1 ${
									deviceView === 'mobile'
										? 'sppcfw-bg-amber-500/20 sppcfw-text-amber-300 sppcfw-border-amber-500/40'
										: deviceView === 'tablet'
										? 'sppcfw-bg-blue-500/20 sppcfw-text-blue-300 sppcfw-border-blue-500/40'
										: 'sppcfw-bg-purple-500/20 sppcfw-text-purple-300 sppcfw-border-purple-500/40'
								}`,
							},
							deviceView === 'mobile' ? '📱 Mobile' : deviceView === 'tablet' ? '📱 Tablet' : '🖥 Desktop'
						)
					),
					h(
						'button',
						{
							className: 'sppcfw-text-[#9ca3af] hover:sppcfw-text-white sppcfw-font-bold sppcfw-text-sm sppcfw-px-2',
							onClick: closeInspector,
							title: 'Close Inspector',
						},
						'✕'
					)
				),

				// 3 Tabs Header: Layout | Style | Advanced (Images 2 & 3)
				h(
					'div',
					{ className: 'sppcfw-flex sppcfw-text-xs sppcfw-font-semibold sppcfw-bg-[#16202e] sppcfw-border-b sppcfw-border-[#374151]' },
					h(
						'button',
						{
							className: `sppcfw-flex-1 sppcfw-py-2.5 sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-gap-1.5 sppcfw-border-b-2 sppcfw-transition-colors ${
								activeTab === 'layout' || activeTab === 'content' ? 'sppcfw-border-white sppcfw-text-white sppcfw-bg-[#1f2937]' : 'sppcfw-border-transparent sppcfw-text-[#9ca3af] hover:sppcfw-text-[#d9e3f6]'
							}`,
							onClick: () => setActiveTab('layout'),
						},
						h('span', { className: 'material-symbols-outlined sppcfw-text-sm' }, firstTabIcon),
						firstTabLabel
					),
					h(
						'button',
						{
							className: `sppcfw-flex-1 sppcfw-py-2.5 sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-gap-1.5 sppcfw-border-b-2 sppcfw-transition-colors ${
								activeTab === 'style' ? 'sppcfw-border-white sppcfw-text-white sppcfw-bg-[#1f2937]' : 'sppcfw-border-transparent sppcfw-text-[#9ca3af] hover:sppcfw-text-[#d9e3f6]'
							}`,
							onClick: () => setActiveTab('style'),
						},
						h('span', { className: 'material-symbols-outlined sppcfw-text-sm' }, 'contrast'),
						'Style'
					),
					h(
						'button',
						{
							className: `sppcfw-flex-1 sppcfw-py-2.5 sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-gap-1.5 sppcfw-border-b-2 sppcfw-transition-colors ${
								activeTab === 'advanced' ? 'sppcfw-border-white sppcfw-text-white sppcfw-bg-[#1f2937]' : 'sppcfw-border-transparent sppcfw-text-[#9ca3af] hover:sppcfw-text-[#d9e3f6]'
							}`,
							onClick: () => setActiveTab('advanced'),
						},
						h('span', { className: 'material-symbols-outlined sppcfw-text-sm' }, 'settings'),
						'Advanced'
					)
				)
			),

			// Inspector Body Content
			h(
				'div',
				{ className: 'sppcfw-p-4 sppcfw-overflow-y-auto custom-scrollbar sppcfw-space-y-4 sppcfw-flex-1 sppcfw-text-xs' },

				// --- 1. CONTENT / LAYOUT TAB ---
				(activeTab === 'layout' || activeTab === 'content') &&
					h(
						'div',
						{ className: 'sppcfw-space-y-4' },

						// Accordion: Container (Matching reference image)
						isContainer &&
							h(
								'div',
								{ className: 'sppcfw-space-y-4' },

								// Accordion Header
								h(
									'div',
									{
										className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-cursor-pointer sppcfw-select-none sppcfw-pb-1',
										onClick: () => setIsContainerOpen(!isContainerOpen),
									},
									h(
										'h4',
										{ className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' },
										h('span', { className: 'sppcfw-text-[10px] sppcfw-text-gray-300' }, isContainerOpen ? '▾' : '▸'),
										'Container'
									)
								),

								isContainerOpen &&
									h(
										'div',
										{ className: 'sppcfw-space-y-4' },

										// 1. Container Layout (Flexbox / Grid)
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
											h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db] sppcfw-font-medium' }, 'Container Layout'),
											h(
												'div',
												{ className: 'sppcfw-relative sppcfw-w-44' },
												h(
													'select',
													{
														className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-pr-6 sppcfw-text-xs sppcfw-text-white sppcfw-appearance-none focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea] sppcfw-cursor-pointer',
														value: getSetting('flex_direction') === 'grid' ? 'Grid' : 'Flexbox',
														onChange: e => handleSettingChange('flex_direction', e.target.value === 'Grid' ? 'grid' : 'row'),
													},
													h('option', { value: 'Flexbox' }, 'Flexbox'),
													h('option', { value: 'Grid' }, 'Grid')
												),
											)
										),

										// Separator Divider
										h('hr', { className: 'sppcfw-border-[#374151]/60' }),

										// 2. Content Width (Boxed / Full Width)
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
											h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db] sppcfw-font-medium' }, 'Content Width'),
											h(
												'div',
												{ className: 'sppcfw-relative sppcfw-w-44' },
												h(
													'select',
													{
														className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-pr-6 sppcfw-text-xs sppcfw-text-white sppcfw-appearance-none focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea] sppcfw-cursor-pointer',
														value: getSetting('width_mode') === 'full' ? 'Full Width' : 'Boxed',
														onChange: e => handleSettingChange('width_mode', e.target.value === 'Full Width' ? 'full' : 'boxed'),
													},
													h('option', { value: 'Boxed' }, 'Boxed'),
													h('option', { value: 'Full Width' }, 'Full Width')
												),
											)
										),

										// 3. Width Control
										h(
											'div',
											{ className: 'sppcfw-space-y-1.5' },
											renderControlHeader('Width', true, widthUnit, setWidthUnit, ['px', '%', 'vw']),
											renderSliderInput(getSetting('boxed_width') || '1140px', v => handleSettingChange('boxed_width', v), 100, 2000, 1, widthUnit, '1140')
										),

										// 4. Min Height Control
										h(
											'div',
											{ className: 'sppcfw-space-y-1.5' },
											renderControlHeader('Min Height', true, heightUnit, setHeightUnit, ['px', 'vh', 'em']),
											renderSliderInput(getSetting('min_height') || '', v => handleSettingChange('min_height', v), 0, 1000, 1, heightUnit, ''),
											h('p', { className: 'sppcfw-text-[11px] sppcfw-text-gray-400 sppcfw-italic sppcfw-pt-0.5' }, 'To achieve full height Container use 100vh.')
										),

										// Separator Divider
										h('hr', { className: 'sppcfw-border-[#374151]/60' }),

										// 5. Items Section Subtitle
										h('h4', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white sppcfw-pt-1' }, 'Items'),

										// 6. Direction (Row, Col, Row-Reverse, Col-Reverse)
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5 sppcfw-text-xs sppcfw-text-[#e5e7eb] sppcfw-font-medium' },
												'Direction',
												h('span', { className: 'material-symbols-outlined sppcfw-text-[13px] sppcfw-text-gray-400', title: deviceView }, 'desktop_windows')
											),
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-bg-[#111827] sppcfw-overflow-hidden' },
												[
													{ val: 'row', title: 'Row - Horizontal', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' }, h('path', { d: 'M3 8h10M9 4l4 4-4 4' })) },
													{ val: 'column', title: 'Column - Vertical', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' }, h('path', { d: 'M8 3v10M4 9l4 4 4-4' })) },
													{ val: 'row-reverse', title: 'Row Reverse', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' }, h('path', { d: 'M13 8H3M7 4L3 8l4 4' })) },
													{ val: 'column-reverse', title: 'Column Reverse', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' }, h('path', { d: 'M8 13V3M4 7l4-4 4 4' })) },
												].map(dirOpt => {
													const isAct = (getSetting('flex_direction') || 'row') === dirOpt.val;
													return h(
														'button',
														{
															key: dirOpt.val,
															type: 'button',
															className: `sppcfw-p-2 sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-transition-colors sppcfw-cursor-pointer ${
																isAct ? 'sppcfw-bg-[#374151] sppcfw-text-white' : 'sppcfw-text-gray-400 hover:sppcfw-text-white hover:sppcfw-bg-[#1f2937]'
															}`,
															onClick: () => handleSettingChange('flex_direction', dirOpt.val),
															title: dirOpt.title,
														},
														dirOpt.icon
													);
												})
											)
										),

										// 7. Justify Content (Full Width 6 Icons)
										h(
											'div',
											{ className: 'sppcfw-space-y-1.5' },
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5 sppcfw-text-xs sppcfw-text-[#e5e7eb] sppcfw-font-medium' },
												'Justify Content',
												h('span', { className: 'material-symbols-outlined sppcfw-text-[13px] sppcfw-text-gray-400', title: deviceView }, 'desktop_windows')
											),
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-bg-[#111827] sppcfw-overflow-hidden' },
												[
													{ val: 'flex-start', title: 'Start', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'currentColor' }, h('rect', { x: 1.5, y: 1.5, width: 1.5, height: 13, rx: 0.5 }), h('rect', { x: 4.5, y: 4, width: 3, height: 8, rx: 0.5 }), h('rect', { x: 9, y: 4, width: 3, height: 8, rx: 0.5 })) },
													{ val: 'center', title: 'Center', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'currentColor' }, h('rect', { x: 4, y: 4, width: 3.5, height: 8, rx: 0.5 }), h('rect', { x: 8.5, y: 4, width: 3.5, height: 8, rx: 0.5 })) },
													{ val: 'flex-end', title: 'End', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'currentColor' }, h('rect', { x: 13, y: 1.5, width: 1.5, height: 13, rx: 0.5 }), h('rect', { x: 4, y: 4, width: 3, height: 8, rx: 0.5 }), h('rect', { x: 8.5, y: 4, width: 3, height: 8, rx: 0.5 })) },
													{ val: 'space-between', title: 'Space Between', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'currentColor' }, h('rect', { x: 1.5, y: 1.5, width: 1.5, height: 13, rx: 0.5 }), h('rect', { x: 13, y: 1.5, width: 1.5, height: 13, rx: 0.5 }), h('rect', { x: 4.5, y: 4, width: 2.5, height: 8, rx: 0.5 }), h('rect', { x: 9, y: 4, width: 2.5, height: 8, rx: 0.5 })) },
													{ val: 'space-around', title: 'Space Around', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'currentColor' }, h('rect', { x: 2.5, y: 4, width: 3, height: 8, rx: 0.5 }), h('rect', { x: 7, y: 2, width: 1.5, height: 12, rx: 0.5, opacity: 0.35 }), h('rect', { x: 10.5, y: 4, width: 3, height: 8, rx: 0.5 })) },
													{ val: 'space-evenly', title: 'Space Evenly', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'currentColor' }, h('rect', { x: 2, y: 4, width: 2.5, height: 8, rx: 0.5 }), h('rect', { x: 6.75, y: 4, width: 2.5, height: 8, rx: 0.5 }), h('rect', { x: 11.5, y: 4, width: 2.5, height: 8, rx: 0.5 })) },
												].map(jcOpt => {
													const isAct = (getSetting('justify_content') || 'flex-start') === jcOpt.val;
													return h(
														'button',
														{
															key: jcOpt.val,
															type: 'button',
															className: `sppcfw-flex-1 sppcfw-py-2 sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-transition-colors sppcfw-cursor-pointer ${
																isAct ? 'sppcfw-bg-[#374151] sppcfw-text-white' : 'sppcfw-text-gray-400 hover:sppcfw-text-white hover:sppcfw-bg-[#1f2937]'
															}`,
															onClick: () => handleSettingChange('justify_content', jcOpt.val),
															title: jcOpt.title,
														},
														jcOpt.icon
													);
												})
											)
										),

										// 8. Align Items (4 Icons)
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5 sppcfw-text-xs sppcfw-text-[#e5e7eb] sppcfw-font-medium' },
												'Align Items',
												h('span', { className: 'material-symbols-outlined sppcfw-text-[13px] sppcfw-text-gray-400', title: deviceView }, 'desktop_windows')
											),
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-bg-[#111827] sppcfw-overflow-hidden' },
												[
													{ val: 'flex-start', title: 'Start', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'currentColor' }, h('rect', { x: 1.5, y: 1.5, width: 13, height: 1.5, rx: 0.5 }), h('rect', { x: 6, y: 4.5, width: 4, height: 9, rx: 0.5 })) },
													{ val: 'center', title: 'Center', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'currentColor' }, h('rect', { x: 1.5, y: 7.25, width: 13, height: 1.5, rx: 0.5 }), h('rect', { x: 6, y: 2.5, width: 4, height: 11, rx: 0.5 })) },
													{ val: 'flex-end', title: 'End', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'currentColor' }, h('rect', { x: 1.5, y: 13, width: 13, height: 1.5, rx: 0.5 }), h('rect', { x: 6, y: 2.5, width: 4, height: 9, rx: 0.5 })) },
													{ val: 'stretch', title: 'Stretch', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'currentColor' }, h('rect', { x: 1.5, y: 1.5, width: 13, height: 1.5, rx: 0.5 }), h('rect', { x: 1.5, y: 13, width: 13, height: 1.5, rx: 0.5 }), h('rect', { x: 6, y: 4, width: 4, height: 8, rx: 0.5 })) },
												].map(aiOpt => {
													const isAct = (getSetting('align_items') || 'stretch') === aiOpt.val;
													return h(
														'button',
														{
															key: aiOpt.val,
															type: 'button',
															className: `sppcfw-p-2 sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-transition-colors sppcfw-cursor-pointer ${
																isAct ? 'sppcfw-bg-[#374151] sppcfw-text-white' : 'sppcfw-text-gray-400 hover:sppcfw-text-white hover:sppcfw-bg-[#1f2937]'
															}`,
															onClick: () => handleSettingChange('align_items', aiOpt.val),
															title: aiOpt.title,
														},
														aiOpt.icon
													);
												})
											)
										),

										// 9. Gaps (Dual Column & Row inputs with Link button)
										h(
											'div',
											{ className: 'sppcfw-space-y-1.5' },
											renderControlHeader('Gaps', true, gapsUnit, setGapsUnit, ['px', 'em', '%']),
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-items-start sppcfw-gap-1.5' },
												h(
													'div',
													{ className: 'sppcfw-flex-1 sppcfw-grid sppcfw-grid-cols-2 sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-bg-[#111827] sppcfw-overflow-hidden' },
													h(
														'div',
														{ className: 'sppcfw-border-r sppcfw-border-[#374151]' },
														h('input', {
															type: 'number',
															className: 'sppcfw-w-full sppcfw-bg-transparent sppcfw-px-2 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-center sppcfw-text-white focus:sppcfw-outline-none',
															value: parseInt(getSetting('column_gap') || getSetting('gap') || '20', 10),
															onChange: e => {
																const v = e.target.value ? e.target.value + gapsUnit : '0px';
																if (isGapsLinked) {
																	handleSettingChange('gap', v);
																	handleSettingChange('column_gap', v);
																	handleSettingChange('row_gap', v);
																} else {
																	handleSettingChange('column_gap', v);
																}
															},
														})
													),
													h(
														'div',
														null,
														h('input', {
															type: 'number',
															className: 'sppcfw-w-full sppcfw-bg-transparent sppcfw-px-2 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-center sppcfw-text-white focus:sppcfw-outline-none',
															value: parseInt(getSetting('row_gap') || getSetting('gap') || '20', 10),
															onChange: e => {
																const v = e.target.value ? e.target.value + gapsUnit : '0px';
																if (isGapsLinked) {
																	handleSettingChange('gap', v);
																	handleSettingChange('column_gap', v);
																	handleSettingChange('row_gap', v);
																} else {
																	handleSettingChange('row_gap', v);
																}
															},
														})
													)
												),
												h(
													'button',
													{
														type: 'button',
														className: `sppcfw-p-2 sppcfw-rounded sppcfw-border sppcfw-transition-colors sppcfw-cursor-pointer ${
															isGapsLinked
																? 'sppcfw-bg-[#374151] sppcfw-border-[#4b5563] sppcfw-text-white'
																: 'sppcfw-bg-[#111827] sppcfw-border-[#374151] sppcfw-text-gray-400 hover:sppcfw-text-white'
														}`,
														onClick: () => setIsGapsLinked(!isGapsLinked),
														title: isGapsLinked ? 'Unlink values' : 'Link values',
													},
													h('span', { className: 'material-symbols-outlined sppcfw-text-xs' }, isGapsLinked ? 'link' : 'link_off')
												)
											),
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-text-[10px] sppcfw-text-gray-400 sppcfw-pr-9' },
												h('span', { className: 'sppcfw-flex-1 sppcfw-text-center' }, 'Column'),
												h('span', { className: 'sppcfw-flex-1 sppcfw-text-center' }, 'Row')
											)
										),

										// 10. Wrap (No Wrap / Wrap)
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5 sppcfw-text-xs sppcfw-text-[#e5e7eb] sppcfw-font-medium' },
												'Wrap',
												h('span', { className: 'material-symbols-outlined sppcfw-text-[13px] sppcfw-text-gray-400', title: deviceView }, 'desktop_windows')
											),
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-bg-[#111827] sppcfw-overflow-hidden' },
												[
													{ val: 'nowrap', title: 'No Wrap', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'none', stroke: 'currentColor', strokeWidth: 1.75, strokeLinecap: 'round', strokeLinejoin: 'round' }, h('path', { d: 'M2 3v10M5 8h8M9.5 4.5L13 8l-3.5 3.5' })) },
													{ val: 'wrap', title: 'Wrap', icon: h('svg', { width: 14, height: 14, viewBox: '0 0 16 16', fill: 'none', stroke: 'currentColor', strokeWidth: 1.75, strokeLinecap: 'round', strokeLinejoin: 'round' }, h('path', { d: 'M3 5.5h7a3 3 0 0 1 3 3v0a3 3 0 0 1-3 3H4M7 8.5L4 11.5l3 3' })) },
												].map(wrapOpt => {
													const isAct = (getSetting('flex_wrap') || 'nowrap') === wrapOpt.val;
													return h(
														'button',
														{
															key: wrapOpt.val,
															type: 'button',
															className: `sppcfw-p-2 sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-transition-colors sppcfw-cursor-pointer ${
																isAct ? 'sppcfw-bg-[#374151] sppcfw-text-white' : 'sppcfw-text-gray-400 hover:sppcfw-text-white hover:sppcfw-bg-[#1f2937]'
															}`,
															onClick: () => handleSettingChange('flex_wrap', wrapOpt.val),
															title: wrapOpt.title,
														},
														wrapOpt.icon
													);
												})
											)
										)
									)
							),

						// COLUMN LAYOUT CONTROLS (WHEN A COLUMN IS SELECTED)
						isColumn &&
							h(
								'div',
								{ className: 'sppcfw-space-y-4' },

								// Column Structure Accordion
								h(
									'div',
									{ className: 'sppcfw-border-b sppcfw-border-[#374151] sppcfw-pb-4 sppcfw-space-y-3' },
									h(
										'div',
										{
											className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-cursor-pointer sppcfw-select-none',
											onClick: () => setIsContainerOpen(!isContainerOpen),
										},
										h('h4', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' }, h('span', { className: 'sppcfw-text-[10px]' }, isContainerOpen ? '▼' : '▶'), 'Column Width & Size')
									),

									isContainerOpen &&
										h(
											'div',
											{ className: 'sppcfw-space-y-3.5 sppcfw-pt-1' },

											// Preset Width Buttons (100%, 50%, 33%, 25%, 67%, 75%)
											h(
												'div',
												{ className: 'sppcfw-space-y-1.5' },
												h('label', { className: 'sppcfw-text-xs sppcfw-text-gray-200 sppcfw-font-medium sppcfw-block' }, 'Quick Width Presets'),
												h(
													'div',
													{ className: 'sppcfw-grid sppcfw-grid-cols-4 sppcfw-gap-1.5' },
													['100%', '50%', '33.33%', '25%', '66.66%', '75%', '20%'].map(w =>
														h(
															'button',
															{
																key: w,
																className: `sppcfw-py-1 sppcfw-text-[11px] sppcfw-font-semibold sppcfw-rounded sppcfw-border sppcfw-transition-all ${
																	(getSetting('sppcfw-flex_width') || 'sppcfw-100%') === w
																		? 'sppcfw-bg-[#9333ea] sppcfw-border-[#9333ea] sppcfw-text-white sppcfw-shadow'
																		: 'sppcfw-bg-[#111827] sppcfw-border-[#374151] sppcfw-text-gray-300 hover:sppcfw-border-[#9333ea] hover:sppcfw-text-white'
																}`,
																onClick: () => {
																	handleSettingChange('flex_width', w);
																	const targetKey = getDeviceKey('flex_width', deviceView);
																	updateElementProperties({ ...selectedElement, label: 'Column (' + w + ')', settings: { ...selectedElement.settings, [targetKey]: w } });
																},
															},
															w
														)
													)
												)
											),

											// Width Slider & Input
											h(
												'div',
												{ className: 'sppcfw-space-y-1.5' },
												h(
													'div',
													{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
													h('div', { className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5 sppcfw-text-gray-200 sppcfw-font-medium' }, 'Flex Width'),
													h('span', { className: 'sppcfw-text-[10px] sppcfw-text-gray-300 font-mono' }, getSetting('flex_width') || '100%')
												),
												h(
													'div',
													{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-3' },
													h('input', {
														type: 'range',
														min: 5,
														max: 100,
														step: 1,
														className: 'sppcfw-flex-1 sppcfw-accent-[#9333ea] sppcfw-bg-[#111827] sppcfw-h-1.5 sppcfw-rounded sppcfw-cursor-pointer',
														value: parseFloat(getSetting('flex_width') || '100') || 100,
														onChange: e => {
															const val = e.target.value + '%';
															handleSettingChange('flex_width', val);
															const targetKey = getDeviceKey('flex_width', deviceView);
															updateElementProperties({ ...selectedElement, label: 'Column (' + val + ')', settings: { ...selectedElement.settings, [targetKey]: val } });
														},
													}),
													h('input', {
														type: 'number',
														min: 5,
														max: 100,
														className: 'sppcfw-w-16 sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2 sppcfw-py-1 sppcfw-text-xs sppcfw-text-center sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
														value: parseFloat(getSetting('flex_width') || '100') || 100,
														onChange: e => {
															const val = (e.target.value || 100) + '%';
															handleSettingChange('flex_width', val);
															const targetKey = getDeviceKey('flex_width', deviceView);
															updateElementProperties({ ...selectedElement, label: 'Column (' + val + ')', settings: { ...selectedElement.settings, [targetKey]: val } });
														},
													})
												)
											),

											// Min Height Control
											h(
												'div',
												{ className: 'sppcfw-space-y-1.5' },
												h(
													'div',
													{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
													h('div', { className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5 sppcfw-text-gray-200 sppcfw-font-medium' }, 'Min Height'),
													h(
														'select',
														{
															className: 'sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-text-[10px] sppcfw-text-gray-300 sppcfw-px-1 sppcfw-py-0.5 sppcfw-rounded focus:sppcfw-outline-none',
															value: heightUnit,
															onChange: e => setHeightUnit(e.target.value),
														},
														h('option', { value: 'px' }, 'px'),
														h('option', { value: 'vh' }, 'vh')
													)
												),
												h(
													'div',
													{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-3' },
													h('input', {
														type: 'range',
														min: 0,
														max: 800,
														className: 'sppcfw-flex-1 sppcfw-accent-[#9333ea] sppcfw-bg-[#111827] sppcfw-h-1.5 sppcfw-rounded sppcfw-cursor-pointer',
														value: parseInt(getSetting('min_height') || '0', 10) || 0,
														onChange: e => handleSettingChange('min_height', e.target.value + heightUnit),
													}),
													h('input', {
														type: 'number',
														className: 'sppcfw-w-16 sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2 sppcfw-py-1 sppcfw-text-xs sppcfw-text-center sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
														value: parseInt(getSetting('min_height') || '0', 10) || '',
														onChange: e => handleSettingChange('min_height', e.target.value + heightUnit),
													})
												)
											)
										)
								),

								// Column Content Layout (Items inside Column)
								h(
									'div',
									{ className: 'sppcfw-border-b sppcfw-border-[#374151] sppcfw-pb-4 sppcfw-space-y-3.5' },
									h('h4', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white' }, 'Column Items Layout'),

									// Direction
									h(
										'div',
										{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
										h('div', { className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5 sppcfw-text-gray-200 sppcfw-font-medium' }, 'Direction'),
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-bg-[#111827] sppcfw-overflow-hidden' },
											h('button', { className: `sppcfw-p-1.5 sppcfw-text-xs sppcfw-text-white hover:sppcfw-bg-[#1f2937] ${getSetting('flex_direction') === 'row' ? 'sppcfw-bg-[#374151]' : ''}`, onClick: () => handleSettingChange('flex_direction', 'row'), title: 'Row' }, '→'),
											h('button', { className: `sppcfw-p-1.5 sppcfw-text-xs sppcfw-text-white hover:sppcfw-bg-[#1f2937] ${getSetting('flex_direction') === 'column' || !getSetting('flex_direction') ? 'sppcfw-bg-[#374151]' : ''}`, onClick: () => handleSettingChange('flex_direction', 'column'), title: 'Column' }, '↓')
										)
									),

									// Justify Content
									h(
										'div',
										{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
										h('div', { className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5 sppcfw-text-gray-200 sppcfw-font-medium' }, 'Justify Content'),
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-bg-[#111827] sppcfw-overflow-hidden' },
											h('button', { className: `sppcfw-p-1.5 sppcfw-text-xs sppcfw-text-white hover:sppcfw-bg-[#1f2937] ${getSetting('justify_content') === 'flex-start' || !getSetting('justify_content') ? 'sppcfw-bg-[#374151]' : ''}`, onClick: () => handleSettingChange('justify_content', 'flex-start'), title: 'Start' }, '├─'),
											h('button', { className: `sppcfw-p-1.5 sppcfw-text-xs sppcfw-text-white hover:sppcfw-bg-[#1f2937] ${getSetting('justify_content') === 'center' ? 'sppcfw-bg-[#374151]' : ''}`, onClick: () => handleSettingChange('justify_content', 'center'), title: 'Center' }, '─┼─'),
											h('button', { className: `sppcfw-p-1.5 sppcfw-text-xs sppcfw-text-white hover:sppcfw-bg-[#1f2937] ${getSetting('justify_content') === 'flex-end' ? 'sppcfw-bg-[#374151]' : ''}`, onClick: () => handleSettingChange('justify_content', 'flex-end'), title: 'End' }, '─┤')
										)
									),

									// Align Items
									h(
										'div',
										{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
										h('div', { className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5 sppcfw-text-gray-200 sppcfw-font-medium' }, 'Align Items'),
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-bg-[#111827] sppcfw-overflow-hidden' },
											h('button', { className: `sppcfw-p-1.5 sppcfw-text-xs sppcfw-text-white hover:sppcfw-bg-[#1f2937] ${getSetting('align_items') === 'flex-start' ? 'sppcfw-bg-[#374151]' : ''}`, onClick: () => handleSettingChange('align_items', 'flex-start'), title: 'Start' }, '┬'),
											h('button', { className: `sppcfw-p-1.5 sppcfw-text-xs sppcfw-text-white hover:sppcfw-bg-[#1f2937] ${getSetting('align_items') === 'center' ? 'sppcfw-bg-[#374151]' : ''}`, onClick: () => handleSettingChange('align_items', 'center'), title: 'Center' }, '┼'),
											h('button', { className: `sppcfw-p-1.5 sppcfw-text-xs sppcfw-text-white hover:sppcfw-bg-[#1f2937] ${getSetting('align_items') === 'flex-end' ? 'sppcfw-bg-[#374151]' : ''}`, onClick: () => handleSettingChange('align_items', 'flex-end'), title: 'End' }, '┴'),
											h('button', { className: `sppcfw-p-1.5 sppcfw-text-xs sppcfw-text-white hover:sppcfw-bg-[#1f2937] ${getSetting('align_items') === 'stretch' || !getSetting('align_items') ? 'sppcfw-bg-[#374151]' : ''}`, onClick: () => handleSettingChange('align_items', 'stretch'), title: 'Stretch' }, '↕')
										)
									),

									// Widget Gap
									h(
										'div',
										{ className: 'sppcfw-space-y-1.5' },
										h('label', { className: 'sppcfw-text-xs sppcfw-text-gray-200 sppcfw-font-medium sppcfw-block' }, 'Widget Gap (px)'),
										h('input', {
											type: 'number',
											className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1 sppcfw-text-xs sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
											value: parseInt(getSetting('gap') || '12', 10) || 12,
											onChange: e => handleSettingChange('gap', e.target.value + 'px'),
										})
									)
								),

								// Column Actions Accordion
								isColumn &&
									h(
										'div',
										{ className: 'sppcfw-space-y-2 sppcfw-pt-2' },
										h('h4', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white sppcfw-mb-2' }, 'Column Actions'),
										h(
											'button',
											{
												className: 'sppcfw-w-full sppcfw-py-2 sppcfw-bg-[#9333ea] hover:sppcfw-bg-[#7e22ce] sppcfw-text-white sppcfw-rounded sppcfw-font-bold sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-gap-1.5 sppcfw-transition-colors sppcfw-shadow sppcfw-text-xs sppcfw-cursor-pointer',
												onClick: () => addColumnToContainer && addColumnToContainer(selectedElement.id),
											},
											h('span', { className: 'material-symbols-outlined sppcfw-text-sm' }, 'add'),
											'Add New Column To Container'
										),
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-gap-2' },
											h(
												'button',
												{
													className: 'sppcfw-flex-1 sppcfw-py-1.5 sppcfw-bg-[#374151] hover:sppcfw-bg-[#4b5563] sppcfw-text-white sppcfw-rounded sppcfw-font-semibold sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-gap-1 sppcfw-transition-colors sppcfw-text-xs sppcfw-cursor-pointer',
													onClick: () => duplicateColumn && duplicateColumn(selectedElement.id),
												},
												h('span', { className: 'material-symbols-outlined sppcfw-text-xs' }, 'content_copy'),
												'Duplicate'
											),
											h(
												'button',
												{
													className: 'sppcfw-flex-1 sppcfw-py-1.5 sppcfw-bg-red-900/40 hover:sppcfw-bg-red-800/60 sppcfw-border sppcfw-border-red-700/50 sppcfw-text-red-200 sppcfw-rounded sppcfw-font-semibold sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-gap-1 sppcfw-transition-colors sppcfw-text-xs sppcfw-cursor-pointer',
													onClick: () => removeElement && removeElement(selectedElement.id),
												},
												h('span', { className: 'material-symbols-outlined sppcfw-text-xs' }, 'delete'),
												'Delete'
											)
										)
									)
							),

						// IMAGE & PRODUCT GALLERY CONTENT CONTROLS (WHEN IMAGE WIDGET IS SELECTED)
						isImage &&
							h(
								'div',
								{ className: 'sppcfw-space-y-4' },

								// Accordion: Image
								h(
									'div',
									{ className: 'sppcfw-border-b sppcfw-border-[#374151] sppcfw-pb-4 sppcfw-space-y-3.5' },
									h(
										'div',
										{
											className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-cursor-pointer sppcfw-select-none',
											onClick: () => setIsImageOpen(!isImageOpen),
										},
										h(
											'h4',
											{ className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' },
											h('span', { className: 'sppcfw-text-[10px] sppcfw-text-gray-300' }, isImageOpen ? '▾' : '▸'),
											'Image'
										)
									),

									isImageOpen &&
										h(
											'div',
											{ className: 'sppcfw-space-y-4 sppcfw-pt-1' },

											// Choose Image Box / Preview
											h(
												'div',
												{ className: 'sppcfw-space-y-2' },
												h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db] sppcfw-font-medium sppcfw-block' }, 'Choose Image'),
												h(
													'div',
													{
														className: 'sppcfw-border-2 sppcfw-border-dashed sppcfw-border-[#374151] hover:sppcfw-border-[#9333ea] sppcfw-rounded-lg sppcfw-p-3 sppcfw-bg-[#111827] sppcfw-text-center sppcfw-cursor-pointer sppcfw-transition-colors sppcfw-relative group',
														onClick: () => {
															if (typeof wp !== 'undefined' && wp.media) {
																const frame = wp.media({
																	title: 'Select Image',
																	button: { text: 'Insert Image' },
																	multiple: false,
																});
																frame.on('select', () => {
																	const attachment = frame.state().get('selection').first().toJSON();
																	if (attachment && attachment.url) {
																		handleSettingChange('custom_image_url', attachment.url);
																	}
																});
																frame.open();
															} else {
																const url = prompt('Enter Image URL:', getSetting('custom_image_url') || '');
																if (url !== null && url.trim() !== '') {
																	handleSettingChange('custom_image_url', url.trim());
																}
															}
														},
													},
													getSetting('custom_image_url')
														? h(
																'div',
																{ className: 'sppcfw-space-y-2' },
																h('img', {
																	src: getSetting('custom_image_url'),
																	alt: 'Selected Custom Preview',
																	className: 'sppcfw-h-28 sppcfw-w-full sppcfw-object-contain sppcfw-rounded',
																}),
																h('div', { className: 'sppcfw-text-[11px] sppcfw-text-[#9333ea] sppcfw-font-semibold' }, 'Click to Change Custom Image')
														  )
														: h(
																'div',
																{ className: 'sppcfw-space-y-2' },
																(sampleData && sampleData.image_url)
																	? h('img', {
																			src: sampleData.image_url,
																			alt: sampleData.title || 'Product Image',
																			className: 'sppcfw-h-28 sppcfw-w-full sppcfw-object-contain sppcfw-rounded sppcfw-bg-[#1f2937]/50 sppcfw-p-1',
																	  })
																	: h(
																			'div',
																			{ className: 'sppcfw-py-4 sppcfw-space-y-1.5' },
																			h('span', { className: 'material-symbols-outlined sppcfw-text-3xl sppcfw-text-gray-400 group-hover:sppcfw-text-[#9333ea]' }, 'add_photo_alternate'),
																			h('div', { className: 'sppcfw-text-xs sppcfw-font-semibold sppcfw-text-gray-200' }, 'Choose Image')
																	  ),
																h(
																	'div',
																	{ className: 'sppcfw-text-[10px] sppcfw-text-gray-400 sppcfw-px-1' },
																	sampleData && sampleData.id
																		? `Previewing Featured Image: ${sampleData.title}`
																		: 'Previewing Demo Image (Click to upload custom)'
																)
														  )
												),
												getSetting('custom_image_url') &&
													h(
														'button',
														{
															type: 'button',
															className: 'sppcfw-text-[11px] sppcfw-text-red-400 hover:sppcfw-text-red-300 sppcfw-underline sppcfw-block sppcfw-cursor-pointer',
															onClick: () => handleSettingChange('custom_image_url', ''),
														},
														'Reset to Product Featured Image'
													)
											),

											// Image Resolution (Size)
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
												h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db] sppcfw-font-medium' }, 'Image Resolution'),
												h(
													'div',
													{ className: 'sppcfw-relative sppcfw-w-44' },
													h(
														'select',
														{
															className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-pr-6 sppcfw-text-xs sppcfw-text-white sppcfw-appearance-none focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea] sppcfw-cursor-pointer',
															value: getSetting('image_size') || 'large',
															onChange: e => handleSettingChange('image_size', e.target.value),
														},
														h('option', { value: 'thumbnail' }, 'Thumbnail (150x150)'),
														h('option', { value: 'medium' }, 'Medium (300x300)'),
														h('option', { value: 'large' }, 'Large (1024x1024)'),
														h('option', { value: 'full' }, 'Full')
													),
												)
											),

											// Alignment
											h(
												'div',
												{ className: 'sppcfw-space-y-1.5' },
												renderControlHeader('Alignment', true),
												renderButtonGroup(
													[
														{ value: 'left', icon: 'format_align_left', title: 'Left' },
														{ value: 'center', icon: 'format_align_center', title: 'Center' },
														{ value: 'right', icon: 'format_align_right', title: 'Right' },
													],
													getStyle('alignment') || 'center',
													v => handleStyleChange('alignment', v)
												)
											),

											// Caption
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
												h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db] sppcfw-font-medium' }, 'Caption'),
												h(
													'div',
													{ className: 'sppcfw-relative sppcfw-w-44' },
													h(
														'select',
														{
															className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-pr-6 sppcfw-text-xs sppcfw-text-white sppcfw-appearance-none focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea] sppcfw-cursor-pointer',
															value: getSetting('caption_type') || 'none',
															onChange: e => handleSettingChange('caption_type', e.target.value),
														},
														h('option', { value: 'none' }, 'None'),
														h('option', { value: 'attachment' }, 'Attachment Caption'),
														h('option', { value: 'custom' }, 'Custom Caption')
													),
												)
											),
											getSetting('caption_type') === 'custom' &&
												h(
													'div',
													{ className: 'sppcfw-space-y-1' },
													h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db] sppcfw-font-medium' }, 'Custom Caption Text'),
													h('input', {
														type: 'text',
														className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
														value: getSetting('custom_caption') || '',
														placeholder: 'Enter image caption...',
														onChange: e => handleSettingChange('custom_caption', e.target.value),
													})
												),

											// Link
											h(
												'div',
												{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
												h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db] sppcfw-font-medium' }, 'Link'),
												h(
													'div',
													{ className: 'sppcfw-relative sppcfw-w-44' },
													h(
														'select',
														{
															className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-pr-6 sppcfw-text-xs sppcfw-text-white sppcfw-appearance-none focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea] sppcfw-cursor-pointer',
															value: getSetting('link_to') || 'none',
															onChange: e => handleSettingChange('link_to', e.target.value),
														},
														h('option', { value: 'none' }, 'None'),
														h('option', { value: 'file' }, 'Media File'),
														h('option', { value: 'custom' }, 'Custom URL')
													)
												)
											),
											getSetting('link_to') === 'custom' &&
												h(
													'div',
													{ className: 'sppcfw-space-y-1' },
													h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db] sppcfw-font-medium' }, 'Custom Link URL'),
													h('input', {
														type: 'text',
														className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
														value: getSetting('custom_link') || '',
														placeholder: 'https://...',
														onChange: e => handleSettingChange('custom_link', e.target.value),
													})
												),

											// Additional Gallery Settings (if product_gallery)
											selectedElement.type === 'product_gallery' &&
												h(
													'div',
													{ className: 'sppcfw-border-t sppcfw-border-[#374151] sppcfw-pt-3.5 sppcfw-space-y-3' },
													h('h4', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white' }, 'Gallery Features'),
													h(
														'div',
														{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
														h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db]' }, 'Show Thumbnail Row'),
														h('input', {
															type: 'checkbox',
															className: 'sppcfw-accent-[#9333ea] sppcfw-cursor-pointer sppcfw-w-4 sppcfw-h-4',
															checked: getSetting('show_thumbnails') !== false,
															onChange: e => handleSettingChange('show_thumbnails', e.target.checked),
														})
													),
													h(
														'div',
														{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
														h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db]' }, 'Lightbox Popup'),
														h('input', {
															type: 'checkbox',
															className: 'sppcfw-accent-[#9333ea] sppcfw-cursor-pointer sppcfw-w-4 sppcfw-h-4',
															checked: getSetting('enable_lightbox') !== false,
															onChange: e => handleSettingChange('enable_lightbox', e.target.checked),
														})
													),
													h(
														'div',
														{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
														h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db]' }, 'Image Zoom'),
														h('input', {
															type: 'checkbox',
															className: 'sppcfw-accent-[#9333ea] sppcfw-cursor-pointer sppcfw-w-4 sppcfw-h-4',
															checked: getSetting('enable_zoom') !== false,
															onChange: e => handleSettingChange('enable_zoom', e.target.checked),
														})
													)
												)
										)
								)
							),

						// OTHER PRODUCT WIDGET CONTENT PANELS (WHEN TITLE, PRICE, BUTTON, ETC IS SELECTED)
						!isContainer && !isColumn && !isImage &&
							h(
								'div',
								{ className: 'sppcfw-space-y-4' },

								// General Widget Content Accordion
								h(
									'div',
									{ className: 'sppcfw-border-b sppcfw-border-[#374151] sppcfw-pb-4 sppcfw-space-y-3.5' },
									h(
										'div',
										{
											className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-cursor-pointer sppcfw-select-none',
											onClick: () => setIsImageOpen(!isImageOpen),
										},
										h(
											'h4',
											{ className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' },
											h('span', { className: 'sppcfw-text-[10px] sppcfw-text-gray-300' }, isImageOpen ? '▾' : '▸'),
											selectedElement.label || 'Widget Content'
										)
									),

									isImageOpen &&
										h(
											'div',
											{ className: 'sppcfw-space-y-3.5 sppcfw-pt-1' },

											// Product Title Settings
											selectedElement.type === 'product_title' &&
												h(
													'div',
													{ className: 'sppcfw-space-y-3' },
													h(
														'div',
														{ className: 'sppcfw-space-y-1' },
														h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db] sppcfw-font-medium' }, 'Title Text Override (optional)'),
														h('input', {
															type: 'text',
															className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
															value: getSetting('title_override') || '',
															placeholder: 'Dynamic Product Title',
															onChange: e => handleSettingChange('title_override', e.target.value),
														})
													),
													h(
														'div',
														{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
														h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db] sppcfw-font-medium' }, 'HTML Tag'),
														h(
															'select',
															{
																className: 'sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2 sppcfw-py-1 sppcfw-text-xs sppcfw-text-white focus:sppcfw-outline-none',
																value: getSetting('html_tag') || 'h1',
																onChange: e => handleSettingChange('html_tag', e.target.value),
															},
															['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p'].map(tag => h('option', { key: tag, value: tag }, tag.toUpperCase()))
														)
													),
													h(
														'div',
														{ className: 'sppcfw-space-y-1.5' },
														renderControlHeader('Alignment', true),
														renderButtonGroup(
															[
																{ value: 'left', icon: 'format_align_left', title: 'Left' },
																{ value: 'center', icon: 'format_align_center', title: 'Center' },
																{ value: 'right', icon: 'format_align_right', title: 'Right' },
															],
															getStyle('alignment') || 'left',
															v => handleStyleChange('alignment', v)
														)
													)
												),

											// Product Price Settings
											selectedElement.type === 'product_price' &&
												h(
													'div',
													{ className: 'sppcfw-space-y-3' },
													h(
														'div',
														{ className: 'sppcfw-space-y-1.5' },
														renderControlHeader('Alignment', true),
														renderButtonGroup(
															[
																{ value: 'left', icon: 'format_align_left', title: 'Left' },
																{ value: 'center', icon: 'format_align_center', title: 'Center' },
																{ value: 'right', icon: 'format_align_right', title: 'Right' },
															],
															getStyle('alignment') || 'left',
															v => handleStyleChange('alignment', v)
														)
													)
												),

											// Product Add to Cart Settings
											selectedElement.type === 'product_add_to_cart' &&
												h(
													'div',
													{ className: 'sppcfw-space-y-3' },
													h(
														'div',
														{ className: 'sppcfw-space-y-1' },
														h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db] sppcfw-font-medium' }, 'Button Text'),
														h('input', {
															type: 'text',
															className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
															value: getSetting('button_text') || 'Add to cart',
															onChange: e => handleSettingChange('button_text', e.target.value),
														})
													),
													h(
														'div',
														{ className: 'sppcfw-space-y-1.5' },
														renderControlHeader('Alignment', true),
														renderButtonGroup(
															[
																{ value: 'left', icon: 'format_align_left', title: 'Left' },
																{ value: 'center', icon: 'format_align_center', title: 'Center' },
																{ value: 'right', icon: 'format_align_right', title: 'Right' },
															],
															getStyle('alignment') || 'left',
															v => handleStyleChange('alignment', v)
														)
													)
												),

											// Product Short / Full Description Settings
											(selectedElement.type === 'product_short_desc' || selectedElement.type === 'product_description') &&
												h(
													'div',
													{ className: 'sppcfw-space-y-3' },
													h(
														'div',
														{ className: 'sppcfw-space-y-1' },
														h('label', { className: 'sppcfw-text-xs sppcfw-text-[#d1d5db] sppcfw-font-medium' }, 'Custom Description Override'),
														h('textarea', {
															rows: 4,
															className: 'sppcfw-w-full sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-p-2 sppcfw-text-xs sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
															value: getSetting('custom_desc') || '',
															placeholder: 'Leave blank to use WooCommerce product description...',
															onChange: e => handleSettingChange('custom_desc', e.target.value),
														})
													)
												),

											// Generic Widget Settings
											selectedElement.type !== 'product_title' &&
												selectedElement.type !== 'product_price' &&
												selectedElement.type !== 'product_add_to_cart' &&
												selectedElement.type !== 'product_short_desc' &&
												selectedElement.type !== 'product_description' &&
												h(
													'div',
													{ className: 'sppcfw-space-y-3' },
													h(
														'div',
														{ className: 'sppcfw-space-y-1.5' },
														renderControlHeader('Alignment', true),
														renderButtonGroup(
															[
																{ value: 'left', icon: 'format_align_left', title: 'Left' },
																{ value: 'center', icon: 'format_align_center', title: 'Center' },
																{ value: 'right', icon: 'format_align_right', title: 'Right' },
															],
															getStyle('alignment') || 'left',
															v => handleStyleChange('alignment', v)
														)
													)
												)
										)
								)
							)
					),

				// --- 2. STYLE TAB (Matching Screenshot 2) ---
				activeTab === 'style' &&
					h(
						'div',
						{ className: 'sppcfw-space-y-4' },

						// Product Gallery / Image Style Accordion (Screenshot 2)
						isImage &&
							h(
								'div',
								{ className: 'sppcfw-border-b sppcfw-border-[#374151] sppcfw-pb-4 sppcfw-space-y-3.5' },
								h(
									'div',
									{
										className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-cursor-pointer sppcfw-select-none',
										onClick: () => setIsImageOpen(!isImageOpen),
									},
									h('h4', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' }, h('span', { className: 'sppcfw-text-[10px]' }, isImageOpen ? '▼' : '▶'), 'Image')
								),

								isImageOpen &&
									h(
										'div',
										{ className: 'sppcfw-space-y-4 sppcfw-pt-1' },

										// Alignment (Screenshot 2)
										h(
											'div',
											{ className: 'sppcfw-space-y-1.5' },
											renderControlHeader('Alignment', true),
											renderButtonGroup(
												[
													{ value: 'left', icon: 'format_align_left', title: 'Left' },
													{ value: 'center', icon: 'format_align_center', title: 'Center' },
													{ value: 'right', icon: 'format_align_right', title: 'Right' },
												],
												getStyle('alignment') || 'center',
												v => handleStyleChange('alignment', v)
											)
										),

										// Width (Screenshot 2)
										h(
											'div',
											{ className: 'sppcfw-space-y-1.5' },
											renderControlHeader('Width', true, imgWidthUnit, setImgWidthUnit, ['%', 'px', 'vw']),
											renderSliderInput(getStyle('width') || '100%', v => handleStyleChange('width', v), 0, 100, 1, imgWidthUnit)
										),

										// Max Width (Screenshot 2)
										h(
											'div',
											{ className: 'sppcfw-space-y-1.5' },
											renderControlHeader('Max Width', true, maxImgWidthUnit, setMaxImgWidthUnit, ['%', 'px', 'vw']),
											renderSliderInput(getStyle('max_width') || '100%', v => handleStyleChange('max_width', v), 0, 100, 1, maxImgWidthUnit)
										),

										// Height (Screenshot 2)
										h(
											'div',
											{ className: 'sppcfw-space-y-1.5' },
											renderControlHeader('Height', true, imgHeightUnit, setImgHeightUnit, ['px', 'vh']),
											renderSliderInput(getStyle('height') || '', v => handleStyleChange('height', v), 0, 1000, 1, imgHeightUnit)
										),

										h('hr', { className: 'sppcfw-border-[#374151] sppcfw-my-2' }),

										// Normal / Hover Mode Switcher (Screenshot 2)
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-overflow-hidden sppcfw-p-0.5' },
											h(
												'button',
												{
													type: 'button',
													className: `sppcfw-flex-1 sppcfw-py-1 sppcfw-text-center sppcfw-rounded sppcfw-text-xs sppcfw-font-semibold sppcfw-transition-colors sppcfw-cursor-pointer ${
														styleMode === 'normal' ? 'sppcfw-bg-[#374151] sppcfw-text-white' : 'sppcfw-text-gray-400 hover:sppcfw-text-white'
													}`,
													onClick: () => setStyleMode('normal'),
												},
												'Normal'
											),
											h(
												'button',
												{
													type: 'button',
													className: `sppcfw-flex-1 sppcfw-py-1 sppcfw-text-center sppcfw-rounded sppcfw-text-xs sppcfw-font-semibold sppcfw-transition-colors sppcfw-cursor-pointer ${
														styleMode === 'hover' ? 'sppcfw-bg-[#374151] sppcfw-text-white' : 'sppcfw-text-gray-400 hover:sppcfw-text-white'
													}`,
													onClick: () => setStyleMode('hover'),
												},
												'Hover'
											)
										),

										// Opacity (Screenshot 2)
										h(
											'div',
											{ className: 'sppcfw-space-y-1.5' },
											renderControlHeader('Opacity', false),
											renderSliderInput(getStyle('opacity') || '1', v => handleStyleChange('opacity', v), 0, 1, 0.05, '')
										),

										// CSS Filters (Screenshot 2)
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-pt-1' },
											h('label', { className: 'sppcfw-text-xs sppcfw-text-gray-200 sppcfw-font-medium' }, 'CSS Filters'),
											h(
												'button',
												{
													type: 'button',
													className: 'sppcfw-p-1.5 sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-text-gray-300 hover:sppcfw-text-white sppcfw-cursor-pointer',
													title: 'Edit CSS Filters',
												},
												h('span', { className: 'material-symbols-outlined sppcfw-text-xs' }, 'edit')
											)
										),

										h('hr', { className: 'sppcfw-border-[#374151] sppcfw-my-2' }),

										// Border Type (Screenshot 2)
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
											h('label', { className: 'sppcfw-text-xs sppcfw-text-gray-200 sppcfw-font-medium' }, 'Border Type'),
											h(
												'select',
												{
													className: 'sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1 sppcfw-text-xs sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea] sppcfw-w-36',
													value: getStyle('border_type') || 'Default',
													onChange: e => handleStyleChange('border_type', e.target.value),
												},
												h('option', { value: 'Default' }, 'Default'),
												h('option', { value: 'None' }, 'None'),
												h('option', { value: 'Solid' }, 'Solid'),
												h('option', { value: 'Double' }, 'Double'),
												h('option', { value: 'Dotted' }, 'Dotted'),
												h('option', { value: 'Dashed' }, 'Dashed'),
												h('option', { value: 'Groove' }, 'Groove')
											)
										),

										// Border Radius (Screenshot 2: 4-box linked inputs)
										h(
											'div',
											{ className: 'sppcfw-space-y-1.5' },
											renderControlHeader('Border Radius', true, radiusUnit, setRadiusUnit, ['px', '%']),
											renderFourBoxInput(selectedElement.styles, 'border_radius', handleMultiStyleChange, radiusUnit, isRadiusLinked, setIsRadiusLinked)
										),

										// Box Shadow (Screenshot 2)
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-pt-1' },
											h('label', { className: 'sppcfw-text-xs sppcfw-text-gray-200 sppcfw-font-medium' }, 'Box Shadow'),
											h(
												'button',
												{
													type: 'button',
													className: 'sppcfw-p-1.5 sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-text-gray-300 hover:sppcfw-text-white sppcfw-cursor-pointer',
													title: 'Edit Box Shadow',
												},
												h('span', { className: 'material-symbols-outlined sppcfw-text-xs' }, 'edit')
											)
										)
									)
							),

						// Non-image Background Accordion
						!isImage &&
							h(
								'div',
								{ className: 'sppcfw-border-b sppcfw-border-[#374151] sppcfw-pb-4 sppcfw-space-y-3' },
								h(
									'div',
									{
										className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-cursor-pointer sppcfw-select-none',
										onClick: () => setIsBgOpen(!isBgOpen),
									},
									h('h4', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' }, h('span', { className: 'sppcfw-text-[10px]' }, isBgOpen ? '▼' : '▶'), 'Background')
								),

								isBgOpen &&
									h(
										'div',
										{ className: 'sppcfw-space-y-3.5 sppcfw-pt-1' },

										// Color Field
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
											h('label', { className: 'sppcfw-text-xs sppcfw-text-gray-200 sppcfw-font-medium' }, 'Color'),
											h('input', {
												type: 'color',
												className: 'sppcfw-w-7 sppcfw-h-7 sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-cursor-pointer sppcfw-p-0.5',
												value: getStyle('bg_color') || '#ffffff',
												onChange: e => handleStyleChange('bg_color', e.target.value),
											})
										)
									)
							)
					),

				// --- 3. ADVANCED TAB (Matching Screenshot 3) ---
				activeTab === 'advanced' &&
					h(
						'div',
						{ className: 'sppcfw-space-y-4' },

						// Layout Accordion (Screenshot 3)
						h(
							'div',
							{ className: 'sppcfw-border-b sppcfw-border-[#374151] sppcfw-pb-4 sppcfw-space-y-3.5' },
							h(
								'div',
								{
									className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-cursor-pointer sppcfw-select-none',
									onClick: () => setIsLayoutAdvOpen(!isLayoutAdvOpen),
								},
								h('h4', { className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-text-white sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' }, h('span', { className: 'sppcfw-text-[10px]' }, isLayoutAdvOpen ? '▼' : '▶'), 'Layout')
							),

							isLayoutAdvOpen &&
								h(
									'div',
									{ className: 'sppcfw-space-y-4 sppcfw-pt-1' },

									// Margin (Screenshot 3: 4-box linked inputs)
									h(
										'div',
										{ className: 'sppcfw-space-y-1.5' },
										renderControlHeader('Margin', true, marginUnit, setMarginUnit, ['px', '%', 'em']),
										renderFourBoxInput(selectedElement.advanced, 'margin', handleMultiAdvancedChange, marginUnit, isMarginLinked, setIsMarginLinked)
									),

									// Padding (Screenshot 3: 4-box linked inputs)
									h(
										'div',
										{ className: 'sppcfw-space-y-1.5' },
										renderControlHeader('Padding', true, paddingUnit, setPaddingUnit, ['px', '%', 'em']),
										renderFourBoxInput(selectedElement.advanced, 'padding', handleMultiAdvancedChange, paddingUnit, isPaddingLinked, setIsPaddingLinked)
									),

									// Width Dropdown (Screenshot 3)
									h(
										'div',
										{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
										renderControlHeader('Width', true),
										h(
											'select',
											{
												className: 'sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1 sppcfw-text-xs sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea] sppcfw-w-36',
												value: getAdvanced('width_mode') || 'Default',
												onChange: e => handleAdvancedChange('width_mode', e.target.value),
											},
											h('option', { value: 'Default' }, 'Default'),
											h('option', { value: 'Full Width (100%)' }, 'Full Width (100%)'),
											h('option', { value: 'Inline (auto)' }, 'Inline (auto)'),
											h('option', { value: 'Custom' }, 'Custom')
										)
									),

									h('hr', { className: 'sppcfw-border-[#374151] sppcfw-my-2' }),

									// Align Self (Screenshot 3)
									h(
										'div',
										{ className: 'sppcfw-space-y-1' },
										renderControlHeader('Align Self', true),
										renderButtonGroup(
											[
												{ value: 'flex-start', icon: 'align_flex_start', title: 'Start' },
												{ value: 'center', icon: 'align_center', title: 'Center' },
												{ value: 'flex-end', icon: 'align_flex_end', title: 'End' },
												{ value: 'stretch', icon: 'align_stretch', title: 'Stretch' },
											],
											getAdvanced('align_self') || '',
											v => handleAdvancedChange('align_self', v)
										),
										h('p', { className: 'sppcfw-text-[10px] sppcfw-text-gray-400 sppcfw-italic sppcfw-pt-0.5' }, 'This control will affect contained elements only.')
									),

									// Order (Screenshot 3)
									h(
										'div',
										{ className: 'sppcfw-space-y-1' },
										renderControlHeader('Order', true),
										renderButtonGroup(
											[
												{ value: 'start', icon: 'vertical_align_top', title: 'Start' },
												{ value: 'end', icon: 'vertical_align_bottom', title: 'End' },
												{ value: 'custom', icon: 'more_vert', title: 'Custom' },
											],
											getAdvanced('order') || '',
											v => handleAdvancedChange('order', v)
										),
										h('p', { className: 'sppcfw-text-[10px] sppcfw-text-gray-400 sppcfw-italic sppcfw-pt-0.5' }, 'This control will affect contained elements only.')
									),

									// Size (Screenshot 3)
									h(
										'div',
										{ className: 'sppcfw-space-y-1.5' },
										renderControlHeader('Size', true),
										renderButtonGroup(
											[
												{ value: 'none', icon: 'block', title: 'None' },
												{ value: 'grow', icon: 'aspect_ratio', title: 'Grow' },
												{ value: 'shrink', icon: 'fit_screen', title: 'Shrink' },
											],
											getAdvanced('size') || '',
											v => handleAdvancedChange('size', v)
										)
									),

									h('hr', { className: 'sppcfw-border-[#374151] sppcfw-my-2' }),

									// Position (Screenshot 3)
									h(
										'div',
										{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
										h('label', { className: 'sppcfw-text-xs sppcfw-text-gray-200 sppcfw-font-medium' }, 'Position'),
										h(
											'select',
											{
												className: 'sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1 sppcfw-text-xs sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea] sppcfw-w-36',
												value: getAdvanced('position') || 'Default',
												onChange: e => handleAdvancedChange('position', e.target.value),
											},
											h('option', { value: 'Default' }, 'Default'),
											h('option', { value: 'Absolute' }, 'Absolute'),
											h('option', { value: 'Fixed' }, 'Fixed')
										)
									),

									// Z-Index (Screenshot 3)
									h(
										'div',
										{ className: 'sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
										renderControlHeader('Z-Index', true),
										h('input', {
											type: 'number',
											className: 'sppcfw-w-24 sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1 sppcfw-text-xs sppcfw-text-center sppcfw-text-white focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
											value: getAdvanced('z_index') || '',
											onChange: e => handleAdvancedChange('z_index', e.target.value),
										})
									),

									// CSS ID (Screenshot 3)
									h(
										'div',
										{ className: 'sppcfw-space-y-1.5' },
										h('label', { className: 'sppcfw-text-xs sppcfw-text-gray-200 sppcfw-font-medium sppcfw-block' }, 'CSS ID'),
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' },
											h('input', {
												type: 'text',
												className: 'sppcfw-flex-1 sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-white sppcfw-placeholder-gray-500 focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
												value: getAdvanced('css_id') || '',
												onChange: e => handleAdvancedChange('css_id', e.target.value),
											}),
											h('button', { type: 'button', className: 'sppcfw-p-1.5 sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-text-gray-400 hover:sppcfw-text-white', title: 'Dynamic Tag' }, h('span', { className: 'material-symbols-outlined sppcfw-text-xs' }, 'storage'))
										)
									),

									// CSS Classes (Screenshot 3)
									h(
										'div',
										{ className: 'sppcfw-space-y-1.5' },
										h('label', { className: 'sppcfw-text-xs sppcfw-text-gray-200 sppcfw-font-medium sppcfw-block' }, 'CSS Classes'),
										h(
											'div',
											{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5' },
											h('input', {
												type: 'text',
												className: 'sppcfw-flex-1 sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-px-2.5 sppcfw-py-1.5 sppcfw-text-xs sppcfw-text-white sppcfw-placeholder-gray-500 focus:sppcfw-outline-none focus:sppcfw-border-[#9333ea]',
												value: getAdvanced('css_classes') || (getAdvanced('custom_class') || ''),
												onChange: e => {
													handleAdvancedChange('css_classes', e.target.value);
													handleAdvancedChange('custom_class', e.target.value);
												},
											}),
											h('button', { type: 'button', className: 'sppcfw-p-1.5 sppcfw-bg-[#111827] sppcfw-border sppcfw-border-[#374151] sppcfw-rounded sppcfw-text-gray-400 hover:sppcfw-text-white', title: 'Dynamic Tag' }, h('span', { className: 'material-symbols-outlined sppcfw-text-xs' }, 'storage'))
										)
									)
								)
						)
					)
			)
		);
	}

	// 4. Central Canvas Component with Viewport Preview
	function CentralCanvas({ deviceView, elements, setElements, selectedElementId, setSelectedElementId, sampleData, pageSettings, removeElement, addWidgetToTarget, addColumnToContainer, duplicateColumn, openElementsTab, isStructureOpen, setIsStructureOpen }) {
		const [isCanvasDragOver, setIsCanvasDragOver] = useState(false);
		const [isAddingContainer, setIsAddingContainer] = useState(false);

		function handleSelectPreset(presetType) {
			const newContainer = createContainerStructure(presetType);
			setElements(prev => [...prev, newContainer]);
			setSelectedElementId(newContainer.id);
			setIsAddingContainer(false);
		}

		return h(
			'main',
			{ className: 'sppcfw-flex-1 sppcfw-bg-[#0f172a] sppcfw-relative sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-w-full sppcfw-overflow-hidden sppcfw-h-full' },

			// Outer Canvas Container
			h(
				'div',
				{
					className: `sppcfw-preview-canvas-container sppcfw-bg-[#ffffff] sppcfw-text-[#111827] sppcfw-pb-16 sppcfw-h-[calc(100vh)] sppcfw-overflow-y-auto custom-scrollbar ${
						isCanvasDragOver ? 'sppcfw-border-2 sppcfw-border-dashed sppcfw-border-[#9333ea] sppcfw-bg-[#faf5ff]' : 'sppcfw-border-[#e5e7eb]'
					} ${deviceView === 'tablet' ? 'sppcfw-preview-viewport-tablet' : deviceView === 'mobile' ? 'sppcfw-preview-viewport-mobile' : 'sppcfw-preview-viewport-desktop'} sppcfw-transition-all`,
				},

				// Empty Canvas View (when elements.length === 0)
				elements.length === 0
					? isAddingContainer
						? h(LayoutStructureChooser, {
								onSelectPreset: handleSelectPreset,
								onClose: () => setIsAddingContainer(false),
						  })
						: h(
								'div',
								{
									className: 'sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-justify-center sppcfw-min-h-[450px] sppcfw-border-2 sppcfw-border-dashed sppcfw-border-[#9333ea]/40 sppcfw-rounded-xl sppcfw-p-12 sppcfw-text-center sppcfw-bg-[#faf5ff] sppcfw-cursor-pointer hover:sppcfw-border-[#9333ea] sppcfw-transition-all sppcfw-tab-group',
									onClick: () => setIsAddingContainer(true),
								},
								h('div', { className: 'sppcfw-w-16 sppcfw-h-16 sppcfw-rounded-full sppcfw-bg-[#9333ea]/10 sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-mb-4 group-hover:sppcfw-scale-110 sppcfw-transition-transform' }, h('span', { className: 'material-symbols-outlined sppcfw-text-4xl sppcfw-text-[#9333ea]' }, 'add_circle')),
								h('h2', { className: 'sppcfw-text-2xl sppcfw-font-extrabold sppcfw-text-[#111827] sppcfw-mb-2' }, 'Select Layout Structure'),
								h('p', { className: 'sppcfw-text-sm sppcfw-text-[#6b7280] sppcfw-max-w-md sppcfw-mb-6' }, 'Choose a Layout Structure (Flexbox or Grid) to start building your single product page layout.'),
								h(
									'button',
									{
										className: 'sppcfw-px-6 sppcfw-py-2.5 sppcfw-bg-[#9333ea] hover:sppcfw-bg-[#7e22ce] sppcfw-text-white sppcfw-rounded-lg sppcfw-font-bold sppcfw-shadow-lg sppcfw-flex sppcfw-items-center sppcfw-gap-2 sppcfw-text-sm sppcfw-transition-all sppcfw-cursor-pointer',
										onClick: e => {
											e.stopPropagation();
											setIsAddingContainer(true);
										},
									},
									h('span', { className: 'material-symbols-outlined sppcfw-text-lg' }, 'add'),
									'Add Layout / Container'
								)
						  )
					: h(
							'div',
							{ className: 'sppcfw-space-y-6' },
							elements.map((container, cIdx) =>
								h(CanvasContainerRenderer, {
									key: container.id,
									container,
									cIdx,
									elements,
									setElements,
									selectedElementId,
									setSelectedElementId,
									removeElement,
									sampleData,
									pageSettings,
									addWidgetToTarget,
									addColumnToContainer,
									duplicateColumn,
									openElementsTab,
									openStructureChooser: () => setIsAddingContainer(true),
									deviceView,
								})
							),

							// Add Container Section Bottom Action Bar
							isAddingContainer
								? h(LayoutStructureChooser, {
										onSelectPreset: handleSelectPreset,
										onClose: () => setIsAddingContainer(false),
								  })
								: h(
										'div',
										{
											className: 'sppcfw-py-4 sppcfw-border-2 sppcfw-border-dashed sppcfw-border-[#d1d5db] hover:sppcfw-border-[#9333ea] sppcfw-rounded-lg sppcfw-text-center sppcfw-cursor-pointer sppcfw-bg-[#f9fafb] hover:sppcfw-bg-[#faf5ff] sppcfw-transition-all sppcfw-flex sppcfw-justify-center sppcfw-items-center sppcfw-gap-2 sppcfw-tab-group sppcfw-mx-auto sppcfw-max-w-[45rem]',
											onClick: () => setIsAddingContainer(true),
										},
										h('span', { className: 'material-symbols-outlined sppcfw-text-xl sppcfw-text-[#9333ea] group-hover:sppcfw-scale-125 sppcfw-transition-transform' }, 'add_circle'),
										h('span', { className: 'sppcfw-text-sm sppcfw-font-bold sppcfw-text-[#4b5563] group-hover:sppcfw-text-[#9333ea]' }, 'Add Container Section')
								  )
					  )
			),

			// Breadcrumb Footer
			h(
				'div',
				{ className: 'sppcfw-fixed sppcfw-bottom-4 sppcfw-left-[420px] sppcfw-bg-[#16202e] sppcfw-border sppcfw-border-[#4d4354] sppcfw-rounded sppcfw-px-3 sppcfw-py-1 sppcfw-text-xs font-mono sppcfw-text-[#cfc2d7] sppcfw-z-20 sppcfw-shadow-md' },
				'Layout > ' + (findElementInTree(elements, selectedElementId)?.label || 'Empty Selection')
			)
		);
	}

	// 5. Right Floating Structure Panel (Dockable Window - Draggable bounded to workspace & Vertically Resizable)
	function FloatingStructurePanel({ elements, setElements, selectedElementId, setSelectedElementId, removeElement, openElementsTab, closeStructure }) {
		const [isCollapsed, setIsCollapsed] = useState(false);
		const [position, setPosition] = useState({ top: 16, left: null, right: 16 });
		const [isDragging, setIsDragging] = useState(false);
		const [contentHeight, setContentHeight] = useState(280);
		const [isResizing, setIsResizing] = useState(false);

		const panelRef = useRef(null);
		const contentRef = useRef(null);
		const dragRef = useRef({ startX: 0, startY: 0, initialTop: 16, initialLeft: null });
		const resizeRef = useRef({ startY: 0, startHeight: 280 });

		// Drag handler for moving the panel anywhere
		const handleMouseDown = (e) => {
			if (e.target.closest('button')) return;
			if (!panelRef.current) return;

			const panelEl = panelRef.current;
			const workspaceEl = panelEl.closest('.sppcfw-builder-workspace') || document.body;
			const wsRect = workspaceEl.getBoundingClientRect();
			const panelRect = panelEl.getBoundingClientRect();

			setIsDragging(true);
			dragRef.current = {
				startX: e.clientX,
				startY: e.clientY,
				initialTop: panelRect.top - wsRect.top,
				initialLeft: panelRect.left - wsRect.left,
			};
		};

		// Resize handler for vertical dragging
		const handleResizeMouseDown = (e) => {
			e.preventDefault();
			e.stopPropagation();
			setIsResizing(true);
			const currentH = contentRef.current ? contentRef.current.offsetHeight : contentHeight;
			resizeRef.current = {
				startY: e.clientY,
				startHeight: currentH,
			};
		};

		// Mouse move & mouse up listeners for moving panel
		useEffect(() => {
			if (!isDragging) return;

			const handleMouseMove = (e) => {
				if (!panelRef.current) return;

				const dx = e.clientX - dragRef.current.startX;
				const dy = e.clientY - dragRef.current.startY;

				let newTop = dragRef.current.initialTop + dy;
				let newLeft = dragRef.current.initialLeft + dx;

				const workspaceEl = panelRef.current.closest('.sppcfw-builder-workspace') || document.body;
				const wsWidth = workspaceEl.clientWidth || window.innerWidth;
				const wsHeight = workspaceEl.clientHeight || window.innerHeight;
				const panelWidth = panelRef.current.offsetWidth || 288;
				const panelHeight = panelRef.current.offsetHeight || 300;

				const minTop = 8;
				const maxTop = Math.max(minTop, wsHeight - 40); // Keep header reachable
				const minLeft = 8;
				const maxLeft = Math.max(minLeft, wsWidth - panelWidth - 8);

				newTop = Math.max(minTop, Math.min(maxTop, newTop));
				newLeft = Math.max(minLeft, Math.min(maxLeft, newLeft));

				setPosition({ top: newTop, left: newLeft });
			};

			const handleMouseUp = () => {
				setIsDragging(false);
			};

			window.addEventListener('mousemove', handleMouseMove);
			window.addEventListener('mouseup', handleMouseUp);

			return () => {
				window.removeEventListener('mousemove', handleMouseMove);
				window.removeEventListener('mouseup', handleMouseUp);
			};
		}, [isDragging]);

		// Mouse move & mouse up listeners for resizing panel vertically
		useEffect(() => {
			if (!isResizing) return;

			const handleMouseMove = (e) => {
				const dy = e.clientY - resizeRef.current.startY;
				let newHeight = resizeRef.current.startHeight + dy;

				// Strictly limited to max-h-400 (between 100px and 400px)
				newHeight = Math.max(100, Math.min(400, newHeight));
				setContentHeight(newHeight);
			};

			const handleMouseUp = () => {
				setIsResizing(false);
			};

			window.addEventListener('mousemove', handleMouseMove);
			window.addEventListener('mouseup', handleMouseUp);

			return () => {
				window.removeEventListener('mousemove', handleMouseMove);
				window.removeEventListener('mouseup', handleMouseUp);
			};
		}, [isResizing]);

		const panelStyle = position.left !== undefined && position.left !== null
			? { top: `${position.top}px`, left: `${position.left}px`, right: 'auto', position: 'absolute' }
			: { top: '16px', right: '16px', position: 'absolute' };

		return h(
			'aside',
			{
				ref: panelRef,
				className: `sppcfw-floating-structure-panel floating-structure-panel sppcfw-absolute sppcfw-z-40 sppcfw-w-72 sppcfw-bg-[#16202e] sppcfw-border sppcfw-border-[#4d4354] sppcfw-rounded-lg sppcfw-shadow-2xl sppcfw-overflow-hidden sppcfw-flex sppcfw-flex-col sppcfw-text-[#d9e3f6] sppcfw-select-none ${
					isDragging ? 'dragging sppcfw-opacity-95 sppcfw-shadow-[0_20px_35px_rgba(0,0,0,0.6)]' : ''
				}`,
				style: panelStyle,
			},
			// Header Drag Handle
			h(
				'div',
				{
					className: 'sppcfw-p-3 sppcfw-bg-[#121c2a] sppcfw-border-b sppcfw-border-[#374151] sppcfw-flex sppcfw-justify-between sppcfw-items-center sppcfw-cursor-move sppcfw-select-none',
					onMouseDown: handleMouseDown,
					title: 'Drag to move panel',
				},
				h(
					'h3',
					{ className: 'sppcfw-text-xs sppcfw-font-bold sppcfw-flex sppcfw-items-center sppcfw-gap-1.5 sppcfw-pointer-events-none' },
					h('span', { className: 'material-symbols-outlined sppcfw-text-sm sppcfw-text-[#9333ea]' }, 'account_tree'),
					'Structure'
				),
				h(
					'div',
					{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1' },
					h(
						'button',
						{
							type: 'button',
							className: 'sppcfw-text-xs sppcfw-text-[#cfc2d7] hover:sppcfw-text-white sppcfw-font-bold sppcfw-p-1 sppcfw-rounded hover:sppcfw-bg-[#212b39]',
							onClick: () => setIsCollapsed(!isCollapsed),
							title: isCollapsed ? 'Expand' : 'Collapse',
						},
						isCollapsed ? '□' : '–'
					),
					h(
						'button',
						{
							type: 'button',
							className: 'sppcfw-text-xs sppcfw-text-[#cfc2d7] hover:sppcfw-text-white sppcfw-font-bold sppcfw-p-1 sppcfw-rounded hover:sppcfw-bg-[#212b39]',
							onClick: closeStructure,
							title: 'Close',
						},
						'✕'
					)
				)
			),

			// Scrollable Tree Content (Dynamically resized, max-h-400)
			!isCollapsed &&
				h(
					'div',
					{
						ref: contentRef,
						className: 'sppcfw-p-2 sppcfw-overflow-y-auto custom-scrollbar sppcfw-space-y-1',
						style: { height: `${contentHeight}px`, maxHeight: '400px', minHeight: '100px' },
					},
					elements.length === 0
						? h('div', { className: 'sppcfw-text-xs sppcfw-text-[#9ca3af] sppcfw-text-center sppcfw-py-4' }, 'No elements on canvas')
						: elements.map((item, idx) =>
								h(StructureTreeNode, {
									key: item.id,
									index: idx,
									item,
									parentId: null,
									elements,
									setElements,
									selectedElementId,
									setSelectedElementId,
								})
						  )
				),

			// Bottom Vertical Resize Handle
			!isCollapsed &&
				h(
					'div',
					{
						className: 'sppcfw-h-2 sppcfw-w-full sppcfw-cursor-ns-resize sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-bg-[#121c2a] hover:sppcfw-bg-[#9333ea]/30 sppcfw-border-t sppcfw-border-[#374151]/50 sppcfw-transition-colors group',
						onMouseDown: handleResizeMouseDown,
						title: 'Drag to resize vertically (max 400px)',
					},
					h('div', { className: 'sppcfw-w-8 sppcfw-h-0.5 sppcfw-bg-gray-500/60 group-hover:sppcfw-bg-[#9333ea] sppcfw-rounded-full sppcfw-pointer-events-none' })
				)
		);
	}

	// Recursive Structure Tree Node Component
	function StructureTreeNode({ item, index, parentId, elements, setElements, selectedElementId, setSelectedElementId }) {
		const isSelected = selectedElementId === item.id;
		const hasChildren = item.children && item.children.length > 0;
		const [isCollapsed, setIsCollapsed] = useState(false);

		function handleDragStart(e) {
			e.stopPropagation();
			e.dataTransfer.setData('text/plain', 'structure_move:' + item.id);
		}

		function handleDragOver(e) {
			e.preventDefault();
			e.stopPropagation();
		}

		function handleDrop(e) {
			e.preventDefault();
			e.stopPropagation();
			const textData = e.dataTransfer.getData('text/plain');
			if (textData && textData.indexOf('structure_move:') === 0) {
				const sourceId = textData.replace('structure_move:', '');
				if (sourceId && sourceId !== item.id) {
					const targetParentId = item.type === 'container' || item.type === 'column' ? item.id : parentId;
					const targetIndex = item.type === 'container' || item.type === 'column' ? (item.children ? item.children.length : 0) : index;
					setElements(prev => moveElementInTree(prev, sourceId, targetParentId, targetIndex));
				}
			}
		}

		function getItemIcon() {
			if (item.type === 'container') return 'grid_view';
			if (item.type === 'column') return 'view_column';
			if (item.type === 'product_title') return 'title';
			if (item.type === 'product_price') return 'payments';
			if (item.type === 'product_gallery') return 'image';
			if (item.type === 'product_add_to_cart') return 'shopping_cart';
			return 'widgets';
		}

		return h(
			'div',
			{
				draggable: true,
				onDragStart: handleDragStart,
				onDragOver: handleDragOver,
				onDrop: handleDrop,
				className: 'sppcfw-select-none',
			},
			h(
				'div',
				{
					onClick: e => {
						e.stopPropagation();
						setSelectedElementId(item.id);
					},
					className: `sppcfw-flex sppcfw-items-center sppcfw-justify-between sppcfw-p-1.5 sppcfw-rounded sppcfw-cursor-pointer sppcfw-text-xs sppcfw-transition-colors ${
						isSelected ? 'sppcfw-bg-[#9333ea] sppcfw-text-white sppcfw-font-bold' : 'hover:sppcfw-bg-[#212b39] sppcfw-text-[#cfc2d7]'
					}`,
				},
				h(
					'div',
					{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-1.5 sppcfw-overflow-hidden' },
					hasChildren &&
						h(
							'button',
							{
								className: 'sppcfw-text-xs hover:sppcfw-text-white',
								onClick: e => {
									e.stopPropagation();
									setIsCollapsed(!isCollapsed);
								},
							},
							isCollapsed ? '▶' : '▼'
						),
					h('span', { className: 'material-symbols-outlined sppcfw-text-base sppcfw-text-[#ddb8ff]' }, getItemIcon()),
					h('span', { className: 'sppcfw-font-semibold sppcfw-truncate' }, item.label)
				),
				h(
					'span',
					{ className: 'sppcfw-text-[9px] font-mono sppcfw-opacity-80 sppcfw-uppercase sppcfw-px-1 sppcfw-rounded sppcfw-bg-[#091421]' },
					item.type === 'container' ? (item.settings && item.settings.width_mode === 'boxed' ? 'Boxed' : 'Full') : item.type === 'column' ? (item.settings && item.settings.flex_width ? item.settings.flex_width : 'Col') : item.type
				)
			),

			hasChildren &&
				!isCollapsed &&
				h(
					'div',
					{ className: 'sppcfw-pl-3 sppcfw-mt-1 sppcfw-border-l sppcfw-border-[#374151] sppcfw-space-y-1 sppcfw-ml-2' },
					item.children.map((child, childIdx) =>
						h(StructureTreeNode, {
							key: child.id,
							item: child,
							index: childIdx,
							parentId: item.id,
							elements,
							setElements,
							selectedElementId,
							setSelectedElementId,
						})
					)
				)
		);
	}

	// Canvas Container Renderer
	function CanvasContainerRenderer({ container, cIdx, elements, setElements, selectedElementId, setSelectedElementId, removeElement, sampleData, pageSettings, addWidgetToTarget, addColumnToContainer, duplicateColumn, openElementsTab, openStructureChooser, deviceView = 'desktop' }) {
		const isSelected = selectedElementId === container.id;
		const [isContainerDragOver, setIsContainerDragOver] = useState(false);
		const widthMode = getResponsiveProp(container.settings, 'width_mode', deviceView) === 'full' ? 'full' : 'boxed';
		const boxedWidth = getResponsiveProp(container.settings, 'boxed_width', deviceView) || '1140px';
		const isGrid = getResponsiveProp(container.settings, 'flex_direction', deviceView) === 'grid';
		const flexDir = getResponsiveProp(container.settings, 'flex_direction', deviceView) || 'row';
		const justifyContent = getResponsiveProp(container.settings, 'justify_content', deviceView) || 'flex-start';
		const alignItems = getResponsiveProp(container.settings, 'align_items', deviceView) || 'stretch';
		const flexWrap = getResponsiveProp(container.settings, 'flex_wrap', deviceView) || 'nowrap';
		const minHeight = getResponsiveProp(container.settings, 'min_height', deviceView) || '0px';
		const gridCols = getResponsiveProp(container.settings, 'grid_columns', deviceView) || '2';
		const colGap = getResponsiveProp(container.settings, 'column_gap', deviceView) || getResponsiveProp(container.settings, 'gap', deviceView) || '20px';
		const rowGap = getResponsiveProp(container.settings, 'row_gap', deviceView) || getResponsiveProp(container.settings, 'gap', deviceView) || '20px';

		function handleContainerDragStart(e) {
			e.stopPropagation();
			e.dataTransfer.setData('text/plain', 'container_reorder:' + cIdx);
		}

		function handleContainerDragOver(e) {
			e.preventDefault();
			e.stopPropagation();
			setIsContainerDragOver(true);
		}

		function handleContainerDragLeave(e) {
			e.stopPropagation();
			setIsContainerDragOver(false);
		}

		function handleContainerDrop(e) {
			e.preventDefault();
			e.stopPropagation();
			setIsContainerDragOver(false);

			const textData = e.dataTransfer.getData('text/plain');
			if (textData && textData.indexOf('container_reorder:') === 0) {
				const sourceIndex = parseInt(textData.replace('container_reorder:', ''), 10);
				if (!isNaN(sourceIndex) && sourceIndex !== cIdx) {
					setElements(prev => {
						const next = [...prev];
						const [moved] = next.splice(sourceIndex, 1);
						next.splice(cIdx, 0, moved);
						return next;
					});
				}
				return;
			}

			const jsonStr = e.dataTransfer.getData('application/json');
			if (jsonStr) {
				try {
					const data = JSON.parse(jsonStr);
					if (data && data.type) {
						let targetColId = null;
						if (container.children && container.children.length > 0) {
							targetColId = container.children[0].id;
						}
						addWidgetToTarget(data.type, data.name, data.metaKey, targetColId);
						return;
					}
				} catch (err) {}
			}
		}

		return h(
			'div',
			{
				draggable: true,
				onDragStart: handleContainerDragStart,
				onDragOver: handleContainerDragOver,
				onDragLeave: handleContainerDragLeave,
				onDrop: handleContainerDrop,
				onClick: e => {
					e.stopPropagation();
					setSelectedElementId(container.id);
				},
				className: `sppcfw-builder-container-item sppcfw-relative sppcfw-tab-group sppcfw-transition-all sppcfw-rounded-lg sppcfw-p-4 sppcfw-mb-4 ${
					isContainerDragOver ? 'sppcfw-border-2 sppcfw-border-dashed sppcfw-border-[#9333ea] sppcfw-bg-[#faf5ff] ' : isSelected ? 'sppcfw-border-2 sppcfw-border-[#9333ea] sppcfw-ring-[#9333ea]/30' : 'sppcfw-border-[#e5e7eb] hover:sppcfw-border-[#9333ea]/50'
				}`,
				style: {
					maxWidth: widthMode === 'boxed' ? boxedWidth : '100%',
					margin: '0 auto',
					width: '100%',
					minHeight: minHeight,
					backgroundColor: getResponsiveProp(container.styles, 'bg_color', deviceView) || '#ffffff',
					paddingTop: getResponsiveProp(container.styles, 'padding_top', deviceView) || '16px',
					paddingBottom: getResponsiveProp(container.styles, 'padding_bottom', deviceView) || '16px',
				},
			},

			// Toolbar Badge & Handles
			isSelected &&
				h(
					'div',
					{
						className: `sppcfw-absolute ${
							cIdx === 0 ? 'sppcfw-top-0 sppcfw-rounded-b-md' : 'sppcfw-top-[-1.6rem] sppcfw-rounded-t-md'
						} sppcfw-left-1/2 sppcfw--translate-x-1/2 sppcfw-bg-[#c084fc] sppcfw-text-white sppcfw-px-2.5 sppcfw-py-0.5 sppcfw-text-xs sppcfw-flex sppcfw-items-center sppcfw-gap-2.5 sppcfw-z-20 sppcfw-shadow-sm sppcfw-select-none sppcfw-font-bold`,
					},
					h(
						'button',
						{
							className: 'hover:sppcfw-text-black sppcfw-transition-colors sppcfw-cursor-pointer sppcfw-text-sm sppcfw-font-bold',
							onClick: e => {
								e.stopPropagation();
								if (typeof openStructureChooser === 'function') openStructureChooser();
							},
							title: 'Add Container',
						},
						'+'
					),
					h(
						'span',
						{
							className: 'sppcfw-cursor-grab active:sppcfw-cursor-grabbing sppcfw-font-extrabold sppcfw-text-[10px] sppcfw-tracking-wider hover:sppcfw-text-black sppcfw-transition-colors sppcfw-px-1 sppcfw-py-0.5 sppcfw-rounded hover:sppcfw-bg-white/20',
							draggable: true,
							onDragStart: handleContainerDragStart,
							title: 'Drag to reorder container up or down',
						},
						':::'
					),
					h(
						'button',
						{
							className: 'hover:sppcfw-text-red-200 sppcfw-transition-colors sppcfw-cursor-pointer sppcfw-font-bold sppcfw-text-xs',
							onClick: e => {
								e.stopPropagation();
								removeElement(container.id);
							},
							title: 'Delete Container',
						},
						'✕'
					)
				),

			// Column Flex / Grid Layout Wrapper
			h(
				'div',
				{
					className: isGrid ? `grid grid-cols-${gridCols}` : 'flex',
					style: {
						display: isGrid ? 'grid' : 'flex',
						flexDirection: !isGrid ? flexDir : undefined,
						justifyContent: !isGrid ? justifyContent : undefined,
						alignItems: !isGrid ? alignItems : undefined,
						flexWrap: !isGrid ? flexWrap : undefined,
						columnGap: colGap,
						rowGap: rowGap,
					},
				},
				container.children &&
					container.children.map(column =>
						h(CanvasColumnRenderer, {
							key: column.id,
							column,
							containerId: container.id,
							elements,
							setElements,
							selectedElementId,
							setSelectedElementId,
							removeElement,
							sampleData,
							pageSettings,
							addWidgetToTarget,
							addColumnToContainer,
							duplicateColumn,
							openElementsTab,
							deviceView,
						})
					)
			)
		);
	}

	// Canvas Column Renderer
	function CanvasColumnRenderer({ column, containerId, elements, setElements, selectedElementId, setSelectedElementId, removeElement, sampleData, pageSettings, addWidgetToTarget, addColumnToContainer, duplicateColumn, openElementsTab, deviceView = 'desktop' }) {
		const isSelected = selectedElementId === column.id;
		const flexWidth = getResponsiveProp(column.settings, 'flex_width', deviceView) || '100%';
		const flexDir = getResponsiveProp(column.settings, 'flex_direction', deviceView) || 'column';
		const justifyContent = getResponsiveProp(column.settings, 'justify_content', deviceView) || 'flex-start';
		const alignItems = getResponsiveProp(column.settings, 'align_items', deviceView) || 'stretch';
		const gap = getResponsiveProp(column.settings, 'gap', deviceView) || '12px';
		const minHeight = getResponsiveProp(column.settings, 'min_height', deviceView) || '120px';

		function handleColumnDrop(e) {
			e.preventDefault();
			e.stopPropagation();

			const jsonStr = e.dataTransfer.getData('application/json');
			if (jsonStr) {
				try {
					const data = JSON.parse(jsonStr);
					if (data && data.type) {
						addWidgetToTarget(data.type, data.name, data.metaKey, column.id);
						return;
					}
				} catch (err) {}
			}

			const textData = e.dataTransfer.getData('text/plain');
			if (textData && textData.indexOf('structure_move:') === 0) {
				const sourceId = textData.replace('structure_move:', '');
				if (sourceId) {
					setElements(prev => moveElementInTree(prev, sourceId, column.id, column.children ? column.children.length : 0));
				}
			}
		}

		return h(
			'div',
			{
				onClick: e => {
					e.stopPropagation();
					setSelectedElementId(column.id);
				},
				onDragOver: e => e.preventDefault(),
				onDrop: handleColumnDrop,
				className: `builder-column-item sppcfw-flex-1 sppcfw-min-w-[180px] sppcfw-border sppcfw-border-dashed sppcfw-rounded sppcfw-p-3 sppcfw-relative sppcfw-transition-all sppcfw-min-h-[120px] ${
					isSelected ? 'sppcfw-border-[#9333ea] sppcfw-bg-[#faf5ff] sppcfw-ring-2 sppcfw-ring-[#9333ea]/30' : 'sppcfw-border-[#d1d5db] hover:sppcfw-border-[#9333ea]/50 sppcfw-bg-[#f9fafb]'
				}`,
				style: {
					flex: `1 1 calc(${flexWidth} - 16px)`,
					minHeight: minHeight,
					display: 'flex',
					flexDirection: flexDir,
					justifyContent: justifyContent,
					alignItems: alignItems,
					gap: gap,
					backgroundColor: getResponsiveProp(column.styles, 'bg_color', deviceView) || 'transparent',
					borderColor: getResponsiveProp(column.styles, 'border_color', deviceView) || '#d1d5db',
					borderWidth: getResponsiveProp(column.styles, 'border_width', deviceView) || '1px',
					borderRadius: getResponsiveProp(column.styles, 'border_radius', deviceView) || '4px',
					paddingTop: getResponsiveProp(column.styles, 'padding_top', deviceView) || '12px',
					paddingRight: getResponsiveProp(column.styles, 'padding_right', deviceView) || '12px',
					paddingBottom: getResponsiveProp(column.styles, 'padding_bottom', deviceView) || '12px',
					paddingLeft: getResponsiveProp(column.styles, 'padding_left', deviceView) || '12px',
				},
			},

			isSelected &&
				h(
					'div',
					{ className: 'sppcfw-absolute sppcfw--top-3 sppcfw-right-2 sppcfw-bg-[#9333ea] sppcfw-text-white sppcfw-px-2 sppcfw-py-0.5 sppcfw-rounded sppcfw-text-[10px] font-mono sppcfw-z-20 sppcfw-shadow-md sppcfw-flex sppcfw-items-center sppcfw-gap-2 sppcfw-select-none' },
					h('span', { className: 'sppcfw-font-bold' }, column.label || 'Column'),
					h(
						'button',
						{
							className: 'hover:sppcfw-text-black sppcfw-text-xs sppcfw-font-bold sppcfw-transition-colors sppcfw-cursor-pointer sppcfw-px-1',
							onClick: e => {
								e.stopPropagation();
								if (typeof addColumnToContainer === 'function') addColumnToContainer(containerId);
							},
							title: 'Add New Column to Container',
						},
						'+'
					),
					h(
						'button',
						{
							className: 'hover:sppcfw-text-black sppcfw-text-xs sppcfw-transition-colors sppcfw-cursor-pointer sppcfw-px-0.5',
							onClick: e => {
								e.stopPropagation();
								if (typeof duplicateColumn === 'function') duplicateColumn(column.id);
							},
							title: 'Duplicate Column',
						},
						'📋'
					),
					h(
						'button',
						{
							className: 'hover:sppcfw-text-red-200 sppcfw-text-xs sppcfw-font-bold sppcfw-transition-colors sppcfw-cursor-pointer sppcfw-px-0.5',
							onClick: e => {
								e.stopPropagation();
								if (typeof removeElement === 'function') removeElement(column.id);
							},
							title: 'Delete Column',
						},
						'✕'
					)
				),

			column.children && column.children.length > 0
				? column.children.map(child =>
						h(CanvasWidgetRenderer, {
							key: child.id,
							widget: child,
							columnId: column.id,
							elements,
							setElements,
							selectedElementId,
							setSelectedElementId,
							removeElement,
							sampleData,
							pageSettings,
							deviceView,
						})
				  )
				: h(
						'div',
						{
							className: 'sppcfw-flex sppcfw-flex-col sppcfw-items-center sppcfw-justify-center sppcfw-min-h-[140px] sppcfw-text-center sppcfw-border-2 sppcfw-border-dashed sppcfw-border-[#cbd5e1] sppcfw-rounded-lg sppcfw-p-6 sppcfw-bg-white sppcfw-select-none sppcfw-cursor-pointer sppcfw-tab-group hover:sppcfw-border-[#9333ea] sppcfw-transition-all',
							onClick: e => {
								e.stopPropagation();
								if (typeof openElementsTab === 'function') openElementsTab();
							},
						},
						h(
							'button',
							{
								type: 'button',
								className: 'sppcfw-w-10 sppcfw-h-10 sppcfw-border sppcfw-border-[#cbd5e1] group-hover:sppcfw-border-[#9333ea] sppcfw-bg-white sppcfw-text-[#94a3b8] group-hover:sppcfw-text-[#9333ea] sppcfw-rounded-md sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-cursor-pointer sppcfw-shadow-sm group-hover:sppcfw-shadow sppcfw-transition-all',
								title: 'Add Element',
								onClick: e => {
									e.stopPropagation();
									if (typeof openElementsTab === 'function') openElementsTab();
								},
							},
							h('span', { className: 'material-symbols-outlined sppcfw-text-lg' }, 'add')
						),
						h('span', { className: 'sppcfw-text-xs sppcfw-font-semibold sppcfw-text-[#64748b] group-hover:sppcfw-text-[#9333ea] sppcfw-mt-2' }, 'Add Element')
				  )
		);
	}

	// Canvas Widget Renderer
	function CanvasWidgetRenderer({ widget, columnId, elements, setElements, selectedElementId, setSelectedElementId, removeElement, sampleData, pageSettings, deviceView = 'desktop' }) {
		const isSelected = selectedElementId === widget.id;

		function handleWidgetDragStart(e) {
			e.stopPropagation();
			e.dataTransfer.setData('text/plain', 'structure_move:' + widget.id);
		}

		return h(
			'div',
			{
				draggable: true,
				onDragStart: handleWidgetDragStart,
				onClick: e => {
					e.stopPropagation();
					setSelectedElementId(widget.id);
				},
				className: `widget-canvas-item sppcfw-p-3 sppcfw-rounded sppcfw-cursor-grab active:sppcfw-cursor-grabbing sppcfw-relative sppcfw-tab-group ${
					isSelected ? 'is-selected sppcfw-ring-2 sppcfw-ring-[#9333ea]' : ''
				} ${widget.advanced && widget.advanced.custom_class ? widget.advanced.custom_class : ''}`,
				style: {
					color: getResponsiveProp(widget.styles, 'text_color', deviceView) || 'inherit',
					fontFamily: getResponsiveProp(widget.styles, 'font_family', deviceView) || 'inherit',
					fontSize: getResponsiveProp(widget.styles, 'font_size', deviceView) || 'inherit',
					fontWeight: getResponsiveProp(widget.styles, 'font_weight', deviceView) || 'inherit',
					lineHeight: getResponsiveProp(widget.styles, 'line_height', deviceView) || 'inherit',
					backgroundColor: getResponsiveProp(widget.styles, 'bg_color', deviceView) || 'transparent',
					borderColor: getResponsiveProp(widget.styles, 'border_color', deviceView) || 'transparent',
					borderWidth: getResponsiveProp(widget.styles, 'border_width', deviceView) || '0px',
					borderRadius: getResponsiveProp(widget.styles, 'border_radius', deviceView) || '0px',
					paddingTop: getResponsiveProp(widget.styles, 'padding_top', deviceView) || '0px',
					paddingRight: getResponsiveProp(widget.styles, 'padding_right', deviceView) || '0px',
					paddingBottom: getResponsiveProp(widget.styles, 'padding_bottom', deviceView) || '0px',
					paddingLeft: getResponsiveProp(widget.styles, 'padding_left', deviceView) || '0px',
					marginTop: getResponsiveProp(widget.styles, 'margin_top', deviceView) || '0px',
					marginRight: getResponsiveProp(widget.styles, 'margin_right', deviceView) || '0px',
					marginBottom: getResponsiveProp(widget.styles, 'margin_bottom', deviceView) || '0px',
					marginLeft: getResponsiveProp(widget.styles, 'margin_left', deviceView) || '0px',
					textAlign: getResponsiveProp(widget.settings, 'alignment', deviceView) || 'left',
				},
			},

			isSelected &&
				h(
					'div',
					{ className: 'sppcfw-absolute sppcfw--top-3 sppcfw-right-2 sppcfw-bg-[#9333ea] sppcfw-text-white sppcfw-px-2 sppcfw-py-0.5 sppcfw-rounded sppcfw-text-[10px] sppcfw-flex sppcfw-items-center sppcfw-gap-1 sppcfw-z-20 sppcfw-shadow font-mono sppcfw-select-none' },
					h('span', null, widget.label),
					h(
						'button',
						{
							className: 'hover:sppcfw-text-red-300 sppcfw-font-bold sppcfw-ml-1',
							onClick: e => {
								e.stopPropagation();
								removeElement(widget.id);
							},
						},
						'✕'
					)
				),

			renderLiveWidgetContent(widget, sampleData, pageSettings)
		);
	}

	// Live Content Rendering for Canvas
	function renderLiveWidgetContent(el, sample, pageSettings) {
		const staticFallback = typeof CANVAS_STATIC_DATA !== 'undefined' ? CANVAS_STATIC_DATA : {};
		const safeSample = sample || staticFallback;

		switch (el.type) {
			case 'product_title':
				const titleTag = (el.settings && el.settings.html_tag) || 'h1';
				const titleText = (el.settings && el.settings.title_override) || safeSample.title || staticFallback.title || 'Product Title';
				return h(titleTag, { className: 'sppcfw-text-2xl sppcfw-font-bold sppcfw-text-[#111827]' }, titleText);
			case 'product_price':
				const displayPrice = safeSample.price || staticFallback.price || '$49.99';
				const isOnSale = safeSample.on_sale || (safeSample.sale_price && safeSample.regular_price && safeSample.sale_price !== safeSample.regular_price);
				return h(
					'div',
					{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-3' },
					h('span', { className: 'sppcfw-text-2xl sppcfw-font-extrabold sppcfw-text-[#9333ea]', dangerouslySetInnerHTML: { __html: displayPrice } }),
					isOnSale && h('span', { className: 'sppcfw-bg-[#ef4444] sppcfw-text-white sppcfw-text-xs sppcfw-px-2 sppcfw-py-1 sppcfw-rounded sppcfw-font-bold sppcfw-uppercase' }, 'Sale')
				);
			case 'image':
			case 'product_gallery':
				const imgStyles = el.styles || {};
				const imgSettings = el.settings || {};
				const imgWidth = imgStyles.width || '100%';
				const imgMaxWidth = imgStyles.max_width || '100%';
				const imgHeight = imgStyles.height || 'auto';
				const imgOpacity = imgStyles.opacity !== undefined ? imgStyles.opacity : '1';
				const alignVal = imgStyles.alignment || 'center';
				const flexAlign = alignVal === 'left' ? 'justify-start' : alignVal === 'right' ? 'justify-end' : 'justify-center';

				const radTop = imgStyles.border_radius_top || imgStyles.border_radius || '0px';
				const radRight = imgStyles.border_radius_right || imgStyles.border_radius || '0px';
				const radBottom = imgStyles.border_radius_bottom || imgStyles.border_radius || '0px';
				const radLeft = imgStyles.border_radius_left || imgStyles.border_radius || '0px';
				const borderRadiusCss = `${radTop} ${radRight} ${radBottom} ${radLeft}`;

				const customImgStyle = {
					width: imgWidth,
					maxWidth: imgMaxWidth,
					height: imgHeight,
					opacity: parseFloat(imgOpacity),
					borderRadius: borderRadiusCss,
					borderStyle: imgStyles.border_type && imgStyles.border_type !== 'Default' ? imgStyles.border_type.toLowerCase() : 'none',
					objectFit: 'contain',
				};

				const activeImgSrc = imgSettings.custom_image_url || safeSample.image_url || staticFallback.image_url || '';

				return h(
					'div',
					{ className: `sppcfw-w-full sppcfw-rounded sppcfw-overflow-hidden sppcfw-text-center sppcfw-flex ${flexAlign}` },
					h('img', {
						src: activeImgSrc,
						alt: safeSample.title || 'Product Image',
						style: customImgStyle,
						className: 'sppcfw-max-h-[450px] sppcfw-object-contain sppcfw-transition-all sppcfw-shadow-sm'
					})
				);
			case 'product_add_to_cart':
				const btnLabel = (el.settings && el.settings.button_text) || 'Add to cart';
				return h(
					'div',
					{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-3' },
					h('input', { type: 'number', defaultValue: 1, min: 1, className: 'sppcfw-w-16 sppcfw-p-2 sppcfw-border sppcfw-border-[#d1d5db] sppcfw-rounded sppcfw-text-center sppcfw-font-bold sppcfw-text-[#111827]' }),
					h('button', { className: 'sppcfw-px-6 sppcfw-py-2.5 sppcfw-bg-[#9333ea] sppcfw-text-white sppcfw-rounded sppcfw-font-bold sppcfw-shadow' }, btnLabel)
				);
			case 'product_rating':
				const rCount = safeSample.rating_count !== undefined ? safeSample.rating_count : 5;
				return h(
					'div',
					{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2 sppcfw-text-[#f59e0b]' },
					h('span', { className: 'sppcfw-text-lg' }, '★★★★★'),
					h('span', { className: 'sppcfw-text-xs sppcfw-text-[#6b7280]' }, `(${rCount} reviews)`)
				);
			case 'product_short_desc':
				return h('p', { className: 'sppcfw-text-sm sppcfw-text-[#4b5563]' }, (el.settings && el.settings.custom_desc) || safeSample.short_description || staticFallback.short_description);
			case 'product_description':
				return h(
					'div',
					{ className: 'sppcfw-border sppcfw-border-[#e5e7eb] sppcfw-rounded sppcfw-p-4 sppcfw-bg-[#f9fafb]' },
					h('h3', { className: 'sppcfw-font-bold sppcfw-border-b sppcfw-pb-2 sppcfw-mb-2 sppcfw-text-[#111827]' }, 'Description'),
					h('p', { className: 'sppcfw-text-sm sppcfw-text-[#4b5563]' }, (el.settings && el.settings.custom_desc) || safeSample.description || staticFallback.description)
				);
			case 'product_meta':
				return h(
					'div',
					{ className: 'sppcfw-text-xs sppcfw-text-[#6b7280] sppcfw-space-y-1' },
					h('div', null, h('strong', null, 'SKU: '), safeSample.sku || staticFallback.sku || 'SAMPLE-SKU-123'),
					h('div', null, h('strong', null, 'Category: '), safeSample.categories || staticFallback.categories || 'Clothing'),
					safeSample.tags && h('div', null, h('strong', null, 'Tags: '), safeSample.tags || staticFallback.tags)
				);
			case 'product_meta_item':
				return h(
					'div',
					{ className: 'sppcfw-p-2.5 sppcfw-bg-[#f3f4f6] sppcfw-rounded sppcfw-border sppcfw-border-[#e5e7eb] sppcfw-text-sm sppcfw-flex sppcfw-items-center sppcfw-justify-between' },
					h('span', { className: 'sppcfw-font-semibold sppcfw-text-[#111827]' }, el.label),
					h('span', { className: 'sppcfw-text-[#4b5563] font-mono sppcfw-text-xs' }, el.metaKey || 'Meta Field')
				);
			case 'custom_message':
				return h(
					'div',
					{ className: 'sppcfw-p-3 sppcfw-bg-[#faf5ff] sppcfw-border-l-4 sppcfw-border-[#9333ea] sppcfw-rounded-r sppcfw-text-xs sppcfw-text-[#7e22ce] sppcfw-font-medium' },
					el.settings && el.settings.custom_message ? el.settings.custom_message : '✨ Limited Offer: Free Shipping on orders over $50!'
				);
			case 'plus_minus_buttons':
				return h(
					'div',
					{ className: 'sppcfw-inline-flex sppcfw-items-center sppcfw-border sppcfw-border-[#d1d5db] sppcfw-rounded sppcfw-overflow-hidden' },
					h('button', { className: 'sppcfw-px-3 sppcfw-py-1 sppcfw-bg-[#f3f4f6] sppcfw-font-bold sppcfw-text-[#111827]' }, '-'),
					h('span', { className: 'sppcfw-px-4 sppcfw-py-1 sppcfw-text-sm sppcfw-font-bold sppcfw-text-[#111827]' }, '1'),
					h('button', { className: 'sppcfw-px-3 sppcfw-py-1 sppcfw-bg-[#f3f4f6] sppcfw-font-bold sppcfw-text-[#111827]' }, '+')
				);
			default:
				return h('div', { className: 'sppcfw-p-3 sppcfw-border sppcfw-border-dashed sppcfw-text-xs sppcfw-text-[#6b7280]' }, el.label);
		}
	}

	// 6. Display Conditions Modal Component
	function DisplayConditionsModal({ displayConditions, setDisplayConditions, categories, products, closeModal, saveTemplate }) {
		return h(
			'div',
			{ className: 'sppcfw-fixed sppcfw-inset-0 sppcfw-bg-black/70 sppcfw-backdrop-blur-sm sppcfw-z-50 sppcfw-flex sppcfw-items-center sppcfw-justify-center sppcfw-p-4' },
			h(
				'div',
				{ className: 'sppcfw-bg-[#16202e] sppcfw-border sppcfw-border-[#4d4354] sppcfw-rounded-lg sppcfw-w-full sppcfw-max-w-lg sppcfw-shadow-2xl sppcfw-p-6 sppcfw-text-[#d9e3f6]' },
				h(
					'div',
					{ className: 'sppcfw-flex sppcfw-justify-between sppcfw-items-center sppcfw-border-b sppcfw-border-[#374151] sppcfw-pb-3 sppcfw-mb-4' },
					h('h3', { className: 'sppcfw-text-lg sppcfw-font-bold sppcfw-text-white sppcfw-flex sppcfw-items-center sppcfw-gap-2' }, h('span', { className: 'material-symbols-outlined sppcfw-text-[#9333ea]' }, 'tune'), 'Publish Display Conditions'),
					h('button', { className: 'sppcfw-text-[#cfc2d7] hover:sppcfw-text-white sppcfw-font-bold', onClick: closeModal }, '✕')
				),

				h(
					'div',
					{ className: 'sppcfw-space-y-4 sppcfw-mb-6' },
					h('p', { className: 'sppcfw-text-xs sppcfw-text-[#cfc2d7]' }, 'Choose where your single product page builder template will be applied:'),
					h(
						'div',
						{ className: 'sppcfw-space-y-3' },
						h(
							'label',
							{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2 sppcfw-text-sm sppcfw-cursor-pointer' },
							h('input', {
								type: 'radio',
								name: 'condition_scope',
								value: 'entire',
								checked: displayConditions.scope === 'entire',
								onChange: () => setDisplayConditions({ ...displayConditions, scope: 'entire' }),
							}),
							h('span', { className: 'sppcfw-font-semibold' }, 'Entire Website'),
							h('span', { className: 'sppcfw-text-xs sppcfw-text-[#9ca3af]' }, '(All Single Product Pages)')
						),
						h(
							'label',
							{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2 sppcfw-text-sm sppcfw-cursor-pointer' },
							h('input', {
								type: 'radio',
								name: 'condition_scope',
								value: 'category',
								checked: displayConditions.scope === 'category',
								onChange: () => setDisplayConditions({ ...displayConditions, scope: 'category' }),
							}),
							h('span', { className: 'sppcfw-font-semibold' }, 'Specific Category'),
							h('span', { className: 'sppcfw-text-xs sppcfw-text-[#9ca3af]' }, '(Category-Based Scope)')
						),
						h(
							'label',
							{ className: 'sppcfw-flex sppcfw-items-center sppcfw-gap-2 sppcfw-text-sm sppcfw-cursor-pointer' },
							h('input', {
								type: 'radio',
								name: 'condition_scope',
								value: 'product',
								checked: displayConditions.scope === 'product',
								onChange: () => setDisplayConditions({ ...displayConditions, scope: 'product' }),
							}),
							h('span', { className: 'sppcfw-font-semibold' }, 'Specific Product / Separate Page'),
							h('span', { className: 'sppcfw-text-xs sppcfw-text-[#9ca3af]' }, '(Product-Based Scope)')
						)
					)
				),

				h(
					'div',
					{ className: 'sppcfw-flex sppcfw-justify-end sppcfw-gap-3 sppcfw-pt-3 sppcfw-border-t sppcfw-border-[#374151]' },
					h('button', { className: 'sppcfw-px-4 sppcfw-py-2 sppcfw-bg-[#121c2a] hover:sppcfw-bg-[#212b39] sppcfw-text-[#d9e3f6] sppcfw-rounded sppcfw-text-xs sppcfw-font-semibold', onClick: closeModal }, 'Cancel'),
					h(
						'button',
						{
							className: 'sppcfw-px-5 sppcfw-py-2 sppcfw-bg-[#9333ea] hover:sppcfw-bg-[#7e22ce] sppcfw-text-white sppcfw-rounded sppcfw-text-xs sppcfw-font-bold sppcfw-shadow',
							onClick: () => {
								closeModal();
								saveTemplate();
							},
						},
						'Save & Publish'
					)
				)
			)
		);
	}

	// Mount React App
	document.addEventListener('DOMContentLoaded', function () {
		const rootEl = document.getElementById('sppcfw-builder-root');
		if (rootEl && window.wp && window.wp.element) {
			window.wp.element.render(h(BuilderApp, null), rootEl);
		}
	});
})();
