<?php
/**
 * Controls for VisualComparison.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasAdminPages;
use Nexcess\MAPPS\Concerns\HasHooks;
use Nexcess\MAPPS\Exceptions\InvalidUrlException;
use Nexcess\MAPPS\Integrations\Dashboard;
use Nexcess\MAPPS\Support\VisualRegressionUrl;

class VisualComparison extends Integration {
	use HasAdminPages;
	use HasHooks;

	/**
	 * The settings group.
	 */
	const SETTINGS_GROUP = 'nexcess_mapps_visual_comparison';

	/**
	 * The option name used to store custom URLs.
	 */
	const SETTING_NAME = 'nexcess_mapps_visual_regression_urls';

	/**
	 * The maximum number of URLs permitted per site.
	 */
	const MAXIMUM_URLS = 25;

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mapps_site
			&& $this->settings->mapps_plugin_updates_enabled;
	}

	/**
	 * Retrieve all actions for the integration.
	 *
	 * @return array[]
	 */
	protected function getActions() {
		return [
			// phpcs:disable WordPress.Arrays
			[ 'admin_init', [ $this, 'registerDashboardSection' ], 11 ],
			[ 'admin_init', [ $this, 'registerSetting' ]              ],
			// phpcs:enable WordPress.Arrays
		];
	}

	/**
	 * Retrieve all filters for the integration.
	 *
	 * @return array[]
	 */
	protected function getFilters() {
		return [
			[ 'option_' . self::SETTING_NAME, [ $this, 'expandOptionValue' ] ],
		];
	}

	/**
	 * Automatically expand the contents of self::SETTING_NAME to an array of
	 * VisualRegressionUrl objects.
	 *
	 * @param mixed $value The option value.
	 *
	 * @return VisualRegressionUrl[] An array of regression URLs.
	 */
	public function expandOptionValue( $value ) {
		if ( ! is_array( $value ) ) {
			$value = json_decode( $value, true ) ?: [];
		}

		$values = array_map( function ( $entry ) {
			$path = ! empty( $entry['path'] ) ? $entry['path'] : false;

			if ( $path ) {
				$path = new VisualRegressionUrl( $path, ! empty( $entry['description'] ) ? $entry['description'] : '' );
			}

			return $path;
		}, (array) $value );

		return array_values( array_filter( $values ) );
	}

	/**
	 * Register the Visual Comparison settings section on the MAPPS dashboard.
	 */
	public function registerDashboardSection() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_settings_section(
			'visual-comparison',
			_x( 'Visual Comparison', 'settings section', 'nexcess-mapps' ),
			function () {
				$this->renderTemplate( 'visualcomparison', [
					'settings' => $this->settings,
					'urls'     => self::getUrls(),
				] );
			},
			Dashboard::ADMIN_MENU_SLUG
		);
	}

	/**
	 * Register the SETTING_NAME setting.
	 */
	public function registerSetting() {
		register_setting( self::SETTINGS_GROUP, self::SETTING_NAME, [
			'sanitize_callback' => [ $this, 'sanitizeSetting' ],
		] );
	}

	/**
	 * Sanitize the URLs submitted via the Settings API.
	 *
	 * @param mixed $value The value being sanitized.
	 *
	 * @return string|false A JSON-encoded string of visual regression URLs or FALSE on error.
	 */
	public function sanitizeSetting( $value ) {
		$value        = (array) $value;
		$urls         = [];
		$paths        = [];
		$descriptions = [];

		if ( ! isset( $value['path'], $value['description'] ) ) {
			return false;
		}

		// Loop through the rows and assemble VisualRegressionUrl objects.
		foreach ( (array) $value['path'] as $index => $path ) {
			$description = ! empty( $value['description'][ $index ] )
				? trim( sanitize_text_field( $value['description'][ $index ] ) )
				: '';

			// If duplicate, non-empty descriptions are provided, they must be incremented.
			if ( ! empty( $description ) && in_array( $description, $descriptions, true ) ) {
				$i = 2;

				while ( in_array( $description . " ($i)", $descriptions, true ) ) {
					$i++;
				}

				$description .= " ($i)";
			}

			$url  = new VisualRegressionUrl(
				sanitize_text_field( $path ),
				$description
			);
			$path = $url->getPath();

			// If we already have this path, move on.
			if ( in_array( $path, $paths, true ) ) {
				continue;
			}

			$paths[] = $path;
			$urls[]  = $url;

			if ( ! empty( $description ) ) {
				$descriptions[] = $description;
			}
		}

		// Apply limits to the number of URLs.
		if ( count( $urls ) > self::MAXIMUM_URLS ) {
			$message = sprintf(
				/* Translators: %1$d is the maximum number of URLs permitted. */
				__( 'In order to provide timely feedback, visual comparison runs are limited to %1$d URLs.', 'nexcess-mapps' ),
				self::MAXIMUM_URLS
			);

			$message .= '<br><br>' . __( 'The following URLs could not be saved:', 'nexcess-mapps' );

			foreach ( array_slice( $urls, self::MAXIMUM_URLS ) as $url ) {
				$message .= sprintf( '<br>- %1$s (%2$s)', $url->getPath(), $url->getDescription() );
			}

			add_settings_error( self::SETTING_NAME, 'mapps-visual-comparison-too-many-urls', $message );

			// Finally, ensure only the permitted values are saved.
			$urls = array_slice( $urls, 0, self::MAXIMUM_URLS );
		}

		return wp_json_encode( $urls );
	}

	/**
	 * Retrieve the URLs that should be checked during visual comparison.
	 *
	 * @return \Nexcess\MAPPS\Support\VisualRegressionUrl[]
	 */
	public static function getUrls() {
		return get_option( self::SETTING_NAME, false ) ?: self::getDefaultUrls();
	}

	/**
	 * Get the default URLs to check during visual comparison.
	 *
	 * @return \Nexcess\MAPPS\Support\VisualRegressionUrl[]
	 */
	protected static function getDefaultUrls() {
		$urls = [
			new VisualRegressionUrl( '/', 'Homepage' ),
		];

		// If the site has a static front page, explicitly grab its page_for_posts.
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$urls[] = new VisualRegressionUrl( get_permalink( get_option( 'page_for_posts', '' ) ) ?: '', 'Page for posts' );
		}

		$urls = array_merge(
			$urls,
			self::getDefaultPostUrls(),
			self::getDefaultTaxonomyUrls()
		);

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$urls = array_merge( $urls, self::getDefaultWooCommerceUrls() );
		}

		return $urls;
	}

	/**
	 * Get that represent various post types.
	 *
	 * @global $wpdb
	 *
	 * @return \Nexcess\MAPPS\Support\VisualRegressionUrl[]
	 */
	protected static function getDefaultPostUrls() {
		global $wpdb;

		$post_urls  = [];
		$post_types = get_post_types( [
			'public' => true,
		], 'names' );

		// Find one example of each public post type.
		$results = $wpdb->get_results( $wpdb->prepare(
			"
				SELECT p.post_type, p.ID
				FROM {$wpdb->posts} p
				WHERE p.post_type IN (" . implode( ', ', array_fill( 0, count( $post_types ), '%s' ) ) . ")
				AND p.post_status IN ('publish', 'inherit')
				GROUP BY p.post_type
			",
			$post_types
		) );

		foreach ( $results as $post ) {
			$post_urls[] = new VisualRegressionUrl( get_permalink( $post->ID ) ?: '', 'Single ' . $post->post_type );
		}

		return $post_urls;
	}

	/**
	 * Select URLs to represent taxonomy terms.
	 *
	 * @global $wpdb
	 *
	 * @return \Nexcess\MAPPS\Support\VisualRegressionUrl[]
	 */
	protected static function getDefaultTaxonomyUrls() {
		global $wpdb;

		$tax_urls   = [];
		$taxonomies = get_taxonomies( [
			'publicly_queryable' => true,
		] );

		$results = $wpdb->get_results( $wpdb->prepare(
			"
				SELECT t.taxonomy, t.term_id
				FROM {$wpdb->term_taxonomy} t
				WHERE t.taxonomy IN (" . implode( ', ', array_fill( 0, count( $taxonomies ), '%s' ) ) . ')
				AND t.count > 0
				GROUP BY t.taxonomy
			',
			$taxonomies
		) );

		foreach ( $results as $term ) {
			$link = get_term_link( (int) $term->term_id, $term->taxonomy );

			if ( ! is_wp_error( $link ) ) {
				$tax_urls[] = new VisualRegressionUrl( $link, ucwords( $term->taxonomy ) . ' archive' );
			}
		}

		return $tax_urls;
	}

	/**
	 * Get the default WooCommerce-specific URLs to check during visual comparison.
	 *
	 * @return \Nexcess\MAPPS\Support\VisualRegressionUrl[]
	 */
	protected static function getDefaultWooCommerceUrls() {
		$pages = [
			'woocommerce_shop_page_id'      => 'Shop',
			'woocommerce_cart_page_id'      => 'Cart',
			'woocommerce_checkout_page_id'  => 'Checkout',
			'woocommerce_myaccount_page_id' => 'My Account',
		];
		$urls  = [];

		foreach ( $pages as $option => $name ) {
			try {
				$page_id = get_option( $option, false );

				if ( ! $page_id ) {
					continue;
				}

				$urls[] = new VisualRegressionUrl( get_permalink( $page_id ) ?: '', $name );
			} catch ( InvalidUrlException $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// Skip over the URL.
			}
		}

		return $urls;
	}
}
