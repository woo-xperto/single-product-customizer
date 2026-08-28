<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div>
    <?php
     global $product;

     $sppcfw_product = $product;

     if (! $sppcfw_product) {
         $sppcfw_referer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '';

         // Get post ID from URL
         $sppcfw_post_id = url_to_postid($sppcfw_referer);
         if (! $sppcfw_post_id) {
             return false;
         }
         $sppcfw_product = wc_get_product($sppcfw_post_id);
         $product        = $sppcfw_product; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited
     }

    if ($sppcfw_product && is_a($sppcfw_product, 'WC_Product')) {
        if ($sppcfw_product->is_type('variable')) {
            woocommerce_variable_add_to_cart();
        } else if ($sppcfw_product->is_type('grouped')) {
            woocommerce_grouped_add_to_cart();
        }
    }
    ?>
</div>