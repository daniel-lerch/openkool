CREATE TABLE `ko_admin` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `leute_id` mediumint(9) NOT NULL DEFAULT '0',
  `login` varchar(50) NOT NULL,
  `password` varchar(32) NOT NULL,
  `admin` text NOT NULL,
  `leute_admin` text NOT NULL,
  `leute_admin_filter` text NOT NULL,
  `leute_admin_spalten` text NOT NULL,
  `leute_admin_groups` text NOT NULL,
  `leute_admin_gs` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `leute_admin_assign` tinyint(4) NOT NULL,
  `res_admin` text NOT NULL,
  `rota_admin` text NOT NULL,
  `event_admin` text NOT NULL,
  `fileshare_admin` text NOT NULL,
  `kg_admin` text NOT NULL,
  `tapes_admin` text NOT NULL,
  `groups_admin` text NOT NULL,
  `donations_admin` text NOT NULL,
  `tracking_admin` text NOT NULL,
  `modules` text NOT NULL,
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `disabled` varchar(32) NOT NULL,
  `admingroups` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(50) NOT NULL,
	`kota_columns_ko_kleingruppen` text NOT NULL,
	`res_force_global` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`event_force_global` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`event_reminder_rights` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `ko_admin` VALUES(2, -1, 'ko_guest', '098f6bcd4621d373cade4e832627b4f6', '0', '0', '', '', '', 0, 0, '2', '1', '1', '0', '0', '0', '0', '', '', 'daten,reservation', '0000-00-00 00:00:00', '', '', '', '', '', 0, 0, 0);

CREATE TABLE `ko_admingroups` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `admin` text NOT NULL,
  `leute_admin` text NOT NULL,
  `leute_admin_filter` text NOT NULL,
  `leute_admin_spalten` text NOT NULL,
  `leute_admin_groups` text NOT NULL,
  `leute_admin_gs` tinyint(3) unsigned NOT NULL,
  `leute_admin_assign` tinyint(4) NOT NULL,
  `res_admin` text NOT NULL,
  `rota_admin` text NOT NULL,
  `event_admin` text NOT NULL,
  `fileshare_admin` text NOT NULL,
  `kg_admin` text NOT NULL,
  `tapes_admin` text NOT NULL,
  `groups_admin` text NOT NULL,
  `donations_admin` text NOT NULL,
  `tracking_admin` text NOT NULL,
  `modules` text NOT NULL,
	`kota_columns_ko_kleingruppen` text NOT NULL,
  `res_force_global` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `event_force_global` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`event_reminder_rights` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_donations` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `valutadate` date NOT NULL,
  `person` mediumint(8) unsigned NOT NULL,
  `account` mediumint(8) unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `reoccuring` varchar(10) NOT NULL,
  `promise` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `source` (`source`),
  KEY `date` (`date`),
  KEY `person` (`person`),
  KEY `account` (`account`),
  KEY `reoccuring` (`reoccuring`),
  KEY `valutadate` (`valutadate`),
  KEY `promise` (`promise`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_donations_accounts` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `number` varchar(50) NOT NULL,
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_etiketten` (
  `vorlage` varchar(32) NOT NULL DEFAULT '',
  `key` varchar(100) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  KEY `vorlage` (`vorlage`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'name', '3x8');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'per_row', '3');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'per_col', '8');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'border_top', '2');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'border_right', '2');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'border_bottom', '2');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'border_left', '2');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'spacing_horiz', '5');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'spacing_vert', '2');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'align_horiz', 'L');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'align_vert', 'T');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'textsize', '11');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'ra_textsize', '7');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'ra_margin_top', '5');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'ra_margin_left', '3');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'font', 'arial');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'ra_font', 'arial');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'pic_file', '');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'page_orientation', 'P');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'pic_x', '');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'pic_y', '');
INSERT INTO `ko_etiketten` VALUES('46033b974ad2d50859ea599df0dffba4', 'pic_w', '');

CREATE TABLE `ko_reminder` (
	`id` mediumint(9) NOT NULL AUTO_INCREMENT,
	`title` MEDIUMTEXT NOT NULL,
	`action` VARCHAR(30) NOT NULL,
	`filter` MEDIUMTEXT NOT NULL,
	`deadline` mediumint(9) NOT NULL,
	`subject` TEXT NOT NULL,
	`text` TEXT NOT NULL,
	`recipients_mails` TEXT NOT NULL,
	`recipients_groups` TEXT NOT NULL,
	`recipients_leute` TEXT NOT NULL,
	`status` tinyint(4) NOT NULL DEFAULT '0',
	`type` tinyint(4) NOT NULL,
	`crdate` DATETIME NOT NULL,
	`cruser` mediumint(9) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_reminder_mapping` (
	`id` mediumint(9) NOT NULL AUTO_INCREMENT,
	`reminder_id` mediumint(9) NOT NULL,
	`event_id` mediumint(9) NOT NULL DEFAULT '0',
	`leute_id` mediumint(9) NOT NULL DEFAULT '0',
	`crdate` DATETIME NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_event` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `eventgruppen_id` mediumint(9) NOT NULL,
  `startdatum` date NOT NULL,
  `enddatum` date NOT NULL,
  `startzeit` time NOT NULL,
  `endzeit` time NOT NULL,
  `title` varchar(255) NOT NULL,
  `room` varchar(200) NOT NULL,
  `kommentar` text NOT NULL,
  `kommentar2` text NOT NULL,
  `rota` tinyint(4) NOT NULL DEFAULT '0',
  `reservationen` text NOT NULL,
  `url` tinytext NOT NULL,
  `gs_gid` varchar(20) NOT NULL,
  `cdate` datetime NOT NULL,
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `last_change` datetime NOT NULL,
  `import_id` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `eventgruppen_id` (`eventgruppen_id`),
  KEY `dp` (`rota`),
  KEY `startdatum` (`startdatum`),
  KEY `import_id` (`import_id`(200))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_event_program` (
	`id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
	`pid` varchar(200) NOT NULL,
	`time` time NOT NULL DEFAULT '00:00:00',
	`title` text NOT NULL,
	`team` text NOT NULL,
	`crdate` datetime NOT NULL,
	`cruser` mediumint(9) NOT NULL,
	`sorting` mediumint(9) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_eventgruppen` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `calendar_id` mediumint(9) NOT NULL,
  `name` varchar(100) NOT NULL,
  `shortname` varchar(5) NOT NULL,
  `room` varchar(200) NOT NULL,
  `startzeit` time NOT NULL,
  `endzeit` time NOT NULL,
  `title` varchar(255) NOT NULL,
  `kommentar` text NOT NULL,
  `farbe` varchar(6) NOT NULL DEFAULT '',
  `resitems` varchar(255) NOT NULL DEFAULT '',
  `rota` tinyint(4) NOT NULL DEFAULT '0',
  `res_startzeit` time NOT NULL DEFAULT '00:00:00',
  `res_endzeit` time NOT NULL DEFAULT '00:00:00',
  `res_combined` tinyint(4) NOT NULL DEFAULT '0',
  `tapes` tinyint(4) NOT NULL DEFAULT '0',
  `url` tinytext NOT NULL,
  `moderation` tinyint(4) NOT NULL DEFAULT '0',
  `notify` varchar(250) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `gcal_url` varchar(250) NOT NULL,
  `ical_url` varchar(255) NOT NULL,
  `update` int(11) NOT NULL,
  `last_update` datetime NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `calendar_id` (`calendar_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_eventgruppen_program` (
	`id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
	`pid` varchar(200) NOT NULL,
	`time` time NOT NULL DEFAULT '00:00:00',
	`title` text NOT NULL,
	`team` text NOT NULL,
	`crdate` datetime NOT NULL,
	`cruser` mediumint(9) NOT NULL,
	`sorting` mediumint(9) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_event_calendar` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_event_mod` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `eventgruppen_id` mediumint(9) NOT NULL,
  `startdatum` date NOT NULL,
  `enddatum` date NOT NULL,
  `startzeit` time NOT NULL,
  `endzeit` time NOT NULL,
  `title` varchar(255) NOT NULL,
  `room` varchar(200) NOT NULL,
  `kommentar` varchar(200) NOT NULL,
  `kommentar2` text NOT NULL,
  `rota` tinyint(4) NOT NULL DEFAULT '0',
  `resitems` text NOT NULL,
  `res_startzeit` time NOT NULL,
  `res_endzeit` time NOT NULL,
  `url` tinytext NOT NULL,
  `gs_gid` varchar(20) NOT NULL,
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `_event_id` mediumint(9) NOT NULL DEFAULT '0',
  `_user_id` mediumint(9) NOT NULL DEFAULT '0',
  `_delete` tinyint(4) NOT NULL DEFAULT '0',
  `_crdate` datetime NOT NULL,
  `cdate` datetime NOT NULL,
  `last_change` datetime NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_familie` (
  `famid` mediumint(9) NOT NULL AUTO_INCREMENT,
  `nachname` varchar(50) NOT NULL DEFAULT '',
  `adresse` varchar(100) NOT NULL DEFAULT '',
  `adresse_zusatz` varchar(100) NOT NULL DEFAULT '',
  `plz` varchar(11) NOT NULL DEFAULT '',
  `ort` varchar(50) NOT NULL DEFAULT '',
  `land` varchar(50) NOT NULL DEFAULT '',
  `telp` varchar(30) NOT NULL DEFAULT '',
  `famanrede` varchar(100) NOT NULL DEFAULT '',
  `famfirstname` varchar(100) NOT NULL,
  `famlastname` varchar(100) NOT NULL,
  `famemail` enum('','husband','wife') NOT NULL,
  PRIMARY KEY (`famid`),
  KEY `nachname` (`nachname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_fileshare` (
  `id` varchar(32) NOT NULL DEFAULT '0',
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT '',
  `c_date` datetime NOT NULL,
  `parent` mediumint(9) NOT NULL DEFAULT '0',
  `filesize` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_fileshare_folders` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `parent` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user` mediumint(9) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `c_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `share_users` text NOT NULL,
  `comment` text NOT NULL,
  `share_rights` varchar(255) NOT NULL DEFAULT '',
  `flag` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`,`user`),
  KEY `flag` (`flag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_fileshare_sent` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `file_id` varchar(32) NOT NULL DEFAULT '',
  `recipient` varchar(255) NOT NULL DEFAULT '',
  `recipient_id` varchar(32) NOT NULL DEFAULT '',
  `d_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`,`recipient`),
  KEY `recipient_id` (`recipient_id`),
  KEY `d_date` (`d_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_filter` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `typ` varchar(20) NOT NULL DEFAULT '',
  `dbcol` varchar(200) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `group` varchar(20) NOT NULL,
  `allow_neg` tinyint(4) NOT NULL DEFAULT '0',
  `sql1` varchar(255) NOT NULL DEFAULT '',
  `sql2` varchar(255) NOT NULL DEFAULT '',
  `sql3` varchar(255) NOT NULL DEFAULT '',
  `numvars` tinyint(4) NOT NULL DEFAULT '0',
  `var1` varchar(50) NOT NULL DEFAULT '',
  `code1` longtext NOT NULL,
  `var2` varchar(50) NOT NULL DEFAULT '',
  `code2` longtext NOT NULL,
  `var3` varchar(50) NOT NULL DEFAULT '',
  `code3` longtext NOT NULL,
  `allow_fastfilter` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `group` (`group`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `ko_filter` VALUES(1, 'leute', 'anrede', 'salutation', 'person', 1, 'anrede REGEXP ''[VAR1]''', '', '', 1, 'salutation', '<select name="var1" size="0"><option value=""></option><option value="Herr">Herr</option><option value="Frau">Frau</option></select>', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(70, 'leute', 'ko_filter.name', 'filterpreset', 'misc', 0, '[VAR1]', '', '', 1, 'filterpreset', 'FCN:ko_specialfilter_filterpreset', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(2, 'leute', 'nachname', 'last name', 'person', 1, 'nachname REGEXP ''[VAR1]''', '', '', 1, 'last name', '<input type="text" name="var1" size="12" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(3, 'leute', 'vorname', 'first name', 'person', 1, 'vorname REGEXP ''[VAR1]''', '', '', 1, 'first name', '<input type="text" name="var1" size="12" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(4, 'leute', 'adresse', 'address', 'person', 1, 'adresse REGEXP ''[VAR1]''', '', '', 1, 'address', '<input type="text" name="var1" size="12" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(5, 'leute', 'adresse_zusatz', 'address2', 'person', 1, 'adresse_zusatz REGEXP ''[VAR1]''', '', '', 1, 'address2', '<input type="text" name="var1" size="12" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(6, 'leute', 'plz', 'zip code', 'person', 1, 'plz REGEXP ''[VAR1]''', '', '', 1, 'zip code', '<input type="text" name="var1" size="12" maxlength="10" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(7, 'leute', 'ort', 'city', 'person', 1, 'ort REGEXP ''[VAR1]''', '', '', 1, 'city', '<input type="text" name="var1" size="12" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(8, 'leute', 'land', 'country', 'person', 1, 'land REGEXP ''[VAR1]''', '', '', 1, 'country', '<input type="text" name="var1" size="12" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(9, 'leute', 'telp', 'tel p', 'com', 1, 'telp REGEXP ''[VAR1]''', '', '', 1, 'tel p', '<input type="text" name="var1" size="12" maxlength="30" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(10, 'leute', 'telg', 'tel b', 'com', 1, 'telg REGEXP ''[VAR1]''', '', '', 1, 'tel b', '<input type="text" name="var1" size="12" maxlength="30" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(11, 'leute', 'natel', 'mobile', 'com', 1, 'natel REGEXP ''[VAR1]''', '', '', 1, 'mobile', '<input type="text" name="var1" size="12" maxlength="30" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(12, 'leute', 'fax', 'fax', 'com', 1, 'fax REGEXP ''[VAR1]''', '', '', 1, 'fax', '<input type="text" name="var1" size="12" maxlength="30" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(13, 'leute', 'email', 'email', 'com', 1, 'email REGEXP ''[VAR1]''', '', '', 1, 'email', '<input type="text" name="var1" size="12" maxlength="100" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(14, 'leute', 'url', 'url', 'com', 1, 'web REGEXP ''[VAR1]''', '', '', 1, 'url', '<input type="text" name="var1" size="12" maxlength="100" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(15, 'leute', 'zivilstand', 'civil status', 'status', 1, 'zivilstand REGEXP ''[VAR1]''', '', '', 1, 'civil status', 'FCN:ko_specialfilter_enum_ll:ko_leute:zivilstand', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(16, 'leute', 'geburtsdatum', 'birthdate', 'status', 1, 'DAYOFMONTH(geburtsdatum) LIKE ''[VAR1]''', 'MONTH(geburtsdatum) LIKE ''[VAR2]''', 'YEAR(geburtsdatum) REGEXP ''[VAR3]''', 3, 'day', '<input type="text" name="var1" size="12" maxlength="2" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'month', '<input type="text" name="var2" size="12" maxlength="2" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'year', '<input type="text" name="var3" size="12" maxlength="4" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 0);
INSERT INTO `ko_filter` VALUES(17, 'leute', 'geschlecht', 'sex', 'status', 1, 'geschlecht REGEXP ''[VAR1]''', '', '', 1, 'sex', 'FCN:ko_specialfilter_enum_ll:ko_leute:geschlecht', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(18, 'leute', '', 'smallgroup', 'smallgroup', 1, 'smallgroups REGEXP ''[VAR1]''', '', '', 1, 'smallgroup', '<select name="var1" size="0"><option value=""></option></select>', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(76, 'leute', '', 'duplicates', 'misc', 0, '', '', '', 1, 'test field', 'FCN:ko_specialfilter_duplicates', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(20, 'leute', 'memo1', 'memo1', 'misc', 1, 'memo1 REGEXP ''[VAR1]''', '', '', 1, 'memo1', '<input type="text" name="var1" size="12" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(21, 'leute', 'memo2', 'memo2', 'misc', 1, 'memo2 REGEXP ''[VAR1]''', '', '', 1, 'memo2', '<input type="text" name="var1" size="12" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(24, 'leute', '', 'group', 'groups', 1, 'groups REGEXP ''[VAR1][g:0-9]*[VAR2]''', '', '', 2, 'group', '<select name="var1" size="7" onclick="if(!checkList(1)) return false;sendReq(''../groups/inc/ajax.php'', ''action,group_id'', ''grouproleselectfilter,''+this.options[this.selectedIndex].value, do_fill_grouproles_select_filter);"></select>', 'role', '<select name="var2" size="0"></select>', '', '', 0);
INSERT INTO `ko_filter` VALUES(25, 'leute', 'geburtsdatum', 'year', 'status', 0, 'YEAR(geburtsdatum) >= [VAR1] && `geburtsdatum` != ''0000-00-00''', 'YEAR(geburtsdatum) <= [VAR2]', '', 2, 'lower limit', '<input type="text" name="var1" size="12" maxlength="4" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'upper limit', '<input type="text" name="var2" size="12" maxlength="4" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', 0);
INSERT INTO `ko_filter` VALUES(29, 'leute', '', 'role', 'groups', 1, 'groups REGEXP ''[VAR1]''', '', '', 1, 'role', '<select name="var1" size="0"><option value="0"></option></select>', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(26, 'leute', 'kinder', 'children', 'family', 1, 'kinder REGEXP ''[VAR1]''', '', '', 1, 'number of children', '<input type="text" name="var1" size="12" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(30, 'leute', 'famid', 'family', 'family', 1, 'famid LIKE ''[VAR1]''', '', '', 1, 'family', '', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(31, 'leute', 'famfunction', 'family role', 'family', 1, 'famfunction REGEXP ''[VAR1]''', '', '', 1, 'family role', 'FCN:ko_specialfilter_enum_ll:ko_leute:famfunction', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(69, 'leute', 'ko_rota_schedulling.event_id', 'rota', 'groups', 1, '`event_id` IN ([VAR1])', '', '', 2, 'rota event', 'FCN:ko_specialfilter_rota', 'teams', 'FCN:ko_specialfilter_rota_teams', '', '', 0);
INSERT INTO `ko_filter` VALUES(27, 'leute', 'lastchange', 'last change', 'misc', 1, '(365 * YEAR(CURDATE()) + 30 * MONTH(CURDATE()) + DAYOFMONTH(CURDATE()) ) - (365 * YEAR(lastchange) + 30 * MONTH(lastchange) + DAYOFMONTH(lastchange) ) <= ''[VAR1]''', '', '', 1, 'last change', '<select name="var1" size="0"> <option value="7">7 Tage zurück</option> <option value="30">1 Monat zurück</option> <option value="183">6 Monate zurück</option> <option value="365">1 Jahr zurück</option> <option value="730">2 Jahre zurück</option> </select>', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(28, 'leute', 'geburtsdatum', 'age', 'status', 0, '(YEAR(CURDATE())-YEAR(geburtsdatum))- (RIGHT(CURDATE(),5)<RIGHT(geburtsdatum,5)) >= [VAR1]', '(YEAR(CURDATE())-YEAR(geburtsdatum))- (RIGHT(CURDATE(),5)<RIGHT(geburtsdatum,5)) <= [VAR2]', '', 2, 'lower limit', '<input type="text" name="var1" size="12" maxlength="3" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'upper limit', '<input type="text" name="var2" size="12" maxlength="3" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', 0);
INSERT INTO `ko_filter` VALUES(54, 'leute', 'firm', 'firm', 'person', 1, 'firm REGEXP ''[VAR1]''', '', '', 1, 'firm', '<input type="text" name="var1" size="12" maxlength="200" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(55, 'leute', 'department', 'department', 'person', 1, 'department REGEXP ''[VAR1]''', '', '', 1, 'department', '<input type="text" name="var1" size="12" maxlength="200" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(65, 'leute', 'ko_groups_datafields_data.value', 'grp data', 'groups', 1, 'datafield_id = ''[VAR1]''', 'value REGEXP ''[VAR2]''', '', 2, 'group datafield', 'FCN:ko_specialfilter_groupdatafields', 'value', '<div name="groups_datafields_filter">\r\n<input type="text" name="var2" size="12" maxlength="200" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />\r\n</div>', '', '', 0);
INSERT INTO `ko_filter` VALUES(66, 'leute', 'ko_kleingruppen.region', 'sg region', 'smallgroup', 1, 'region REGEXP ''[VAR1]''', '', '', 1, 'small group region', 'FCN:ko_specialfilter_kleingruppen_region', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(67, 'leute', 'ko_kleingruppen.type', 'sg type', 'smallgroup', 1, 'type REGEXP ''[VAR1]''', '', '', 1, 'small group type', 'FCN:ko_specialfilter_kleingruppen_type', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(68, 'leute', 'ko_kleingruppen.wochentag', 'sg day', 'smallgroup', 1, 'wochentag REGEXP ''[VAR1]''', '', '', 1, 'small group day', 'FCN:ko_specialfilter_enum_ll:ko_kleingruppen:wochentag', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(71, 'leute', '', 'crdate', 'misc', 0, '`crdate` >= ''[VAR1]''', 'DATE_FORMAT(`crdate`, ''%Y-%m-%d'') <= ''[VAR2]''', '', 2, 'Created after (YYYY-MM-DD)', 'FCN:ko_specialfilter_crdate:1', 'Created before (YYYY-MM-DD)', 'FCN:ko_specialfilter_crdate:2', '', '', 0);
INSERT INTO `ko_filter` VALUES(72, 'leute', 'ko_donations.date', 'donation', 'misc', 1, 'YEAR(`date`) = ''[VAR1]''', '`account` = ''[VAR2]''', '', 2, 'year', 'FCN:ko_specialfilter_donation', 'account', 'FCN:ko_specialfilter_donation_account', '', '', 0);
INSERT INTO `ko_filter` VALUES(73, 'leute', 'famid', 'addchildren', 'family', 0, 'YEAR(CURDATE())-YEAR(`geburtsdatum`) >= ''[VAR1]''', 'YEAR(CURDATE())-YEAR(`geburtsdatum`) <= ''[VAR2]''', '', 3, 'age_min', '<input type="text" name="var1" size="12" maxlength="3" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'age_max', '<input type="text" name="var2" size="12" maxlength="3" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'only_children', '<input type="checkbox" name="var3" value="1" />', 0);
INSERT INTO `ko_filter` VALUES(74, 'leute', 'famid', 'addparents', 'family', 0, '', '', '', 1, 'only_parents', '<input type="checkbox" name="var1" value="1" />', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(75, 'leute', '', 'smallgrouproles', 'smallgroup', 1, 'smallgroups REGEXP '':[VAR1]''', '', '', 1, 'smallgroup role', 'FCN:ko_specialfilter_smallgrouproles', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(77, 'leute', 'ko_admin.leute_id', 'logins', 'misc', 0, '`admingroups` REGEXP ''(^|,)[VAR1](,|$)'' AND `disabled` = ''''', '', '', 1, 'usergroup', 'FCN:ko_specialfilter_logins', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(78, 'leute', 'geburtsdatum', 'dobrange', 'status', 1, 'geburtsdatum >= ''[VAR1]''', 'geburtsdatum <= ''[VAR2]''', '', 2, 'lower', '<input type="text" name="var1" size="12" maxlength="10" />', 'upper', '<input type="text" name="var2" size="12" maxlength="10" />', '', '', 0);
INSERT INTO `ko_filter` VALUES(79, 'leute', 'id', 'id', 'misc', 0, 'id = ''[VAR1]''', '', '', 1, 'id', '<input type="text" name="var1" size="12" maxlength="11" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(80, 'leute', 'hidden', 'hidden', 'status', 0, '`hidden` = ''1''', '', '', 1, 'hidden', '<input type="checkbox" name="var1" checked="checked" value="1" disabled="disabled" />', '', '', '', '', 1);

CREATE TABLE `ko_grouproles` (
  `id` mediumint(6) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_groups` (
  `id` mediumint(6) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `pid` mediumint(6) unsigned zerofill DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `start` date NOT NULL,
  `stop` date NOT NULL,
  `roles` text NOT NULL,
  `rights_view` text NOT NULL,
  `rights_new` text NOT NULL,
  `rights_edit` text NOT NULL,
  `rights_del` text NOT NULL,
  `crdate` datetime NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `datafields` tinytext NOT NULL,
  `ezmlm_list` varchar(250) NOT NULL,
  `ezmlm_moderator` varchar(250) NOT NULL,
  `mailing_alias` varchar(50) NOT NULL,
  `maxcount` mediumint(9) NOT NULL,
  `count` mediumint(9) NOT NULL,
  `count_role` text NOT NULL,
  `mailing_mod_role` varchar(15) NOT NULL,
  `mailing_mod_logins` smallint(6) NOT NULL,
  `mailing_mod_members` smallint(6) NOT NULL,
  `mailing_mod_others` smallint(6) NOT NULL,
  `mailing_reply_to` varchar(20) NOT NULL,
  `mailing_modify_rcpts` tinyint(1) NOT NULL DEFAULT '1',
  `linked_group` mediumint(6) unsigned zerofill DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `start` (`start`),
  KEY `stop` (`stop`),
  KEY `pid` (`pid`),
  KEY `ezmlm_list` (`ezmlm_list`),
  KEY `mailing_alias` (`mailing_alias`),
  FULLTEXT KEY `roles` (`roles`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_groups_datafields` (
  `id` mediumint(6) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `options` text NOT NULL,
  `reusable` tinyint(2) NOT NULL DEFAULT '0',
  `private` tinyint(2) NOT NULL,
  `preset` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `reusable` (`reusable`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_groups_datafields_data` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `group_id` mediumint(6) unsigned zerofill NOT NULL,
  `datafield_id` mediumint(6) unsigned zerofill NOT NULL,
  `person_id` mediumint(9) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `person_id` (`person_id`),
  KEY `datafield_id` (`datafield_id`,`person_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_help` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(50) NOT NULL,
  `type` varchar(100) NOT NULL,
  `language` varchar(5) NOT NULL,
  `t3_page` mediumint(9) NOT NULL,
  `t3_content` mediumint(9) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module` (`module`,`language`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `ko_help` VALUES(NULL, 'admin', '', 'de', 40, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', '', 'en', 40, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', '', 'nl', 40, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', '', 'de', 47, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', '', 'en', 47, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', '', 'nl', 47, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'home', '', 'de', 49, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'home', '', 'en', 49, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'home', '', 'nl', 49, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', '', 'de', 48, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', '', 'en', 48, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', '', 'nl', 48, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', '', 'de', 46, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', '', 'en', 46, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', '', 'nl', 46, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', '', 'de', 45, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', '', 'en', 45, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', '', 'nl', 45, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'tapes', '', 'de', 43, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'tapes', '', 'en', 43, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'tapes', '', 'nl', 43, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'fileshare', '', 'de', 42, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'fileshare', '', 'en', 42, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'fileshare', '', 'nl', 42, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', '', 'de', 67, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', '', 'en', 67, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', '', 'nl', 67, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_allgemein', 'de', 40, 840, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_allgemein', 'en', 40, 839, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_allgemein', 'nl', 40, 766, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_etiketten', 'de', 40, 539, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_etiketten', 'en', 40, 292, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_etiketten', 'nl', 40, 770, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_layout', 'de', 40, 536, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_layout', 'en', 40, 295, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_layout', 'nl', 40, 767, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_layout_guest', 'de', 40, 538, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_layout_guest', 'en', 40, 293, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_layout_guest', 'nl', 40, 769, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'change_password', 'de', 40, 818, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'change_password', 'en', 40, 817, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_logins', 'de', 40, 540, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_logins', 'en', 40, 291, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_logins', 'nl', 40, 771, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_admingroups', 'de', 40, 543, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_admingroups', 'en', 40, 288, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_admingroups', 'nl', 40, 774, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_logs', 'de', 40, 544, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_logs', 'en', 40, 287, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_logs', 'nl', 40, 775, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_leute_pdf', 'de', 40, 1057, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_filter', 'de', 47, 423, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_filter', 'en', 47, 203, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_filter', 'nl', 47, 668, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_meine_liste', 'de', 47, 429, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_meine_liste', 'en', 47, 197, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_meine_liste', 'nl', 47, 674, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_aktionen', 'de', 47, 430, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_aktionen', 'en', 47, 196, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_aktionen', 'nl', 47, 675, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_schnellfilter', 'de', 47, 442, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_schnellfilter', 'en', 47, 184, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_schnellfilter', 'nl', 47, 687, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_kg', 'de', 47, 445, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_kg', 'en', 47, 181, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_kg', 'nl', 47, 690, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'submenu_filter', 'de', 48, 397, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'submenu_filter', 'en', 48, 170, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'submenu_filter', 'nl', 48, 614, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'form_neuer_termin', 'de', 48, 404, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'form_neuer_termin', 'en', 48, 163, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'form_neuer_termin', 'nl', 48, 621, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'submenu_export', 'de', 48, 411, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'submenu_export', 'en', 48, 156, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'submenu_export', 'nl', 48, 628, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'daten_settings', 'de', 48, 412, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'daten_settings', 'en', 48, 155, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'daten_settings', 'nl', 48, 629, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'new_group', 'en', 46, 222, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'new_group', 'de', 46, 459, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'new_group', 'nl', 46, 737, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_groups', 'en', 46, 221, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_groups', 'de', 46, 460, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_groups', 'nl', 46, 738, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_rights', 'en', 46, 219, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_rights', 'de', 46, 462, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_rights', 'nl', 46, 740, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_roles', 'en', 46, 218, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_roles', 'de', 46, 463, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_roles', 'nl', 46, 741, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_datafields', 'en', 46, 217, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_datafields', 'de', 46, 464, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_datafields', 'nl', 46, 742, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'submenu_objekte', 'en', 45, 242, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'submenu_objekte', 'de', 45, 473, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'submenu_objekte', 'nl', 45, 693, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'show_mod_res', 'en', 45, 238, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'show_mod_res', 'de', 45, 477, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'show_mod_res', 'nl', 45, 697, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'neue_reservation', 'en', 45, 236, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'neue_reservation', 'de', 45, 479, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'neue_reservation', 'nl', 45, 699, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'submenu_filter', 'en', 45, 234, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'submenu_filter', 'de', 45, 481, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'submenu_filter', 'nl', 45, 701, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'res_settings', 'en', 45, 231, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'res_settings', 'de', 45, 484, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'res_settings', 'nl', 45, 704, '');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'settings', 'de', 93, 1159, '');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'schedule', 'en', 93, 1134, '');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'schedule', 'de', 93, 1150, '');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'settings', 'en', 93, 1144, '');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'list_teams', 'en', 93, 1132, '');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'list_teams', 'de', 93, 1148, '');
INSERT INTO `ko_help` VALUES(NULL, 'rota', '', 'de', 93, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'tapes', 'submenu_filter', 'en', 43, 269, '');
INSERT INTO `ko_help` VALUES(NULL, 'tapes', 'submenu_filter', 'de', 43, 517, '');
INSERT INTO `ko_help` VALUES(NULL, 'tapes', 'submenu_filter', 'nl', 43, 791, '');
INSERT INTO `ko_help` VALUES(NULL, 'tapes', 'submenu_print', 'en', 43, 268, '');
INSERT INTO `ko_help` VALUES(NULL, 'tapes', 'submenu_print', 'de', 43, 518, '');
INSERT INTO `ko_help` VALUES(NULL, 'tapes', 'submenu_print', 'nl', 43, 792, '');
INSERT INTO `ko_help` VALUES(NULL, 'tapes', 'submenu_settings', 'en', 43, 265, '');
INSERT INTO `ko_help` VALUES(NULL, 'tapes', 'submenu_settings', 'de', 43, 521, '');
INSERT INTO `ko_help` VALUES(NULL, 'tapes', 'submenu_settings', 'nl', 43, 795, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_accounts', 'en', 67, 721, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_accounts', 'de', 67, 557, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_accounts', 'nl', 67, 830, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_filter', 'en', 67, 724, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_filter', 'de', 67, 554, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_filter', 'nl', 67, 833, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'merge', 'en', 67, 726, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'merge', 'de', 67, 657, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'merge', 'nl', 67, 835, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_export', 'en', 67, 728, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_export', 'de', 67, 550, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_export', 'nl', 67, 837, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_eventgruppen', 'en', 48, 158, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_eventgruppen', 'de', 48, 409, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_eventgruppen', 'nl', 48, 626, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_resitem', 'en', 45, 241, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_resitem', 'de', 45, 474, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_resitem', 'nl', 45, 694, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_tapes', 'en', 43, 272, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_tapes', 'de', 43, 514, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_tapes', 'nl', 43, 788, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_donations', 'en', 67, 722, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_donations', 'de', 67, 556, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_donations', 'nl', 67, 831, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'multiedit', 'en', 49, 841, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'multiedit', 'de', 49, 842, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'show_all', 'en', 47, 865, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'show_all', 'de', 47, 866, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'version_history', 'en', 47, 867, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'version_history', 'de', 47, 868, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'list_donations', 'en', 67, 724, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'list_donations', 'de', 67, 554, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'list_donations', 'nl', 67, 833, '');
INSERT INTO `ko_help` VALUES(NULL, 'rota', '', 'en', 93, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_daten', 'de', 0, 0, '<b>2: </b>Zu den gew?hlten Termingruppen d?rfen neue Termine erfasst, bestehende bearbeitet und gel?scht werden (inkl. Moderation gem?ss Einstellung pro Termingruppe)<br />\r\n<b>3: </b>Rechte von 2 aber ohne Moderation auch bei moderierten Termingruppen<br/>\r\n<b>4: </b>Von Stufe-2-Benutzern erfasste Termine moderieren<br />\r\n<b>ALL-Rechte auf 3:</b><br />Termingruppen/Kalender erstellen');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_reservation', 'de', 0, 0, '<b>2:</b> Neue Reservationen erfassen, die aber je nach Objekt noch moderiert werden.<br /> <b>3: </b>Eigene Reservationen bearbeiten/l?schen.<br /> <b>4:</b> Neue Reservationen ohne Moderation erfassen. Alle Reservationen zu den gew?hlten Objekten bearbeiten/l?schen.<br /> <b>5:</b> Moderieren der Reservations-Anfragen<br /> <b>ALL-Rechte auf 4:</b> Neue Reservations-Gruppen und -Objekte erstellen');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_groups', 'de', 0, 0, '<b>2:</b> Personen Gruppen zuweisen oder Zuweisung aufheben.<br /> <b>3:</b> Gruppen und Rollen bearbeiten.<br /> <b>4:</b> Gruppen und Rollen l?schen.');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_fileshare', 'de', 0, 0, 'Diese Modul wird nur ben?tigt, wenn der Benutzer Webordner anlegen oder bearbeiten muss. F?r den Zugriff auf die Dateien im Webordner muss dieses Modul nicht gew?hlt werden.');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_admin', 'de', 0, 0, 'Normalerweise sollte jeder Benutzer Berechtigungsstufe 1 f?r das Admin-Modul haben, um die eigenen Layout-Einstellungen bearbeiten zu k?nnen.');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_leute', 'de', 47, 420, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_daten', 'en', 0, 0, '<b>2: </b>Add new events, edit or delete events for the selected event groups. Moderation needed if set for the event group.<br />\r\n<b>3: </b>Same as 2 but without moderation even if set for the event group.<br/>\r\n<b>4: </b>Moderate events entered by users with access level 2.<br />\r\n<b>ALL rights set to 3:</b><br />Create new event groups/calendars');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_reservation', 'en', 0, 0, '<b>2:</b> Add new reservations. Moderation needed for items with moderation.<br /> <b>3: </b>Edit/delete own reservations.<br /> <b>4:</b> Add new reservations without moderation and edit/delete all reservations for the given items.<br /> <b>4:</b> Moderate reservation requests.<br /> <b>ALL rights set to 4:</b> Create new reservation groups and items');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_groups', 'en', 0, 0, '<b>2:</b> Add people to groups or remove them.<br /> <b>3:</b> Edit groups and add or edit roles.<br /> <b>4:</b> Delete groups and roles.');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_fileshare', 'en', 0, 0, 'This module is only needed if the user has to create or edit new webfolders. It is not needed to access the files in the webfolders through webDAV.');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_admin', 'en', 0, 0, 'Usually every user should have access to the admin module with access level 1 to edit her own layout settings.');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_leute', 'en', 47, 206, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'list_events_mod', 'en', 48, 160, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'list_events_mod', 'de', 48, 407, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'list_events_mod', 'nl', 48, 624, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_daten', 'nl', 0, 0, '<b>2: </b>Activiteiten toevoegen, bewerken of verwijderen voor de geselecteerde Activiteitengroepen. Moderatie benodigd indien dit is ingesteld voor de Activiteitengroep.<br />\r\n<b>3: </b>Hetzelfde als 2, maar zonder moderatie, zelfs als dat is ingesteld voor de Activiteitengroep.<br />\r\n<b>4: </b>Activiteiten modereren welke ingevoerd zijn door gebruikers met authorisatieniveau 2.<br />\r\n<b>ALLE rechten ingesteld op 3:</b><br />\r\nCre?er nieuwe Activiteitengroepen/Kalenders');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_reservation', 'nl', 0, 0, '<b>2: </b>Reserveringen toevoegen, bewerken of verwijderen voor de geselecteerde Activiteitengroepen. Moderatie benodigd voor items met moderatie. Alleen eigen Reserveringen bewerken/verwijderen.<br />\r\n<b>3: </b>Nieuwe Reserveringen toevoegen zonder moderatie en alle Reserveringen voor de opgegeven items bewerken/verwijderen.<br />\r\n<b>4: </b>Reserveringen modereren welke ingevoerd zijn door gebruikers met authorisatieniveau 2.<br />\r\n<b>ALLE rechten ingesteld op 3:</b><br />\r\nCre?er nieuwe Reserveringsgroepen');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_groups', 'nl', 0, 0, '<b>2: </b>Personen toevoegen aan of verwijderen uit Groepen.<br />\r\n<b>3: </b>Groepen bewerken en Rollen toevoegen of bewerken.<br />\r\n<b>4: </b>Groepen en Rollen verwijderen.<br />\r\n<b>Bevoegdheden die hier worden gegeven, gelden voor alle Groepen</b><br />\r\nVoor individuele Groepen is het authorisatieniveau te verhogen door "Bevoegdheden Gebruiker" in de Groepen-module te gebruiken of door individuele Groepen te bewerken.');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_fileshare', 'nl', 0, 0, 'Deze module is alleen nodig indien de gebruiker webfolders moet kunnen toevoegen of bewerken. Deze module is niet vereist om bestanden in de webfolders te benaderen via webDAV.');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_admin', 'nl', 0, 0, 'Doorgaans moet iedere gebruiker toegang hebben tot de Admin-module met authorisatieniveau 1, om zo zijn eigen lay-out instellingen te kunnen wijzigen.');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_leute', 'nl', 47, 665, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'mailmerge', 'en', 47, 998, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'mailmerge', 'de', 47, 999, '');
INSERT INTO `ko_help` VALUES(NULL, 'tracking', '', 'en', 89, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'tracking', '', 'de', 89, 0, '');
INSERT INTO `ko_help` VALUES(NULL, 'tracking', 'submenu_export', 'en', 89, 1013, '');
INSERT INTO `ko_help` VALUES(NULL, 'tracking', 'submenu_export', 'de', 89, 1023, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_tracking', 'en', 89, 1010, '');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_tracking', 'de', 89, 1020, '');
INSERT INTO `ko_help` VALUES(NULL, 'tracking', 'enter_tracking', 'en', 89, 1011, '');
INSERT INTO `ko_help` VALUES(NULL, 'tracking', 'enter_tracking', 'de', 89, 1021, '');
INSERT INTO `ko_help` VALUES(NULL, 'tracking', 'list_trackings', 'en', 89, 1028, '');
INSERT INTO `ko_help` VALUES(NULL, 'tracking', 'list_trackings', 'de', 89, 1029, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'submenu_news', 'en', 40, 1036, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'submenu_news', 'de', 40, 1037, '');
INSERT INTO `ko_help` VALUES(NULL, 'tracking', 'tracking_settings', 'en', 89, 1047, '');
INSERT INTO `ko_help` VALUES(NULL, 'tracking', 'tracking_settings', 'de', 89, 1049, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_sms_log', 'en', 40, 1055, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_sms_log', 'de', 40, 1060, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'list_news', 'en', 40, 1036, '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'list_news', 'de', 40, 1059, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'ical_links', 'en', 48, 152, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'ical_links', 'de', 48, 415, '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'ical_links2', 'en', 0, 0, 'Right click on link and select "Copy link" to add the link to your clipboard. Paste it into your calendar application.');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'ical_links2', 'de', 0, 0, 'Mit der rechten Maustaste auf den gew?nschten Link klicken und "Link-Adresse kopieren". Danach im Kalender-Programm als Kalender-URL einf?gen.');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'kota.ko_reminder.text', 'de', 0, 0, 'Im Text der Nachricht können verschiedene Platzhalter eingefügt werden, die anschliessend vom kOOL durch Absender-, Empfänger-, oder Eventspezifische Informationen ersetzt werden.\nAls Beispiel:\n\n###r__salutation_formal_name###\nHier erhalten Sie wichtige Informationen zum Event ###e_title###:\nStartzeit: ###e_startzeit###, Startdatum: ###e_startdatum###.\n\nMit freundlichen Grüssen,\n###s_vorname### ###s_nachname###');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'kota.ko_reminder.text', 'en', 0, 0, 'Several placeholders may be inserted into the text of the reminder. These will then be replaced by the corresponding information of an event, receiver or sender.\nAn Example:\n\n###r__salutation_formal_name###\nHere you get some important information concerning the event ###e_title###:\nStarting time: ###e_startzeit###, Starting date: ###e_startdatum###.\n\nYours sincerely,\n###s_vorname### ###s_nachname###');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'ical_links', 'en', 45, 1002, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'ical_links', 'de', 45, 1003, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'ical_links2', 'en', 0, 0, 'Right click on link and select "Copy link" to add the link to your clipboard. Paste it into your calendar application.');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'ical_links2', 'de', 0, 0, 'Mit der rechten Maustaste auf den gew?nschten Link klicken und "Link-Adresse kopieren". Danach im Kalender-Programm als Kalender-URL einf?gen.');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'merge_duplicates', 'en', 47, 1068, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'merge_duplicates', 'de', 47, 1069, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'leute_settings', 'en', 47, 1113, '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'leute_settings', 'de', 47, 1110, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'list_items', 'en', 45, 242, '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'list_items', 'de', 45, 473, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'groups_settings', 'en', 46, 1119, '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'groups_settings', 'de', 46, 1120, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'donation_settings', 'en', 67, 1123, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'donation_settings', 'de', 67, 1125, '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_filter_amount', 'en', 0, 0, '<h1>Filter for the amount:</h1><ul><li><b>100-200</b>: Find amount between 100 and 200 (including)</li><li><b>&gt;100</b>: Amount greater than or equal to 100</li><li><b>&lt;100</b>: Amount smaller than or equal to 100</li><li><b>=100</b>: Amount is exactly 100</li><li><b>Other values</b>: Partial matches: "100" finds "100", "1000" and e.g. "1009"</li></ul>');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_filter_amount', 'de', 0, 0, '<h1>Suche nach Betrag:</h1><ul><li><b>100-200</b>: Betrag zwischen 100 und 200 (inklusive)</li><li><b>&gt;100</b>: Betrag grösser als 100 (inklusive)</li><li><b>&lt;100</b>: Betrag kleiner als 100 (inklusive)</li><li><b>=100</b>: Betrag exakt 100</li><li><b>Sonstige Eingaben</b>: Teilsuche, z.B. "100" findet "100" aber auch "1000" oder "1009"</li></ul>');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'filter_link_adv', 'de', 0, 0, '<h1>Manuelle Filter-Verknüpfung</h1>Für jeden angewandten Filter erscheint in obiger Liste eine Nummer, über die der Filter im Textfeld referenziert werden kann.<br />Beispiele:<ul><li><b>0 UND 1:</b> Damit wird der erste Filter ("0") mit einem logischen UND mit dem zweiten ("1") verknüpft.</li><li><b>0 UND (1 ODER 2)</b>: Der erste ("0") sowie entweder der zweite oder der dritte Filter ("(1 ODER 2)") müssen zutreffen.</li></ul>');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'filter_link_adv', 'en', 0, 0, '<h1>Manually set filter links</h1>In the list above you can see a number for every currently applied filter. Use these in the input below to add a reference to each filter.<br />Some examples:<ul><li><b>0 AND 1:</b>The first ("0") and second ("1") filter will be linked by a logical AND.</li><li><b>0 AND (1 OR 2)</b>: The first ("0") filter and either the second or third filter ("(1 OR 2)")must match.</li></ul>');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'kota.ko_groups.linked_group', 'en', '', '', 'When adding an address to the current group this option will also assign this address to the group specified here. This only applies to newly assigned addresses. If the address is removed from this group it will stay in the linked group.');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'kota.ko_groups.linked_group', 'de', '', '', 'Beim Hinzufügen einer Adresse zu dieser Gruppe wird diese Adresse auch der verknüpften Gruppe zugewiesen. Dies gilt nur für Neuzuweisungen von Adressen zu dieser Gruppe. Wird eine Adresse aus dieser Gruppe entfernt, bleibt sie in der verknüpften Gruppe bestehen.');

CREATE TABLE `ko_kleingruppen` (
  `id` mediumint(4) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `alter` varchar(20) NOT NULL DEFAULT '',
  `geschlecht` enum('','m','w','mixed') NOT NULL,
  `wochentag` enum('','monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `ort` varchar(250) NOT NULL,
  `zeit` tinytext NOT NULL,
  `treffen` enum('','weekly','biweekly','once a month','twice a month','threetimes a month') NOT NULL,
  `anz_frei` tinyint(4) NOT NULL DEFAULT '0',
  `kg-gen` mediumint(9) NOT NULL DEFAULT '0',
  `type` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `comments` text NOT NULL,
  `picture` varchar(200) NOT NULL,
  `url` tinytext NOT NULL,
  `eventGroupID` mediumint(9) NOT NULL,
  `mailing_alias` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`region`,`eventGroupID`),
  KEY `mailing_alias` (`mailing_alias`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_leute` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `famid` mediumint(9) NOT NULL DEFAULT '0',
  `anrede` enum('','Mr','Mrs','Miss','Ms') NOT NULL,
  `firm` varchar(250) NOT NULL,
  `department` varchar(250) NOT NULL,
  `vorname` varchar(50) NOT NULL,
  `nachname` varchar(50) NOT NULL,
  `adresse` varchar(100) NOT NULL,
  `adresse_zusatz` varchar(100) NOT NULL,
  `plz` varchar(11) NOT NULL,
  `ort` varchar(50) NOT NULL,
  `land` varchar(50) NOT NULL,
  `telp` varchar(30) NOT NULL,
  `telg` varchar(30) NOT NULL,
  `natel` varchar(30) NOT NULL,
  `fax` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `web` varchar(250) NOT NULL,
  `geburtsdatum` date NOT NULL,
  `zivilstand` enum('','single','married','separated','divorced','widowed') NOT NULL,
  `geschlecht` enum('','m','w') NOT NULL DEFAULT '',
  `memo1` blob NOT NULL,
  `memo2` blob NOT NULL,
  `kinder` smallint(4) NOT NULL DEFAULT '0',
  `smallgroups` text NOT NULL,
  `lastchange` datetime NOT NULL,
  `famfunction` enum('','husband','wife','child') NOT NULL,
  `groups` text NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `crdate` datetime NOT NULL,
  `cruserid` mediumint(9) NOT NULL,
  `rectype` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nachname` (`nachname`),
  KEY `geburtsdatum` (`geburtsdatum`),
  KEY `famid` (`famid`),
  KEY `deleted` (`deleted`),
  KEY `hidden` (`hidden`),
  KEY `crdate` (`crdate`),
  KEY `lastchange` (`lastchange`),
  KEY `famfunction` (`famfunction`),
  FULLTEXT KEY `groups` (`groups`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_leute_changes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `user_id` mediumint(9) NOT NULL,
  `leute_id` mediumint(9) NOT NULL,
  `changes` text NOT NULL,
  `df` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `liduiddate` (`leute_id`,`user_id`,`date`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_leute_mod` (
  `_id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `_leute_id` mediumint(8) NOT NULL DEFAULT '0',
  `firm` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `anrede` enum('','Herr','Frau') NOT NULL,
  `vorname` varchar(100) NOT NULL,
  `nachname` varchar(100) NOT NULL,
  `adresse` varchar(100) NOT NULL,
  `adresse_zusatz` varchar(100) NOT NULL,
  `plz` varchar(20) NOT NULL,
  `ort` varchar(100) NOT NULL,
  `land` varchar(100) NOT NULL,
  `telp` varchar(30) NOT NULL,
  `telg` varchar(30) NOT NULL,
  `natel` varchar(30) NOT NULL,
  `fax` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `web` varchar(100) NOT NULL,
  `geburtsdatum` date NOT NULL,
  `geschlecht` enum('','m','w') NOT NULL,
	`zivilstand` enum('','single','married','separated','divorced','widowed') NOT NULL,
  `memo1` blob NOT NULL,
  `memo2` blob NOT NULL,
  `famfunction` enum('','husband','wife','child') NOT NULL,
  `rectype` varchar(10) NOT NULL,
  `_bemerkung` text NOT NULL,
  `_group_id` tinytext NOT NULL,
  `_group_datafields` text NOT NULL,
  `_crdate` datetime NOT NULL,
  `_cruserid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`_id`),
  KEY `nachname` (`nachname`),
  KEY `geburtsdatum` (`geburtsdatum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_leute_preferred_fields` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `lid` mediumint(9) unsigned NOT NULL,
  `field` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `lid` (`lid`),
  KEY `field` (`field`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `session_id` varchar(200) NOT NULL,
  `request_data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `date` (`date`),
  KEY `type` (`type`,`comment`(8))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_mailing_mails` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL,
  `user_id` mediumint(9) NOT NULL,
  `crdate` datetime NOT NULL,
  `code` varchar(32) NOT NULL,
  `recipient` varchar(100) NOT NULL,
  `from` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `header` text NOT NULL,
  `body` longtext NOT NULL,
  `sender_email` varchar(250) NOT NULL,
  `modify_rcpts` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_mailing_recipients` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `mail_id` mediumint(8) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `leute_id` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_id` (`mail_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_mailmerge` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `crdate` datetime NOT NULL,
  `num_recipients` mediumint(9) NOT NULL,
  `preset` varchar(255) NOT NULL,
  `salutation` varchar(20) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `closing` varchar(255) NOT NULL,
  `signature` varchar(255) NOT NULL,
  `sig_file` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`crdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_news` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `type` mediumint(9) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `text` longtext NOT NULL,
  `cdate` date NOT NULL DEFAULT '0000-00-00',
  `author` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_pdf_layout` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `ko_pdf_layout` VALUES(1, 'leute', 'Layout 1', 'a:8:{s:4:"page";a:5:{s:11:"orientation";s:1:"L";s:11:"margin_left";s:2:"10";s:10:"margin_top";s:2:"15";s:12:"margin_right";s:2:"10";s:13:"margin_bottom";s:2:"10";}s:6:"header";a:3:{s:4:"left";a:3:{s:4:"font";s:6:"arialb";s:8:"fontsize";s:2:"12";s:4:"text";s:22:"kOOL - the church tool";}s:6:"center";a:3:{s:4:"font";s:6:"arialb";s:8:"fontsize";s:2:"12";s:4:"text";s:0:"";}s:5:"right";a:3:{s:4:"font";s:6:"arialb";s:8:"fontsize";s:2:"12";s:4:"text";s:0:"";}}s:6:"footer";a:3:{s:4:"left";a:3:{s:4:"font";s:5:"arial";s:8:"fontsize";s:2:"11";s:4:"text";s:0:"";}s:6:"center";a:3:{s:4:"font";s:5:"arial";s:8:"fontsize";s:2:"11";s:4:"text";s:18:"- [[PageNumber]] -";}s:5:"right";a:3:{s:4:"font";s:5:"arial";s:8:"fontsize";s:2:"11";s:4:"text";s:46:"[[Day]].[[Month]].[[Year]] [[Hour]]:[[Minute]]";}}s:9:"headerrow";a:3:{s:4:"font";s:6:"arialb";s:8:"fontsize";s:2:"11";s:9:"fillcolor";s:3:"204";}s:18:"columns_datafields";b:0;s:4:"sort";s:8:"nachname";s:10:"sort_order";s:3:"ASC";s:12:"col_template";a:1:{s:8:"_default";a:2:{s:4:"font";s:5:"arial";s:8:"fontsize";s:2:"11";}}}');

CREATE TABLE `ko_reservation` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `item_id` mediumint(9) NOT NULL,
  `startdatum` date NOT NULL,
  `enddatum` date NOT NULL,
  `startzeit` time NOT NULL,
  `endzeit` time NOT NULL,
  `zweck` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `telefon` varchar(50) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  `code` varchar(32) NOT NULL DEFAULT '',
  `cdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_change` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` mediumint(6) NOT NULL DEFAULT '0',
  `serie_id` mediumint(9) NOT NULL,
  `linked_items` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `serie_id` (`serie_id`),
  KEY `item_id` (`item_id`),
  KEY `user_id` (`user_id`),
  KEY `startdatum` (`startdatum`),
  KEY `last_change` (`last_change`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_reservation_mod` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `item_id` mediumint(9) NOT NULL,
  `startdatum` date NOT NULL,
  `enddatum` date NOT NULL,
  `startzeit` time NOT NULL,
  `endzeit` time NOT NULL,
  `zweck` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `telefon` varchar(50) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  `code` varchar(32) NOT NULL DEFAULT '',
  `cdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_change` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` mediumint(6) NOT NULL DEFAULT '0',
  `serie_id` mediumint(9) NOT NULL,
  `linked_items` text NOT NULL,
  `_event_id` mediumint(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `serie_id` (`serie_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_resgruppen` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_resitem` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `beschreibung` text NOT NULL,
  `bild` varchar(255) NOT NULL DEFAULT '',
  `farbe` varchar(6) NOT NULL DEFAULT '',
  `gruppen_id` smallint(4) NOT NULL DEFAULT '0',
  `moderation` smallint(4) NOT NULL DEFAULT '0',
  `linked_items` text NOT NULL,
	`email_recipient` varchar(255) NOT NULL,
	`email_text` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_rota_schedulling` (
  `team_id` mediumint(8) unsigned NOT NULL,
  `event_id` varchar(8) NOT NULL,
  `schedule` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  KEY `dienst` (`team_id`,`event_id`),
  KEY `event` (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_rota_teams` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `rotatype` varchar(20) NOT NULL,
  `group_id` text NOT NULL,
  `eg_id` text NOT NULL,
  `export_eg` mediumint(9) NOT NULL,
	`sort` int(11) NOT NULL,
  `allow_consensus` tinyint(4) NOT NULL DEFAULT '0',
	`consensus_description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rotatype` (`rotatype`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_rota_consensus` (
  `team_id` mediumint(8) unsigned NOT NULL,
  `event_id` varchar(8) NOT NULL,
  `person_id` mediumint(8) NOT NULL,
  `answer` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  KEY `main` (`team_id`,`event_id`,`person_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_scheduler_tasks` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `crontime` varchar(200) NOT NULL,
  `status` int(10) unsigned NOT NULL,
  `call` text NOT NULL,
  `last_call` datetime NOT NULL,
  `next_call` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'Delete old downloads', '47 2 * * *', 0, 'ko_task_delete_old_downloads', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'Mailing', '*/5 * * * *', 0, 'ko_task_mailing', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'iCal import', '*/5 * * * *', 1, 'ko_task_import_events_ical', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'Send Reminders', '*/15 * * * *', 0, 'ko_task_reminder', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

CREATE TABLE `ko_settings` (
  `key` varchar(100) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `ko_settings` VALUES('show_leute_cols', 'vorname,nachname,adresse,plz,ort');
INSERT INTO `ko_settings` VALUES('leute_col_name', 'a:3:{s:2:"nl";a:28:{s:5:"famid";s:5:"Gezin";s:6:"anrede";s:6:"Aanhef";s:4:"firm";s:7:"Bedrijf";s:10:"department";s:8:"Afdeling";s:7:"vorname";s:8:"Voornaam";s:8:"nachname";s:10:"Achternaam";s:7:"adresse";s:5:"Adres";s:14:"adresse_zusatz";s:12:"Adresregel 2";s:3:"plz";s:8:"Postcode";s:3:"ort";s:10:"Woonplaats";s:4:"land";s:4:"Land";s:4:"telp";s:14:"Telefoon thuis";s:4:"telg";s:13:"Telefoon werk";s:5:"natel";s:6:"Mobiel";s:3:"fax";s:3:"Fax";s:5:"email";s:6:"E-mail";s:3:"web";s:3:"URL";s:12:"geburtsdatum";s:13:"Geboortedatum";s:10:"zivilstand";s:17:"Burgerlijke staat";s:10:"geschlecht";s:8:"Geslacht";s:5:"memo1";s:5:"Memo1";s:5:"memo2";s:5:"Memo2";s:6:"kinder";s:8:"Kinderen";s:11:"smallgroups";s:8:"Celgroep";s:10:"lastchange";s:16:"Laatst gewijzigd";s:11:"famfunction";s:9:"Gezinsrol";s:6:"groups";s:7:"Groepen";s:6:"hidden";s:9:"Verborgen";}s:2:"en";a:28:{s:5:"famid";s:6:"Family";s:6:"anrede";s:10:"Salutation";s:4:"firm";s:7:"Company";s:10:"department";s:10:"Department";s:7:"vorname";s:8:"Forename";s:8:"nachname";s:7:"Surname";s:7:"adresse";s:7:"Address";s:14:"adresse_zusatz";s:6:"Line 2";s:3:"plz";s:9:"Post code";s:3:"ort";s:9:"Town/City";s:4:"land";s:7:"Country";s:4:"telp";s:7:"Home no";s:4:"telg";s:7:"Work no";s:5:"natel";s:6:"Mobile";s:3:"fax";s:3:"Fax";s:5:"email";s:5:"Email";s:3:"web";s:3:"URL";s:12:"geburtsdatum";s:3:"DOB";s:10:"zivilstand";s:14:"Marital Status";s:10:"geschlecht";s:3:"Sex";s:5:"memo1";s:7:"Notes 1";s:5:"memo2";s:7:"Notes 2";s:6:"kinder";s:8:"Children";s:11:"smallgroups";s:11:"Smallgroups";s:10:"lastchange";s:11:"Last change";s:11:"famfunction";s:11:"Family role";s:6:"groups";s:6:"Groups";s:6:"hidden";s:6:"Hidden";}s:2:"de";a:28:{s:5:"famid";s:7:"Familie";s:6:"anrede";s:6:"Anrede";s:4:"firm";s:5:"Firma";s:10:"department";s:9:"Abteilung";s:7:"vorname";s:7:"Vorname";s:8:"nachname";s:8:"Nachname";s:7:"adresse";s:7:"Adresse";s:14:"adresse_zusatz";s:13:"AdresseZusatz";s:3:"plz";s:3:"PLZ";s:3:"ort";s:3:"Ort";s:4:"land";s:4:"Land";s:4:"telp";s:4:"TelP";s:4:"telg";s:4:"TelG";s:5:"natel";s:12:"Mobiltelefon";s:3:"fax";s:3:"Fax";s:5:"email";s:6:"E-Mail";s:3:"web";s:3:"Web";s:12:"geburtsdatum";s:12:"Geburtsdatum";s:10:"zivilstand";s:10:"Zivilstand";s:10:"geschlecht";s:10:"Geschlecht";s:5:"memo1";s:5:"Memo1";s:5:"memo2";s:5:"Memo2";s:6:"kinder";s:6:"Kinder";s:11:"smallgroups";s:12:"Kleingruppen";s:10:"lastchange";s:15:"LetzteAenderung";s:11:"famfunction";s:11:"FamFunktion";s:6:"groups";s:7:"Gruppen";s:6:"hidden";s:9:"Versteckt";}}');
INSERT INTO `ko_settings` VALUES('mailing_mails_per_cycle', '30');
INSERT INTO `ko_settings` VALUES('daten_perm_filter_ende', '');
INSERT INTO `ko_settings` VALUES('res_perm_filter_start', '');
INSERT INTO `ko_settings` VALUES('res_perm_filter_ende', '');
INSERT INTO `ko_settings` VALUES('show_limit_groups', '20');
INSERT INTO `ko_settings` VALUES('default_view_groups', 'list_groups');
INSERT INTO `ko_settings` VALUES('res_show_persondata', '1');
INSERT INTO `ko_settings` VALUES('rota_export_calid', '');
INSERT INTO `ko_settings` VALUES('show_limit_leute', '50');
INSERT INTO `ko_settings` VALUES('info_name', '');
INSERT INTO `ko_settings` VALUES('info_email', 'kOOL@churchtool.org');
INSERT INTO `ko_settings` VALUES('info_phone', '');
INSERT INTO `ko_settings` VALUES('info_url', '');
INSERT INTO `ko_settings` VALUES('ps_filter_sel_ds1_koi[ko_donations][person]', '');
INSERT INTO `ko_settings` VALUES('show_limit_daten', '50');
INSERT INTO `ko_settings` VALUES('info_address', '');
INSERT INTO `ko_settings` VALUES('info_zip', '');
INSERT INTO `ko_settings` VALUES('info_city', '');
INSERT INTO `ko_settings` VALUES('sms_sender_ids', 'kOOL');
INSERT INTO `ko_settings` VALUES('show_limit_logins', '20');
INSERT INTO `ko_settings` VALUES('show_limit_reservation', '50');
INSERT INTO `ko_settings` VALUES('cache_sms_balance', '0');
INSERT INTO `ko_settings` VALUES('show_limit_logs', '50');
INSERT INTO `ko_settings` VALUES('xls_default_font', '');
INSERT INTO `ko_settings` VALUES('xls_title_font', '');
INSERT INTO `ko_settings` VALUES('xls_title_bold', '1');
INSERT INTO `ko_settings` VALUES('xls_title_color', 'blue');
INSERT INTO `ko_settings` VALUES('default_view_admin', 'set_layout');
INSERT INTO `ko_settings` VALUES('default_view_daten', 'show_cal_monat');
INSERT INTO `ko_settings` VALUES('default_view_leute', 'show_all');
INSERT INTO `ko_settings` VALUES('default_view_reservation', 'show_cal_monat');
INSERT INTO `ko_settings` VALUES('show_limit_fileshare', '20');
INSERT INTO `ko_settings` VALUES('modules_dropdown', 'ja');
INSERT INTO `ko_settings` VALUES('fileshare_mailtext', 'You have received a file from <ABSENDER> (<ABSENDEREMAIL>). The following comments were added:\r\n---\r\n<TEXT>\r\n---\r\nDownload your file here: <LINK>');
INSERT INTO `ko_settings` VALUES('default_view_fileshare', 'list_webfolders');
INSERT INTO `ko_settings` VALUES('default_view_tools', '');
INSERT INTO `ko_settings` VALUES('show_limit_kg', '20');
INSERT INTO `ko_settings` VALUES('login_edit_person', '0');
INSERT INTO `ko_settings` VALUES('cal_jahr_num', '6');
INSERT INTO `ko_settings` VALUES('default_view_tapes', 'list_tapes');
INSERT INTO `ko_settings` VALUES('show_limit_tapes', '20');
INSERT INTO `ko_settings` VALUES('tapes_new_plus', '7');
INSERT INTO `ko_settings` VALUES('tapes_new_minus', '21');
INSERT INTO `ko_settings` VALUES('tapes_guess_series', '1');
INSERT INTO `ko_settings` VALUES('tapes_clear_printqueue', '0');
INSERT INTO `ko_settings` VALUES('familie_col_name', 'a:3:{s:2:"de";a:10:{s:8:"nachname";s:8:"Nachname";s:7:"adresse";s:7:"Adresse";s:14:"adresse_zusatz";s:14:"Adresse Zusatz";s:3:"plz";s:3:"PLZ";s:3:"ort";s:3:"Ort";s:4:"land";s:4:"Land";s:4:"telp";s:5:"Tel P";s:9:"famanrede";s:15:"Familien-Anrede";s:12:"famfirstname";s:16:"Familien-Vorname";s:11:"famlastname";s:17:"Familien-Nachname";}s:2:"en";a:10:{s:8:"nachname";s:7:"Surname";s:7:"adresse";s:7:"Address";s:14:"adresse_zusatz";s:14:"Address line 2";s:3:"plz";s:9:"Post code";s:3:"ort";s:9:"Town/City";s:4:"land";s:7:"Country";s:4:"telp";s:10:"Home Phone";s:9:"famanrede";s:17:"Family Salutation";s:12:"famfirstname";s:16:"Family Firstname";s:11:"famlastname";s:15:"Family Lastname";}s:2:"nl";a:10:{s:8:"nachname";s:10:"Achternaam";s:7:"adresse";s:5:"Adres";s:14:"adresse_zusatz";s:12:"Adresregel 2";s:3:"plz";s:8:"Postcode";s:3:"ort";s:10:"Woonplaats";s:4:"land";s:4:"Land";s:4:"telp";s:14:"Telefoon thuis";s:9:"famanrede";s:12:"Aanhef gezin";s:12:"famfirstname";s:0:"";s:11:"famlastname";s:0:"";}}');
INSERT INTO `ko_settings` VALUES('daten_perm_filter_start', '');
INSERT INTO `ko_settings` VALUES('res_mandatory', '');
INSERT INTO `ko_settings` VALUES('res_send_email', '');
INSERT INTO `ko_settings` VALUES('res_allow_multires_for_guest', '0');
INSERT INTO `ko_settings` VALUES('show_limit_donations', '20');
INSERT INTO `ko_settings` VALUES('sms_country_code', '41');
INSERT INTO `ko_settings` VALUES('change_password', '0');
INSERT INTO `ko_settings` VALUES('leute_hidden_mode', '1');
INSERT INTO `ko_settings` VALUES('rota_teamrole', '');
INSERT INTO `ko_settings` VALUES('rota_leaderrole', '');
INSERT INTO `ko_settings` VALUES('daten_access_calendar', '1');
INSERT INTO `ko_settings` VALUES('tracking_add_roles', '0');
INSERT INTO `ko_settings` VALUES('rota_showroles', '0');
INSERT INTO `ko_settings` VALUES('mailing_max_recipients', '0');
INSERT INTO `ko_settings` VALUES('mailing_only_alias', '0');
INSERT INTO `ko_settings` VALUES('daten_gs_pid', '');
INSERT INTO `ko_settings` VALUES('daten_gs_role', '');
INSERT INTO `ko_settings` VALUES('rota_weekstart', '0');
INSERT INTO `ko_settings` VALUES('default_view_rota', 'schedule');
INSERT INTO `ko_settings` VALUES('rota_export_weekly_teams', '0');
INSERT INTO `ko_settings` VALUES('typo3_host', '');
INSERT INTO `ko_settings` VALUES('typo3_db', '');
INSERT INTO `ko_settings` VALUES('typo3_user', '');
INSERT INTO `ko_settings` VALUES('typo3_pwd', '');
INSERT INTO `ko_settings` VALUES('mailing_allow_double', '0');
INSERT INTO `ko_settings` VALUES('res_show_purpose', '1');
INSERT INTO `ko_settings` VALUES('leute_real_delete', '0');
INSERT INTO `ko_settings` VALUES('daten_show_mod_to_all', '0');
INSERT INTO `ko_settings` VALUES('res_show_mod_to_all', '0');
INSERT INTO `ko_settings` VALUES('daten_mod_exclude_fields', '');
INSERT INTO `ko_settings` VALUES('leute_assign_global_notification', '');
INSERT INTO `ko_settings` VALUES('res_attach_ics_for_user', '');
INSERT INTO `ko_settings` VALUES('res_access_mode', '0');

CREATE TABLE `ko_tapes` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `preacher` varchar(255) NOT NULL DEFAULT '',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `group_id` mediumint(9) NOT NULL,
  `serie_id` mediumint(9) NOT NULL,
  `item_number` varchar(200) NOT NULL,
  `price` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_tapes_groups` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `printname` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_tapes_printlayout` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `default` tinyint(4) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `ko_tapes_printlayout` VALUES(2, 'Tapes 6x2', 0, 'a:6:{s:10:"page_width";s:3:"210";s:11:"page_height";s:3:"297";s:5:"items";s:2:"12";s:5:"rootx";a:12:{i:0;s:2:"12";i:1;s:3:"110";i:2;s:2:"12";i:3;s:3:"110";i:4;s:2:"12";i:5;s:3:"110";i:6;s:2:"12";i:7;s:3:"110";i:8;s:2:"12";i:9;s:3:"110";i:10;s:2:"12";i:11;s:3:"110";}s:5:"rooty";a:12:{i:0;s:2:"15";i:1;s:2:"15";i:2;s:2:"62";i:3;s:2:"62";i:4;s:3:"109";i:5;s:3:"109";i:6;s:3:"156";i:7;s:3:"156";i:8;s:3:"203";i:9;s:3:"203";i:10;s:3:"250";i:11;s:3:"250";}s:6:"layout";a:8:{s:5:"title";a:6:{s:2:"do";i:1;s:1:"x";s:1:"3";s:1:"y";s:1:"2";s:4:"font";s:6:"arialb";s:8:"fontsize";s:2:"10";s:5:"align";s:1:"L";}s:8:"subtitle";a:1:{s:2:"do";i:0;}s:4:"date";a:6:{s:2:"do";i:1;s:1:"x";s:2:"85";s:1:"y";s:1:"7";s:4:"font";s:5:"arial";s:8:"fontsize";s:1:"9";s:5:"align";s:1:"R";}s:5:"group";a:1:{s:2:"do";i:0;}s:5:"serie";a:1:{s:2:"do";i:0;}s:8:"preacher";a:6:{s:2:"do";i:1;s:1:"x";s:1:"3";s:1:"y";s:1:"7";s:4:"font";s:5:"arial";s:8:"fontsize";s:1:"9";s:5:"align";s:1:"L";}s:11:"item_number";a:1:{s:2:"do";i:0;}s:5:"price";a:1:{s:2:"do";i:0;}}}');
INSERT INTO `ko_tapes_printlayout` VALUES(1, 'List', 0, 'a:6:{s:10:"page_width";s:3:"210";s:11:"page_height";s:3:"297";s:5:"items";s:2:"50";s:5:"rootx";a:50:{i:0;s:2:"20";i:1;s:2:"20";i:2;s:2:"20";i:3;s:2:"20";i:4;s:2:"20";i:5;s:2:"20";i:6;s:2:"20";i:7;s:2:"20";i:8;s:2:"20";i:9;s:2:"20";i:10;s:2:"20";i:11;s:2:"20";i:12;s:2:"20";i:13;s:2:"20";i:14;s:2:"20";i:15;s:2:"20";i:16;s:2:"20";i:17;s:2:"20";i:18;s:2:"20";i:19;s:2:"20";i:20;s:2:"20";i:21;s:2:"20";i:22;s:2:"20";i:23;s:2:"20";i:24;s:2:"20";i:25;s:2:"20";i:26;s:2:"20";i:27;s:2:"20";i:28;s:2:"20";i:29;s:2:"20";i:30;s:2:"20";i:31;s:2:"20";i:32;s:2:"20";i:33;s:2:"20";i:34;s:2:"20";i:35;s:2:"20";i:36;s:2:"20";i:37;s:2:"20";i:38;s:2:"20";i:39;s:2:"20";i:40;s:2:"20";i:41;s:2:"20";i:42;s:2:"20";i:43;s:2:"20";i:44;s:2:"20";i:45;s:2:"20";i:46;s:2:"20";i:47;s:2:"20";i:48;s:2:"20";i:49;s:2:"20";}s:5:"rooty";a:50:{i:0;s:2:"30";i:1;s:2:"35";i:2;s:2:"40";i:3;s:2:"45";i:4;s:2:"50";i:5;s:2:"55";i:6;s:2:"60";i:7;s:2:"65";i:8;s:2:"70";i:9;s:2:"75";i:10;s:2:"80";i:11;s:2:"85";i:12;s:2:"90";i:13;s:2:"95";i:14;s:3:"100";i:15;s:3:"105";i:16;s:3:"110";i:17;s:3:"115";i:18;s:3:"120";i:19;s:3:"125";i:20;s:3:"130";i:21;s:3:"135";i:22;s:3:"140";i:23;s:3:"145";i:24;s:3:"150";i:25;s:3:"155";i:26;s:3:"160";i:27;s:3:"165";i:28;s:3:"170";i:29;s:3:"175";i:30;s:3:"180";i:31;s:3:"185";i:32;s:3:"190";i:33;s:3:"195";i:34;s:3:"200";i:35;s:3:"205";i:36;s:3:"210";i:37;s:3:"215";i:38;s:3:"220";i:39;s:3:"225";i:40;s:3:"230";i:41;s:3:"235";i:42;s:3:"240";i:43;s:3:"245";i:44;s:3:"250";i:45;s:3:"255";i:46;s:3:"260";i:47;s:3:"265";i:48;s:3:"270";i:49;s:3:"275";}s:6:"layout";a:8:{s:5:"title";a:6:{s:2:"do";i:1;s:1:"x";s:1:"0";s:1:"y";s:1:"0";s:4:"font";s:6:"arialb";s:8:"fontsize";s:1:"9";s:5:"align";s:1:"L";}s:8:"subtitle";a:1:{s:2:"do";i:0;}s:4:"date";a:6:{s:2:"do";i:1;s:1:"x";s:3:"170";s:1:"y";s:1:"0";s:4:"font";s:5:"arial";s:8:"fontsize";s:1:"9";s:5:"align";s:1:"R";}s:5:"group";a:1:{s:2:"do";i:0;}s:5:"serie";a:1:{s:2:"do";i:0;}s:8:"preacher";a:6:{s:2:"do";i:1;s:1:"x";s:3:"100";s:1:"y";s:1:"0";s:4:"font";s:5:"arial";s:8:"fontsize";s:1:"9";s:5:"align";s:1:"L";}s:11:"item_number";a:1:{s:2:"do";i:0;}s:5:"price";a:1:{s:2:"do";i:0;}}}');
INSERT INTO `ko_tapes_printlayout` VALUES(3, 'Tapes 6x1', 1, 'a:6:{s:10:"page_width";s:3:"210";s:11:"page_height";s:3:"297";s:5:"items";s:1:"6";s:5:"rootx";a:6:{i:0;s:2:"12";i:1;s:2:"12";i:2;s:2:"12";i:3;s:2:"12";i:4;s:2:"12";i:5;s:2:"12";}s:5:"rooty";a:6:{i:0;s:2:"15";i:1;s:2:"62";i:2;s:3:"109";i:3;s:3:"156";i:4;s:3:"203";i:5;s:3:"250";}s:6:"layout";a:8:{s:5:"title";a:6:{s:2:"do";i:1;s:1:"x";s:1:"3";s:1:"y";s:1:"2";s:4:"font";s:6:"arialb";s:8:"fontsize";s:2:"10";s:5:"align";s:1:"L";}s:8:"subtitle";a:1:{s:2:"do";i:0;}s:4:"date";a:6:{s:2:"do";i:1;s:1:"x";s:2:"85";s:1:"y";s:1:"7";s:4:"font";s:5:"arial";s:8:"fontsize";s:1:"9";s:5:"align";s:1:"R";}s:5:"group";a:1:{s:2:"do";i:0;}s:5:"serie";a:1:{s:2:"do";i:0;}s:8:"preacher";a:6:{s:2:"do";i:1;s:1:"x";s:1:"3";s:1:"y";s:1:"7";s:4:"font";s:5:"arial";s:8:"fontsize";s:1:"9";s:5:"align";s:1:"L";}s:11:"item_number";a:1:{s:2:"do";i:0;}s:5:"price";a:1:{s:2:"do";i:0;}}}');

CREATE TABLE `ko_tapes_series` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `printname` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_tracking` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` mediumint(9) NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `label_value` varchar(200) NOT NULL,
  `types` text NOT NULL,
  `mode` varchar(20) NOT NULL,
  `filter` text NOT NULL,
  `date_eventgroup` mediumint(9) NOT NULL,
  `date_weekdays` varchar(20) NOT NULL,
  `dates` text NOT NULL,
  `description` text NOT NULL,
  `type_multiple` tinyint(4) NOT NULL DEFAULT '0',
	`hidden` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_tracking_entries` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `lid` mediumint(9) NOT NULL,
  `tid` mediumint(9) NOT NULL,
  `date` date NOT NULL,
  `type` varchar(200) NOT NULL,
  `value` varchar(200) NOT NULL,
  `comment` text NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` mediumint(9) unsigned NOT NULL,
  `last_change` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tidliddate` (`tid`,`lid`,`date`),
  KEY `tid` (`tid`),
  KEY `tiddate` (`tid`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_tracking_groups` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ko_userprefs` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `type` varchar(50) NOT NULL,
  `key` varchar(50) NOT NULL DEFAULT '',
  `value` text NOT NULL,
	`mailing_alias` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `ko_userprefs` VALUES(1, 2, '', 'submenu_daten_left', 'termine,termingruppen,export', '');
INSERT INTO `ko_userprefs` VALUES(2, 2, '', 'submenu_reservation_left', 'reservationen,objekte,moderation,export', '');
INSERT INTO `ko_userprefs` VALUES(3, 2, '', 'submenu_reservation_right', 'filter,objekt beschreibungen,itemlist_objekte', '');
INSERT INTO `ko_userprefs` VALUES(4, 2, '', 'front_modules_left', 'daten_cal', '');
INSERT INTO `ko_userprefs` VALUES(5, 2, '', 'front_modules_center', 'news', '');
INSERT INTO `ko_userprefs` VALUES(6, 2, '', 'front_modules_right', 'adressaenderung,dienstplan', '');
INSERT INTO `ko_userprefs` VALUES(7, 2, '', 'show_limit_daten', '20', '');
INSERT INTO `ko_userprefs` VALUES(8, 2, '', 'cal_jahr_num', '6', '');
INSERT INTO `ko_userprefs` VALUES(9, 2, '', 'show_limit_reservation', '20', '');
INSERT INTO `ko_userprefs` VALUES(10, 2, '', 'default_view_daten', 'show_cal_monat', '');
INSERT INTO `ko_userprefs` VALUES(11, 2, '', 'default_view_reservation', 'show_cal_monat', '');
INSERT INTO `ko_userprefs` VALUES(12, 2, '', 'submenu_daten_right', 'filter,itemlist_termingruppen', '');
INSERT INTO `ko_userprefs` VALUES(13, 2, '', 'leute_children_columns', '_father,_mother,_natel', '');
INSERT INTO `ko_userprefs` VALUES(14, 2, '', 'daten_monthly_title', 'eventgruppen_id', '');
INSERT INTO `ko_userprefs` VALUES(15, 2, '', 'daten_title_length', '30', '');
INSERT INTO `ko_userprefs` VALUES(16, 2, '', 'daten_pdf_show_time', '2', '');
INSERT INTO `ko_userprefs` VALUES(17, 2, '', 'daten_pdf_week_start', '1', '');
INSERT INTO `ko_userprefs` VALUES(18, 2, '', 'daten_pdf_week_length', '7', '');
INSERT INTO `ko_userprefs` VALUES(19, 2, '', 'daten_mark_sunday', '0', '');
INSERT INTO `ko_userprefs` VALUES(20, 2, '', 'daten_ical_deadline', '0', '');
INSERT INTO `ko_userprefs` VALUES(21, 2, '', 'cal_woche_start', '6', '');
INSERT INTO `ko_userprefs` VALUES(22, 2, '', 'cal_woche_end', '22', '');
INSERT INTO `ko_userprefs` VALUES(23, 2, '', 'show_dateres_combined', '0', '');
INSERT INTO `ko_userprefs` VALUES(24, 2, '', 'res_pdf_show_time', '2', '');
INSERT INTO `ko_userprefs` VALUES(25, 2, '', 'res_pdf_show_comment', '0', '');
INSERT INTO `ko_userprefs` VALUES(26, 2, '', 'res_pdf_week_start', '1', '');
INSERT INTO `ko_userprefs` VALUES(27, 2, '', 'res_pdf_week_length', '7', '');
INSERT INTO `ko_userprefs` VALUES(28, 2, '', 'res_mark_sunday', '0', '');
INSERT INTO `ko_userprefs` VALUES(29, 2, '', 'res_monthly_title', 'item_id', '');
INSERT INTO `ko_userprefs` VALUES(30, 2, '', 'res_title_length', '30', '');
INSERT INTO `ko_userprefs` VALUES(31, 2, '', 'res_ical_deadline', '0', '');
INSERT INTO `ko_userprefs` VALUES(32, 2, '', 'geburtstagsliste_deadline_plus', '21', '');
INSERT INTO `ko_userprefs` VALUES(33, 2, '', 'geburtstagsliste_deadline_minus', '5', '');
INSERT INTO `ko_userprefs` VALUES(34, 2, '', 'birthday_filter', '', '');
INSERT INTO `ko_userprefs` VALUES(35, 2, '', 'leute_force_family_firstname', '0', '');
INSERT INTO `ko_userprefs` VALUES(36, 2, '', 'show_passed_groups', '1', '');
INSERT INTO `ko_userprefs` VALUES(37, 2, '', 'tracking_show_inactive', '1', '');
INSERT INTO `ko_userprefs` VALUES(38, 2, '', 'tracking_order_people', 'role', '');
INSERT INTO `ko_userprefs` VALUES(39, 2, '', 'rota_eventfields', '', '');
INSERT INTO `ko_userprefs` VALUES(40, 2, '', 'rota_pdf_fontsize', '10', '');
INSERT INTO `ko_userprefs` VALUES(41, 2, '', 'rota_delimiter', ' ', '');
INSERT INTO `ko_userprefs` VALUES(42, 2, '', 'rota_markempty', '0', '');
INSERT INTO `ko_userprefs` VALUES(43, 2, '', 'rota_orderby', 'vorname', '');
INSERT INTO `ko_userprefs` VALUES(44, 2, '', 'rota_pdf_use_colors', '1', '');
INSERT INTO `ko_userprefs` VALUES(45, 2, '', 'rota_pdf_names', '2', '');
