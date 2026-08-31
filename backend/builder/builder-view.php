<?php
/**
 * Single Product Page Builder Admin Screen View
 *
 * @package Single_Product_Customizer
 */

if (!defined('ABSPATH')) {
	exit;
}
?>
<script src="<?php echo esc_url(SPPCFW_DIR_URL . 'backend/assets/js/tailwindcss.js'); ?>"></script>
<script id="sppcfw-tailwind-config">
	tailwind.config = {
		prefix: "sppcfw-",
		darkMode: "class",
		theme: {
			extend: {
				colors: {
					"tertiary-container": "#9a5c00",
					"inverse-surface": "#d9e3f6",
					"on-secondary-fixed": "#001e31",
					"surface-variant": "#2b3544",
					"surface-dim": "#091421",
					"on-error": "#690005",
					"background": "#091421",
					"surface-tint": "#ddb8ff",
					"on-primary-container": "#f6e6ff",
					"surface-container-highest": "#2b3544",
					"on-primary": "#490080",
					"tertiary-fixed-dim": "#ffb86b",
					"tertiary": "#ffb86b",
					"secondary": "#92ccff",
					"primary-container": "#9333ea",
					"on-background": "#d9e3f6",
					"surface-container": "#16202e",
					"on-tertiary-fixed-variant": "#683d00",
					"primary-fixed-dim": "#ddb8ff",
					"on-tertiary-container": "#ffe8d4",
					"on-secondary": "#003351",
					"on-tertiary-fixed": "#2c1700",
					"secondary-fixed-dim": "#92ccff",
					"on-secondary-fixed-variant": "#6800b4",
					"surface-container-high": "#212b39",
					"inverse-on-surface": "#27313f",
					"primary-fixed": "#f0dbff",
					"secondary-fixed": "#cce5ff",
					"primary": "#ddb8ff",
					"surface-bright": "#303a48",
					"tertiary-fixed": "#ffdcbc",
					"inverse-primary": "#861fdd",
					"on-surface-variant": "#cfc2d7",
					"surface-container-lowest": "#050f1c",
					"outline": "#988ca0",
					"secondary-container": "#3a98d7",
					"surface-container-low": "#121c2a",
					"on-secondary-container": "#002c46",
					"on-primary-fixed": "#2c0051",
					"outline-variant": "#4d4354",
					"surface": "#091421",
					"error": "#ffb4ab",
					"on-tertiary": "#492900",
					"error-container": "#93000a",
					"on-secondary-fixed-variant": "#004b73",
					"on-surface": "#d9e3f6",
					"on-error-container": "#ffdad6"
				},
				borderRadius: {
					DEFAULT: "0.125rem",
					lg: "0.25rem",
					xl: "0.5rem",
					full: "0.75rem"
				},
				spacing: {
					gutter: "12px",
					"panel-width": "320px",
					"touch-target": "32px",
					"sidebar-width": "64px",
					"control-gap": "8px"
				},
				fontFamily: {
					"label-caps": ["Inter", "sans-serif"],
					"data-mono": ["JetBrains Mono", "monospace"],
					"headline-sm": ["Geist", "sans-serif"],
					"body-md": ["Inter", "sans-serif"]
				},
				fontSize: {
					"label-caps": ["11px", { lineHeight: "16px", letterSpacing: "0.05em", fontWeight: "700" }],
					"data-mono": ["12px", { lineHeight: "16px", fontWeight: "400" }],
					"headline-sm": ["18px", { lineHeight: "24px", fontWeight: "600" }],
					"body-md": ["14px", { lineHeight: "20px", fontWeight: "400" }]
				}
			}
		}
	};
</script>
<style>
	#wpcontent, #wpbody-content {
		padding: 0 !important;
		margin: 0 !important;
	}
	#wpadminbar, #adminmenumain, #wpfooter {
		display: none !important;
	}
	#wpcontent {
		margin-left: 0 !important;
	}
	html.wp-toolbar {
		padding-top: 0 !important;
	}
	body {
		overflow: hidden !important;
	}
	.notice, .updated, .error, .is-dismissible, .notice-warning, .notice-info, .notice-error, .notice-success, #wpbody-content > div.notice, #wpbody-content > div.updated, .sppcfw_sreview_notices, #sppcfw-review-notice, .sales-campaign-notice, .sppcfw-setup-notice, div[class*="notice"], div[id*="notice"] {
		display: none !important;
	}
</style>
<div id="sppcfw-builder-root" class="dark sppcfw-h-screen sppcfw-w-screen sppcfw-overflow-hidden sppcfw-text-on-background"></div>
