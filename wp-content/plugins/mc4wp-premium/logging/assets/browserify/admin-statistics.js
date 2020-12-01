'use strict';

import Chart from 'chart.js';

const colors = "cc3333,993333,ffcccc,ff9999,33cc33,339933,ccffcc,99ff99".split(',').map(s => '#' + s);
const datasets = window.mc4wp_statistics_data.map((s, i) => {
	s.fill = false;
	s.borderColor = colors[i] || getRandomColor();
	s.backgroundColor = colors[i] || getRandomColor();
	return s;
});

// Show text if no data for selected time period
Chart.plugins.register({
	afterDraw: function(chart) {
		if (chart.data.datasets.length === 0) {
			// No data is present
			const ctx = chart.chart.ctx;
			const width = chart.chart.width;
			const height = chart.chart.height;
			chart.clear();

			ctx.save();
			ctx.textAlign = 'center';
			ctx.textBaseline = 'middle';
			ctx.font = "16px normal 'Helvetica Nueue'";
			ctx.fillText('No data to display', width / 2, height / 2);
			ctx.restore();
		}
	}
});

const settings = window.mc4wp_statistics_settings || {};
const dateRangeSelectorElement = document.getElementById('mc4wp-graph-range');
const customRangeOptionsElement = document.getElementById('mc4wp-graph-custom-range-options');
const ctx = document.getElementById('mc4wp-graph').getContext('2d');

function getRandomColor() {
	const letters = '0123456789ABCDEF'.split('');
	let color = '#';
	for (let i = 0; i < 6; i++ ) {
		color += letters[Math.floor(Math.random() * 16)];
	}
	return color;
}

function plotGraph() {
	const unit = settings.ticksize;

	let tooltipFormat;
	if (unit === "hour") {
		tooltipFormat = "MMM D @ HH:mm"
	} else if(unit === "day" || unit ==="week") {
		tooltipFormat = "MMM D, YYYY";
	} else {
		tooltipFormat = "MMM YYYY";
	}

	const chart = new Chart(ctx, {
		type: 'bar',
		data: {
			datasets: datasets,
		},
		options: {
			animation: {
				duration: 0 // disable animations
			},
			layout: {
				padding: {
					left: 20,
					right: 20,
					top: 20,
					bottom: 20
				}
			},
			scales: {
				xAxes: [{
					stacked: true,
					type: 'time',
					time: {
						unit: unit,
						tooltipFormat: tooltipFormat,
					}
				}],
				yAxes: [{
					stacked: true,
					ticks: {
						min: 0,
						precision: 0,
					}
				}]
			}
		}
	});
}

dateRangeSelectorElement.addEventListener('change', (evt) => {
	customRangeOptionsElement.style.display = evt.target.value === 'custom' ? '' : 'none';
});
plotGraph();
