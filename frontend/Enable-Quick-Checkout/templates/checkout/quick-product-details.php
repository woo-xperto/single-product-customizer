<?php
if (!defined('ABSPATH')) {
    exit;
}

// Product details (title, review, short description) are only available when Pro license is active
if (!function_exists('sppcfw_is_pro_active') || !sppcfw_is_pro_active()) {
    return;
}

$sppcfw_show_product_title               = (bool) get_option('sppcfw_show_product_title', 1);
$sppcfw_show_review                      = (bool) get_option('sppcfw_show_review', 1);
$sppcfw_show_product_short_description = (bool) get_option('sppcfw_show_short_description', 1);

$sppcfw_show_product_title               = apply_filters('sppcfw_show_product_title', $sppcfw_show_product_title);
$sppcfw_show_review                      = apply_filters('sppcfw_show_review', $sppcfw_show_review);
$sppcfw_show_product_short_description = apply_filters('sppcfw_show_product_short_description', $sppcfw_show_product_short_description);

if (!($sppcfw_show_product_title || $sppcfw_show_review || $sppcfw_show_product_short_description)) {
    return;
}
?>

<div class="sppcfw-product-details">
    <?php if ($sppcfw_show_product_title): ?>
        <h1 class="sppcfw-product-title"><?php the_title(); ?></h1>
    <?php endif; ?>

    <?php if ($sppcfw_show_review): ?>
        <div class="sppcfw-product-review">
            <?php woocommerce_template_single_rating(); ?>
        </div>
    <?php endif; ?>

    <?php if ($sppcfw_show_product_short_description): ?>
        <div class="sppcfw-product-short-description">
            <?php woocommerce_template_single_excerpt(); ?>
        </div>
    <?php endif; ?>
</div>
