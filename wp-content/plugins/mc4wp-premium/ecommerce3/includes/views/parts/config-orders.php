<?php
/** @var MC4WP_Ecommerce_Object_Count $order_count */

$status = $this->order_syncer->status();
?>
<h3>
    <?php _e('Orders', 'mc4wp-ecommerce'); ?>
    <?php printf('<span class="mc4wp-status-label">%d/%d</span>', $order_count->tracked, $order_count->all); ?>
</h3>

<p>
    <?php _e('Adding your orders to Mailchimp will allow you to see purchases made by your list subscribers.', 'mc4wp-ecommerce'); ?>
</p>

<div id="mc4wp-ecommerce-orders-progress-bar"></div>

<noscript><?php esc_html_e('Please enable JavaScript to use this feature.', 'mc4wp-ecommerce'); ?></noscript>

<form class="object-sync" method="POST" action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=mc4wp_ecommerce_synchronize_orders_' . ($status !== false ? 'stop' : 'start') ) ); ?>">
	<input type="hidden" name="ids" value="<?php echo esc_attr( join(',', $untracked_order_ids) ); ?>" />
	<input type="hidden" name="_redirect_to" value="<?php echo esc_url( add_query_arg(  array( 'order-sync-started' => '' ) ) ); ?>" />
	<p>
		<?php
		if ($order_count->untracked === 0) {
			echo sprintf('<input type="submit" value="%s" class="button" disabled />', 'All done!');
		} else {
			echo sprintf('<input type="submit" value="%s" class="button" />', $status !== false ? 'Cancel' : 'Synchronize');
		}

		$display = $status !== false ? 'inline-block' : 'none';
		printf('<span class="sync-busy" style="display: %s;"> &nbsp; <span class="description"><span class="sync-status">%2.0f%%</span> &mdash; Synchronizing... This can take a while.</span></span>', $display, $status * 100 )
		?>
	</p>
</form>
