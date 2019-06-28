<?php
/***************************************************************
*  Copyright notice
*
*  Loosly based on class ICalExporter from ical2scheduler from dhtmlx.com (GNU GPL v2)
*  Modified for kOOL - the church tool by Renzo Lauper
* 
*  (c) 2003-2015 Renzo Lauper (renzo@churchtool.org)
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



class iCalReader {
	//returns the string value of the day instead of its ordinal number or return number
	function getConvertDay($i, $mode=false) {
		$a = array ('SU','MO','TU','WE','TH','FR','SA');
		if($mode) {
			for($y=0;$y<sizeof($a);$y++){
				if($a[$y] == $i) {
					return $y;
				}
			}
		}
		else{
			return $a[$i];	
		}
	}
	
	//returns the appropriate line 
	function getConvertType($i, $mode=false) {
		$a = array ('day' => 'DAILY','week' => 'WEEKLY','month' => 'MONTHLY','year' => 'YEARLY');
		if($mode) {
			foreach ($a as $key => $value) {
				if($a[$key] == $i) {
					return $key;
				}
			}
		}
		else {
			return $a[$i];
		}
	}
	
	
	function getTime($date) {
		$mas = explode('-',$date);
		if($mas[0] == 9999) { 
			return '99990201T000000';
		}
		else {
			return date('Ymd\THis',strtotime($date));
		}
	}
	


	//give date in ical format and return date in MySQL format
	function getMySQLDate($str) {
		preg_match('/[0-9]{8}[T][0-9]{6}/',trim($str),$date);
		if(isset($date[0])) {
			if($date[0] != '') {
				$y = substr($date[0], 0, 4);
				$mn = substr($date[0], 4, 2);
				$d = substr($date[0], 6, 2);
				$h = substr($date[0], 9, 2);
				$m = substr($date[0], 11, 2);
				$s = substr($date[0], 13, 2);
				return $y.'-'.$mn.'-'.$d.' '.$h.':'.$m.':'.$s;
			}
		}
		elseif(strlen(trim($str)) == 8) {
			$y = substr($str, 0, 4);
			$mn = substr($str, 4, 2);
			$d = substr($str, 6, 2);
			return $y.'-'.$mn.'-'.$d.' 00:00:00';
		}
	}



	//get parse a string into an array
	function getParseString($str) {
		$arr_n = array();
		$arr = explode('BEGIN:VEVENT',$str);
		for($x=1;$x<sizeof($arr);$x++) {
			$arr2 = explode("\n",$arr[$x]);
			for($y=1;$y<sizeof($arr2);$y++) {
				$mas = explode(':',$arr2[$y]);
				$mas_ = explode(';',$mas[0]);
				if(isset($mas_[0])){
					$mas[0] = $mas_[0];
				}
				//Implode mas[1+] again, as they were exploded at ':' which might be part of the content
				$all = $mas;
				unset($all[0]);
				$mas[1] = implode(':', $all);
				while ($y + 1 < sizeof($arr2) && substr($arr2[$y + 1], 0, 1) == ' ') $mas[1] .= "\n" . substr($arr2[($y++) + 1], 1);
				switch(trim($mas[0])) {
					case 'DTSTART':
						$arr_n[$x]['start_date'] = date('Y-m-d H:i:s', strtotime($mas[1]));
						break;
						
					case 'DTEND':
						$arr_n[$x]['end_date'] = date('Y-m-d H:i:s', strtotime($mas[1]));
						break;
						
					case 'RRULE':
						$rrule = explode(';', $mas[1]);
						for($z=0;$z<sizeof($rrule);$z++) {
							$rrule_n = explode('=', $rrule[$z]);
							switch($rrule_n[0]) {
								case 'FREQ':
									$arr_n[$x]['type'] = $this->getConvertType($rrule_n[1], true);
									break;
									
								case 'INTERVAL':
									$arr_n[$x]['interval'] = $rrule_n[1];
									break;
									
								case 'COUNT':
									$arr_n[$x]['count'] = $rrule_n[1];
									break;

								case 'BYDAY':
									$bayday = explode(',',$rrule_n[1]);
									if(sizeof($bayday) == 1) {
										if(strlen(trim($bayday[0])) == 3) {
											$arr_n[$x]['day'] = substr($bayday[0], 0, 1);
											$arr_n[$x]['counts'] = $this->getConvertDay(substr($bayday[0], 1, 2), true);
										}
										else {
											$arr_n[$x]['days'] = $this->getConvertDay($bayday[0], true);
										}
									}
									else {
										$arr_n[$x]['days'] = '';
										for($nx=0;$nx<sizeof($bayday);$nx++) {
											$arr_n[$x]['days'] .= $this->getConvertDay($bayday[$nx], true);
											if($nx != sizeof($bayday)-1) {
												$arr_n[$x]['days'] .= ',';
											}
										}
									}
									break;
									
								case 'UNTIL':
									$arr_n[$x]['until'] = $this->getMySQLDate($rrule_n[1]);
									break;
							}
						}
						break;
						
					case 'EXDATE':
						$exdate = explode(',',trim($mas[1]));
						if(sizeof($exdate) == 1) {
							$arr_n[$x]['exdate'][] = $this->getMySQLDate($exdate[0]);
						}
						else {
							for($nx=0;$nx<sizeof($exdate);$nx++) {
								$arr_n[$x]['exdate'][$nx] = $this->getMySQLDate($exdate[$nx]);
							}
						}
						break;
					
					case 'RECURRENCE-ID':	
						$arr_n[$x]['rec_id'] = $this->getMySQLDate($mas[1]);
						break;
						
					case 'UID':
						$arr_n[$x]['event_id'] = trim($mas[1]);
						break;
						
					case 'SUMMARY':
						$arr_n[$x]['text'] = trim($mas[1]);
						break;

					case 'DESCRIPTION':
						$arr_n[$x]['description'] = trim($mas[1]);
						break;

					case 'LOCATION':
						$arr_n[$x]['location'] = trim($mas[1]);
						break;

					case 'URL':
						$arr_n[$x]['url'] = trim($mas[1]);
						break;
				}
			}
			if(isset($arr_n[$x]['rec_id'])){
				$arr_n[$x]['event_pid'] = $arr_n[$x]['event_id'];
			}
			if(isset($arr_n[$x]['exdate'])){
				$arr_n[$x]['event_pid'] = $arr_n[$x]['event_id'];
			}
		}
		return $arr_n;
	}
	



	/**
	 * Return an array of events read from the given ics file
	 * Events are prepared for insertion into DB table ko_event
	 *
	 * @param $str string: ics string from the iCal feed
	 * @param $egid int: Event group id to assign the events to
	 * @returns $events array: An array of events extracted from the ics string, ready to be inserted into ko_event
	 */
	function getEvents($str, $egid) {
		if(strpos($str, 'BEGIN:VCALENDAR') === false) {
			$str = file_get_contents($str);
		}
		$data = $this->getParseString($str);

		$events = array();
		foreach($data as $d) {
			$e = array();

			$e['eventgruppen_id'] = $egid;
			$e['last_change'] = date('Y-m-d H:i:s');
			$e['import_id'] = 'eventgroup'.$egid.':'.$d['event_id'];
			if(isset($d['text'])) $e['title'] = stripslashes(utf8_decode($d['text']));
			if(isset($d['description'])) $e['kommentar'] = stripslashes(utf8_decode($d['description']));
			if(isset($d['location'])) $e['room'] = stripslashes(utf8_decode($d['location']));
			if(isset($d['url'])) $e['url'] = $d['url'];

			//Start and end date/time
			$ts1 = strtotime($d['start_date']);
			if($d['end_date']) {
				$ts2 = strtotime($d['end_date']);
			} else {
				//If no enddate is given, set the event's duration to one hour
				$ts2 = $ts1 + 3600;
			}
			$e['startdatum'] = date('Y-m-d', $ts1);

			//All day event, enddate is given as +1
			if(substr($d['start_date'], -8) == '00:00:00' && substr($d['end_date'], -8) == '00:00:00') {
				$e['enddatum'] = date('Y-m-d', strtotime(date('Y-m-d', $ts2).' -1 day'));
				$e['startzeit'] = $e['endzeit'] = '';
			}
			//Date and time given
			else {
				$e['enddatum'] = date('Y-m-d', $ts2);
				$e['startzeit'] = date('H:i:s', $ts1);
				$e['endzeit'] = date('H:i:s', $ts2);
			}


			if(isset($d['type']) && $d['type'] != '') {
				//Recurring events
				$this->getRecurringEvents($d, $e, $events);
			} else {
				//Single event
        if(strtotime($e['enddatum']) >= strtotime(date('Y-m-d'))) $events[] = $e;
			}
		}//foreach(data as d)

		return $events;
	}//getEvents()




	/**
	 * Create a series of events from recurring ical events
	 * 
	 * @param $d array: Data array from this->getParseString() for this event (holding the recurring information)
	 * @param $e array: kOOL data for this event (only change date and event_id for the recurring events)
	 * @param &$events array: Array of imported events, to which new recurring events will be added (passed by reference)
	 */
	function getRecurringEvents($d, $e, &$events) {
		if(!$d['interval']) $d['interval'] = 1;

		if($d['until']) {
			$until = date('Y-m-d', strtotime($d['until']));
		} else if($d['count']) {
			$until = date('Y-m-d', strtotime($e['startdatum'].' +'.($d['count']*$d['interval']).' '.$d['type']));
		} else {
			//Set deadline to +2 years if not given
			$until = date('Y-m-d', strtotime('+2 year'));
		}


		//Remove time from exdates
		$exdates = array();
		if(isset($d['exdate']) && is_array($d['exdate'])) {
			foreach($d['exdate'] as $exd) {
				$exdates[] = substr($exd, 0, 10);
			}
		}

		$ts = strtotime($e['startdatum']);
		while($ts < strtotime($until)) {
			if(!in_array(date('Y-m-d', $ts), $exdates)) {
				$re = $e;
				$re['startdatum'] = date('Y-m-d', $ts);
				$re['enddatum'] = date('Y-m-d', $ts + (strtotime($e['enddatum'])-strtotime($e['startdatum'])));
				$re['import_id'] = $e['import_id'].'_'.date('Ymd', $ts);

				//Only future events
        if(strtotime($re['enddatum']) >= strtotime(date('Y-m-d'))) $events[] = $re;
			}

			$ts = strtotime(date('Y-m-d H:i:s', $ts).' +'.$d['interval'].' '.$d['type']);
		}
	}//getRecurringEvents()

}//class iCalReader
?>
