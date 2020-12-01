<?php

if(!defined('WPINC')) {
    die();
}

if ( ! class_exists( 'WC_Integration_idevAffiliate_Integration' ) ) :

class WC_Integration_idevAffiliate_Integration extends WC_Integration {

	/**
	 * Init and hook in the integration.
	 */
	public function __construct() {
		global $woocommerce;

		$this->id                 = 'integration-idevaffiliate';
		$this->method_title       = __( 'iDevAffiliate for WooCommerce', 'idev-affiliate' );
		$this->method_description = __( 'iDevAffiliate Settings', 'idev-affiliate' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->idevaffiliate_url = $this->get_option( 'idevaffiliate_url' );

		// Actions.
		add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );

		// Filters.
		add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'sanitize_settings' ) );

	}


	/**
	 * Initialize integration settings form fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
                    'idevaffiliate_url' => array(
                        'title'             => __( 'iDevAffiliate Installation URL', 'idev-affiliate' ),
                        'type'              => 'text',
                        'description'       => __( 'Example: http://www.yoursite.com/idevaffiliate/', 'idev-affiliate' ),
                        'desc_tip'          => false,
                        'default'           => ''
                    )
		);
	}


	/**
	 * Santize our settings
	 * @see process_admin_options()
	 */
	public function sanitize_settings( $settings ) {
            // We're just going to make the api key all upper case characters since that's how our imaginary API works
            if ( isset( $settings ) && isset( $settings['idevaffiliate_url'] ) ) {
                $settings['idevaffiliate_url'] = trim( strip_tags( $settings['idevaffiliate_url'] ) );
            }
            return $settings;
	}


	/**
	 * Validate the API key
	 * @see validate_settings_fields()
	 */
	public function validate_idevaffiliate_url_field( $key ) {
            // get the posted value
            $value = trim($_POST[ $this->plugin_id . $this->id . '_' . $key ]);
            
            // check if the API key is longer than 20 characters. Our imaginary API doesn't create keys that large so something must be wrong. Throw an error which will prevent the user from saving.
            if ( $value == '' || !filter_var( $value, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) )  {
                $this->errors[] = $key;
            }
            return $value;
	}


	/**
	 * Display errors by overriding the display_errors() method
	 * @see display_errors()
	 */
	public function display_errors( ) {

            // loop through each error and display it
            foreach ( $this->errors as $key => $value ) {
                ?>
                <div class="error">
                        <p><?php _e( 'Looks like you made a mistake with the <strong>iDevAffiliate Installation URL</strong> field. Please enter a valid URL.', 'idev-affiliate' ); ?></p>
                </div>
                <?php
            }
	}


}

endif;
