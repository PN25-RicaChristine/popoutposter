/**
 * Custom scripting for WP Admin in the Nexcess MAPPS environment.
 */

(function ($) {

	/**
	 * Remember when admin notices from the platform are dismissed.
	 */
	$('.mapps-notice').on('click', '.notice-dismiss', function (e) {
		var notice = e.target.parentElement;

		$.post(ajaxurl, {
			action: 'mapps_dismissed_notice',
			notice: notice.dataset.id,
			_wpnonce: notice.dataset.nonce,
		});
	});
} (jQuery, undefined));

/**
 * Visual comparison URLs.
 */
(function () {
	/**
	 * Append a row to the table, using the given template.
	 *
	 * @param {Node} tmpl - The template node to be cloned.
	 * @param {Node} list - The list to which the new node should be appended.
	 */
	function addRow(tmpl, list) {
		var row = tmpl.cloneNode(true);
		row.removeAttribute('hidden');
		row.querySelectorAll('input[disabled]').forEach(function (input) {
			input.removeAttribute('disabled');
		});

		list.insertBefore(row, tmpl);
	}

	/**
	 * Delete a row from the table.
	 *
	 * @param {Node} row - The row to be deleted.
	 */
	function deleteRow(row) {
		while ('TR' !== row.tagName) {
			row = row.parentElement;
		}

		row.remove();
	}

	var table = document.getElementById('mapps-visual-comparison-urls');

	// No table means there's nothing to do.
	if (! table) {
		return;
	}

	var tbody = table.querySelector('tbody'),
		template = table.querySelector('.mapps-template-row'),
		addRowBtn = document.querySelector('.mapps-add-row-btn'),
		observer = new MutationObserver(function (mutations) {
			el = mutations[0].target;
			if (el.childElementCount > el.dataset.urlLimit) {
				addRowBtn.setAttribute('disabled', true);
			} else {
				addRowBtn.removeAttribute('disabled');
			}
		});

	observer.observe(tbody, {
		childList: true,
	});

	addRowBtn.addEventListener('click', function (e) {
		e.preventDefault();

		addRow(template, tbody);
	});

	tbody.addEventListener('click', function (e) {
		if (! e.target.classList.contains('mapps-delete-row-btn')) {
			return;
		}

		e.preventDefault();

		deleteRow(e.target);
	});
} (undefined));
