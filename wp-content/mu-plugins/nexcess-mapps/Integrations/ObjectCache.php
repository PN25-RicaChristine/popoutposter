<?php
/**
 * Object cache integration for MWX.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\AdminBar;
use Nexcess\MAPPS\Concerns\HasHooks;
use Nexcess\MAPPS\Concerns\ManagesDropIns;
use Nexcess\MAPPS\Concerns\ManagesWpConfig;
use Nexcess\MAPPS\Support\AdminNotice;

class ObjectCache extends Integration {
	use HasHooks;
	use ManagesDropIns;
	use ManagesWpConfig;

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mapps_site;
	}

	/**
	 * Retrieve all actions for the integration.
	 *
	 * @return array[]
	 */
	protected function getActions() {
		// phpcs:disable WordPress.Arrays
		return [
			[ 'init',                                                 [ $this, 'registerAdminBarMenu'     ] ],
			[ 'admin_action_nexcess-mapps-flush-object-cache',        [ $this, 'adminBarFlushObjectCache' ] ],
			[ 'admin_post_nexcess-mapps-flush-object-cache',          [ $this, 'adminBarFlushObjectCache' ] ],
			[ 'admin_action_nexcess-mapps-delete-expired-transients', [ $this, 'adminBarDeleteExpiredTransients' ] ],
			[ 'admin_post_nexcess-mapps-delete-expired-transients',   [ $this, 'adminBarDeleteExpiredTransients' ] ],

			// Automatically add/remove object-cache.php based on caching plugin state.
			[ 'activate_redis-cache/redis-cache.php',   [ $this, 'enableRedisCache'  ] ],
			[ 'deactivate_redis-cache/redis-cache.php', [ $this, 'disableRedisCache' ] ],
			[ 'activate_wp-redis/wp-redis.php',   [ $this, 'enableWPRedis'  ] ],
			[ 'deactivate_wp-redis/wp-redis.php', [ $this, 'disableWPRedis' ] ],
		];
		// phpcs:enable WordPress.Arrays
	}

	/**
	 * Retrieve all filters for the integration.
	 *
	 * @return array[]
	 */
	protected function getFilters() {
		// phpcs:disable WordPress.Arrays
		return [
			[ 'added_option',   [ $this, 'maybeClearAlloptionsCache' ] ],
			[ 'updated_option', [ $this, 'maybeClearAlloptionsCache' ] ],
			[ 'deleted_option', [ $this, 'maybeClearAlloptionsCache' ] ],
		];
		// phpcs:enable WordPress.Arrays
	}

	/**
	 * Register the admin bar menu item.
	 */
	public function registerAdminBarMenu() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( wp_using_ext_object_cache() ) {
			$this->adminBar->addMenu(
				'flush-object-cache',
				AdminBar::getActionPostForm(
					'nexcess-mapps-flush-object-cache',
					_x( 'Flush object cache', 'admin bar menu title', 'nexcess-mapps' )
				)
			);
		}

		$this->adminBar->addMenu(
			'delete-expired-transients',
			AdminBar::getActionPostForm(
				'nexcess-mapps-delete-expired-transients',
				_x( 'Delete expired transients', 'admin bar menu title', 'nexcess-mapps' )
			)
		);
	}

	/**
	 * Prevent a cache stampede when updating the alloptions cache key.
	 *
	 * This is a temporary fix, and should be removed once Trac ticket 31245 is resolved.
	 *
	 * @link https://core.trac.wordpress.org/ticket/31245
	 *
	 * @param string $option The option being updated.
	 */
	public function maybeClearAlloptionsCache( $option ) {
		if ( wp_installing() ) {
			return;
		}

		$alloptions = wp_load_alloptions();

		// If the updated option is among alloptions, clear the cached value.
		if ( isset( $alloptions[ $option ] ) ) {
			wp_cache_delete( 'alloptions', 'options' );
		}
	}

	/**
	 * Callback for requests to flush the object cache via the Admin Bar.
	 */
	public function adminBarFlushObjectCache() {
		if ( ! AdminBar::validateActionNonce( 'nexcess-mapps-flush-object-cache' ) ) {
			return $this->adminBar->addNotice( new AdminNotice(
				__( 'We were unable to flush the object cache, please try again.', 'nexcess-mapps' ),
				'error',
				true
			) );
		}

		wp_cache_flush();

		$this->adminBar->addNotice( new AdminNotice(
			__( 'The object cache has been flushed successfully!', 'nexcess-mapps' ),
			'success',
			true
		) );

		// If we have a referrer, we likely came from the front-end of the site.
		$referrer = wp_get_referer();

		if ( $referrer ) {
			return wp_safe_redirect( $referrer );
		}
	}

	/**
	 * Callback for requests to delete expired transients via the Admin Bar.
	 */
	public function adminBarDeleteExpiredTransients() {
		if ( ! AdminBar::validateActionNonce( 'nexcess-mapps-delete-expired-transients' ) ) {
			return $this->adminBar->addNotice( new AdminNotice(
				__( 'We were unable to delete expired transients, please try again.', 'nexcess-mapps' ),
				'error',
				true
			) );
		}

		delete_expired_transients( true );

		$this->adminBar->addNotice( new AdminNotice(
			__( 'Expired transients have been deleted!', 'nexcess-mapps' ),
			'success',
			true
		) );

		// If we have a referrer, we likely came from the front-end of the site.
		$referrer = wp_get_referer();

		if ( $referrer ) {
			return wp_safe_redirect( $referrer );
		}
	}

	/**
	 * Automatically symlink Redis Cache's object-cache.php drop-in upon plugin activation.
	 */
	public function enableRedisCache() {
		if ( ! $this->settings->redis_host || ! $this->settings->redis_port ) {
			return;
		}

		$this->setConfigConstant( 'WP_REDIS_HOST', $this->settings->redis_host );
		$this->setConfigConstant( 'WP_REDIS_PORT', $this->settings->redis_port );
		$this->setConfigConstant( 'WP_REDIS_DISABLE_BANNERS', true );
		$this->setConfigConstant( 'WP_REDIS_DISABLE_COMMENT', true );

		$this->symlinkDropIn( 'object-cache.php', WP_PLUGIN_DIR . '/redis-cache/includes/object-cache.php' );
	}

	/**
	 * Automatically remove Redis Cache's object-cache.php drop-in upon plugin deactivation.
	 */
	public function disableRedisCache() {
		if ( $this->removeDropIn( 'object-cache.php', WP_PLUGIN_DIR . '/redis-cache/includes/object-cache.php' ) ) {
			add_action( 'update_option_active_plugins', [ $this, 'installObjectCacheDropIn' ] );
		}

		$this->removeConfigConstant( 'WP_REDIS_HOST' );
		$this->removeConfigConstant( 'WP_REDIS_PORT' );
		$this->removeConfigConstant( 'WP_REDIS_DISABLE_BANNERS' );
		$this->removeConfigConstant( 'WP_REDIS_DISABLE_COMMENT' );
	}

	/**
	 * Automatically symlink WP Redis' object-cache.php drop-in upon plugin activation.
	 */
	public function enableWPRedis() {
		$this->setConfigVariable( 'redis_server', [
			'host' => $this->settings->redis_host,
			'port' => $this->settings->redis_port,
		] );

		$this->symlinkDropIn( 'object-cache.php', WP_PLUGIN_DIR . '/wp-redis/object-cache.php' );
	}

	/**
	 * Automatically remove WP Redis' object-cache.php drop-in upon plugin deactivation.
	 */
	public function disableWPRedis() {
		if ( $this->removeDropIn( 'object-cache.php', WP_PLUGIN_DIR . '/wp-redis/object-cache.php' ) ) {
			add_action( 'update_option_active_plugins', [ $this, 'installObjectCacheDropIn' ] );
		}

		$this->removeConfigVariable( 'redis_server' );
	}

	/**
	 * Find the current object cache plugin (if one exists) and symlink its object-cache.php drop-in.
	 */
	public function installObjectCacheDropIn() {
		/*
		 * A list of object cache plugins, in order of priority.
		 */
		$plugins = [
			'redis-cache/redis-cache.php' => 'enableRedisCache',
			'wp-redis/wp-redis.php'       => 'enableWPRedis',
		];

		foreach ( $plugins as $plugin => $method ) {
			if ( $this->isPluginActive( $plugin ) ) {
				return $this->$method();
			}
		}
	}
}
