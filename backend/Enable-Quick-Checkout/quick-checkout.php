<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Form submission is handled via AJAX (see ajax-handlers.php)

// Define available templates
$sppcfw_template_files = array(
    'template-1' => __('Template 1 - Modern Horizontal', 'single-product-customizer'),
    'template-2' => __('Template 2 - Classic Vertical', 'single-product-customizer'),
);

$sppcfw_theme = wp_get_theme();
$sppcfw_is_block_theme = $sppcfw_theme->exists() &&
    method_exists($sppcfw_theme, 'is_block_theme') &&
    $sppcfw_theme->is_block_theme();

// Get current options (block themes force Quick Checkout off on init priority 11 before this admin view loads).
$sppcfw_enable_quick_checkout = (int) get_option('sppcfw_enable_quick_checkout', 0);
$sppcfw_enable_builder        = (int) get_option('sppcfw_enable_single_product_builder', 0);
$is_builder_active            = !empty($sppcfw_enable_builder);
$sppcfw_current_template      = get_option('sppcfw_enable_qc', 'template-1');

if (!sppcfw_is_pro_active()) {
    // Force template-1 in admin when Pro is inactive
    $sppcfw_current_template = 'template-1';
}

// Template / extra rows when Quick Checkout is on and builder is not active.
$sppcfw_show_template_selector = !empty($sppcfw_enable_quick_checkout) && !$is_builder_active;
$sppcfw_qc_disabled_attr = '';
?>

<div>
    <h1 class="sppcfw-heading-inline"><?php esc_html_e('Enable Quick Checkout', 'single-product-customizer'); ?></h1>
    <hr class="sppcfw-header-devider">
    <div class="sppcfw-quick-checkout-settings">
        <?php if ($is_builder_active) : ?>
            <div class="sppcfw_builder_notice_badge">
                <span class="dashicons dashicons-info"></span>
                <span>
                    <?php
                    $builder_url = admin_url('admin.php?page=sppcfw-single-page-builder');
                    printf(
                        /* translators: %s: Link to Single Page Builder Options */
                        esc_html__('Please checkout in %s', 'single-product-customizer'),
                        '<a href="' . esc_url($builder_url) . '"><strong>' . esc_html__('single page builder Options', 'single-product-customizer') . '</strong></a>'
                    );
                    ?>
                </span>
            </div>
        <?php endif; ?>
        <form method="post" action="">
            <?php wp_nonce_field('sppcfw_qc_nonce', 'nonce'); ?>

            <div class="sppcfw-quick-checkout-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="sppcfw_enable_quick_checkout" class="<?php echo $is_builder_active ? 'sppcfw-is-disabled' : ''; ?>" title="<?php echo $is_builder_active ? esc_attr__('Single Page Builder is active', 'single-product-customizer') : ''; ?>">
                                <?php esc_html_e('Enable Quick Checkout', 'single-product-customizer'); ?>
                                <input type="checkbox" name="sppcfw_enable_quick_checkout" id="sppcfw_enable_quick_checkout" value="1" class="sppcfw-enable-quick-checkout-toggle" 
                                <?php checked(!empty($sppcfw_enable_quick_checkout) && !$is_builder_active, 1); ?> 
                                <?php disabled($is_builder_active, true); ?> />
                            </label>
                        </th>
                        <td>
                            <p class="description"><?php esc_html_e('Enable Quick Checkout feature for streamlined product purchase experience.', 'single-product-customizer'); ?></p>
                            <p class="description" style="color: #0073aa; font-weight: 500;">
                                <strong>ℹ️ <?php esc_html_e('Note:', 'single-product-customizer'); ?></strong>
                                <?php esc_html_e('Enabling this will automatically enable "AJAX add to cart buttons on archives" in WooCommerce settings. Disabling this will automatically disable that setting as well.', 'single-product-customizer'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr class="sppcfw_product_options_row" style="<?php echo !$sppcfw_show_template_selector ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <div class="sppcfw_product_options">
                                <label class="sppcfw_show_product_title" for="sppcfw_show_product_title_cb">
                                    <?php esc_html_e('Show Product Title', 'single-product-customizer'); ?>
                                </label>
                                <label class="sppcfw_show_review" for="sppcfw_show_review_cb">
                                    <?php esc_html_e('Show Review', 'single-product-customizer'); ?>
                                </label>
                                <label class="sppcfw_show_short_description" for="sppcfw_show_short_description_cb">
                                    <?php esc_html_e('Show Short Description', 'single-product-customizer'); ?>
                                </label>
                            </div>
                        </th>
                        <td>
                            <div class="sppcfw_product_checkboxes">
                                <?php if (sppcfw_is_pro_active()): ?>
                                    <p class="sppcfw_show_product_title">
                                        <input type="checkbox" name="sppcfw_show_product_title" id="sppcfw_show_product_title_cb" value="1" <?php checked((int) get_option('sppcfw_show_product_title', 1), 1); ?> />
                                    </p>
                                    <p class="sppcfw_show_review">
                                        <input type="checkbox" name="sppcfw_show_review" id="sppcfw_show_review_cb" value="1" <?php checked((int) get_option('sppcfw_show_review', 1), 1); ?> />
                                    </p>
                                    <p class="sppcfw_show_short_description">
                                        <input type="checkbox" name="sppcfw_show_short_description" id="sppcfw_show_short_description_cb" value="1" <?php checked((int) get_option('sppcfw_show_short_description', 1), 1); ?> />
                                    </p>
                                <?php else: ?>
                                    <p class="sppcfw_show_product_title"><?php esc_html_e('Pro Features', 'single-product-customizer'); ?></p>
                                    <p class="sppcfw_show_review"><?php esc_html_e('Pro Features', 'single-product-customizer'); ?></p>
                                    <p class="sppcfw_show_short_description"><?php esc_html_e('Pro Features', 'single-product-customizer'); ?></p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="sppcfw_quick_checkout_template_row" style="<?php echo !$sppcfw_show_template_selector ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="sppcfw_enable_qc"><?php esc_html_e('Select Quick Checkout Template', 'single-product-customizer'); ?></label>
                        </th>
                        <td>
                            <div class="sppcfw-template-select-container">
                                <div class="sppcfw-template-left">
                                    <div class="sppcfw-fixed-template">
                                        <strong><?php esc_html_e('Selected template:', 'single-product-customizer'); ?></strong>
                                        <span><?php echo esc_html(isset($sppcfw_template_files[$sppcfw_current_template]) ? $sppcfw_template_files[$sppcfw_current_template] : $sppcfw_template_files['template-1']); ?></span>
                                    </div>

                                    <input type="hidden" name="sppcfw_enable_qc" id="sppcfw_enable_qc" value="<?php echo esc_attr($sppcfw_current_template); ?>" />

                                    <div class="sppcfw-template-image-selector sppcfw-template-preview-picker">
                                         <?php foreach ($sppcfw_template_files as $sppcfw_value => $sppcfw_label): 
                                            $sppcfw_is_pro_active = sppcfw_is_pro_active();
                                            $sppcfw_img_name = ($sppcfw_value === 'template-2') ? ($sppcfw_is_pro_active ? 'template-2-pro' : 'template-2-free') : $sppcfw_value;
                                        ?>
                                            <div class="sppcfw-template-item <?php echo $sppcfw_value === $sppcfw_current_template ? 'is-active' : ''; ?>"
                                                role="button"
                                                tabindex="0"
                                                data-template="<?php echo esc_attr($sppcfw_value); ?>">
                                                <div class="sppcfw-template-thumb">
                                                    <img src="<?php echo esc_url( plugin_dir_url(__FILE__) . 'assets/img/' . $sppcfw_img_name . '.png' ); ?>" alt="<?php echo esc_attr($sppcfw_label); ?>">
                                                </div>
                                                <span class="sppcfw-template-title"><?php echo esc_html($sppcfw_label); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="sppcfw-selected-template-preview">
                                    <div class="sppcfw-select-template-thumb">
                                        <?php 
                                        $sppcfw_preview_img = ($sppcfw_current_template === 'template-2') ? (sppcfw_is_pro_active() ? 'template-2-pro' : 'template-2-free') : $sppcfw_current_template;
                                        ?>
                                        <img id="sppcfw-selected-template-preview-img"
                                            src="<?php echo esc_url( plugin_dir_url(__FILE__) . 'assets/img/' . $sppcfw_preview_img . '.png' ); ?>"
                                            alt="<?php echo esc_attr(isset($sppcfw_template_files[$sppcfw_current_template]) ? $sppcfw_template_files[$sppcfw_current_template] : $sppcfw_template_files['template-1']); ?>">
                                    </div>
                                </div>
                            </div>
                            <p class="description"><?php esc_html_e('Select a template to customize the Quick Checkout feature on product pages.', 'single-product-customizer'); ?></p>
                        </td>
                    </tr>

                </table>
            </div>

            <div style="margin-bottom: 0px;">
                <?php
                submit_button(
                    null,
                    'primary',
                    'submit_sppcfw_quick_checkout',
                    true
                );
                ?>
            </div>
        </form>
    </div>
</div>