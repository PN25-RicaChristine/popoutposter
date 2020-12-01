<?php

namespace Nexcess\MAPPS\Concerns;

trait HasWordPressDependencies {

	/**
	 * Determine if the current version of WordPress is at least $version.
	 *
	 * @param string $version The minimum permitted version of WordPress.
	 *
	 * @return bool
	 */
	public function siteIsAtLeastWordPressVersion( $version ) {
		global $wp_version;

		/*
		 * Stable WordPress releases are in x.y.z format, but can have pre-release versions,
		 * e.g. "5.4-RC4-47505-src".
		 *
		 * We want, for example. 5.4-RC4-47505-src to be considered equal to 5.4, so strip out
		 * the pre-release portion.
		 */
		$current = preg_replace( '/-.+$/', '', $wp_version );

		return version_compare( $version, $current, '<=' );
	}

	/**
	 * Determine whether or not the block editor is enabled for the given post type(s).
	 *
	 * If multiple post types are provided, the function will ensure that the editor is enabled
	 * for *all* of the given types.
	 *
	 * @param string[] ...$post_types The post type(s) to check.
	 *
	 * @return bool Whether or not the Block Editor is enabled for all of the given post types.
	 */
	public function blockEditorIsEnabledFor( ...$post_types ) {
		/*
		 * Ensure the use_block_editor_for_post_type() function, which was introduced in WordPress
		 * version 5.0, exists.
		 *
		 * If not (and assuming it hasn't been polyfilled), assume the store is using the Gutenberg
		 * feature plugin, which may or may not work the way we expect.
		 */
		if ( ! function_exists( 'use_block_editor_for_post_type' ) ) {
			return false;
		}

		foreach ( $post_types as $post_type ) {
			if ( ! use_block_editor_for_post_type( $post_type ) ) {
				return false;
			}
		}

		return true;
	}
}
