<?php

namespace Nexcess\MAPPS\Concerns;

use \WP_Post;

trait QueriesWooCommerce {

	/**
	 * Determine whether or not a store has orders of any post_status.
	 *
	 * This is a simplified version of wp_count_posts().
	 *
	 * @global $wpdb
	 *
	 * @return bool True if there are orders in the database, false otherwise.
	 */
	protected function storeHasOrders() {
		global $wpdb;

		$has_orders = (bool) wp_cache_get( 'store_has_orders', 'nexcess-mapps', false, $is_cached );

		// If nothing exists in the object cache, query the DB and cache the result.
		if ( ! $is_cached ) {
			$has_orders = (bool) $wpdb->get_var( "
				SELECT COUNT(*) FROM {$wpdb->posts}
				WHERE post_type = 'shop_order'
				AND post_status != 'trash'
				LIMIT 1
			" );

			wp_cache_set( 'store_has_orders', $has_orders, 'nexcess-mapps', 0 );
		}

		return $has_orders;
	}

	/**
	 * Trigger a refresh of the cached value of storeHasOrders().
	 *
	 * @param int      $id   The order ID.
	 * @param \WP_Post $post The order object.
	 */
	public static function saveShopOrder( $id, WP_Post $post ) {
		$has_orders = wp_cache_get( 'store_has_orders', 'nexcess-mapps', false, $is_cached );

		// When trashing a post, clear the cache value (if it exists) so it can be re-calculated.
		if ( 'trash' === $post->post_status ) {
			if ( $is_cached ) {
				wp_cache_delete( 'store_has_orders', 'nexcess-mapps' );
			}

			return;
		}

		// If we've made it this far, the shop has orders.
		if ( ! $has_orders ) {
			wp_cache_set( 'store_has_orders', true, 'nexcess-mapps' );
		}
	}
}
