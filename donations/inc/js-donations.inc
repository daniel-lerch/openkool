<script language="javascript" type="text/javascript">
<!--
$(document).ready(function() {
	//Define enter key for filter submenu input elements
	$('#sm_donations_filter input, #sm_donations_filter select').keypress(function(event) {
		if(event.keyCode == '13') {
			$('#submit_donations_filter').click();
			event.preventDefault();
		}
	});

	//Define enter key for itemlist submenu input elements
	$('#sm_donations_itemlist_accounts input').keypress(function(event) {
		if(event.keyCode == '13') {
			$('#save_itemlist').click();
			event.preventDefault();
		}
	});

	//Donation stats: Show/Hide rows per source for each account
	$('.donations-stats-account').click(function() {
		temp = $(this).attr('id').split('_');
		id = temp[2];
		$('.source_account_'+id).toggle();
		var $toggleSign = $('#'+$(this).attr('id')+'_toggle_sign');
		if ($toggleSign.hasClass('fa-plus')) {
			$toggleSign.removeClass('fa-plus');
			$toggleSign.addClass('fa-minus');
		} else {
			$toggleSign.removeClass('fa-minus');
			$toggleSign.addClass('fa-plus');
		}
	});

	// load richtexteditor (CKEDITOR)
	if(kOOL.module == 'donations') {
		$('.richtexteditor').ckeditor({customConfig : '/donations/inc/ckeditor_custom_config.js' });
	}
});


function draw_donations_charts() {
	if ($('#donations-stats-year-chart').length > 0 ) {
		google.charts.load('current', {packages: ['corechart']});
		google.charts.setOnLoadCallback(function () {
			var data = google.visualization.arrayToDataTable(year_data);

			var options = {
				legend: {position: 'top'},
				hAxis: {slantedText: true},
				title: ''
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('donations-stats-year-chart'));
			var $chart = $('#donations-stats-year-chart');

			google.visualization.events.addListener(chart, 'ready', function () {
				$chart.data('google.chart', chart);

				if ($chart.find('.fullscreen-btn').length == 0) {
					$chart.append('<a class=\"btn btn-default absolute-br-btn discrete-btn google-charts-download-btn\" data-target=\"#donations-stats-year-chart\"><i class=\"fa fa-download\"></i></a>');
					$chart.append('<a class=\"btn btn-default absolute-tr-btn discrete-btn fullscreen-btn\" data-target=\"#donations-stats-year-chart\"><i class=\"fa fa-arrows\"></i></a>');

					$chart.on('fullscreen.entering', function () {
						$(this).data('orig-width', $(this).width());
						$(this).data('orig-height', $(this).height());
						$(this).width(screen.width).height(screen.height);
						chart.draw(data, options);
					});
					$chart.on('fullscreen.exited', function () {
						$chart.find('.fullscreen-btn.is-fullscreen').removeClass('is-fullscreen');
						$(this).width($(this).data('orig-width')).height($(this).data('orig-height'));
						chart.draw(data, options);
					});
				}

				initGoogleDownloadBtn($chart, $chart.find('.google-charts-download-btn'));
			});

			chart.draw(data, options);
		});
	}

	if ($('#donations-stats-accounts-chart').length > 0 ) {
		google.charts.load('current', {packages: ['corechart']});
		google.charts.setOnLoadCallback(function () {
			var data = google.visualization.arrayToDataTable(accounts_data);

			var options = {
				legend: {position: 'top'},
				hAxis: {slantedText: true},
				title: ''
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('donations-stats-accounts-chart'));
			var $chart = $('#donations-stats-accounts-chart');

			google.visualization.events.addListener(chart, 'ready', function () {
				$chart.data('google.chart', chart);

				if ($chart.find('.fullscreen-btn').length == 0) {
					$chart.append('<a class=\"btn btn-default absolute-br-btn discrete-btn google-charts-download-btn\" data-target=\"#donations-stats-accounts-chart\"><i class=\"fa fa-download\"></i></a>');
					$chart.append('<a class=\"btn btn-default absolute-tr-btn discrete-btn fullscreen-btn\" data-target=\"#donations-stats-accounts-chart\"><i class=\"fa fa-arrows\"></i></a>');

					$chart.on('fullscreen.entering', function () {
						$(this).data('orig-width', $(this).width());
						$(this).data('orig-height', $(this).height());
						$(this).width(screen.width).height(screen.height);
						chart.draw(data, options);
					});
					$chart.on('fullscreen.exited', function () {
						$chart.find('.fullscreen-btn.is-fullscreen').removeClass('is-fullscreen');
						$(this).width($(this).data('orig-width')).height($(this).data('orig-height'));
						chart.draw(data, options);
					});
				}

				initGoogleDownloadBtn($chart, $chart.find('.google-charts-download-btn'));
			});

			chart.draw(data, options);
		});
	}
}



function donation_recurring(date, amount) {
	d = prompt('<?php print getLL('kota_ko_donations_date'); ?>', date);
	if(d == null) return false;

	a = prompt('<?php print getLL('kota_ko_donations_amount'); ?>', amount);
	if(a == null) return false;

	set_hidden_value('recurring_date', d);
	set_hidden_value('recurring_amount', a);
}
-->
</script>
