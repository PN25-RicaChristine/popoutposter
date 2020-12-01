<?php
/**
 * Dynamically generate a new store for customers.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\AdminNotice;
use Nexcess\MAPPS\Concerns\HasHooks;
use Nexcess\MAPPS\Concerns\HasWordPressDependencies;
use Nexcess\MAPPS\Concerns\QueriesWooCommerce;
use Nexcess\MAPPS\Exceptions\ContentOverwriteException;
use Nexcess\MAPPS\Exceptions\IngestionException;
use WC_Product_CSV_Importer_Controller;
use WP_REST_Request;

use const Nexcess\MAPPS\PLUGIN_URL;
use const Nexcess\MAPPS\PLUGIN_VERSION;

class StoreBuilder extends Integration {
	use HasHooks;
	use HasWordPressDependencies;
	use QueriesWooCommerce;

	/**
	 * The StoreBuilder app URL (without a trailing slash).
	 *
	 * @var string
	 */
	private $appUrl;

	/**
	 * The option that gets set once we've already ingested once.
	 */
	const INGESTION_LOCK_OPTION_NAME = '_storebuilder_created_on';

	/**
	 * The post meta key that gets set on generated content.
	 */
	const GENERATED_AT_POST_META_KEY = '_storebuilder_generated_at';

	/**
	 * The transient name for nonces.
	 */
	const TRANSIENT_NAME = '_storebuilder_nonce';

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mwch_site
			&& $this->siteIsAtLeastWordPressVersion( '5.2' )
			&& $this->settings->is_beta_tester;
	}

	/**
	 * Perform any necessary setup for the integration.
	 *
	 * This method is automatically called as part of Plugin::registerIntegration(), and is the
	 * entry-point for all integrations.
	 */
	public function setup() {
		$this->addHooks();

		$this->appUrl = untrailingslashit(
			defined( 'NEXCESS_MAPPS_STOREBUILDER_URL' )
			? NEXCESS_MAPPS_STOREBUILDER_URL
			: 'https://storebuilder.app'
		);

		if ( ! self::isPluginActive( 'mapps-blocks/plugin.php' ) ) {
			$this->loadPlugin( 'nexcess/mapps-blocks/plugin.php' );
		}
	}

	/**
	 * Retrieve all actions for the integration.
	 *
	 * @return array[]
	 */
	protected function getActions() {
		return [
			[ 'rest_api_init', [ $this, 'registerRoutes' ] ],
			[ 'admin_enqueue_scripts', [ $this, 'adminEnqueueScripts' ] ],
		];
	}

	/**
	 * Retrieve all filters for the integration.
	 *
	 * @return array[]
	 */
	protected function getFilters() {
		return [
			[ 'heartbeat_received', [ $this, 'heartbeat' ], 10, 2 ],
		];
	}

	/**
	 * Register WP REST API routes.
	 */
	public function registerRoutes() {
		register_rest_route( 'nexcess-mapps/v1', 'store-builder', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'ingestContent' ],
			'permission_callback' => [ $this, 'validateNonce' ],
		] );
	}

	/**
	 * Append StoreBuilder data to the WordPress Heartbeat.
	 *
	 * @param mixed[] $response Heartbeat response data to pass back to front end.
	 * @param mixed[] $data     Data received from the front end (unslashed).
	 */
	public function heartbeat( $response, $data ) {
		if ( isset( $data['checkStoreBuilderStatus'] ) && $data['checkStoreBuilderStatus'] ) {
			$response['storeBuilderCompleted'] = ! $this->mayIngestContent();
		}

		return $response;
	}

	/**
	 * Enqueue necessary scripts within WP Admin.
	 */
	public function adminEnqueueScripts() {
		$screen = get_current_screen();

		if (
			null === $screen
			|| 'dashboard' !== $screen->base
			|| ! current_user_can( 'manage_options' )
			|| ! $this->mayIngestContent()
			|| ! $this->blockEditorIsEnabledFor( 'page' )
		) {
			return;
		}

		add_thickbox();

		wp_enqueue_script(
			'nexcess-mapps-storebuilder',
			PLUGIN_URL . '/nexcess-mapps/assets/storebuilder.js',
			[ 'jquery' ],
			PLUGIN_VERSION,
			true
		);

		// Build the questionnaire URL.
		$url = add_query_arg( [
			'admin_email' => rawurlencode( get_option( 'admin_email' ) ),
			'callback'    => rawurlencode( get_rest_url( null, '/nexcess-mapps/v1/store-builder' ) ),
			'nonce'       => rawurlencode( $this->getNonce() ),
			'url'         => rawurlencode( site_url() ),
		], $this->appUrl . '/questionnaire#TB_iframe=true' );

		$message = '<p>' . __( 'StoreBuilder lets you set up your new WooCommerce store in minutes by answering a few simple questions.', 'nexcess-mapps' ) . '</p>';

		// Define the button text.
		$message .= sprintf(
			/* Translators: %1$s is the questionnaire URL, %2$s is the button text. */
			'<a href="%1$s" class="thickbox button button-primary">%2$s</a>',
			esc_attr( $url ),
			__( 'Set up my store!', 'nexcess-mapps' )
		);

		$this->adminBar->addNotice( new AdminNotice( $message, 'success', true, 'storebuilder-setup' ) );
	}

	/**
	 * Retrieve content from the app and ingest it into WordPress.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\ContentOverwriteException if ingesting content would cause
	 *                                                             content to be overwritten.
	 * @throws \Nexcess\MAPPS\Exceptions\IngestionException        if content cannot be ingested.
	 */
	public function ingestContent() {
		if ( ! $this->mayIngestContent() ) {
			throw new ContentOverwriteException(
				__( 'StoreBuilder layouts have already been imported for this store, abandoning in order to prevent overwriting content.', 'nexcess-mapps' )
			);
		}

		// Ingest the content here.
		$url      = add_query_arg( 'url', rawurlencode( site_url() ), $this->appUrl . '/api/layouts' );
		$response = wp_remote_get( $url, [
			'headers' => [
				'Accept' => 'application/json',
			],
		] );

		if ( is_wp_error( $response ) ) {
			throw new IngestionException( sprintf(
				/* Translators: %1$s is the WP_Error message. */
				__( 'Unable to retrieve StoreBuilder content: %1$s', 'nexcess-mapps' ),
				$response->get_error_message()
			) );
		} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			throw new IngestionException( sprintf(
				/* Translators: %1$d is the received status code, %2$s the status message. */
				__( 'Received an unexpected %1$d %2$s response from the StoreBuilder app.', 'nexcess-mapps' ),
				wp_remote_retrieve_response_code( $response ),
				wp_remote_retrieve_response_message( $response )
			) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body ) || empty( $body['data']['layoutStructure'] ) ) {
			throw new IngestionException( __( 'StoreBuilder response body was empty or malformed.', 'nexcess-mapps' ) );
		}

		$content = $body['data']['layoutStructure'];

		// Finally, ingest content.
		try {
			if ( ! empty( $content['industry'] ) ) {
				$this->setIndustry( $content['industry'] );
			}

			if ( ! empty( $content['home'] ) ) {
				$this->setHomepageLayout( $content['home'] );
			}

			if ( ! empty( $content['navigation'] ) ) {
				$this->createMenus( $content['navigation'] );
			}

			if ( ! empty( $content['pages'] ) ) {
				$this->createPages( $content['pages'] );
			}

			if ( ! empty( $content['products'] ) ) {
				$this->createProducts( $content['products'] );
			}
		} catch ( IngestionException $e ) {
			throw new IngestionException(
				/* Translators: %1$s is the previous exception's message. */
				sprintf( __( 'An error occurred while ingesting content: %1$s', 'nexcess-mapps' ), $e->getMessage() ),
				$e->getCode(),
				$e
			);
		}

		// If we've gotten this far, content has been ingested and we can invalidate the transient.
		delete_transient( self::TRANSIENT_NAME );

		// Prevent the StoreBuilder from being run again.
		update_option( self::INGESTION_LOCK_OPTION_NAME, [
			'mapps_version' => PLUGIN_VERSION,
			'timestamp'     => time(),
		] );
	}

	/**
	 * Validate the nonce received from the webhook.
	 *
	 * @param \WP_REST_Request $request The REST API request.
	 *
	 * @return bool True if the nonce matches the expected value, false otherwise.
	 */
	public function validateNonce( WP_REST_Request $request ) {
		$expected = get_transient( self::TRANSIENT_NAME );

		return false !== $expected
			&& $expected === $request->get_header( 'X-StoreBuilder-Nonce' );
	}

	/**
	 * Get a unique token good for a single request to the StoreBuilder app.
	 *
	 * This token acts more as a traditional nonce than the WordPress implementation, as it can
	 * only be used once. Unlike WordPress nonces, they're also not tied to specific users since
	 * the webhook request will be unauthenticated.
	 *
	 * Once generated, this token will live in a transient and be passed to the StoreBuilder
	 * questionnaire, then validated when the StoreBuilder app pings our REST API endpoint.
	 *
	 * @return string The nonce to be used for a single request.
	 */
	protected function getNonce() {
		$nonce = get_transient( self::TRANSIENT_NAME );

		if ( false === $nonce ) {
			$nonce = substr( md5( uniqid( site_url() ) ), 0, 12 );

			set_transient( self::TRANSIENT_NAME, $nonce );
		}

		return $nonce;
	}

	/**
	 * Determine if the store is eligible to ingest content.
	 */
	protected function mayIngestContent() {
		return ! get_option( self::INGESTION_LOCK_OPTION_NAME, false )
			&& ! $this->storeHasOrders();
	}

	/**
	 * Set the homepage content based on content from the StoreBuilder app.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\IngestionException If content cannot be imported.
	 *
	 * @param array[] $blocks Homepage blocks provided by the StoreBuilder app.
	 */
	protected function setHomepageLayout( $blocks ) {
		$page_on_front = get_option( 'page_on_front' );
		$page_attr     = [
			'post_type'    => 'page',
			'post_title'   => _x( 'Home', 'default homepage title', 'nexcess-mapps' ),
			'post_content' => '',
			'post_status'  => 'publish',
		];

		// If we already have a homepage, keep the title and ID.
		if ( $page_on_front ) {
			$existing = get_post( $page_on_front );

			if ( $existing ) {
				$page_attr['ID']         = $existing->ID;
				$page_attr['post_title'] = $existing->post_title;
			}
		}

		foreach ( $blocks as $block ) {
			if ( ! empty( $block['post_content'] ) ) {
				$page_attr['post_content'] .= wp_kses_post( $block['post_content'] );
			}
		}

		$page_id = wp_insert_post( $page_attr, true );

		if ( is_wp_error( $page_id ) ) {
			throw new IngestionException( sprintf(
				/* Translators: %1$s is the WP_Error message. */
				__( 'Unable to set homepage layout: %1$s', 'nexcess-mapps' ),
				$page_id->get_error_message()
			) );
		}

		// Make this the site homepage.
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_id );

		// Add additional post meta.
		update_post_meta( $page_id, self::GENERATED_AT_POST_META_KEY, time() );

		// Customizations for sites running Astra.
		if ( 'astra' === get_option( 'template' ) ) {
			update_post_meta( $page_id, 'ast-breadcrumbs-content', 'disabled' );
			update_post_meta( $page_id, 'ast-featured-image', 'disabled' );
			update_post_meta( $page_id, 'site-content-layout', 'page-builder' );
			update_post_meta( $page_id, 'site-post-title', 'disabled' );
			update_post_meta( $page_id, 'site-sidebar-layout', 'no-sidebar' );
		}
	}

	/**
	 * Set the store's industry.
	 *
	 * @param string $industry The store's industry.
	 */
	protected function setIndustry( $industry = 'other' ) {
		update_option( 'nexcess_mapps_storebuilder_industry', sanitize_text_field( (string) $industry ) );
	}

	/**
	 * Ingest menus defined by the StoreBuilder app.
	 *
	 * @param array[] $items Menu items provided by the StoreBuilder app.
	 */
	protected function createMenus( $items ) {
		$primary = $this->createMenu( _x( 'Primary Navigation', 'menu title', 'nexcess-mapps' ), $items );
		$footer  = $this->createMenu( _x( 'Footer Navigation', 'menu title', 'nexcess-mapps' ), $items );

		// Assign the menus.
		set_theme_mod( 'nav_menu_locations', [
			'primary'     => $primary,
			'footer_menu' => $footer,
		] );
	}

	/**
	 * Create a single menu.
	 *
	 * This method acts as a sub-process for createMenus().
	 *
	 * If an existing menu of the same name is found, it will be renamed.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\IngestionException If the menu cannot be created.
	 *
	 * @param string  $name  The name of the menu.
	 * @param array[] $items The menu items to put into the menu.
	 *
	 * @return int The menu ID.
	 */
	protected function createMenu( $name, $items ) {
		$existing = wp_get_nav_menu_object( $name );

		// If a menu with this name already exists, rename the old.
		if ( $existing ) {
			$increment = 0;
			$updated   = false;

			while ( false === $updated ) {
				$increment++;
				$updated = ! is_wp_error(
					wp_update_nav_menu_object( $existing->term_id, [
						'menu-name' => sprintf( '%1$s (%2$d)', $name, $increment ),
					] )
				);
			}
		}

		// Create the new menu.
		$menu_id = wp_create_nav_menu( $name );

		if ( is_wp_error( $menu_id ) ) {
			throw new IngestionException( sprintf(
				/* Translators: %1$s is the menu name, %2$s is the WP_Error message. */
				__( 'Unable to create menu with name "%1$s": %2$s', 'nexcess-mapps' ),
				$name,
				$menu_id->get_error_message()
			) );
		}

		// Add the menu items.
		foreach ( $items as $item ) {
			if ( empty( $item['title'] ) || empty( $item['path'] ) ) {
				continue;
			}

			wp_update_nav_menu_item( $menu_id, 0,
				[
					'menu-item-title'  => sanitize_text_field( $item['title'] ),
					'menu-item-url'    => sanitize_text_field( $item['path'] ),
					'menu-item-status' => 'publish',
				]
			);
		}

		return $menu_id;
	}

	/**
	 * Ingest pages defined by the StoreBuilder app.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\IngestionException If one or more pages can't be created.

	 * @param array[] $pages Pages provided by the StoreBuilder app.
	 */
	protected function createPages( $pages ) {
		$errors = [];

		foreach ( $pages as $name => $page ) {

			// Don't overwrite pages that already exist.
			if ( get_page_by_path( $name ) ) {
				continue;
			}

			try {
				$page_id = wp_insert_post( [
					'post_title'   => ucfirst( $name ),
					'post_content' => ! empty( $page['post_content'] ) ? wp_kses_post( $page['post_content'] ) : '',
					'post_status'  => 'publish',
					'post_type'    => 'page',
				] );

				if ( is_wp_error( $page_id ) ) {
					throw new IngestionException( sprintf(
						/* Translators: %1$s is the page name, %2$s is the error message. */
						__( 'Error creating page "%1$s": %2$s', 'nexcess-mapps' ),
						$name,
						$page_id->get_error_message()
					) );
				}

				if ( ! empty( $page['template'] ) ) {
					update_post_meta( $page_id, '_wp_page_template', $page['template'] );
				}

				// Track the time this page was generated.
				update_post_meta( $page_id, self::GENERATED_AT_POST_META_KEY, time() );
			} catch ( IngestionException $e ) {
				$errors[] = $e->getMessage();
				continue;
			}
		}

		if ( ! empty( $errors ) ) {
			throw new IngestionException( sprintf(
				/* Translators: %1$s is a newline-separated list of error messages. */
				__( 'The following errors occured while creating pages: %1$s', 'nexcess-mapps' ),
				implode( PHP_EOL, $errors )
			) );
		}
	}

	/**
	 * Ingest products defined by the StoreBuilder app.
	 *
	 * This works in the same way as the WooCommerce core CSV importer, using a CSV file provided
	 * by the StoreBuilder app.
	 *
	 * @param string $csv_url URL for a CSV containing sample products.
	 */
	protected function createProducts( $csv_url ) {
		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once WC_ABSPATH . 'includes/admin/importers/class-wc-product-csv-importer-controller.php';
		include_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';

		/*
		 * By default, WooCommerce is expecting files with .csv or .txt extensions, but download_url()
		 * will produce temp files with .tmp extensions.
		 *
		 * @param array $types Valid extension => MIME-type mappings.
		 */
		add_filter( 'woocommerce_csv_product_import_valid_filetypes', function ( $types ) {
			$types['tmp'] = 'text/csv';

			return $types;
		} );

		// Download the file locally, then import the products.
		try {
			$csv = download_url( $csv_url );

			if ( is_wp_error( $csv ) ) {
				throw new \ErrorException( $csv->get_error_message() );
			}

			// Use the WooCommerce core product importer.
			$importer = WC_Product_CSV_Importer_Controller::get_importer( $csv, [
				'parse' => true,
			] );
			$results  = $importer->import();

			if ( ! empty( $results['failed'] ) ) {
				foreach ( $results['failed'] as $failure ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
					trigger_error(
						esc_html( sprintf(
							/* Translators: %1$s is the CSV importer error message. */
							__( 'StoreBuilder encountered an issue importing a demo product: %1$s', 'nexcess-mapps' ),
							$failure->get_error_message()
						) ),
						E_USER_WARNING
					);
				}
			}

			// Unlink the temporary CSV file.
			if ( file_exists( $csv ) ) {
				unlink( $csv );
			}
		} catch ( \Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error(
				esc_html( sprintf(
					/* Translators: %1$s is the CSV URL, %2$s is the error message. */
					__( 'Unable to import demo products from %1$s: %2$s.', 'nexcess-mapps' ),
					$csv_url,
					$e->getMessage()
				) ),
				E_USER_WARNING
			);
		}
	}
}
