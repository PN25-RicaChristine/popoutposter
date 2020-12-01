<?php

namespace Nexcess\MAPPS\Commands;

use Nexcess\MAPPS\Integrations\SupportUsers;
use WP_CLI;

/**
 * WP-CLI methods for Nexcess support.
 */
class Support {

	/**
	 * Prints information about this WordPress site.
	 *
	 * @since 1.0.0
	 *
	 * @global $wp_version
	 * @global $wpdb
	 */
	public function details() {
		global $wp_version;
		global $wpdb;

		WP_CLI::line();

		WP_CLI::line( WP_CLI::colorize( sprintf( '%%k%%7%s%%n', __( 'Nexcess Constants', 'nexcess-mapps' ) ) ) );
		WP_CLI::line();

		$constants = [
			'NEXCESS_MAPPS_SITE',
			'NEXCESS_MAPPS_MWCH_SITE',
			'NEXCESS_MAPPS_REGRESSION_SITE',
			'NEXCESS_MAPPS_STAGING_SITE',
			'NEXCESS_MAPPS_PLAN_NAME',
			'NEXCESS_MAPPS_PACKAGE_LABEL',
			'NEXCESS_MAPPS_ENDPOINT',
		];

		array_map( [ get_class(), 'format_constant_line' ], $constants );

		self::format_line(
			/* Translators: %1$s will display text for 'not set' or 'hidden for security'. */
			__( 'NEXCESS_MAPPS_TOKEN: %1$s', 'nexcess-mapps' ),
			defined( 'NEXCESS_MAPPS_TOKEN' ) ?
				'%G' . _x( '<hidden for security>', 'hidden API token', 'nexcess-mapps' ) :
				'%R' . _x( '<not set>', 'displayed text when a constant is not defined', 'nexcess-mapps' ),
			'%_'
		);

		WP_CLI::line();
		WP_CLI::line( WP_CLI::colorize( sprintf( '%%k%%7%s%%n', __( 'Environment Setttings', 'nexcess-mapps' ) ) ) );
		WP_CLI::line();

		self::format_boolean_line( defined( 'WP_DEBUG' ) && WP_DEBUG, __( 'Debug Mode', 'nexcess-mapps' ) );

		WP_CLI::line();

		/* Translators: %1$s is the server's host name. */
		self::format_line( __( 'Server Name: %1$s', 'nexcess-mapps' ), gethostname(), '%_' );
		/* Translators: %1$s is the Site's IP Address. */
		self::format_line( __( 'IP: %1$s', 'nexcess-mapps' ), gethostbyname( php_uname( 'n' ) ), '%_' );
		/* Translators: %1$s is server's Operating System Version. */
		self::format_line( __( 'OS Version: %1$s', 'nexcess-mapps' ), self::get_os_version(), '%_' );

		WP_CLI::line();

		/* Translators: %1$s is the site's WordPress version. */
		self::format_line( __( 'WP Version: %1$s', 'nexcess-mapps' ), $wp_version, '%_' );
		/* Translators: %1$s is the PHP version defined used the site. */
		self::format_line( __( 'PHP Version (WP): %1$s', 'nexcess-mapps' ), phpversion(), '%_' );
		/* Translators: %1$s is the PHP memory limit. */
		self::format_line( __( 'PHP Memory Limit: %1$s', 'nexcess-mapps' ), ini_get( 'memory_limit' ), '%_' );
		/* Translators: %1$s is the PHP upload max file size. */
		self::format_line( __( 'PHP Upload Max Filesize: %1$s', 'nexcess-mapps' ), ini_get( 'upload_max_filesize' ), '%_' );
		/* Translators: %1$s is the MySQL version. */
		self::format_line( __( 'MySQL Version: %1$s', 'nexcess-mapps' ), $wpdb->get_var( 'SELECT VERSION()' ), '%_' );

		WP_CLI::line();
		WP_CLI::line( WP_CLI::colorize( sprintf( '%%k%%7%s%%n', __( 'WordPress Configuration', 'nexcess-mapps' ) ) ) );
		WP_CLI::line();

		/* Translators: %1$s is the memory limit for individual requests used by WordPress. */
		self::format_line( __( 'WP Memory Limit (WP_MEMORY_LIMIT): %1$s', 'nexcess-mapps' ), WP_MEMORY_LIMIT, '%_' );
		/* Translators: %1$s is the absolute file path to WordPress. */
		self::format_line( __( 'Absolute Path (ABSPATH): %1$s', 'nexcess-mapps' ), ABSPATH, '%_' );
		/* Translators: %1$s is the language defined by WordPress. Will default to 'en_US' if not defined. */
		self::format_line( __( 'WPLANG: %1$s', 'nexcess-mapps' ), defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US', '%_' );
		self::format_boolean_line( is_multisite(), __( 'WordPress Multisite', 'nexcess-mapps' ) );

		WP_CLI::line();
		WP_CLI::line( WP_CLI::colorize( sprintf( '%%k%%7%s%%n', __( 'Site Information', 'nexcess-mapps' ) ) ) );
		WP_CLI::line();

		/* Translators: %1$s is the site's home url. */
		self::format_line( __( 'Home URL: %1$s', 'nexcess-mapps' ), get_home_url(), '%_' );
		/* Translators: %1$s is the site's url. */
		self::format_line( __( 'Site URL: %1$s', 'nexcess-mapps' ), site_url(), '%_' );
		/* Translators: %1$s is the admin email address. */
		self::format_line( __( 'Admin Email: %1$s', 'nexcess-mapps' ), get_option( 'admin_email' ), '%_' );

		$permalink_structure = get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default';
		/* Translators: %1$s is the site's permalink structure. */
		WP_CLI::line( sprintf( __( 'Permalink Structure: %1$s', 'nexcess-mapps' ), $permalink_structure ) );

		WP_CLI::line();
	}

	/**
	 * Create a new, temporary support user.
	 *
	 * @subcommand support-user
	 */
	public function supportUser() {
		$password = wp_generate_password();

		try {
			$user_id = SupportUsers::createSupportUser( [
				'user_pass' => $password,
			] );
			$user    = get_user_by( 'id', $user_id );

			if ( ! $user ) {
				throw new \Exception( sprintf( 'Could not find user with ID %d', $user_id ) );
			}
		} catch ( \Exception $e ) {
			return WP_CLI::error( 'Something went wrong creating a support user: ' . $e->getMessage() );
		}

		WP_CLI::success( 'A new support user has been created:' );
		WP_CLI::line();
		WP_CLI::line( WP_CLI::colorize( "\t%Wurl:%N " ) . wp_login_url() );
		WP_CLI::line( WP_CLI::colorize( "\t%Wusername:%N {$user->user_login}" ) );
		WP_CLI::line( WP_CLI::colorize( "\t%Wpassword:%N " ) . $password );
		WP_CLI::line();
		WP_CLI::line( 'This user will automatically expire in 72 hours. You may also remove it manually by running:' );
		WP_CLI::line( WP_CLI::colorize( "\t%c$ wp user delete {$user->ID}%n" ) );
	}

	/**
	 * Serves as a shorthand wrapper for WP_CLI::line() combined with WP_CLI::colorize().
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @param string $text        Base text with specifier.
	 * @param mixed  $replacement Replacement text used for sprintf().
	 * @param string $color       Optional. Color code. See WP_CLI::colorize(). Default empty.
	 */
	protected static function format_line( $text, $replacement, $color = '' ) {
		WP_CLI::line( sprintf( $text, WP_CLI::colorize( $color . $replacement . '%N' ) ) );
	}

	/**
	 * Helper function to format the output of a boolean variable.
	 *
	 * @since 1.4.0
	 * @access protected
	 * @static
	 *
	 * @param bool   $enabled
	 * @param string $display_name   Display name for the variable.
	 */
	protected static function format_boolean_line( $enabled, $display_name ) {
		self::format_line(
			sprintf( '%s: %%s', $display_name ),
			$enabled ? __( 'Enabled', 'nexcess-mapps' ) : __( 'Disabled', 'nexcess-mapps' ),
			$enabled ? '%G' : '%R'
		);
	}

	/**
	 * Helper function to format the output of a constant.
	 *
	 * @since 1.4.0
	 * @access protected
	 * @static
	 *
	 * @param string $name Constant name.
	 */
	protected static function format_constant_line( $name ) {
		self::format_line(
			/* Translators: %1$s is the name of the constant. %%s will be either 'Enabled' or 'Disabled' */
			sprintf( __( '%1$s: %%s', 'nexcess-mapps' ), $name ),
			defined( $name ) ? constant( $name ) : _x( '<not set>', 'displayed text when a constant is not defined', 'nexcess-mapps' ),
			'%_'
		);
	}

	/**
	 * Retrieve and process the details for the underlying Operating System
	 *
	 * @since 1.4.0
	 * @access protected
	 * @static
	 *
	 * @return string The OS version or the string 'Unknown' if unable to read or parse config file.
	 */
	protected static function get_os_version() {
		$name = _x( 'Unknown', 'Unknown Operating System Version', 'nexcess-mapps' );

		if ( is_file( '/etc/os-release' ) && is_readable( '/etc/os-release' ) ) {
			$os_details = parse_ini_file( '/etc/os-release' );

			if ( is_array( $os_details ) && isset( $os_details['PRETTY_NAME'] ) ) {
				$name = $os_details['PRETTY_NAME'];
			}
		}

		return $name;
	}
}
