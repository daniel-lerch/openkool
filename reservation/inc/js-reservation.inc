<script language="javascript" type="text/javascript">
<!--

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


function resgroup_select_add(text, value, name) {
	//check for index
	if(value.slice(0, 1) == 'i') {
		sendReq("../reservation/inc/ajax.php", "action,gid,element,sesid", "resgroupselect,"+value.slice(1)+","+name+",<?php print session_id(); ?>", do_fill_select);
	}
}//resgroup_select_add()


// Allow linked items to be selected
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


// Don't allow linked items to be selected
function resgroup_doubleselect_add_no_linked(text, value, name, hid_name) {
	//check for index
	if(value.slice(0, 1) == 'i') {
		sendReq("../reservation/inc/ajax.php", "action,gid,element,li,sesid", "resgroupselect,"+value.slice(1)+","+name.replace("ds2", "ds1")+",1,<?php print session_id(); ?>", do_fill_select);
	}
	//real value selected so add it
	else {
		double_select_add(text, value, name, hid_name);
	}
}//resgroup_doubleselect_add()




<?php if($_SESSION['show'] == 'calendar') { ?>
	var lastView = '';
	/**
		* Initialize the JS calendar
		*/
	$(document).ready(function() {

		$("img.fcDeleteIcon").live("click", function(e) {
			e.stopPropagation();

			serie = $(this).attr("id").slice(-1) == 'm';
			id = parseInt($(this).attr("id").slice(4, -1));
			if(id <= 0) return false;

			c = confirm('<?php print str_replace("'", "\'", getLL('res_delete_res_confirm')); ?>');
			if(!c) return false;
			if(serie) {
				c2 = confirm('<?php print str_replace("'", "\'", getLL('res_delete_serie_confirm')); ?>');
			} else {
				c2 = false;
			}
			sendReq('inc/ajax.php', 'action,id,serie', 'fcdelres,'+id+','+c2, do_element);
		});



		$('#ko_calendar').fullCalendar({
			height: calcCalendarHeight(),

			//Layout for the header: Positions of the buttons
			header: {
				left: 'title',
				center: 'prevYear,prev,today,next,nextYear',
				right: 'month,agendaWeek,agendaDay,resourceMonth,resourceWeek,resourceDay'
			},
			//Header layout
			titleFormat: {
				month: '<?php print str_replace("'", "\'", getLL('fullcalendar_title_month')); ?>',
				week: "<?php print str_replace("'", "\'", getLL('fullcalendar_title_week')); ?>",
				day: '<?php print str_replace("'", "\'", getLL('fullcalendar_title_day')); ?>',
				resourceMonth: '<?php print str_replace("'", "\'", getLL('fullcalendar_title_month')); ?>',
				resourceWeek: "<?php print str_replace("'", "\'", getLL('fullcalendar_title_week')); ?>",
				resourceDay: '<?php print str_replace("'", "\'", getLL('fullcalendar_title_day')); ?>'
			},
			columnFormat: {
				month: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_month')); ?>',
				week: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_week')); ?>',
				day: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_day')); ?>',
				resourceMonth: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_resource_month')); ?>',
				resourceWeek: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_resource_week')); ?>',
				resourceDay: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_resource_day')); ?>'
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
			minTime: <?php $fH = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_start'); print $fH ? $fH : 6; ?>,
			maxTime: <?php $fH = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_end'); print $fH ? $fH : 24; ?>,
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
				day: '<img src="../images/cal_day.gif" border="0" title="<?php print getLL('daten_cal_day'); ?>" />',
				resourceDay: '<img src="../images/resource_day.png" border="0" title="<?php print getLL('daten_resource_day'); ?>" />',
				resourceWeek: '<img src="../images/resource_week.png" border="0" title="<?php print getLL('daten_resource_week'); ?>" />',
				resourceMonth: '<img src="../images/resource_month.png" border="0" title="<?php print getLL('daten_resource_month'); ?>" />'
			},

			//Localized month and day names
			<?php
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
				print 'monthNames: [\''.implode("','", $Month).'\'],'."\n";
				print 'monthNamesShort: [\''.implode("','", $month).'\'],'."\n";
				print 'dayNames: [\''.implode("','", $Day).'\'],'."\n";
				print 'dayNamesShort: [\''.implode("','", $day).'\'],'."\n";
			?>

			// Source for events
			//events: "inc/ajax.php?action=jsongetreservations",

			//Source for items
			resources: "inc/ajax.php?action=jsongetresitems",

			//Display wait_message while loading events through ajax
			loading: function(bool) {
				if(bool) $('#wait_message').show();
				else $('#wait_message').hide();
			},

			//For every rendering of the calendar the current view and date are stored in session variables
			viewDisplay: function(view) {
				//Change event source for resourceViews
				if(view.name.slice(0,8) == 'resource') {
					$('#ko_calendar').fullCalendar('removeEventSource', 'inc/ajax.php?action=jsongetreservations').fullCalendar('refetchEvents');
					if(lastView.slice(0,8) != 'resource') {
						$('#ko_calendar').fullCalendar('addEventSource', 'inc/ajax.php?action=jsongetreservations&view=resource');
					}
				} else {
					$('#ko_calendar').fullCalendar('removeEventSource', 'inc/ajax.php?action=jsongetreservations&view=resource').fullCalendar('refetchEvents');
					if(lastView.slice(0,8) == 'resource' || lastView == '') {
						$('#ko_calendar').fullCalendar('addEventSource', 'inc/ajax.php?action=jsongetreservations');
					}
				}
				lastView = view.name;

				stamp = new Date(view.start);
				date = stamp.getFullYear()+'-'+(stamp.getMonth()+1)+'-'+stamp.getDate();
				sendReq('inc/ajax.php', 'action,ymd,view', 'fcsetdate,'+date+','+view.name, do_element);
			},

			//Clicking on a day opens the form to enter a new event
			dayClick: function(dayDate, allDay, jsEvent, view) {
				<?php if($access['reservation']['MAX'] > 1 && db_get_count('ko_resitem') > 0) { ?>
					//Send only date and time (without timezone info)
					stamp = new Date(dayDate);
					day = stamp.getFullYear()+'-'+(stamp.getMonth()+1)+'-'+stamp.getDate()+' '+stamp.getHours()+':'+stamp.getMinutes()+':'+stamp.getSeconds();
					jumpToUrl('index.php?action=neue_reservation&dayDate='+escape(day));
				<?php } ?>
			},

			//Selecting a time range
			select: function(startDate, endDate, allDay, jsEvent, view, resource) {
				<?php if($access['reservation']['MAX'] > 1 && db_get_count('ko_resitem') > 0) { ?>
					//Send only date and time (without timezone info)
					stamp1 = new Date(startDate);
					dayStart = stamp1.getFullYear()+'-'+(stamp1.getMonth()+1)+'-'+stamp1.getDate()+' '+stamp1.getHours()+':'+stamp1.getMinutes()+':'+stamp1.getSeconds();
					stamp2 = new Date(endDate);
					dayEnd = stamp2.getFullYear()+'-'+(stamp2.getMonth()+1)+'-'+stamp2.getDate()+' '+stamp2.getHours()+':'+stamp2.getMinutes()+':'+stamp2.getSeconds();
					if(resource == undefined) item_id = 0;
					else item_id = resource.id;
					jumpToUrl('index.php?action=neue_reservation&dayDate='+escape(dayStart)+'&endDate='+escape(dayEnd)+'&item='+item_id);
				<?php } ?>
			},

			//Show tooltip with details for this event and show editIcons
			eventMouseover: function(calEvent, jsEvent, view) {
				tooltip.show(calEvent.kOOL_tooltip);
				$(this).find("span.fcEventEditIcons").show();
			},
			//Hide tooltip and editIcons
			eventMouseout: function(calEvent, jsEvent, view) {
				tooltip.hide();
				$(this).find("span.fcEventEditIcons").hide();
			},

			//Dropping a reservation changes start and end
			eventDrop: function(calEvent, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {
				tooltip.hide();
				sendReq('inc/ajax.php', 'action,id,mode,dayDelta,minuteDelta,allDay,item', 'fceditres,'+calEvent.id+',drop,'+dayDelta+','+minuteDelta+','+(allDay ? 1 : 0)+','+calEvent.resource, fc_check_redraw);
			},

			//Click to edit
			eventClick: function(calEvent, jsEvent, view) {
				$target = $(jsEvent.target);
				//Dont edit event if deleteIcon has been clicked (with id item[0-9]*[ms]
				if($target.attr("id") === undefined || $target.attr("id").slice(0,4) != "item") {
					if(calEvent.editable) {
						document.location = "index.php?action=edit_reservation&id="+calEvent.id;
					}
				}
			},

			//Resizing a reservation only changes the end
			eventResize: function(calEvent, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view) {
				tooltip.hide();
				sendReq('inc/ajax.php', 'action,id,mode,dayDelta,minuteDelta', 'fceditres,'+calEvent.id+',resize,'+dayDelta+','+minuteDelta, fc_check_redraw);
			},

			//Add editIcons to each event
			eventRender: function(calEvent, element, view) {
				if(calEvent.kOOL_editIcons) {
					cur = element[0].innerHTML;
					edit = '<span class="fcEventEditIcons">'+calEvent.kOOL_editIcons+'</span>';
					element[0].innerHTML = edit+cur;
				} else {
					return;
				}
			}

		});

		function calcCalendarHeight() {
			var h = $(window).height() - 100;
			return h;
		}

		$(window).resize(function() {
			$('#ko_calendar').fullCalendar('option', 'height', calcCalendarHeight());
		});
		
	});



	/**
	 * Callback function for sendReq()
	 * If ajax call returns false fullCalendar will be forced to refetch and redraw
	 */
	function fc_check_redraw() {
		if(http.readyState == 4) {
			if(http.status == 200) {
				status = http.responseText;
				if(status) {
					$('#ko_calendar').fullCalendar('refetchEvents');
				} else {
					$('#ko_calendar').fullCalendar('refetchEvents');
					$('#ko_calendar').fullCalendar('refetchResources');
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

});


-->
</script>
