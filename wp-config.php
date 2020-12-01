<?php
$redis_server = [
  'host' => '10.75.112.68',
  'port' => '52141',
  'database' => '0'
];
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'a84bbceb_7b7dda' );

/** MySQL database username */
define( 'DB_USER', 'a84bbceb_7b7dda' );

/** MySQL database password */
define( 'DB_PASSWORD', 'Loudly62Wove29Hyper34Wet38' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'vQd]7:(YH|#gtNKYn;qi(QSc.3t)+#;07,?]o@s<lJGb{!uBw4DsBvDrE)m=5m<*' );
define( 'SECURE_AUTH_KEY',   'j|}F`T88_u(Y5;-{1T{dV8~feLA4<f1XZZPSrR^GE!smI^uhdrrgwyR;K}X9{f}]' );
define( 'LOGGED_IN_KEY',     '2#Mt1ZwLfw{fi)*r1$j d3)/iss$Cdexz#.4-`g:H<X`}0j!vxd]UGH:iqw[d@{x' );
define( 'NONCE_KEY',         ';F;wR<qM:k!zjz:h|8MF.iiRr0Z{kG.]7a?96dXlQ_?Tn/|`|2=#tf:YbnlIj2nO' );
define( 'AUTH_SALT',         'Ahzf}C]FzU9/$)zgQho!j<0r3~6o(2],L^4xmtBo(!#xP!vT1phGB_xQPr?Wv_#O' );
define( 'SECURE_AUTH_SALT',  'fe7THZZ`_G9[l1azInd:au7`_?vT#iMPx%2DT_,pQ67Zh*N+EN#3Vc-9Y!ejxh5S' );
define( 'LOGGED_IN_SALT',    '`^4sHUdzEpJ5Rhw kR,>755kY()DMyITg$x=9PPphuic ~o(P($hLTe5f{HAd,7X' );
define( 'NONCE_SALT',        'j&8sZZN+8K6BP>Yg/6N*s.jj=]Fshy[StLO%cO-GPGR[_V/<|E)pth_B3U03tc)7' );
define( 'WP_CACHE_KEY_SALT', 'boI_ o18ky|?46[a]Z{mr$ln@YD)&dIhQs$T>SXl2nCL}._p>8h$k(,-?5_Zs}jV' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




define( 'DISABLE_WP_CRON', true );
define( 'FS_CHMOD_DIR', 0755 );
define( 'FS_CHMOD_FILE', 0644 );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
