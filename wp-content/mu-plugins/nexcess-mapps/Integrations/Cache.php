<?php
/**
 * Generic cache integration for MWX.
 *
 * More specific implementations are available:
 *
 * @see Nexcess\MAPPS\Integrations\ObjectCache
 * @see Nexcess\MAPPS\Integrations\OPcache
 * @see Nexcess\MAPPS\Integrations\Varnish
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasHooks;

class Cache extends Integration {
	use HasHooks;

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
			[ 'muplugins_loaded', [ $this, 'maybeFlushAllCaches' ] ],

			// Add/remove Cache Enabler rewrite rules when the plugin's state changes.
			[ 'activate_cache-enabler/cache-enabler.php',   [ $this, 'injectCacheEnablerRewriteRules' ] ],
			[ 'deactivate_cache-enabler/cache-enabler.php', [ $this, 'removeCacheEnablerRewriteRules' ] ],
		];
		// phpcs:enable WordPress.Arrays
	}

	/**
	 * Check for the presence of a .flush-cache file in the web root.
	 *
	 * If present, flush the object cache and OPcache, then remove the file.
	 *
	 * This handles a case when a migration is executed which directly manipulates the database and
	 * filesystem. This can sometimes leave the cache in a state where it's still populated with
	 * the original theme, plugins, and site options, causing a broken site experience.
	 */
	public function maybeFlushAllCaches() {
		$filepath = ABSPATH . '.flush-cache';

		// No file means there's nothing to do.
		if ( ! file_exists( $filepath ) ) {
			return;
		}

		// Only remove the file if all relevant caches were flushed successfully.
		if ( wp_cache_flush() && OPcache::flushOPcache() ) {
			unlink( $filepath );
		}
	}

	/**
	 * Inject the Cache Enabler rewrite rules into the site's Htaccess file.
	 *
	 * @link https://www.keycdn.com/support/wordpress-cache-enabler-plugin#apache
	 *
	 * @todo https://github.com/liquidweb/nexcess-mapps/issues/109
	 */
	public static function injectCacheEnablerRewriteRules() {
		$htaccess = ABSPATH . '.htaccess';

		// Don't add additional instructions.
		add_filter( 'insert_with_markers_inline_instructions', '__return_empty_array' );

		if ( file_exists( $htaccess ) && is_readable( $htaccess ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$contents = (string) file_get_contents( $htaccess );
		} else {
			$contents = '';
		}

		// We haven't yet added Cache Enabler rules.
		if ( false === strpos( $contents, '# BEGIN Cache Enabler' ) ) {
			if ( false !== strpos( $contents, '# BEGIN WordPress' ) ) {
				$contents = str_replace(
					'# BEGIN WordPress',
					'# BEGIN Cache Enabler' . PHP_EOL . '# END Cache Enabler' . PHP_EOL . PHP_EOL . '# BEGIN WordPress',
					$contents
				);
			} else {
				$contents = '# BEGIN Cache Enabler' . PHP_EOL . '# END Cache Enabler';
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
			file_put_contents( $htaccess, $contents );
		}

		// At this point, we should have an Htaccess file and the necessary markers.
		$rules = dirname( __DIR__ ) . '/snippets/cache-enabler-htaccess.conf';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$lines = explode( PHP_EOL, (string) file_get_contents( $rules ) );

		return insert_with_markers( $htaccess, 'Cache Enabler', $lines );
	}

	/**
	 * Remove the Cache Enabler rewrite rules from the site's Htaccess file.
	 *
	 * @return bool True if the lines were found and removed, false otherwise.
	 *
	 * @todo https://github.com/liquidweb/nexcess-mapps/issues/109
	 */
	public static function removeCacheEnablerRewriteRules() {
		$htaccess = ABSPATH . '.htaccess';

		// Nothing to do or unable to act.
		if ( ! file_exists( $htaccess ) || ! is_readable( $htaccess ) ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$original = (string) file_get_contents( $htaccess );

		/*
		 * Since the extract_from_markers() function doesn't grab the BEGIN/END tags, parse them
		 * using regular expressions.
		 *
		 * This pattern matches the "# BEGIN Cache Enabler" and "# END Cache Enabler" comments,
		 * along with anything in-between them (with a bit of whitespace normalization).
		 */
		$regex    = '/\s*# BEGIN Cache Enabler\s+([\S\s]+)\s*# END Cache Enabler\s*/m';
		$contents = (string) preg_replace( $regex, PHP_EOL, $original );

		// If nothing's changed, return false.
		if ( trim( $contents ) === trim( $original ) ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
		return (bool) file_put_contents( $htaccess, $contents );
	}
}
