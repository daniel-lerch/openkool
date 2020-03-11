<?php
namespace kOOL;

require_once __DIR__.'/recurrence.php';
use HMuenzer\recurrence;


class ICalReader
{
	protected function parseString($str) {
		if(is_resource($str)) {
			$stream = $str;
		} else {
			$stream = fopen('php://memory','r+');
			fwrite($stream,$str);
			rewind($stream);
		}
		$events = array();
		while(!feof($stream)) {
			if($this->goToEvent($stream)) {
				$events[] = $this->parseEvent($stream);
			}
		}
		return $events;
	}

	protected function goToEvent($stream) {
		while(!feof($stream)) {
			$line = $this->readLine($stream);
			if($line == 'BEGIN:VEVENT') {
				return true;
			}
		}
		return false;
	}

	protected function parseEvent($stream) {
		$event = array();
		$alarm = FALSE;
		while(!feof($stream)) {
			$line = $this->readLine($stream);
			if($line == 'END:VEVENT') break;

			//Ignore VALARM
			// Necessary as VALARM can have it's own UID which would conflict with events's UID
			if($line == 'BEGIN:VALARM') {
				$alarm = TRUE;
				continue;
			}
			if($line == 'END:VALARM') {
				$alarm = FALSE;
				continue;
			}
			if($alarm) continue;

			if(ctype_space($line[0])) {
				end($event);
				if(is_array(current($event))) {
					$a =& $event[key($event)];
					end($a);
					$a[key($a)] .= substr($line,1);
				} else {
					$event[key($event)] .= substr($line,1);
				}
			} else {
				list($name,$value) = explode(':',$line,2);
				$ex = explode(';',$name);
				$name = array_shift($ex);
				foreach($ex as $e) {
					if(substr($e,0,5) == 'TZID=') {
						$tzid = substr($e,5);
						//Some feeds add quotes around the time zone, so we have to remove them
						$tzid = str_replace('"', '', html_entity_decode($tzid, ENT_COMPAT | ENT_HTML401, 'utf-8'));
						if(isset($event['TZID']) && $event['TZID'] != $tzid) {
							//throw new \Exception('The iCal reader only supports one timezone (TZID) per event. For this event several different timezones were specified.');
						}
						$event['TZID'] = $tzid;
					}
					if($name == 'RECURRENCE-ID' && substr($e,0,6) == 'RANGE=') {
						$event['RECURRENCE-ID-RANGE'] = substr($e,6);
					}
				}
				if(isset($event[$name])) {
					if(!is_array($event[$name])) {
						$event[$name] = array($event[$name]);
					}
					$event[$name][] = $value;
				} else {
					$event[$name] = $value;
				}
			}
		}
		array_walk_recursive($event,function(&$value) {$value = trim($value);});
		return $event;
	}

	protected function readLine($stream) {
		return rtrim(fgets($stream),"\r\n");
	}

	public function getEvents($str, $egid, $static_title='') {
		$recurrenceCountLimit = 2000;
		$recurrenceDateLimit = new \DateTime;
		$recurrenceDateLimit->add(new \DateInterval('P2Y'));
		$lowerDateLimit = new \DateTime();
		$targetTimeZone = new \DateTimeZone(date_default_timezone_get());

		if(is_string($str) && strpos($str, 'BEGIN:VCALENDAR') === false) {
			$str = fopen($str,'r');
		}
		$data = $this->parseString($str);

		// extract all events with recurrence-id (specialization of dates or ranges in a recurrence)
		$r_events = array();
		foreach($data as $i => $d) {
			if(!empty($d['RECURRENCE-ID'])) {
				$d['RECURRENCE-ID'] = strtotime($d['RECURRENCE-ID']);
				$r_events[$d['UID']][] = $d;
				unset($data[$i]);
			}
		}

		$events = array();
		foreach($data as $d) {
			$e = array();

			$e['eventgruppen_id'] = $egid;
			$e['last_change'] = date('Y-m-d H:i:s');
			$e['import_id'] = 'eventgroup'.$egid.':'.$d['UID'];

			if($static_title) {
				$e['title'] = $static_title;
				$e['kommentar'] = '';
			} else {
				if(isset($d['SUMMARY'])) $e['title'] = $this->decodeString($d['SUMMARY']);
				if(isset($d['DESCRIPTION'])) $e['kommentar'] = $this->decodeString($d['DESCRIPTION']);
			}
			if(isset($d['LOCATION'])) $e['room'] = $this->decodeString($d['LOCATION']);
			if(isset($d['URL'])) $e['url'] = $d['URL'];

			$recurrence = new recurrence($d);
			$dates = array();
			if($recurrence->error) {
				try {
					$timezone = $d['TZID'] ? new \DateTimeZone($d['TZID']) : null;
				} catch(\Exception $exc) {
					$timezone = null;
				}
				$dates[] = array(
					'start' => new \DateTime($d['DTSTART'],$timezone),
					'end' => $d['DTEND'] ? new \DateTime($d['DTEND'],$timezone) : false
				);
			} else {
				for($c = 0; $c < $recurrenceCountLimit && ($ds = $recurrence->next()) != false; $c++) {
					if(substr($ds['dtstart'],0,strpos($ds['dtstart'],'-')) > 3000) continue;

					$start = new \DateTime($ds['dtstart']);
					if($start > $recurrenceDateLimit) continue;
					$end = new \DateTime($ds['dtend']);

					//Set data to base event
					$title = $e['title'];
					$kommentar = $e['kommentar'];
					$room = $e['room'];
					$url = $e['url'];

					//Find recurrence entry which is set to overwrite default data from base recurring event
					if(isset($r_events[$d['UID']])) {
						$rid = strtotime($ds['recurrence-id']);

						$range = isset($r_event['RECURRENCE-ID-RANGE']) ? $r_event['RECURRENCE-ID-RANGE'] : '';
						foreach($r_events[$d['UID']] as $r_event) {
							if($r_event['RECURRENCE-ID'] == $rid ||
								($r_event['RECURRENCE-ID'] > $rid && $range == 'THISANDPRIOR') ||
								($r_event['RECURRENCE-ID'] < $rid && $range == 'THISANDFUTURE')) {
								try {
									$timezone = $r_event['TZID'] ? new \DateTimeZone($r_event['TZID']) : null;
								} catch(\Exception $exc) {
									$timezone = null;
								}
								$start = new \DateTime($r_event['DTSTART'],$timezone);
								$end = $r_event['DTEND'] ? new \DateTime($r_event['DTEND'],$timezone) : false;

								if($static_title) {
									$title = $static_title;
									$kommentar = '';
								} else {
									if(isset($r_event['SUMMARY'])) $title = $this->decodeString($r_event['SUMMARY']);
									else $title = '';
									if(isset($r_event['DESCRIPTION'])) $kommentar = $this->decodeString($r_event['DESCRIPTION']);
									else $kommentar = '';
								}
								if(isset($r_event['LOCATION'])) $room = $this->decodeString($r_event['LOCATION']);
								else $room = '';
								if(isset($r_event['URL'])) $url = $r_event['URL'];
								else $url = '';

							}
						}
					}

					$dates[] = array(
						'start' => $start,
						'end' => $end,
						'title' => $title,
						'kommentar' => $kommentar,
						'room' => $room,
						'url' => $url,
					);
				}
			}

			$import_id = $e['import_id'];
			foreach($dates as $date) {

				if(!$date['end']) {
					//If no enddate is given, set the event's duration to one hour
					$date['end'] = clone $date['start'];
					$date['end']->add(new \DateInterval('PT1H'));
				}

				$date['start']->setTimezone($targetTimeZone);
				$date['end']->setTimezone($targetTimeZone);

				if(!$lowerDateLimit || $lowerDateLimit->format('Ymd') <= $date['end']->format('Ymd')) {

					if($date['start']->format('H:i:s') == '00:00:00' && $date['end']->format('H:i:s') == '00:00:00') {
						//All day event, enddate is given as +1
						$date['end']->sub(new \DateInterval('P1D'));
						$e['startzeit'] = '';
						$e['endzeit'] = '';
					} else {
						$e['startzeit'] = $date['start']->format('H:i:s');
						$e['endzeit'] = $date['end']->format('H:i:s');
					}

					$e['startdatum'] = $date['start']->format('Y-m-d');
					$e['enddatum'] = $date['end']->format('Y-m-d');

					//Apply all data from recurring event (which may overwrite data from original entry)
					if($date['title']) $e['title'] = $date['title'];
					if($date['kommentar']) $e['kommentar'] = $date['kommentar'];
					if($date['room']) $e['room'] = $date['room'];
					if($date['url']) $e['url'] = $date['url'];

					if(count($dates) > 1) {
						$e['import_id'] = $import_id.'_'.$date['start']->format('Ymd');
					}

					$events[] = $e;
				}
			}

		}
		return $events;
	}

	/**
	 * Get a list of absence entries from a ics-file
	 *
	 * @param $icalFile
	 * @return array
	 * @throws \Exception
	 */
	public function getAbsences($icalFile) {
		$recurrenceCountLimit = 2000;
		$recurrenceDateLimit = new \DateTime;
		$recurrenceDateLimit->add(new \DateInterval('P2Y'));
		$lowerDateLimit = new \DateTime();
		$targetTimeZone = new \DateTimeZone(date_default_timezone_get());

		if (is_string($icalFile) && strpos($icalFile, 'BEGIN:VCALENDAR') === FALSE) {
			$icalFile = fopen($icalFile, 'r');
		}
		$icalEntries = $this->parseString($icalFile);

		$r_absences = [];
		foreach ($icalEntries as $key => $icalEntry) {
			if (!empty($icalEntry['RECURRENCE-ID'])) {
				$icalEntry['RECURRENCE-ID'] = strtotime($icalEntry['RECURRENCE-ID']);
				$r_absences[$icalEntry['UID']][] = $icalEntry;
				unset($icalEntries[$key]);
			}

			if($icalEntry['X-MICROSOFT-CDO-BUSYSTATUS'] != "OOF") {
				unset($icalEntries[$key]);
			}
		}

		$absences = [];
		foreach ($icalEntries as $icalEntry) {
			$absence = [];
			$absence['crdate'] = date('Y-m-d H:i:s');
			$absence['ical_id'] = $icalEntry['UID'];

			if (isset($icalEntry['SUMMARY'])) $absence['title'] = $this->decodeString($icalEntry['SUMMARY']);
			if (isset($icalEntry['DESCRIPTION'])) $absence['description'] = $this->decodeString($icalEntry['DESCRIPTION']);

			$recurrence = new recurrence($icalEntry);
			$dates = [];
			if ($recurrence->error) {
				try {
					$timezone = $icalEntry['TZID'] ? new \DateTimeZone($icalEntry['TZID']) : NULL;
				} catch (\Exception $exc) {
					$timezone = NULL;
				}
				$dates[] = [
					'start' => new \DateTime($icalEntry['DTSTART'], $timezone),
					'end' => $icalEntry['DTEND'] ? new \DateTime($icalEntry['DTEND'], $timezone) : FALSE,
					'title' => $absence['title'],
					'description' => $absence['description'],
				];
			} else {
				for ($c = 0; $c < $recurrenceCountLimit && ($ds = $recurrence->next()) != FALSE; $c++) {
					if (substr($ds['dtstart'], 0, strpos($ds['dtstart'], '-')) > 3000) continue;

					$start = new \DateTime($ds['dtstart']);
					if ($start > $recurrenceDateLimit) continue;
					$end = new \DateTime($ds['dtend']);

					//Set data to base event
					$title = $absence['title'];
					$description = $absence['description'];
					$r_absence = [];

					//Find recurrence entry which is set to overwrite default data from base recurring event
					if (isset($r_absences[$icalEntry['UID']])) {
						$rid = strtotime($ds['recurrence-id']);

						$range = isset($r_absence['RECURRENCE-ID-RANGE']) ? $r_absence['RECURRENCE-ID-RANGE'] : '';
						foreach ($r_absences[$icalEntry['UID']] as $r_absence) {
							if ($r_absence['RECURRENCE-ID'] == $rid ||
								($r_absence['RECURRENCE-ID'] > $rid && $range == 'THISANDPRIOR') ||
								($r_absence['RECURRENCE-ID'] < $rid && $range == 'THISANDFUTURE')) {
								try {
									$timezone = $r_absence['TZID'] ? new \DateTimeZone($r_absence['TZID']) : NULL;
								} catch (\Exception $exc) {
									$timezone = NULL;
								}
								$start = new \DateTime($r_absence['DTSTART'], $timezone);
								$end = $r_absence['DTEND'] ? new \DateTime($r_absence['DTEND'], $timezone) : FALSE;

								if (isset($r_absence['SUMMARY'])) $title = $this->decodeString($r_absence['SUMMARY']);
								else $title = '';
								if (isset($r_absence['DESCRIPTION'])) $description = $this->decodeString($r_absence['DESCRIPTION']);
								else $description = '';
							}
						}
					}

					$dates[] = [
						'start' => $start,
						'end' => $end,
						'title' => $title,
						'description' => $description,
					];
				}
			}

			$ical_id = $absence['ical_id'];
			foreach ($dates as $date) {
				if (!$date['end']) {
					$date['end'] = clone $date['start'];
					$date['end']->add(new \DateInterval('PT1H'));
				}

				$date['start']->setTimezone($targetTimeZone);
				$date['end']->setTimezone($targetTimeZone);

				if (!$lowerDateLimit || $lowerDateLimit->format('Ymd') <= $date['end']->format('Ymd')) {
					if ($date['start']->format('H:i:s') == '00:00:00' && $date['end']->format('H:i:s') == '00:00:00') {
						//All day event, enddate is given as +1
						$date['end']->sub(new \DateInterval('P1D'));
					}

					$absence['from_date'] = $date['start']->format('Y-m-d');
					$absence['to_date'] = $date['end']->format('Y-m-d');
					if ($date['title']) $absence['title'] = $date['title'];
					$absence['description'] = $absence['title'];
					if ($date['description']) $absence['description'].= ", " . $date['description'];

					if (count($dates) > 1) {
						$absence['ical_id'] = $ical_id. '_' . $date['start']->format('Ymd');
					}

					$absence['type'] = ko_daten_absence_map_type($absence['title']);
					unset($absence['title']);

					$absences[] = $absence;
				}
			}
		}
		return $absences;
	}


	protected function decodeString($s) {
		$new = stripslashes(iconv('UTF-8', 'ISO-8859-1//TRANSLIT', str_replace("\\n","\n",$s)));
		if($new == '') $new = stripslashes(utf8_decode(str_replace("\\n","\n",$s)));
		return $new;
	}
}
