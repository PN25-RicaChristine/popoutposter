<?php

namespace Nexcess\MAPPS\Commands;

use WP_CLI;

/**
 * WP-CLI sub-commands for modifying a site's wp-config.php file.
 */
class Config {

	/**
	 * Regenerate the WP_CACHE_KEY_SALT
	 *
	 * ## EXAMPLES
	 *
	 * $ wp nxmapps config regenerate-cache-key
	 * Success: WP_CACHE_KEY_SALT regenerated.
	 *
	 * @subcommand regenerate-cache-key
	 */
	public function regenerate_cache_key() {
		$salt = wp_generate_password( 64, true, true );

		return WP_CLI::runcommand( 'config set --type=constant WP_CACHE_KEY_SALT ' . escapeshellarg( $salt ) );
	}
}
