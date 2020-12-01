<?php

namespace Nexcess\MAPPS\Commands;

use WP_CLI;

use function WP_CLI\Utils\get_flag_value;


/**
 * WP-CLI sub-commands related to managing cache on Nexcess MAPPS sites.
 *
 * These commands will generally map to the underlying caching plugins/tools,
 * but provide a standard interface for the Nexcess MAPPS platform.
 */
class Cache {

	/**
	 * An array of valid cache types, used for filtering.
	 *
	 * @var string[]
	 */
	private $cache_types = [
		'object',
		'page',
	];

	/**
	 * The options used when launching sub-processes.
	 *
	 * @see WP_CLI::runcommand()
	 *
	 * @var bool[]
	 */
	private $subprocess_opts = [
		'launch'     => false,
		'exit_error' => false,
		'return'     => true,
	];

	/**
	 * Enable caching layers for a site.
	 *
	 * ## OPTIONS
	 *
	 * [<type>...]
	 * : The caching layer to enable.
	 * ---
	 * options:
	 *   - object
	 *   - page
	 *
	 * [--all]
	 * : Enable all available cache types.
	 *
	 * ## EXAMPLES
	 *
	 * # Enable all caching
	 * $ wp nxmapps cache enable --all
	 *
	 * # Only enable object caching
	 * $ wp nxmapps cache enable object
	 *
	 * @param mixed[] $args       Positional arguments.
	 * @param mixed[] $assoc_args Associative arguments/options passed to the command.
	 */
	public function enable( $args = [], $assoc_args = [] ) {
		$all     = get_flag_value( $assoc_args, 'all', false );
		$enabled = [];

		if ( empty( array_intersect( $args, $this->cache_types ) ) && ! $all ) {
			WP_CLI::warning( 'No cache types were specified. Please specify one or more cache types, or --all.' );
			return 1;
		}

		// Enable object caching.
		if ( $all || in_array( 'object', $args, true ) ) {
			WP_CLI::log( WP_CLI::colorize( '%cEnabling object caching...%n' ) );
			WP_CLI::runcommand( 'plugin install redis-cache --activate', $this->subprocess_opts );
			WP_CLI::runcommand( 'redis enable', $this->subprocess_opts );

			$enabled[] = 'object';
		}

		// Enable page caching.
		if ( $all || in_array( 'page', $args, true ) ) {
			WP_CLI::log( WP_CLI::colorize( '%cEnabling page caching...%n' ) );
			WP_CLI::runcommand( 'plugin install cache-enabler --activate', $this->subprocess_opts );

			$enabled[] = 'page';
		}

		// Finally, report status.
		if ( empty( $enabled ) ) {
			return WP_CLI::warning( 'No cache types were enabled.' );
		}

		return WP_CLI::success( sprintf(
			'The following cache type(s) have been enabled: %s.',
			implode( ', ', $enabled )
		) );
	}

	/**
	 * Disable caching layers for a site.
	 *
	 * ## OPTIONS
	 *
	 * [<type>...]
	 * : The caching layer to disable.
	 * ---
	 * options:
	 *   - object
	 *   - page
	 *
	 * [--all]
	 * : Disable all available cache types.
	 *
	 * ## EXAMPLES
	 *
	 * # Disable all caching
	 * $ wp nxmapps cache disable --all
	 *
	 * # Only disable object caching
	 * $ wp nxmapps cache disable object
	 *
	 * @param mixed[] $args       Positional arguments.
	 * @param mixed[] $assoc_args Associative arguments/options passed to the command.
	 */
	public function disable( $args = [], $assoc_args = [] ) {
		$all      = get_flag_value( $assoc_args, 'all', false );
		$disabled = [];

		if ( empty( array_intersect( $args, $this->cache_types ) ) && ! $all ) {
			WP_CLI::warning( 'No cache types were specified. Please specify one or more cache types, or --all.' );
			return 1;
		}

		// Disable object caching.
		if ( $all || in_array( 'object', $args, true ) ) {
			WP_CLI::log( WP_CLI::colorize( '%cDisabling object caching...%n' ) );
			WP_CLI::runcommand( 'plugin deactivate redis-cache wp-redis', $this->subprocess_opts );

			if ( file_exists( WP_CONTENT_DIR . '/object-cache.php' ) ) {
				unlink( WP_CONTENT_DIR . '/object-cache.php' );
			}

			$disabled[] = 'object';
		}

		// Disable page caching.
		if ( $all || in_array( 'page', $args, true ) ) {
			WP_CLI::log( WP_CLI::colorize( '%cDisabling page caching...%n' ) );
			WP_CLI::runcommand( 'plugin deactivate cache-enabler', $this->subprocess_opts );

			if ( file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) ) {
				unlink( WP_CONTENT_DIR . '/advanced-cache.php' );
			}

			$disabled[] = 'page';
		}

		// Finally, report status.
		if ( empty( $disabled ) ) {
			return WP_CLI::warning( 'No cache types were disabled.' );
		}

		return WP_CLI::success( sprintf(
			'The following cache type(s) have been disabled: %s.',
			implode( ', ', $disabled )
		) );
	}
}
