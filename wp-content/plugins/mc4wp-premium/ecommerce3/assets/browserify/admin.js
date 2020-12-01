const m = require('mithril');
const QueueProcessor = require('./_queue-processor.js');

// ask for confirmation for elements with [data-confirm] attribute
require('./_confirm-attr.js')();

function fetchJSON(url, args) {
	args.headers = args.headers ?? {};
	args.headers['Accept'] = 'application/json';
	return window.fetch(url, args)
		.then(r => r.json());
}

let forms = document.querySelectorAll('.object-sync');
[].forEach.call(forms, (form) => {
	// only attach submit listener to forms starting a sync
	if (form.action.indexOf('_start') === -1) {
		return;
	}

	form.addEventListener('submit', (evt) => {
		evt.preventDefault();
		let formData = new FormData(form)
		let elStatusLine = form.querySelector('.sync-busy');
		let elStatus = elStatusLine.querySelector('.sync-status');

		form.querySelector('input[type="submit"]').disabled = true;
		form.parentElement.querySelector('.mc4wp-status-label').style.display = 'none';

		fetchJSON(form.action, {
			method: form.method,
			body: formData,
		}).then((data) => {
			if (data === false) {
				elStatusLine.style.display = 'none';
				form.querySelector('input[type="submit"]').disabled = false;
				return;
			}

			elStatusLine.style.display = '';
			elStatus.textContent = Math.round(data * 100) + '%';

			// update status element every 2.4 seconds
			let fetchStatus = () => {
				fetchJSON(form.action.replace('_start', '_status'), {})
					.then((data) => {
						if (data === false) {
							elStatusLine.style.display = 'none';
							form.querySelector('input[type="submit"]').disabled = false;
							return;
						}

						elStatus.textContent = Math.round(data * 100) + '%';
						window.setTimeout(fetchStatus, 2400);
					})
			}
			window.setTimeout(fetchStatus, 2400);
		});
	});
})

// queue processor
const queueRootElement = document.getElementById('queue-processor');
if (queueRootElement) {
    m.mount(queueRootElement, QueueProcessor);
}
