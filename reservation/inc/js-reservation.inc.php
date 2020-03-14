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
	var lastView = '<?php print $_SESSION['cal_view']; ?>';

	/**
		* Initialize the JS calendar
		*/
	$(document).ready(function() {

		$("body").on("mouseenter", '.fcEventEditIcons', function(e) {
			$(this).closest('a').data('actionIcon',true);
		});
		$("body").on("mouseleave", '.fcEventEditIcons', function(e) {
			$(this).closest('a').data('actionIcon',false);
		});

		$("body").on("mouseenter", '.fcDeleteIcon', function(e) {
			$(this).closest('a').data('delete',true);
		});

		$("body").on("mouseleave", '.fcDeleteIcon', function(e) {
			$(this).closest('a').data('delete',false);
		});

		var buttonLabels = {
			prev: "<?php print getLL('daten_navigation_previous'); ?>",
			next: '<?php print getLL('daten_navigation_next'); ?>',
			prevYear: '<?php print getLL('daten_navigation_previous2'); ?>',
			nextYear: '<?php print getLL('daten_navigation_next2'); ?>',
			today: '<?php print getLL('time_today'); ?>',
			month: '<?php print getLL('daten_cal_month'); ?>',
			agendaWeek: '<?php print getLL('daten_cal_week'); ?>',
			agendaDay: '<?php print getLL('daten_cal_day'); ?>',
			timelineDay: '<?php print getLL('daten_resource_day'); ?>',
			timelineWeek: '<?php print getLL('daten_resource_week'); ?>',
			timelineMonth: '<?php print getLL('daten_resource_month'); ?>'
		};

		$('body').on('mouseover', '.fc-button', function() {
			var btnClass = this.className;
			var btnType = btnClass.replace(/^.*fc-([^ ]*)-button.*$/g, '$1');
			$(this).attr('title', buttonLabels[btnType]);
		});

		$(window).on('scroll', function() {
			$('.tooltip.in').remove();
		});

		var $koCalendar = $('#ko_calendar');
		var resourcesCache = null;
		$koCalendar.fullCalendar({
			//Open Source License for scheduler
			schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
			locale: kOOL.language,
			titleRangeSeparator: " \u2014 ",
			height: calcCalendarHeight,
			resourceAreaWidth: '180px',
			weekNumbers: true,
			weekNumbersWithinDays: true,

			navLinks: true,
			navLinkDayClick: function(date, jsEvent) {
				if (lastView.slice(0, 8) == 'timeline') $koCalendar.fullCalendar('changeView', 'timelineDay');
				else $koCalendar.fullCalendar('changeView', 'agendaDay');
				$koCalendar.fullCalendar('gotoDate', date);
			},
			navLinkWeekClick: function(date, jsEvent) {
				if (lastView.slice(0, 8) == 'timeline') $koCalendar.fullCalendar('changeView', 'timelineWeek');
				else $koCalendar.fullCalendar('changeView', 'agendaWeek');
				$koCalendar.fullCalendar('gotoDate', date);
			},

			//Layout for the header: Positions of the buttons
			header: {
				left: 'title',
				center: 'prevYear,prev,today,next,nextYear',
				right: 'month,agendaWeek,agendaDay,timelineMonth,timelineWeek,timelineDay'
			},
			footer: {
				left: 'month,agendaWeek,agendaDay,timelineMonth,timelineWeek,timelineDay',
				center: '',
				right: ''
			},
			//Header layout
			//titleFormat: {
			//	month: '<?php //print str_replace("'", "\'", getLL('fullcalendar_title_month')); ?>//',
			//	week: "<?php //print str_replace("'", "\'", getLL('fullcalendar_title_week')); ?>//",
			//	day: '<?php //print str_replace("'", "\'", getLL('fullcalendar_title_day')); ?>//',
			//	timelineMonth: '<?php //print str_replace("'", "\'", getLL('fullcalendar_title_month')); ?>//',
			//	timelineWeek: "<?php //print str_replace("'", "\'", getLL('fullcalendar_title_week')); ?>//",
			//	timelineDay: '<?php //print str_replace("'", "\'", getLL('fullcalendar_title_day')); ?>//'
			//},
			//columnFormat: {
			//	month: '<?php //print str_replace("'", "\'", getLL('fullcalendar_column_month')); ?>//',
			//	week: '<?php //print str_replace("'", "\'", getLL('fullcalendar_column_week')); ?>//',
			//	day: '<?php //print str_replace("'", "\'", getLL('fullcalendar_column_day')); ?>//',
			//	timelineMonth: '<?php //print str_replace("'", "\'", getLL('fullcalendar_column_resource_month')); ?>//',
			//	timelineWeek: '<?php //print str_replace("'", "\'", getLL('fullcalendar_column_resource_week')); ?>//',
			//	timelineDay: '<?php //print str_replace("'", "\'", getLL('fullcalendar_column_resource_day')); ?>//'
			//},
			//timeFormat: {
			//	agenda: '<?php //print str_replace("'", "\'", getLL('fullcalendar_time_agenda')); ?>//',
			//	month: '<?php //print str_replace("'", "\'", getLL('fullcalendar_time_default')); ?>//',
			//	timeline: '<?php //print str_replace("'", "\'", getLL('fullcalendar_time_default')); ?>//'
			//},
			views: {
				month: {
					columnFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_month')); ?>',
					timeFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_time_default')); ?>',
					slotLabelFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_time_default')); ?>',
					titleFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_title_month')); ?>',
				},
				agenda: {
					timeFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_time_agenda')); ?>',
					slotLabelFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_time_agenda')); ?>',
				},
				agendaWeek: {
					columnFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_week')); ?>',
					titleFormat: "<?php print str_replace("'", "\'", getLL('fullcalendar_title_week')); ?>",
				},
				agendaDay: {
					columnFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_day')); ?>',
					titleFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_title_day')); ?>',
				},
				timeline: {
					timeFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_time_default')); ?>',
					slotLabelFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_time_default')); ?>',
				},
				timelineMonth: {
					slotLabelFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_timeline_month_upper')); ?>',
					columnFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_resource_month')); ?>',
					titleFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_title_month')); ?>',
				},
				timelineWeek: {
					slotLabelFormat: [
						'<?php print str_replace("'", "\'", getLL('fullcalendar_column_timeline_week_upper')); ?>', // top level of text
						'<?php print str_replace("'", "\'", getLL('fullcalendar_column_timeline_week_lower')); ?>'        // lower level of text
					],
					slowWidth: 10,
					columnFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_resource_week')); ?>',
					titleFormat: "<?php print str_replace("'", "\'", getLL('fullcalendar_title_week')); ?>",
				},
				timelineDay: {
					slotLabelFormat: [
						'<?php print str_replace("'", "\'", getLL('fullcalendar_column_timeline_day_upper')); ?>', // top level of text
						'<?php print str_replace("'", "\'", getLL('fullcalendar_column_timeline_day_lower')); ?>'        // lower level of text
					],
					columnFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_column_resource_day')); ?>',
					titleFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_title_day')); ?>'
				}
			},

			//Set current date and view according to session variables
			defaultDate: moment('<?php print $_SESSION['cal_jahr'].'-'.(strlen($_SESSION['cal_monat']) == 1 ? '0'.$_SESSION['cal_monat']:$_SESSION['cal_monat']).'-'.(strlen($_SESSION['cal_tag']) == 1 ? '0'.$_SESSION['cal_tag'] : $_SESSION['cal_tag']); ?>'),
			defaultView: '<?php print $_SESSION['cal_view']; ?>',

			//Some general layout settings
			aspectRatio: 1.5,
			allDayText: '<?php print str_replace("'", "\'", getLL('time_all_day')); ?>',
			minTime: '<?php $fH = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_start'); print ($fH != '' ? (strlen($fH) == 1 ? '0'.$fH : $fH).':00:00' : '06:00:00'); ?>',
			maxTime: '<?php $fH = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_end'); print ($fH != '' ? (strlen($fH) == 1 ? '0'.$fH : $fH).':00:00' : '24:00:00'); ?>',
			nextDayThreshold: '<?php $fH = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_start'); print ($fH != '' ? (strlen($fH) == 1 ? '0'.$fH : $fH).':00:00' : '06:00:00'); ?>',
			editable: false,
			firstDay: 1,  //Start week on monday
			selectable: true,
			selectHelper: true,
			resourceLabelText: '<?php print getLL('res_items_list_title'); ?>',
			//displayEventTime: false,

			//Navigation buttons with icon or text
			buttonIcons: {
				prev: 'left-single-arrow',
				next: 'right-single-arrow',
				prevYear: 'left-double-arrow',
				nextYear: 'right-double-arrow',
				today: 'k-today',
				month: 'k-month',
				agendaWeek: 'k-week',
				agendaDay: 'k-day',
				timelineMonth: 'k-tl-month',
				timelineWeek: 'k-tl-week',
				timelineDay: 'k-tl-day'
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

			refetchResourcesOnNavigate: true,

			//Source for resources
			resources: function(callback, start, end, timezone) {
				if ($koCalendar.fullCalendar('getView').name.slice(0, 8) == 'timeline') {
					$.get("inc/ajax.php?action=jsongetresitems").success(function (data) {
						resourcesCache = JSON.parse(data);
						callback(resourcesCache);
					});
				} else {
					callback([]);
				}
			},

			//Display wait_message while loading events through ajax
			loading: function(bool) {
				if(bool) $('#wait_message').show();
				else $('#wait_message').hide();
			},

			eventSources: ['<?php print $_SESSION['cal_view']; ?>'.slice(0,8) == 'timeline' ? 'inc/ajax.php?action=jsongetreservations&view=resource' : 'inc/ajax.php?action=jsongetreservations'],

			//For every rendering of the calendar the current view and date are stored in session variables
			viewRender: function(view) {
				var cal = $('#ko_calendar');
				//Change event source for resourceViews
				if(view.name.slice(0,8) == 'timeline') {
					if(lastView.slice(0,8) != 'timeline') {
						cal.fullCalendar('removeEventSource', 'inc/ajax.php?action=jsongetreservations');
						cal.fullCalendar('addEventSource', 'inc/ajax.php?action=jsongetreservations&view=resource');
					}
				} else {
					if(lastView.slice(0,8) == 'timeline' || lastView == '') {
						cal.fullCalendar('removeEventSource', 'inc/ajax.php?action=jsongetreservations&view=resource');
						cal.fullCalendar('addEventSource', 'inc/ajax.php?action=jsongetreservations');
					}
				}
				if (view.name == 'timelineMonth') {
					$('#pdf_link_footer').hide();
				} else {
					$('#pdf_link_footer').show();
				}
				lastView = view.name;
				var setDate = view.start.format('YYYY-MM-DD');
				if (view.name == 'month' && view.start.date() > 7) {
					setDate = view.start.add(1, 'month').startOf('month').format('YYYY-MM-DD');
				}
				sendReq('inc/ajax.php', 'action,ymd,view', 'fcsetdate,'+setDate+','+view.name, do_element);

			},

			//Clicking on a day opens the form to enter a new event
			dayClick: function(dayDate, jsEvent, view, resource) {
				$('.tooltip.in').remove();

				<?php if($access['reservation']['MAX'] > 1 && db_get_count('ko_resitem') > 0) { ?>
					//Send only date and time (without timezone info)
					day = dayDate.format('YYYY-MM-DD HH:mm:SS');
					if(resource == undefined) item_id = 0;
					else item_id = resource.id;
					jumpToUrl('index.php?action=neue_reservation&dayDate='+escape(day)+'&item='+item_id);
				<?php } ?>
			},

			//Selecting a time range
			select: function(startDate, endDate, jsEvent, view, resource) {
				<?php if($access['reservation']['MAX'] > 1 && db_get_count('ko_resitem') > 0) { ?>
					var dateBeforeEnd = new moment(endDate);
					dateBeforeEnd.subtract(1, 'days');

					//Send only date and time (without timezone info)
					dayStart = startDate.format('YYYY-MM-DD HH:mm:SS');
					dayEnd = endDate.format('YYYY-MM-DD HH:mm:SS');
					dayBeforeEnd = dateBeforeEnd.format('YYYY-MM-DD HH:mm:SS');
					if(resource == undefined) item_id = 0;
					else item_id = resource.id;

					if (!startDate.hasTime() && !endDate.hasTime() && dateBeforeEnd.isSame(startDate)) {
						jumpToUrl('index.php?action=neue_reservation&dayDate='+escape(dayStart)+'&item='+item_id);
					} else if (!startDate.hasTime()) {
						jumpToUrl('index.php?action=neue_reservation&dayDate='+escape(dayStart)+'&endDate='+escape(dayBeforeEnd)+'&item='+item_id);
					} else {
						jumpToUrl('index.php?action=neue_reservation&dayDate='+escape(dayStart)+'&endDate='+escape(dayEnd)+'&item='+item_id);
					}
				<?php } ?>
			},

			//Show tooltip with details for this event and show editIcons
			eventMouseover: function(calEvent, jsEvent, view) {
				var target = $(this);
				var eventJQObj = target.closest('.fc-event');

				$('.tooltip.in').remove();
				if(calEvent.kOOL_tooltip) {
					eventJQObj.tooltip({
						html: true,
						title: calEvent.kOOL_tooltip,
						container: 'body',
						trigger: 'manual'
					});
					eventJQObj.tooltip('show');
				}

				var icons = target.find("span.fcEventEditIcons");

				var brtl = target.css('border-radius-top-left');
				var brbl = target.css('border-radius-bottom-left');
				if (!brtl) brtl = '0px';
				if (!brbl) brbl = '0px';
				icons.css('border-radius-top-left', brtl);
				icons.css('border-radius-bottom-left', brbl);

				icons.show();
			},
			//Hide tooltip and editIcons
			eventMouseout: function(calEvent, jsEvent, view) {
				$('.tooltip.in').remove();
				$(this).find("span.fcEventEditIcons").hide();
			},

			//Dropping a reservation changes start and end
			eventDrop: function(event, delta, revertFunc, jsEvent, ui, view) {
				$('.tooltip.in').remove();
				sendReq('inc/ajax.php', 'action,id,mode,secondDelta,allDay,item,isMod', 'fceditres,'+event.id+',drop,'+delta.asSeconds()+','+(event.allDay ? 1 : 0)+','+event.resourceId+','+event.isMod, fc_check_redraw);
			},

			//Click to edit
			eventClick: function(calEvent, jsEvent, view) {
				var actionIcon = $(this).data('actionIcon');
				var deleteIcon = $(this).data('delete');
				if(deleteIcon === true) {
					var btn = $(this).find('button.fcDeleteIcon');
					var serie = $(btn).attr("id").slice(-1) == 'm';
					var id = parseInt($(btn).attr("id").slice(4, -1));

					if(id <= 0) return false;

					var eventId = $(btn).data('event-id');
					var eventTitle = $(btn).data('event-title');
					var eventGroup = $(btn).data('event-group');
					if (eventId) {
						c = confirm('<?php print str_replace("'", "\'", getLL('res_delete_res_confirm_event')); ?>');
						if(!c) return false;

						c2 = false;

						c3 = confirm('<?php print str_replace("'", "\'", getLL('res_delete_event_confirm')); ?>');
					} else {
						c = confirm('<?php print str_replace("'", "\'", getLL('res_delete_res_confirm')); ?>');
						if(!c) return false;

						if(serie) {
							c2 = confirm('<?php print str_replace("'", "\'", getLL('res_delete_serie_confirm')); ?>');
						} else {
							c2 = false;
						}

						c3 = false;
					}

					sendReq('inc/ajax.php', ['action','id','serie','delevent'], ['fcdelres', id, c2, c3], do_element);
				}

				//Add link to absence item
				if($(this).hasClass('fc-absence')) {
					if($(this).hasClass('fc-absence-readonly') === false) {
						document.location = "/daten/index.php?action=edit_absence&id="+calEvent.id;
					}
				}

				if (!actionIcon) {
					$target = $(jsEvent.target).parent();
					//Dont edit event if deleteIcon has been clicked (with id item[0-9]*[ms]
					if($target.attr("id") === undefined || $target.attr("id").slice(0,4) != "item") {
						if(calEvent.editable) {
							if (calEvent.isMod) document.location = "index.php?action=res_mod_edit&id="+calEvent.id;
							else document.location = "index.php?action=edit_reservation&id="+calEvent.id;
						}
					}
				}

				if (onMobile && !calEvent.editable) {
					eventJQObj = $(jsEvent.currentTarget);
					$('.tooltip.in').remove();
					if(calEvent.kOOL_tooltip) {
						eventJQObj.tooltip({
							html: true,
							title: calEvent.kOOL_tooltip,
							container: 'body',
							trigger: 'manual'
						});
						eventJQObj.tooltip('show');
					}
				}
			},

			//Resizing a reservation only changes the end
			eventResize: function(event, delta, revertFunc, jsEvent, ui, view) {
				$('.tooltip.in').remove();
				sendReq('inc/ajax.php', 'action,id,mode,secondDelta', 'fceditres,'+event.id+',editEnd,'+delta.asSeconds(), fc_check_redraw);
			},

			//Add editIcons to each event
			eventRender: function(calEvent, element, view) {
				if(calEvent.kOOL_editIcons) {
					cur = element[0].innerHTML;
					edit = '<span class="fcEventEditIcons">'+calEvent.kOOL_editIcons+'</span>';
					element[0].innerHTML = edit+cur;
				}
				if (calEvent.isMod) {
					var color = calEvent.color.substr(1);
					var highlightColor = get_highlight_color(color);
					element.css('background', 'repeating-linear-gradient(45deg,#'+color+',#'+color+' 10px,#'+highlightColor+' 4px,#'+highlightColor+' 14px)');
				}
			}

		});

	});


function calcCalendarHeight() {
	var h = Math.max(450, $('#main-table-layout').height() - 100);
	return h;
}



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
	$('input[name="sel_repeat_dates"]').parent().on('dp.change', function(e) {
		$('input[name="rd_wiederholung"][value="dates"]').prop('checked',true);
	});

	$("body").on("click", '#chk_perm_filter', function(e) {
		if(this.checked == true) {
			jumpToUrl("index.php?action=set_perm_filter");
		} else {
			jumpToUrl("index.php?action=unset_perm_filter");
		}
	});

	$('body').on('ko-validate', '[name="formular"]', function() {
		var $validationOk = $(this).data('ko-validation-ok');

		var startDatum = ($('[name^="'+escapeSelector('koi[ko_reservation][startdatum]')+'"]').val()+"").trim();
		var endDatum = ($('[name^="'+escapeSelector('koi[ko_reservation][enddatum]')+'"]').val()+"").trim();
		var startZeit = ($('[name^="'+escapeSelector('koi[ko_reservation][startzeit]')+'"]').val()+"").trim();
		var endZeit = ($('[name^="'+escapeSelector('koi[ko_reservation][endzeit]')+'"]').val()+"").trim();

		var okFields = $(this).data('ko-validation-ok-fields');
		var nokFields = $(this).data('ko-validation-nok-fields');
		var ok = $(this).data('ko-validation-ok');

		var index = 0;
		if (endDatum && endDatum != startDatum && startZeit && !endZeit) {
			index = okFields.indexOf('koi[ko_reservation][endzeit]');
			if (index >= 0) okFields.splice(index);

			nokFields.push('koi[ko_reservation][endzeit]');
			ok = false;
		} else {
			index = nokFields.indexOf('koi[ko_reservation][endzeit]');
			if (index >= 0) nokFields.splice(index);

			okFields.push('koi[ko_reservation][endzeit]');
		}

		$(this).data('ko-validation-ok', ok);
		$(this).data('ko-validation-ok-fields', okFields);
		$(this).data('ko-validation-nok-fields', nokFields);
	});

	if(kOOL.module == 'reservation') {
		$('.richtexteditor').ckeditor({customConfig : '/reservation/inc/ckeditor_custom_config.js' });
	}

<?PHP
	if($do_action == "multiedit") {
?>


	$('div[id^="koi_ko_reservation_startdatum_"]').on('dp.change', function (e) {
		if ($('input[name="koi[ko_reservation][doForAll]"]') === 'undefined') return;

		var startfield = $(this).find('input').attr('name');
		var regex = /.*\[(.*)\]/gm;
		var m;

		while ((m = regex.exec(startfield)) !== null) {
			if (m.index === regex.lastIndex) {
				regex.lastIndex++;
			}

			m.forEach(function (match, groupIndex) {
				if (groupIndex === 1) {
					var id = match;
					var endfield = "koi[ko_reservation][enddatum][" + id + "]";
					$("input[name='" + endfield + "']").val($("input[name='" + startfield + "']").val())
				}
			});
		}
	});

	if ($('input[name="koi[ko_reservation][doForAll]"]') !== undefined &&
		$('input[name="koi[ko_reservation][doForAll]"]').length > 0) {

		var startfields = $('input[name^="koi[ko_reservation][startdatum]"]');
		var regex = /.*\[(\d*)\]/gm;
		var m;

		if (startfields !== undefined && startfields.length > 0) {
			startfields.each(function (index) {
				while ((m = regex.exec($(this).attr("name"))) !== null) {
					if (m.index === regex.lastIndex) {
						regex.lastIndex++;
					}

					m.forEach(function (match, groupIndex) {
						if (groupIndex === 1) {
							var id = match;
							var endfield = $("input[name='koi[ko_reservation][enddatum][" + id + "]']");
							if (endfield.val() === '') {
								endfield.val($(this).val());
							}
						}
					});
				}
			});
		}
	}

<?PHP
}
?>

});


-->
</script>
