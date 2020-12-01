<?php
/**
 * Plugin Name: Nexcess MAPPS Blocks
 * Plugin URI:  https://nexcess.net
 * Description: Custom blocks for use with Nexcess MAPPS.
 * Version:     1.0.0
 * Author:      Zeek Interactive
 * Author URI:  https://zeek.com
 * Text Domain: mapps-blocks
 */

namespace Nexcess\MAPPS\Blocks;

require_once __DIR__ . '/blocks/plugin.php';

define( __NAMESPACE__ . '\PLUGIN_URI', plugin_dir_url( __FILE__ ) );
define( __NAMESPACE__ . '\PLUGIN_VERSION', '1.0.0' );

/**
 * Add a class of .frontend to any pages generated via StoreBuilder.
 *
 * @param string[] $classes Current class names being applied to the page body.
 *
 * @return string[] The filtered array of class names.
 */
function get_body_class( $classes ) {
	if ( metadata_exists( 'post', get_the_id(), '_storebuilder_generated_at' ) ) {
		$classes[] = 'frontend';
	}

	return $classes;
}
add_filter( 'body_class', __NAMESPACE__ . '\get_body_class', 99, 1 );

/**
 * Register custom scripts and styles.
 */
function register_scripts_styles() {
	wp_register_script(
		'mapps-blocks-core',
		PLUGIN_URI . 'dist/bundle.js',
		[],
		PLUGIN_VERSION,
		true
	);

	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
	wp_register_script(
		'twitter-widget',
		'https://platform.twitter.com/widgets.js',
		[],
		'',
		true
	);

	wp_register_style(
		'mapps-blocks-core',
		PLUGIN_URI . 'dist/bundle.css',
		[],
		PLUGIN_VERSION
	);

	// Industry-specific styles.
	$industry = get_store_industry();

	wp_register_style(
		'mapps-blocks-' . $industry,
		sprintf( '%1$sdist/%2$s.css', PLUGIN_URI, $industry ),
		[ 'mapps-blocks-core' ],
		PLUGIN_VERSION
	);
}
add_action( 'init', __NAMESPACE__ . '\register_scripts_styles' );

/**
 * Enqueue scripts and styles.
 */
function enqueue_scripts_styles() {
	wp_enqueue_script( 'mapps-block-core' );
	wp_enqueue_style( 'mapps-blocks-' . get_store_industry() );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts_styles' );

/**
 * Enqueue scripts and styles within the block editor.
 *
 * @global $editing
 */
function admin_enqueue_styles() {
	global $editing;

	if ( $editing ) {
		wp_enqueue_script( 'mapps-blocks-' . get_store_industry() );
		wp_enqueue_style( 'mapps-blocks-' . get_store_industry() );
	}
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\admin_enqueue_styles' );

/*
 * Enqueue scripts for twitter block
 */
function enqueue_twitter_widget_api() {
	if ( has_block( 'lw/tweets', get_the_ID() ) ) {
		wp_enqueue_script( 'twitter-widget' );
	}
}
add_action( 'enqueue_block_assets', __NAMESPACE__ . '\enqueue_twitter_widget_api' );

/**
 * Get the industry defined for the store.
 *
 * @return string
 */
function get_store_industry() {
	return get_option( 'nexcess_mapps_storebuilder_industry', 'apparel' );
}
