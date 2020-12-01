<?php
/**
 * The main admin page for Nexcess MAPPS customers.
 *
 * @global \Nexcess\MAPPS\Settings $settings The current settings object.
 * @global string[]                $urls     URLs currently flagged for visual regression testing.
 */

use Nexcess\MAPPS\Integrations\VisualComparison;
use Nexcess\MAPPS\Support\Helpers;

$site_url = Helpers::truncate( mb_substr( site_url( '', 'https' ), 8 ), 15, 6 );

?>

<div class="mapps-layout-fluid-deferred">
	<div class="mapps-primary">
		<h2><?php esc_html_e( 'Visual Comparison', 'nexcess-mapps' ); ?></h2>
		<p><?php esc_html_e( 'The following paths will be inspected after each plugin update:', 'nexcess-mapps' ); ?></p>

		<form method="POST" action="<?php echo esc_attr( admin_url( 'options.php' ) ); ?>">
			<table id="mapps-visual-comparison-urls" class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th class="mapps-visual-comparison-base"></th>
						<th class="mapps-visual-comparison-input"><?php echo esc_html( _x( 'Path', 'table heading', 'nexcess-mapps' ) ); ?></th>
						<th class="mapps-visual-comparison-input"><?php echo esc_html( _x( 'Description', 'table heading', 'nexcess-mapps' ) ); ?></th>
						<th class="mapps-visual-comparison-actions"><span class="screen-reader-text"><?php echo esc_html( _x( 'Actions', 'table heading', 'nexcess-mapps' ) ); ?></span></th>
					</tr>
				</thead>
				<tbody data-url-limit="<?php echo esc_attr( (string) VisualComparison::MAXIMUM_URLS ); ?>">
					<?php foreach ( $urls as $url ) : ?>
						<tr>
							<td class="mapps-visual-comparison-base">
								<code><?php echo esc_html( $site_url ); ?></code>
							</td>
							<td class="mapps-visual-comparison-input">
								<input name="<?php echo esc_attr( VisualComparison::SETTING_NAME ); ?>[path][]" type="text" class="large-text code" value="<?php echo esc_attr( $url->getPath() ); ?>" placeholder="/" />
							</td>
							<td class="mapps-visual-comparison-input">
								<input name="<?php echo esc_attr( VisualComparison::SETTING_NAME ); ?>[description][]" type="text" class="large-text" value="<?php echo esc_attr( $url->getDescription() ); ?>" placeholder="<?php esc_attr_e( 'Some important page', 'nexcess-mapps' ); ?>" />
							</td>
							<td>
								<button type="button" class="mapps-delete-row-btn button-link button-link-delete"><?php esc_html_e( 'Delete', 'nexcess-mapps' ); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>

					<tr class="mapps-template-row" hidden>
						<td class="mapps-visual-comparison-base">
							<code><?php echo esc_html( $site_url ); ?></code>
						</td>
						<td class="mapps-visual-comparison-input">
							<input name="<?php echo esc_attr( VisualComparison::SETTING_NAME ); ?>[path][]" type="text" class="large-text code" value="" placeholder="/" disabled />
						</td>
						<td class="mapps-visual-comparison-input">
							<input name="<?php echo esc_attr( VisualComparison::SETTING_NAME ); ?>[description][]" type="text" class="large-text" value="" placeholder="<?php esc_attr_e( 'Some important page', 'nexcess-mapps' ); ?>" disabled />
						</td>
						<td class="mapps-visual-comparison-actions">
							<button type="button" class="mapps-delete-row-btn button-link button-link-delete"><?php esc_html_e( 'Delete', 'nexcess-mapps' ); ?></button>
						</td>
					</tr>
				</tbody>
			</table>

			<p class="submit" style="text-align: right;">
				<button type="button" class="mapps-add-row-btn button"><?php esc_html_e( 'Add URL', 'nexcess-mapps' ); ?></button>
			</p>

			<?php settings_fields( VisualComparison::SETTINGS_GROUP ); ?>
			<input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr( stripslashes( $_SERVER['REQUEST_URI'] ) ); ?>#visual-comparison" />
			<?php submit_button(); ?>
		</form>
	</div>

	<div class="mapps-sidebar card">
		<h3><?php esc_html_e( 'About Visual Comparison', 'nexcess-mapps' ); ?></h3>
		<p><?php esc_html_e( 'Occasionally, updating a plugin can cause major changes to the appearance or behavior of your site.', 'nexcess-mapps' ); ?></p>
		<p><?php esc_html_e( 'We don\'t like that kind of surprise around here, so we perform visual regression testing on key pages of your site.', 'nexcess-mapps' ); ?></p>
		<p><?php esc_html_e( 'Before upgrading anything on a live site, we create a copy of your site, then take screenshots before and after the plugin update; if anything has changed, we hold back the update and let you know.', 'nexcess-mapps' ); ?></p>
		<p><a href="https://help.nexcess.net/74095-wordpress/how-to-use-visual-comparison-tool" class="button"><?php esc_html_e( 'Learn More', 'nexcess-mapps' ); ?></a></p>
	</div>
</div>
