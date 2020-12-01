<?php
/**
 * Custom autoloader definitions.
 */

namespace Nexcess\MAPPS;

/**
 * PSR-4 autoloader for the Nexcess MAPPS MU plugin.
 *
 * @param string $class The class name being requested.
 */
spl_autoload_register( function ( $class ) {
	// Do nothing for things outside this namespace.
	if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
		return;
	}

	$relative_class = str_replace( __NAMESPACE__, '', $class );
	$filename       = __DIR__ . '/' . str_replace( '\\', '/', $relative_class ) . '.php';

	// Trigger a warning (not a fatal error) if the class could not be located.
	if ( ! file_exists( $filename ) ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		trigger_error(
			esc_html( sprintf( 'Class %1$s could not be found!', $class ) ),
			E_USER_WARNING
		);

		return;
	}

	require_once $filename;
} );

/**
 * Autoloader for known WordPress classes.
 *
 * This is not meant to be a comprehensive list, but enable us to call WordPress classes without
 * littering our codebase with require_once statements.
 *
 * If/when WordPress core introduces its own autoloader, this should be removed.
 *
 * @param string $class The class name being requested.
 */
spl_autoload_register( function ( $class ) {
	$classes = [
		'PHPMailer'                       => '/wp-includes/class-phpmailer.php',
		'PHPMailer\\PHPMailer\\Exception' => '/wp-includes/PHPMailer/Exception.php',
		'PHPMailer\\PHPMailer\\PHPMailer' => '/wp-includes/PHPMailer/PHPMailer.php',
		'PHPMailer\\PHPMailer\\SMTP'      => '/wp-includes/PHPMailer/SMTP.php',
		'SMTP'                            => '/wp-includes/class-smtp.php',
		'WP_Filesystem_Base'              => '/wp-admin/includes/class-wp-filesystem-base.php',
		'WP_Filesystem_Direct'            => '/wp-admin/includes/class-wp-filesystem-direct.php',
	];

	if ( isset( $classes[ $class ] ) ) {
		$file = ABSPATH . $classes[ $class ];

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
} );
