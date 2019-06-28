<script language="javascript" type="text/javascript">
<!--
function event_cal_select_add(text, value, name) {
	//check for index
	if(value.slice(0, 1) == 'i') {
		sendReq("../daten/inc/ajax.php", "action,cid,element,sesid", "calselect,"+value.slice(1)+","+name+",<?php print session_id(); ?>", do_fill_select);
	}
}//event_cal_select_add()


function resgroup_doubleselect_add(text, value, name, hid_name) {
	//check for index
	if(value.slice(0, 1) == 'i') {
		sendReq("../reservation/inc/ajax.php", "action,gid,element,li,sesid", "resgroupselect,"+value.slice(1)+","+name.replace("ds2", "ds1")+",0,<?php print session_id(); ?>", do_fill_select);
	}
	//real value selected so add it
	else {
		double_select_add(text, value, name, hid_name);
	}
}//resgroup_doubleselect_add()



function repeat_disable(value) {
 	if(value == "") {
 		document.getElementsByName("sel_bis_tag")[0].disabled = "";
 		document.getElementsByName("sel_bis_monat")[0].disabled = "";
 		document.getElementsByName("sel_bis_jahr")[0].disabled = "";
 	} else {
 		document.getElementsByName("sel_bis_tag")[0].disabled = "disabled";
 		document.getElementsByName("sel_bis_monat")[0].disabled = "disabled";
 		document.getElementsByName("sel_bis_jahr")[0].disabled = "disabled";
 	}
}//repeat_disable()



<?php if($_SESSION['show'] == 'calendar') { ?>

	<?php
		//Create month names for fullcalendar formatDate function
		$Month = array();
		$month = array();
		for($m=1; $m<13; $m++) {
			$Month[] = strftime('%B', mktime(1,1,1,$m,1,2009));
			$month[] = strftime('%b', mktime(1,1,1,$m,1,2009));
		}
		$Day = array();
		$day = array();
		for($d=18; $d<26; $d++) {
			$Day[] = strftime('%A', mktime(1,1,1,10,$d,2009));
			$day[] = strftime('%a', mktime(1,1,1,10,$d,2009));
		}
		print 'var ll_month_names = new Array(\''.implode("','", $Month).'\');'."\n";
		print 'var ll_month_names_short = new Array(\''.implode("','", $month).'\');'."\n";
		print 'var ll_day_names = new Array(\''.implode("','", $Day).'\');'."\n";
		print 'var ll_day_names_short = new Array(\''.implode("','", $day).'\');'."\n";
	?>


	/*
	 * FullCalendar v1.4.7 Google Calendar Extension
	 *
	 * Copyright (c) 2009 Adam Shaw
	 * Dual licensed under the MIT and GPL licenses:
	 *   http://www.opensource.org/licenses/mit-license.php
	 *   http://www.gnu.org/licenses/gpl.html
	 *
	 * Date: Mon Jul 5 16:07:40 2010 -0700
	 *
	 */
	(function($) {

		$.fullCalendar.gcalFeed = function(feedUrl, options) {
			
			feedUrl = feedUrl.replace(/\/basic$/, '/full');
			options = options || {};
			
			return function(start, end, callback) {
				var params = {
					'start-min': $.fullCalendar.formatDate(start, 'u'),
					'start-max': $.fullCalendar.formatDate(end, 'u'),
					'singleevents': true,
					'max-results': 9999
				};
				var ctz = options.currentTimezone;
				if (ctz) {
					params.ctz = ctz = ctz.replace(' ', '_');
				}
				$.getJSON(feedUrl + "?alt=json-in-script&callback=?", params, function(data) {
					var events = [];
					if (data.feed.entry) {
						$.each(data.feed.entry, function(i, entry) {
							var startStr = entry['gd$when'][0]['startTime'],
								start = $.fullCalendar.parseISO8601(startStr, true),
								end = $.fullCalendar.parseISO8601(entry['gd$when'][0]['endTime'], true),
								allDay = startStr.indexOf('T') == -1,
								url;
							$.each(entry.link, function() {
								if (this.type == 'text/html') {
									url = this.href;
									if (ctz) {
										url += (url.indexOf('?') == -1 ? '?' : '&') + 'ctz=' + ctz;
									}
								}
							});
							if (allDay) {
								$.fullCalendar.addDays(end, -1); // make inclusive
							}
							var dateString = $.fullCalendar.formatDate(start, options.dateFormat, {monthNames: ll_month_names, monthNamesShort: ll_month_names_short, dayNames: ll_day_names, dayNamesShort: ll_day_names_short});
							events.push({
								id: entry['gCal$uid']['value'],
								title: entry['title']['$t'],
								start: start,
								end: end,
								allDay: allDay,
								location: entry['gd$where'][0]['valueString'],
								description: entry['content']['$t'],
								className: options.className,
								editable: options.editable || false,
								kOOL_tooltip: options.egName+'<br /><b>'+dateString+'</b><br />'+entry['title']['$t']+'<br />'+entry['content']['$t']
							});
						});
					}
					callback(events);
				});
			}
			
		}

	})(jQuery);


	/**
		* Initialize the JS calendar
		*/
	$(document).ready(function() {

		$('#ko_calendar').fullCalendar({
			//Layout for the header: Positions of the buttons
			header: {
				left: 'title',
				center: 'prevYear,prev,today,next,nextYear',
				right: 'month,agendaWeek,agendaDay'
			},
			//Header layout
			titleFormat: {
				month: '<?php print str_replace("'", "\'", getLL('fullcalendar_title_month')); ?>',
				week: "<?php print str_replace("'", "\'", getLL('fullcalendar_title_week')); ?>",
				day: '<?php print str_replace("'", "\'", getLL('fullcalendar_title_day')); ?>'
			},
			columnFormat: {
				month: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_month')); ?>',
				week: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_week')); ?>',
				day: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_day')); ?>'
			},
			timeFormat: {
				agenda: '<?php print str_replace("'", "\'", getLL('fullcalendar_time_agenda')); ?>',
				// default:
				'': '<?php print str_replace("'", "\'", getLL('fullcalendar_time_default')); ?>'
			},

			//Set current date and view according to session variables
			year: <?php print $_SESSION['cal_jahr']; ?>,
			month: <?php print $_SESSION['cal_monat']-1; ?>,
			date: <?php print $_SESSION['cal_tag']; ?>,
			defaultView: '<?php print $_SESSION['cal_view']; ?>',

			//Some general layout settings
			aspectRatio: 1.5,
			allDayText: '<?php print str_replace("'", "\'", getLL('time_all_day')); ?>',
			firstHour: <?php $fH = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_start'); print $fH ? $fH : 6; ?>,
			editable: false,
			weekMode: 'variable',
			firstDay: 1,  //Start week on monday
			axisFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_axis')); ?>',
			selectable: true,
			selectHelper: true,

			//Navigation buttons with icons and titles
			buttonText: {
				prev: '<img src="../images/icon_arrow_left.png" border="0" title="<?php print getLL('daten_navigation_previous'); ?>" />',
				next: '<img src="../images/icon_arrow_right.png" border="0" title="<?php print getLL('daten_navigation_next'); ?>" />',
				prevYear: '<img src="../images/icon_doublearrow_left.png" border="0" title="<?php print getLL('daten_navigation_previous2'); ?>" />',
				nextYear: '<img src="../images/icon_doublearrow_right.png" border="0" title="<?php print getLL('daten_navigation_next2'); ?>" />',
				today: '<img src="../images/icon_today.png" border="0" title="<?php print getLL('time_today'); ?>" />',
				month: '<img src="../images/cal_month.gif" border="0" title="<?php print getLL('daten_cal_month'); ?>" />',
				week: '<img src="../images/cal_week.gif" border="0" title="<?php print getLL('daten_cal_week'); ?>" />',
				day: '<img src="../images/cal_day.gif" border="0" title="<?php print getLL('daten_cal_day'); ?>" />'
			},

			//Localized month and day names
			monthNames: ll_month_names,
			monthNamesShort: ll_month_names_short,
			dayNames: ll_day_names,
			dayNamesShort: ll_day_names_short,

			// Source for events
			eventSources: [
				"inc/ajax.php?action=jsongetevents"
			],

			//Display wait_message while loading events through ajax
			loading: function(bool) {
				if(bool) $('#wait_message').show();
				else $('#wait_message').hide();
			},

			//For every rendering of the calendar the current view and date are stored in session variables
			viewDisplay: function(view) {
				stamp = new Date(view.start);
				date = stamp.getFullYear()+'-'+(stamp.getMonth()+1)+'-'+stamp.getDate();
				sendReq('inc/ajax.php', 'action,ymd,view', 'fcsetdate,'+date+','+view.name, do_element);
			},

			//Clicking on a day opens the form to enter a new event
			dayClick: function(dayDate, allDay, jsEvent, view) {
				<?php if($access['daten']['MAX'] > 1 && db_get_count("ko_eventgruppen", "id", "AND `type` = '0'") > 0) { ?>
					//Send only date and time (without timezone info)
					stamp = new Date(dayDate);
					day = stamp.getFullYear()+'-'+(stamp.getMonth()+1)+'-'+stamp.getDate()+' '+stamp.getHours()+':'+stamp.getMinutes()+':'+stamp.getSeconds();
					jumpToUrl('index.php?action=neuer_termin&dayDate='+escape(day));
				<?php } ?>
			},

			//Selecting an event range
			select: function(startDate, endDate, jsEvent, view) {
				<?php if($access['daten']['MAX'] > 1 && db_get_count("ko_eventgruppen", "id", "AND `type` = '0'") > 0) { ?>
					//Send only date and time (without timezone info)
					stamp1 = new Date(startDate);
					dayStart = stamp1.getFullYear()+'-'+(stamp1.getMonth()+1)+'-'+stamp1.getDate()+' '+stamp1.getHours()+':'+stamp1.getMinutes()+':'+stamp1.getSeconds();
					stamp2 = new Date(endDate);
					dayEnd = stamp2.getFullYear()+'-'+(stamp2.getMonth()+1)+'-'+stamp2.getDate()+' '+stamp2.getHours()+':'+stamp2.getMinutes()+':'+stamp2.getSeconds();
					jumpToUrl('index.php?action=neuer_termin&dayDate='+escape(dayStart)+'&endDate='+escape(dayEnd));
				<?php } ?>
			},

			//Show tooltip with details for this event and show editIcons
			eventMouseover: function(calEvent, jsEvent, view) {
				if(calEvent.kOOL_tooltip != null) tooltip.show(calEvent.kOOL_tooltip);
				$(this).find("span.fcEventEditIcons").show();
			},
			//Hide tooltip and editIcons
			eventMouseout: function(calEvent, jsEvent, view) {
				tooltip.hide();
				$(this).find("span.fcEventEditIcons").hide();
			},

			//Dropping an event changes start and end
			eventDrop: function(calEvent, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {
				tooltip.hide();
				sendReq('inc/ajax.php', 'action,id,mode,dayDelta,minuteDelta', 'fceditevent,'+calEvent.id+',drop,'+dayDelta+','+minuteDelta, fc_check_redraw);
			},

			//Resizing an event only changes the end
			eventResize: function(calEvent, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view) {
				tooltip.hide();
				sendReq('inc/ajax.php', 'action,id,mode,dayDelta,minuteDelta', 'fceditevent,'+calEvent.id+',resize,'+dayDelta+','+minuteDelta, fc_check_redraw);
			},

			//Click to edit
			eventClick: function(calEvent, jsEvent, view) {
				$target = $(jsEvent.target);
				//Dont edit event if deleteIcon has been clicked (with id item[0-9]*[ms])
				if($target.attr("id") === undefined || $target.attr("id").slice(0,5) != "event") {
					if(calEvent.editable) {
						document.location = "index.php?action=edit_termin&id="+calEvent.id;
					}
					//Add link to birthday entries: Set filter for people module
					else if($(this).hasClass('fc-birthday')) {
						stamp = new Date(calEvent.start);
						dob = stamp.getDate()+'-'+(stamp.getMonth()+1);
						document.location = "/leute/index.php?action=set_dobfilter&dob="+dob;
					}
				}
			},

			//Add editIcons to each event
			eventRender: function(calEvent, element, view) {
				//dumpProps(element);
				if(calEvent.kOOL_editIcons) {
					cur = element[0].innerHTML;
					edit = '<span class="fcEventEditIcons">'+calEvent.kOOL_editIcons+'</span>';
					element[0].innerHTML = edit+cur;
				} else {
					return;
				}
			}

		});

		<?php
			ko_get_eventgruppen($all_egs);
			foreach($all_egs as $egid => $eg) {
				if($all_egs[$egid]['gcal_url'] == '') continue;
				if($access['daten']['ALL'] < 1 && $access['daten'][$egid] < 1) continue;
				print 'gcalSource'.$egid.' = $.fullCalendar.gcalFeed(\''.$all_egs[$egid]['gcal_url'].'\', {className: \'eg'.$egid.'\', egName: \''.$all_egs[$egid]['name'].'\', dateFormat: \''.getLL('fullcalendar_gcal_dateformat').'\'});'."\n";
			}
			foreach($_SESSION['show_tg'] as $egid) {
				if($all_egs[$egid]['gcal_url'] == '') continue;
				if($access['daten']['ALL'] < 1 && $access['daten'][$egid] < 1) continue;
				print '$(\'#ko_calendar\').fullCalendar(\'addEventSource\', gcalSource'.$egid.');'."\n";
			}
		?>
		
	});



	/**
	 * Callback function for sendReq()
	 * If ajax call returns false fullCalendar will be forced to refetch and redraw
	 */
	function fc_check_redraw() {
		if(http.readyState == 4) {
			if(http.status == 200) {
				responseText = http.responseText;
				split = responseText.split("@@@");
				status = split[0];
				respType = split[1];
				respMessage = split[2];
				if(status) {
				} else {
					$('#ko_calendar').fullCalendar('refetchEvents');
					if(respType == 'error') {
						$('.errortxt').html(respMessage).fadeIn(500).delay(6000).fadeOut(500);
					}
				}
			}
			else if (http.status == 404) {
				alert("Request URL does not exist");
			}

			//Message-Box ausblenden
			msg = document.getElementsByName('wait_message')[0];
			msg.style.display = "none";
			document.body.style.cursor = 'default';
		}
	}//fc_check-redraw()

<?php } ?>



$(document).ready(function() {
	$("#chk_perm_filter").live("click", function(e) {
		if(this.checked == true) {
			jumpToUrl("index.php?action=set_perm_filter");
		} else {
			jumpToUrl("index.php?action=unset_perm_filter");
		}
	});

	$("input.daten_neuer_termin").click(function() {
		el = $("select[name='koi[ko_event][eventgruppen_id][0]']");
		if(!el.val()) {
			alert('<?php print str_replace("'", "\'", getLL('error_daten_8')); ?>');
			return false;
		}
	});

	if(kOOL.module == 'daten') {
		$('.richtexteditor').ckeditor({customConfig : '/daten/inc/ckeditor_custom_config.js' });
	}
});


-->
</script>
