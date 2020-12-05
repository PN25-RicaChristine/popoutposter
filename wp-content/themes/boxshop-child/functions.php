<?php 
function boxshop_child_register_scripts(){
    $parent_style = 'boxshop-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css', array('boxshop-reset'), boxshop_get_theme_version() );
    wp_enqueue_style( 'boxshop-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style )
    );
}

add_action( 'wp_enqueue_scripts', 'boxshop_child_register_scripts' );
function cw_change_product_price_display( $price ) {
    // $price .= ' <a href="https://popoutposter.com/shipping-rates/" target="_blank" style="color: #ffffff!important;text-decoration: underline;">+P&P</a>';
    return $price;
}
add_filter( 'woocommerce_get_price_html', 'cw_change_product_price_display' );
add_filter( 'woocommerce_cart_item_price', 'cw_change_product_price_display' );

add_filter( 'woocommerce_get_checkout_url', 'my_change_checkout_url', 30 );

function my_change_checkout_url( $url ) {
   $url = "https://popoutposter.com/checkout/";
   return $url;
}

add_filter( 'woocommerce_order_button_text', 'woo_custom_order_button_text' ); 

function woo_custom_order_button_text() {
    return __( 'BUY NOW', 'woocommerce' ); 
}

add_filter( 'woocommerce_cart_shipping_method_full_label', 'bbloomer_remove_shipping_label', 9999, 2 );
   
function bbloomer_remove_shipping_label( $label, $method ) {
    $new_label = preg_replace( '/^.+:/', '', $label );
    return $new_label;
}

add_filter( 'woocommerce_cart_total', 'custom_total_message', 10, 1 );
function custom_total_message( $price ) {
    if( is_checkout() )
        $price .= __('</br><i style="color: #555 !important; font-size: 11px;">(Inclusive of VAT)</i>');

    return $price;
}

add_action('wp_head', 'wh_alter_pro_cat_desc', 5);

function wh_alter_pro_cat_desc()
{
    if (is_product_category())
    {
        $term = get_queried_object();
        $productCatMetaDesc = get_term_meta($term->term_id, 'wh_meta_desc', true);
        if (empty($productCatMetaDesc))
            return;

        ?>
        <meta name="description" content="<?= $productCatMetaDesc; ?>">
        <?php
    }
}