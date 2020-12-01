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
    $price .= ' <a href="https://popoutposter.com/shipping-rates/" target="_blank" style="color: #ffffff!important;text-decoration: underline;">Post & Package</a>';
    return $price;
}
add_filter( 'woocommerce_get_price_html', 'cw_change_product_price_display' );
add_filter( 'woocommerce_cart_item_price', 'cw_change_product_price_display' );
