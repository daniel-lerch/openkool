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
var lastView = '<?php print $_SESSION['cal_view']; ?>';

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


	/**
		* Initialize the JS calendar
		*/
	$(document).ready(function() {

		var buttonLabels = {
			prev: "<?php print getLL('daten_navigation_previous'); ?>",
			next: '<?php print getLL('daten_navigation_next'); ?>',
			prevYear: '<?php print getLL('daten_navigation_previous2'); ?>',
			nextYear: '<?php print getLL('daten_navigation_next2'); ?>',
			today: '<?php print getLL('time_today'); ?>',
			month: '<?php print getLL('daten_cal_month'); ?>',
			agendaWeek: '<?php print getLL('daten_cal_week'); ?>',
			agendaDay: '<?php print getLL('daten_cal_day'); ?>'
		};

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

		$('body').on('mouseover', '.fc-button', function() {
			var btnClass = this.className;
			var btnType = btnClass.replace(/^.*fc-([^ ]*)-button.*$/g, '$1');
			$(this).attr('title', buttonLabels[btnType]);
		});

		$(window).on('scroll', function() {
			$('.tooltip.in').remove();
		});

		var $koCalendar = $('#ko_calendar');
		$koCalendar.fullCalendar({
			//Layout for the header: Positions of the buttons
			locale: kOOL.language,
			titleRangeSeparator: " \u2014 ",
			height: calcCalendarHeight,

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
					timeFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_time_agenda')); ?>',
					slotLabelFormat: '<?php print str_replace("'", "\'", getLL('fullcalendar_time_agenda')); ?>',
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
			weekNumbers: true,
			weekNumbersWithinDays: true,

			navLinks: true,
			navLinkDayClick: function(date, jsEvent) {
				$koCalendar.fullCalendar('changeView', 'agendaDay');
				$koCalendar.fullCalendar('gotoDate', date);
			},
			navLinkWeekClick: function(date, jsEvent) {
				$koCalendar.fullCalendar('changeView', 'agendaWeek');
				$koCalendar.fullCalendar('gotoDate', date);
			},

			//Layout for the header: Positions of the buttons
			header: {
				left: 'title',
				center: 'prevYear,prev,today,next,nextYear',
				right: 'month,agendaWeek,agendaDay'
			},
			footer: {
				left: 'month,agendaWeek,agendaDay',
				center: '',
				right: ''
			},

			//Navigation buttons with icons and titles
			buttonIcons: {
				prev: 'left-single-arrow',
				next: 'right-single-arrow',
				prevYear: 'left-double-arrow',
				nextYear: 'right-double-arrow',
				today: 'k-today',
				month: 'k-month',
				agendaWeek: 'k-week',
				agendaDay: 'k-day'
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
			viewRender: function(view) {
				var setDate = view.start.format('YYYY-MM-DD');
				if (view.name == 'month' && view.start.date() > 7) {
					setDate = view.start.add(1, 'month').startOf('month').format('YYYY-MM-DD');
				}
				sendReq('inc/ajax.php', 'action,ymd,view', 'fcsetdate,'+setDate+','+view.name, do_element);
			},

			//Clicking on a day opens the form to enter a new event
			dayClick: function(dayDate, jsEvent, view) {
				$('.tooltip.in').remove();
				<?php if($access['daten']['MAX'] > 1 && db_get_count("ko_eventgruppen", "id", "AND `type` = '0'") > 0) { ?>
					//Send only date and time (without timezone info)
					day = dayDate.format('YYYY-MM-DD HH:mm:SS');
					jumpToUrl('index.php?action=neuer_termin&dayDate='+escape(day));
				<?php } ?>
			},

			//Selecting an event range
			select: function(startDate, endDate, jsEvent, view) {
				<?php if($access['daten']['MAX'] > 1 && db_get_count("ko_eventgruppen", "id", "AND `type` = '0'") > 0) { ?>
					var dateBeforeEnd = new moment(endDate);
					dateBeforeEnd.subtract(1, 'days');

					//Send only date and time (without timezone info)
					dayStart = startDate.format('YYYY-MM-DD HH:mm:SS');
					dayEnd = endDate.format('YYYY-MM-DD HH:mm:SS');
					dayBeforeEnd = dateBeforeEnd.format('YYYY-MM-DD HH:mm:SS');
					if (!startDate.hasTime() && !endDate.hasTime() && dateBeforeEnd.isSame(startDate)) {
						jumpToUrl('index.php?action=neuer_termin&dayDate='+escape(dayStart));
					} else if (!startDate.hasTime()) {
						jumpToUrl('index.php?action=neuer_termin&dayDate='+escape(dayStart)+'&endDate='+escape(dayBeforeEnd));
					} else {
						jumpToUrl('index.php?action=neuer_termin&dayDate='+escape(dayStart)+'&endDate='+escape(dayEnd));
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
				/*var outerHeight = $(jsEvent.target).closest('.fc-event').outerHeight();
				var innerHeight = $(jsEvent.target).closest('.fc-event').height();
				icons.height(outerHeight);
				icons.css('margin-top', '-' + ((outerHeight - innerHeight) / 2).toString() + 'px');

				var outerWidth = $(jsEvent.target).closest('.fc-event').outerWidth();
				var innerWidth = $(jsEvent.target).closest('.fc-event').width();
				icons.css('margin-left', '-' + ((outerWidth - innerWidth) / 2).toString() + 'px');*/

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

			//Dropping an event changes start and end
			eventDrop: function(event, delta, revertFunc, jsEvent, ui, view) {
				$('.tooltip.in').remove();
				sendReq('inc/ajax.php', 'action,id,mode,allDay,secondDelta', 'fceditevent,'+event.id+',drop,'+(event.allDay ? 1 : 0)+','+delta.asSeconds(), fc_check_redraw);
			},

			//Resizing an event only changes the end
			eventResize: function(event, delta, revertFunc, jsEvent, ui, view) {
				$('.tooltip.in').remove();
				sendReq('inc/ajax.php', 'action,id,mode,secondDelta', 'fceditevent,'+event.id+',editEnd,'+delta.asSeconds(), fc_check_redraw);
			},

			windowResize: function(view) {
				if($(window).width() < 514){
					$('#ko_calendar').fullCalendar( 'changeView', 'basicDay' );
				} else {
					$('#ko_calendar').fullCalendar( 'changeView', 'month' );
				}
			},

			//Click to edit
			eventClick: function(calEvent, jsEvent, view) {
				//Dont edit event if deleteIcon has been clicked (with id item[0-9]*[ms])
				if($(this).data('actionIcon') !== true) {
					if(calEvent.editable) {
						document.location = "index.php?action=edit_termin&id="+calEvent.id;
					}
					//Add link to absence item
					else if($(this).hasClass('fc-absence')) {
						if($(this).hasClass('fc-absence-readonly') === false) {
							document.location = "/daten/index.php?action=edit_absence&id="+calEvent.id;
						}
					}
					else if($(this).hasClass('fc-amtstag')) {
						if($(this).hasClass('fc-amtstag-readonly') === false) {
							document.location = "/rota/index.php?action=schedule&amtstag="+calEvent.start;
						}
					}
					//Add link to birthday entries: Set filter for people module
					else if($(this).hasClass('fc-birthday')) {
						stamp = new Date(calEvent.start);
						dob = stamp.getDate()+'-'+(stamp.getMonth()+1);
						document.location = "/leute/index.php?action=set_dobfilter&dob="+dob;
					}
				} else if ($(this).data('delete') === true) {
					var id = calEvent.id;
					if (!id) return false;

					c=confirm('<?= getLL('daten_delete_event_confirm') ?>');
					if(!c) return false;
					sendReq(
						'inc/ajax.php',
						'action,id',
						'fcdelevent,'+id);
					$('#ko_calendar').fullCalendar('removeEvents', id);
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

			//Add editIcons to each event
			eventRender: function(calEvent, element, view) {
				//dumpProps(element);
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

				element.find('.fc-title').html(calEvent.title);
			},
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
	$('input[name="sel_repeat_dates"]').parent().on('dp.change', function(e) {
		$('input[name="rd_wiederholung"][value="dates"]').prop('checked',true);
	});

	$('body').on("click", "#chk_perm_filter", function(e) {
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

	<?php if (ko_module_installed('reservation')) { ?>
	// automatically insert event start- and endtime into reservation form
	$('#sel_ds1_sel_do_res').click(function() {
		var startEl = document.getElementsByName('koi[ko_event][startzeit][0]');
		if (startEl.length > 0) {
			var $resStart = $('[name="res_startzeit"]');
			var $resEnd = $('[name="res_endzeit"]');
			if (!($resStart.val() && $resEnd.val())) {
				$resStart.val($(startEl[0]).val());
				$resEnd.val($(document.getElementsByName('koi[ko_event][endzeit][0]')[0]).val());
			}
		}
	});
	<?php } ?>

	$('body').on('ko-validate', '[name="formular"]', function() {
		var $validationOk = $(this).data('ko-validation-ok');

		var startDatum = ($('[name^="'+escapeSelector('koi[ko_event][startdatum]')+'"]').val()+"").trim();
		var endDatum = ($('[name^="'+escapeSelector('koi[ko_event][enddatum]')+'"]').val()+"").trim();
		var startZeit = ($('[name^="'+escapeSelector('koi[ko_event][startzeit]')+'"]').val()+"").trim();
		var endZeit = ($('[name^="'+escapeSelector('koi[ko_event][endzeit]')+'"]').val()+"").trim();

		var okFields = $(this).data('ko-validation-ok-fields');
		var nokFields = $(this).data('ko-validation-nok-fields');
		var ok = $(this).data('ko-validation-ok');

		var index = 0;
		if (endDatum && endDatum != startDatum && startZeit && !endZeit) {
			index = okFields.indexOf('koi[ko_event][endzeit]');
			if (index >= 0) okFields.splice(index);

			nokFields.push('koi[ko_event][endzeit]');
			ok = false;
		} else {
			index = nokFields.indexOf('koi[ko_event][endzeit]');
			if (index >= 0) nokFields.splice(index);

			okFields.push('koi[ko_event][endzeit]');
		}

		$(this).data('ko-validation-ok', ok);
		$(this).data('ko-validation-ok-fields', okFields);
		$(this).data('ko-validation-nok-fields', nokFields);
	});

	if(kOOL.module == 'daten') {
		$('.richtexteditor').ckeditor({customConfig : '/daten/inc/ckeditor_custom_config.js' });
	}
});



function seleventgroup_before_change(newValue, oldValue) {
	if (newValue && newValue.substr(0, 1) != 'i') {
		return selEventGroup(newValue);
	}
	return true;
}


-->
</script>
