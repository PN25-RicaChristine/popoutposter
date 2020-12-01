/**
 * Scripting related to the StoreBuilder integration.
 */

(function ($) {
	var $document = $(document),
		storeBuilderNotice = $document.find('.mapps-notice[data-id="storebuilder-setup"]');

	// If the current user can't see the StoreBuilder setup notice, we have nothing to do.
	if (! storeBuilderNotice) {
		return;
	}

	/**
	 * Query whether or not StoreBuilder has been completed.
	 */
	$document.on('heartbeat-send', function (event, data) {
		data.checkStoreBuilderStatus = true;
	})

	/**
	 * Listen for StoreBuilder to be completed via the Heartbeat API.
	 *
	 * If it's been completed (whether by another user or in another tab), remove the prompt.
	 */
	$document.on('heartbeat-tick', function (event, data) {
		if (! data.storeBuilderCompleted) {
			return;
		}

		storeBuilderNotice.fadeOut(200, function () {
			this.remove();
		});
	});
} (jQuery));
