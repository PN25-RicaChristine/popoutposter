<?php
/*
Plugin Name: iDevAffiliate WooCommerce Integration
Plugin URI: http://www.idevdirect.com/
Description: WooCommerce / iDevAffiliate Integration
Version: 19.8
Author: iDevDirect.com LLC
Author URI: http://www.idevdirect.com/
*/

if(!defined('WPINC')) {
    die();
}

if(!class_exists('iDevAffiliate_WooCommerce')):
    class iDevAffiliate_WooCommerce {
        private static $instance;
        private $dir, $url;
        const DS = DIRECTORY_SEPARATOR;
        public static function getInstance(){
            if(self::$instance==null) {
                self::$instance = new self;
                self::$instance->dir = dirname(__FILE__);
                self::$instance->url = WP_PLUGIN_URL . DIRECTORY_SEPARATOR . basename(self::$instance->dir);
                self::$instance->actions();
            }

            return self::$instance;
        }

        private function __construct() {
            ;
        }
        /**
         * call all actions/filters here
         */
        private function actions() {

            //add_action('woocommerce_payment_complete', array($this,'trackingOrder'), 10, 1);
            add_action('woocommerce_order_status_completed', array($this,'trackingOrder'), 10, 1);

            //add ip address
            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'store_ip') );

            if( is_admin() ) {
                
                add_action( 'plugins_loaded', array( $this, 'pluginsLoaded' ) );
                
                add_action( 'admin_init', array($this,'checkWooCommerce') );
                
            }
        }

        public function store_ip( $order_id ) {

            $exist = get_post_meta($order_id, 'idevaffiliate_woosub_ip', true);

            if ( $exist != '' ) {
                return;
            }

            $ip = getenv( 'HTTP_CLIENT_IP' ) ?: getenv( 'HTTP_X_FORWARDED_FOR' ) ?:
                getenv( 'HTTP_X_FORWARDED' ) ?: getenv( 'HTTP_FORWARDED_FOR' ) ?:
                    getenv( 'HTTP_FORWARDED' ) ?: getenv( 'REMOTE_ADDR' );

            update_post_meta( $order_id, 'idevaffiliate_woosub_ip', $ip );
        }
        
        public function pluginsLoaded() {
            // Checks if WooCommerce is installed.
            if ( class_exists( 'WC_Integration' ) ) {
                // Include our integration class.
                include_once $this->dir . self::DS .  'includes' . self::DS . 'class-wc-integration-idevaffiliate.php';
                // Register the integration.
                add_filter( 'woocommerce_integrations', array( $this, 'addIntegration' ) );
            } else {
                    // throw an admin error if you like
            }
        }
        
        public function checkWooCommerce() {
            $plugin = plugin_basename( __FILE__ );
            $plugin_data = get_plugin_data( __FILE__, false );

            if ( !class_exists( 'WC_Integration' ) ) {
                if( is_plugin_active($plugin) ) {
                    deactivate_plugins( $plugin );
                }
            }
        }

        public function addIntegration($integrations) {
            $integrations[] = 'WC_Integration_idevAffiliate_Integration';
            return $integrations;
        }

        public function trackingOrder( $order_id ) {
            
            global $wpdb;

            $options = get_option('woocommerce_integration-idevaffiliate_settings');
            $alert_url = $options['idevaffiliate_url'];

            // Order Details
            $order = new WC_Order( $order_id );
            $order_number = ltrim( $order->get_order_number(), '#' );
            
            $order_total = $order->get_total();
            $shipping = $order->order_shipping;
            
            $taxs = $order->get_tax_totals();
            $tax_total = 0;
            if( is_array($taxs) && !empty($taxs) ) {
                foreach($taxs as $code => $tax) {
                    $tax_total += wc_round_tax_total($tax->amount);
                }
            }
            
            $total = $order_total - $shipping - $tax_total;
            
            //check for idev woocommerce subscription plugins
            if(class_exists('iDevAffiliate_WooCommerce_Subscription')) {
                if(WC_Subscriptions_Order::order_contains_subscription( $order )) {
                    return;
                }
            } 

            // Get Coupon Code            
            $coupon = $wpdb->get_var($wpdb->prepare("SELECT order_item_name FROM {$wpdb->prefix}woocommerce_order_items where order_id = %d and order_item_type = %s", $order_id, 'coupon'));


            // Get IP Address
            $ip_address = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}postmeta where post_id = %d and meta_key = %s", $order_id, '_customer_ip_address'));

            if ( $ip_address == NULL ) {
                //check on user meta
                $ip_address = get_post_meta($order_id, 'idevaffiliate_woosub_ip', true);
                $ip_explode = explode(', ', $ip_address);

                if ( count($ip_explode) > 1 ) {
                    $ip_address = $ip_explode[0];
                }

            }

            if ( $ip_address == '' ) {
                //wp_mail('zikubd@gmail.com', 'woo subscription - Invalid IP: iam-premium server', 'order object: <br>' . print_r($order, true));
                //wp_mail( get_option( 'admin_email' ), 'woo subscription - Invalid IP: ' . site_url(), 'order object: <br>' . print_r($order, true)  );
                return;
            }

            // Get Products Purchased
            $idev_result = $wpdb->get_results($wpdb->prepare("SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_items where order_id = %d and order_item_type = %s", $order_id, 'line_item'));
            foreach( $idev_result as $results ) {
                $product_list = $results->order_item_id;
                $product_ids = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta where order_item_id = %d and meta_key = %s", $product_list, '_product_id'));
                //check for multiple skus ie _qty
                $product_qty = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta where order_item_id = %d and meta_key = %s", $product_list, '_qty'));

                $temp_skus = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}postmeta where post_id = %d and meta_key = %s", $product_ids, '_sku'));

                //fix space problem in url
                $temp_skus = str_replace(' ', '-', $temp_skus);

                if ( intval($product_qty) > 1 ) {
                    for ( $i = 0; $i < $product_qty; $i++ ) {
                        $skus[] = $temp_skus;
                    }
                } else {
                    $skus[] = $temp_skus;
                }
            }
            $products_purchased = implode('|', $skus);

            $idv_url = "{$alert_url}sale.php?profile=85&idev_saleamt=$total&idev_ordernum=$order_number&coupon_code=$coupon&products_purchased=$products_purchased&ip_address={$ip_address}";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $idv_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            if(curl_exec($ch) === false)
            {
                //echo 'Curl error: ' . curl_error($ch);
                //wp_mail(get_option( 'admin_email' ), 'Woocommerce idevaffiliate testing. Server: ' . site_url(), curl_error($ch));
            }
            else {
                //success
                //wp_mail('zikubd@gmail.com', 'woo New' . site_url(), 'URL: ' . $idv_url);
            }
            curl_close($ch);

        }
    }

    iDevAffiliate_WooCommerce::getInstance();

endif;
