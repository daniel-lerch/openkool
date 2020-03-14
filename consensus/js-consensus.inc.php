<script language="javascript" type="text/javascript">
<!--
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003-2020 Renzo Lauper (renzo@churchtool.org)
 *  All rights reserved
 *
 *  This script is part of the kOOL project. The kOOL project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  kOOL is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

	$(document).ready(function() {
		$('body').on("click", ".open .button.editable", function() {
			t = this.id.split("_");
			event_id = t[0];
			team_id = t[1];
			answer = t[3];

			sendReq("../consensus/ajax.php", "action,eventid,teamid,answer,x", "addconsensusentry," + event_id + "," + team_id + "," + answer + ",<?php print $_GET['x']; ?>", do_element_content);
		});

		$(document).on('click', '#consensus_filter', function() {
			if (typeof $(this).data('bs.popover') == "undefined") {
				$(this).popover({
					'content': $('#filter-popover').html(),
					'html': true,
					'title': '',
					'placement': 'bottom'
				}).popover('show');

				$(this).on('show.bs.popover', function () {
					return false;
				});
				$(this).on('hide.bs.popover', function () {
					return false;
				});
			} else {
				$('.popover').toggleClass('hidden');
			}
		});

		$('body').on('change', 'select[name="sel_consensus_group"]', function () {
			consensus_filter();
		});

		$('body').on('change', 'select[name="sel_consensus_status"]', function() {
			consensus_filter();
		});

		var _selectRange = false, _deselectQueue = [];
		<?PHP
		 	echo ko_consensus_get_jquery_selectable();
		?>

	});

	/**
	 * Apply filter on <tr> from consensus_entries table according to
	 * the filters set in popover. Filter not saved in session or anything.
	 */
	function consensus_filter() {
		var targets = [
			'#consensus_entries',
			'#consensus_entries_amtstage'
		];

		var selected_id = $('select[name="sel_consensus_group"]').val();
		var selected_status = $('select[name="sel_consensus_status"]').val();

		if (selected_status === '0') {
			selected_status = null;
		}

		targets.forEach(function (e) {
			var rows = $(e).find('tr');
			var show_counter = 0;
			rows.each(function () {
				$(this).show();
				if ($(this).data("type") === "header") return true;

				var filter_status = String($(this).data('filter-status'));
				var show = false;
				if (filter_status === undefined) {
					show = true;
				} else if (selected_id === '0' && selected_status === null) {
					show = true;
				} else if (selected_id > 0 && selected_status !== null) {
					if (String($(this).data('filter-group')) === selected_id && filter_status.search(selected_status) >= 0) {
						show = true;
					}
				} else if (filter_status.search(selected_status) >= 0) {
					show = true;
				} else if (String($(this).data('filter-group')) === selected_id) {
					show = true;
				}

				if (show === false) {
					$(this).hide();
				} else {
					show_counter++;
				}
			});

			if (show_counter === 0) {
				$('div.notification_warning').text("<?PHP echo getLL('rota_consensus_filter_no_results'); ?>").show();
			} else {
				$('div.notification_warning').text("").hide();
			}
		});

		$('.popover').toggleClass('hidden');
	}

	-->
</script>
