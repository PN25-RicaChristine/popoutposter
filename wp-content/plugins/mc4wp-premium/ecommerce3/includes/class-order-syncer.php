<?php

class MC4WP_Ecommerce_Order_Syncer extends MC4WP_Ecommerce_Syncer {
	protected $key = 'orders';

	public function sync( $order_id ) {

		/** @var MC4WP_Ecommerce $ecommerce */
		$ecommerce = mc4wp( 'ecommerce' );
		/** @var MC4WP_Debug_Log $log */
		$log = mc4wp( 'log' );

		// unset tracking cookies temporarily because these would be the admin's cookie
		unset( $_COOKIE[ 'mc_tc' ] );
		unset( $_COOKIE[ 'mc_cid' ] );

		try {
			$ecommerce->update_order( $order_id );
			$log->info(sprintf( 'Ecommerce: Success! Added order %d to Mailchimp.', $order_id ));
		} catch ( Exception $e ) {
			if ( $e->getCode() === MC4WP_Ecommerce::ERR_NO_ITEMS ) {
				$log->info(sprintf( "Ecommerce: Skipping order %d: %s", $order_id, $e->getMessage()));
			} else if ( $e->getCode() === MC4WP_Ecommerce::ERR_NO_EMAIL_ADDRESS ) {
				$log->info(sprintf( "Ecommerce: Skipping order %d: %s", $order_id, __( 'Order has no email address.', 'mc4wp-premium' ) ));
			} else {
				$log->warning( sprintf( "Ecommerce: Error adding order %d: %s", $order_id, $e ));
			}
		}
	}


}
