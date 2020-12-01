<?php

class MC4WP_Ecommerce_Product_Syncer extends MC4WP_Ecommerce_Syncer {

	protected $key = 'products';

	public function sync( $product_id ) {
		/** @var MC4WP_Ecommerce $ecommerce */
		$ecommerce = mc4wp('ecommerce');
		/** @var MC4WP_Debug_Log $log */
		$log = mc4wp('log');
		try {
			$ecommerce->update_product($product_id);
			$log->info(sprintf('Ecommerce: Added or updated product %d', $product_id));
		} catch (Exception $e) {
			$log->warning(sprintf("Ecommerce: Error adding product %d: %s", $product_id, $e));
		}

	}


}
