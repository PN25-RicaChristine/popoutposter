<?php

/**
 * The main admin page for Nexcess MAPPS customers.
 *
 * @global \Nexcess\MAPPS\Settings $settings The current settings object.
 */

use Nexcess\MAPPS\Integrations\Dashboard;
use Nexcess\MAPPS\Support\Branding;

// Fetch the company name.
$company_name = Branding::getCompanyName();

$page_title = $settings->is_mwch_site
	/* Translators: %1$s is the branded company name. */
	? sprintf( __( 'Managed WooCommerce, powered by %s', 'nexcess-mapps' ), $company_name )
	/* Translators: %1$s is the branded company name. */
	: sprintf( __( 'Managed WordPress, powered by %s', 'nexcess-mapps' ), $company_name );
?>

<div class="wrap mapps-wrap">
	<h1 class="nexcess-page-title"><?php echo esc_html( $page_title ); ?></h1>
	<?php do_tabbed_settings_sections( Dashboard::ADMIN_MENU_SLUG ); ?>
</div>
