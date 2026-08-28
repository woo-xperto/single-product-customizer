<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div>
    <?php
     global $product;

     if (! $product) {
         $referer = $_SERVER['HTTP_REFERER'] ?? '';
         $referer = esc_url_raw($referer);

         // Get post ID from URL
         $post_id = url_to_postid($referer);
         if (! $post_id) {
             return false;
         }
         $product = wc_get_product($post_id);
     }

    if ($product->is_type('variable')) {
        woocommerce_variable_add_to_cart();
    } else if ($product->is_type('grouped')) {
        woocommerce_grouped_add_to_cart();
    }
    ?>
</div>