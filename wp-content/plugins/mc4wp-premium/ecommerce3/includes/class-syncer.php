<?php

abstract class MC4WP_Ecommerce_Syncer {

	protected $key;

	// TODO: Authenticate AJAX requests for starting/stopping
	public function hook() {
		add_action("wp_ajax_mc4wp_ecommerce_synchronize_{$this->key}_start", $this->create_http_response(array( $this, 'start' ) ) );
		add_action("wp_ajax_mc4wp_ecommerce_synchronize_{$this->key}_stop",  $this->create_http_response( array( $this, 'stop' )  ) );
		add_action("wp_ajax_mc4wp_ecommerce_synchronize_{$this->key}_status",  $this->create_http_response( array( $this, 'status' )  ) );
		add_action("wp_ajax_nopriv_mc4wp_ecommerce_synchronize_{$this->key}_tick",  $this->create_http_response( array( $this, 'tick' )  ) );
	}

	private function create_http_response(callable $method) {
		return function() use($method) {
			$data = $method();

			if ( $_SERVER[ 'HTTP_ACCEPT' ] === 'application/json' ) {
				wp_send_json( $data );
			} else {
				wp_redirect( isset( $_POST[ '_redirect_to' ] ) ? $_POST[ '_redirect_to' ] : $_SERVER[ 'HTTP_REFERER' ] );
			}

			exit;
		};
	}

	public function start() {
		// make sure only one instance is running
		if ($this->status() !== false) {
			return false;
		}

		// start wizard
		$ids = $_POST['ids'] !== '' ? explode(',', $_POST['ids']) : array();
		$current = 0;

		if (count($ids) > 0) {
			$this->get_log()->info("Ecommerce: Started {$this->key} sync.");
			add_option( 'mc4wp_ecommerce_' . $this->key . '_wizard_ids', $ids, null, false );
			add_option( 'mc4wp_ecommerce_' . $this->key . '_wizard_current', $current, null, false );
			wp_remote_post( add_query_arg( array( 'action' => 'mc4wp_ecommerce_synchronize_' . $this->key . '_tick' ), admin_url( 'admin-ajax.php' ) ),
				array(
					'blocking' => false,
					'timeout' => 0.1,
				)
			);
		}

		return $this->status();
	}

	/**
	 * @return bool|float False if not running, float percentage if running.
	 */
	public function status() {
		$ids = get_option('mc4wp_ecommerce_'. $this->key .'_wizard_ids');
		$current = get_option('mc4wp_ecommerce_'. $this->key .'_wizard_current');
		if ($ids === false || $current === false || !is_array($ids) || !is_numeric($current)) {
			return false;
		}

		return $current / count($ids);
	}

	public function stop() {
		delete_option('mc4wp_ecommerce_'. $this->key .'_wizard_ids');
		delete_option('mc4wp_ecommerce_'. $this->key .'_wizard_current');
		$this->get_log()->info("Ecommerce: Stopped {$this->key} sync.");
		return true;
	}

	public function tick() {
		ignore_user_abort( true );

		/* Don't make the request block till we finish, if possible. */
		if ( function_exists( 'fastcgi_finish_request' ) && version_compare( phpversion(), '7.0.16', '>=' ) ) {
			if ( ! headers_sent() ) {
				header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
				header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
			}

			fastcgi_finish_request();
		}

		// stop if not running (called improperly or user cancelled)
		if ($this->status() === false) {
			return;
		}

		$ids = get_option('mc4wp_ecommerce_'. $this->key .'_wizard_ids');
		$current = get_option('mc4wp_ecommerce_'. $this->key .'_wizard_current');

		// if done, clean-up and finish
		if ($current >= count($ids)) {
			delete_option('mc4wp_ecommerce_'. $this->key .'_wizard_ids');
			delete_option('mc4wp_ecommerce_'. $this->key .'_wizard_current');
			$this->get_log()->info("Ecommerce: Finished {$this->key} sync.");
			return;
		}

		$this->register_shutdown_function();

		// process current ID in list
		$current_id = $ids[$current++];
		$this->sync($current_id);

		// update current id
		update_option('mc4wp_ecommerce_'. $this->key .'_wizard_current', $current);

		// if we have ample time left, keep going in same request
		$max_execution_time = (int) ini_get('max_execution_time');
		$max_execution_time = $max_execution_time > 0 ? $max_execution_time : 20;
		if (microtime(true) < (WP_START_TIMESTAMP + ($max_execution_time * 0.65))) {
			// Delete options from cache to ensure we get the latest database value next time this method runs
			// This is necessary because sync may have been stopped by now.
			wp_cache_delete('mc4wp_ecommerce_'. $this->key .'_wizard_ids', 'options');
			wp_cache_delete('mc4wp_ecommerce_'. $this->key .'_wizard_current', 'options');

			$this->tick();
		}
	}

	abstract function sync( $id );

	private function register_shutdown_function() {
		// return early if function already registered
		static $registered;
		if ( $registered === true ) {
			return;
		}

		// register shtudown function that renews request by spawning new HTTP request
		$key = $this->key;
		register_shutdown_function(function() use( $key ) {
			wp_remote_post( admin_url( 'admin-ajax.php?action=mc4wp_ecommerce_synchronize_'. $key .'_tick' ),
				array(
					'blocking' => false,
					'timeout' => 0.1,
				)
			);
		});
		$registered = true;
	}

	/**
	 * @return MC4WP_Debug_Log
	 */
	private function get_log() {
		return mc4wp( 'log' );
	}
}
