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
  `kg_admin` text NOT NULL,
  `groups_admin` text NOT NULL,
  `donations_admin` text NOT NULL,
  `tracking_admin` text NOT NULL,
  `crm_admin` text NOT NULL,
  `vesr_admin` text NOT NULL,
  `modules` text NOT NULL,
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `disabled` varchar(32) NOT NULL,
  `admingroups` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(50) NOT NULL,
	`kota_columns_ko_kleingruppen` text NOT NULL,
	`kota_columns_ko_event` text NOT NULL,
	`res_force_global` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`event_force_global` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`event_reminder_rights` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`event_absence_rights` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`groups_terms_rights` text NOT NULL,
	`disable_password_change` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`subscription_admin` text NOT NULL,
	`taxonomy_admin` text NOT NULL,
	`ical_hash` varchar(32) NOT NULL,
	`allow_bypass_information_lock` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

INSERT INTO `ko_admin` (`id`, `leute_id`, `login`, `password`, `modules`) VALUES(2, -1, 'ko_guest', '098f6bcd4621d373cade4e832627b4f6', '');

INSERT INTO `ko_admin` (`id`, `leute_id`, `login`, `password`, `modules`, `leute_admin`, `groups_admin`) VALUES(NULL, -1, '_checkin_user', '31622ad9c487574e5fa3276b56005291', 'leute,groups', '2', '2');

CREATE TABLE `ko_admingroups` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
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
  `kg_admin` text NOT NULL,
  `groups_admin` text NOT NULL,
  `donations_admin` text NOT NULL,
  `tracking_admin` text NOT NULL,
  `crm_admin` text NOT NULL,
  `vesr_admin` text NOT NULL,
  `modules` text NOT NULL,
  `kota_columns_ko_kleingruppen` text NOT NULL,
  `kota_columns_ko_event` text NOT NULL,
  `res_force_global` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `event_force_global` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `event_reminder_rights` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `event_absence_rights` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `groups_terms_rights` text NOT NULL,
  `disable_password_change` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `subscription_admin` text NOT NULL,
  `taxonomy_admin` text NOT NULL,
  `allow_bypass_information_lock` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_vesr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reason` varchar(20) NOT NULL,
  `file` varchar(50) NOT NULL,
  `code` varchar(128) NOT NULL,
  `transaction` varchar(3) NOT NULL,
  `account` varchar(11) NOT NULL,
  `refnumber` varchar(27) NOT NULL,
  `amount` varchar(11) NOT NULL,
  `bankreference` varchar(10) NOT NULL,
  `paydate` date NOT NULL,
  `bankdate1` date NOT NULL,
  `bankdate2` date NOT NULL,
  `microfilm` varchar(9) NOT NULL,
  `reject` varchar(1) NOT NULL,
  `valutadate` date NOT NULL,
  `tax` varchar(4) NOT NULL,
  `type` varchar(40) NOT NULL,
  `misc_id` mediumint(9) unsigned NOT NULL,
  `misc` text NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`reason`),
  KEY `vesr_type` (`type`),
  KEY `vesr_misc_id` (`misc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE `ko_vesr_camt` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` text NOT NULL,
  `reason` varchar(50) NOT NULL,
  `cruser` mediumint(9) NOT NULL,
  `amount` varchar(11) NOT NULL,
  `valuta_date` datetime NOT NULL,
  `booking_date` datetime NOT NULL,
  `refnumber` text NOT NULL,
  `charges` varchar(20) NOT NULL,
  `crdate` datetime NOT NULL,
  `currency` varchar(30) NOT NULL,
  `note` text NOT NULL,
  `purpose` text NOT NULL,
  `purpose_code` text NOT NULL,
  `account_number` text NOT NULL,
  `account_name` text NOT NULL,
  `participant_number` text NOT NULL,
  `reject` tinyint(1) NOT NULL,
  `source` varchar(255) NOT NULL,
  `file` text NOT NULL,
  `uid` varchar(100) NOT NULL,
  `_p_city` text NOT NULL,
  `_p_country` text NOT NULL,
  `_p_email` text NOT NULL,
  `_p_extra_address_lines` text NOT NULL,
  `_p_gender` text NOT NULL,
  `_p_identification` text NOT NULL,
  `_p_mobile` text NOT NULL,
  `_p_name` text NOT NULL,
  `_p_address` text NOT NULL,
  `_p_phone` text NOT NULL,
  `_p_zip` text NOT NULL,
  `additional_information` text NOT NULL,
  `misc_id` mediumint(9) unsigned NOT NULL,
  `misc` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `reason` (`reason`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE `ko_labels` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`name` text NOT NULL,
	`page_format` varchar(30) NOT NULL,
	`page_orientation` varchar(30) NOT NULL,
	`per_row` mediumint(9) NOT NULL,
	`per_col` mediumint(9) NOT NULL,
	`border_top` smallint NOT NULL,
	`border_right` smallint NOT NULL,
	`border_bottom` smallint NOT NULL,
	`border_left` smallint NOT NULL,
	`spacing_horiz` smallint NOT NULL,
	`spacing_vert` smallint NOT NULL,
	`align_horiz` varchar(30) NOT NULL,
	`align_vert` varchar(30) NOT NULL,
	`font` varchar(100) NOT NULL,
	`textsize` mediumint(9) NOT NULL,
	`ra_margin_top` smallint NOT NULL,
	`ra_margin_left` smallint NOT NULL,
	`ra_font` varchar(100) NOT NULL,
	`ra_textsize` mediumint(9) NOT NULL,
	`pp_position` varchar(20) NOT NULL,
	`pic_file` text NOT NULL,
	`pic_w` smallint NOT NULL,
	`pic_x` smallint NOT NULL,
	`pic_y` smallint NOT NULL,
	`crdate` DATETIME NOT NULL,
	`cruser` mediumint(9) NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

INSERT INTO `ko_labels` VALUES (NULL, '3x8', 'A4', 'P', '3', '8', '2', '2', '2', '2', '5', '2', 'L', 'T', 'arial', '11', '5', '3', 'arial', '7', 'address', '', '', '', '', NOW(), 1, NOW(), 1);
INSERT INTO `ko_labels` VALUES(NULL, 'Couverts C5', 'C5', 'L', 1, 1, 100, 10, 10, 120, 0, 0, 'L', 'T', 'arial', 11, 0, 0, 'arial', 9, 'stamp', '', 0, 0, 0, NOW(), 1, NOW(), 1);


CREATE TABLE `ko_detailed_person_exports` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `template` text NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` mediumint(8) unsigned NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

INSERT INTO `ko_detailed_person_exports` VALUES (NULL, 'Word', 'my_images/kota_ko_detailed_person_exports_template_1.docx', NOW(), '', '', '');

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
  `thanked` tinyint(4) NOT NULL,
  `camt_uid` varchar(100) NOT NULL,
  `crm_project_id` int(10) NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` mediumint(8) unsigned NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `source` (`source`),
  KEY `date` (`date`),
  KEY `person` (`person`),
  KEY `account` (`account`),
  KEY `reoccuring` (`reoccuring`),
  KEY `valutadate` (`valutadate`),
  KEY `promise` (`promise`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_donations_mod` (
	`id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
	`account` mediumint(8) unsigned NOT NULL DEFAULT '0',
	`date` date NOT NULL,
	`amount` decimal(15,2) NOT NULL,
	`valutadate` date NOT NULL,
	`comment` varchar(255) NOT NULL,
	`source` varchar(255) NOT NULL,
	`camt_uid` varchar(100) NOT NULL,
	`person` mediumint(8) unsigned NOT NULL,
	`_p_anrede` varchar(50) NOT NULL,
	`_p_firm` varchar(250) NOT NULL,
	`_p_department` varchar(250) NOT NULL,
	`_p_vorname` varchar(50) NOT NULL,
	`_p_nachname` varchar(50) NOT NULL,
	`_p_adresse` varchar(100) NOT NULL,
	`_p_adresse_zusatz` varchar(100) NOT NULL,
	`_p_plz` varchar(11) NOT NULL,
	`_p_ort` varchar(50) NOT NULL,
	`_p_land` varchar(50) NOT NULL,
	`_p_email` varchar(50) NOT NULL,
	`_account_number` varchar(50) NOT NULL,
	`_account_name` varchar(255) NOT NULL,
	`_crdate` datetime NOT NULL,
	`_cruser` mediumint(8) unsigned NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_donations_accounts` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `number` varchar(50) NOT NULL,
  `comment` varchar(255) NOT NULL,
	`group_id` text NOT NULL,
	`accountgroup_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `archived` tinyint(3) NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` mediumint(8) unsigned NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_donations_accountgroups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `archived` tinyint(3) NOT NULL,
	`crdate` datetime NOT NULL,
	`cruser` mediumint(8) unsigned NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
	`replyto_email` VARCHAR(250) NOT NULL,
	`crdate` DATETIME NOT NULL,
	`cruser` mediumint(9) NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` int NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_reminder_mapping` (
	`id` mediumint(9) NOT NULL AUTO_INCREMENT,
	`reminder_id` mediumint(9) NOT NULL,
	`event_id` mediumint(9) NOT NULL DEFAULT '0',
	`leute_id` mediumint(9) NOT NULL DEFAULT '0',
	`crdate` DATETIME NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
  `do_notify` tinyint(3) NOT NULL DEFAULT '1',
	`slug` varchar(255) NOT NULL,
  `cdate` datetime NOT NULL,
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `last_change` datetime NOT NULL,
  `lastchange_user` int NOT NULL DEFAULT '0',
  `import_id` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `eventgruppen_id` (`eventgruppen_id`),
  KEY `dp` (`rota`),
  KEY `startdatum` (`startdatum`),
  KEY `import_id` (`import_id`(200))
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_event_program` (
	`id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
	`pid` varchar(200) NOT NULL,
	`time` time NOT NULL DEFAULT '00:00:00',
	`name` varchar(200) NOT NULL,
	`title` text NOT NULL,
	`team` text NOT NULL,
	`infrastructure` text NOT NULL,
	`crdate` datetime NOT NULL,
	`cruser` mediumint(9) NOT NULL,
	`sorting` mediumint(9) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
  `url` tinytext NOT NULL,
  `moderation` tinyint(4) NOT NULL DEFAULT '0',
  `notify` varchar(250) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `ical_url` varchar(255) NOT NULL,
  `ical_title` varchar(255) NOT NULL,
  `update` int(11) NOT NULL,
  `last_update` datetime NOT NULL,
	`responsible_for_res` mediumint(9) NOT NULL,
	`crdate` DATETIME NOT NULL,
	`cruser` mediumint(9) NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` int NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `calendar_id` (`calendar_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_eventgruppen_program` (
	`id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
	`pid` varchar(200) NOT NULL,
	`time` time NOT NULL DEFAULT '00:00:00',
	`name` varchar(200) NOT NULL,
	`title` text NOT NULL,
	`team` text NOT NULL,
	`infrastructure` text NOT NULL,
	`crdate` datetime NOT NULL,
	`cruser` mediumint(9) NOT NULL,
	`sorting` mediumint(9) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_event_calendar` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_event_mod` (
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
  `resitems` text NOT NULL,
  `res_startzeit` time NOT NULL,
  `res_endzeit` time NOT NULL,
  `res_startdatum` date NOT NULL,
  `res_enddatum` date NOT NULL,
  `responsible_for_res` mediumint(9) NOT NULL,
  `url` tinytext NOT NULL,
  `gs_gid` varchar(20) NOT NULL,
	`slug` varchar(255) NOT NULL,
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `_res_mod_on_conflict` tinyint(3) NOT NULL,
  `_event_id` mediumint(9) NOT NULL DEFAULT '0',
  `_user_id` mediumint(9) NOT NULL DEFAULT '0',
  `_delete` tinyint(4) NOT NULL DEFAULT '0',
  `_crdate` datetime NOT NULL,
  `cdate` datetime NOT NULL,
  `last_change` datetime NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_event_rooms` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL DEFAULT '',
  `title_short` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(100) DEFAULT '',
  `coordinates` varchar(30) DEFAULT '',
  `url` varchar(200) DEFAULT '',
  `crdate` datetime NOT NULL,
  `cruser` mediumint(8) unsigned NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` int NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
   UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'anrede', 'salutation', 'person', 1, 'anrede REGEXP ''[VAR1]''', '', '', 1, 'salutation', '<select class="input-sm form-control" name="var1" size="0"><option value=""></option><option value="Herr">Herr</option><option value="Frau">Frau</option></select>', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ko_filter.name', 'filterpreset', 'misc', 0, '[VAR1]', '', '', 1, 'filterpreset', 'FCN:ko_specialfilter_filterpreset', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'nachname', 'last name', 'person', 1, 'nachname REGEXP ''[VAR1]''', '', '', 1, 'last name', '<input class="input-sm form-control" type="text" name="var1" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'vorname', 'first name', 'person', 1, 'vorname REGEXP ''[VAR1]''', '', '', 1, 'first name', '<input class="input-sm form-control" type="text" name="var1" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'adresse', 'address', 'person', 1, 'adresse REGEXP ''[VAR1]''', '', '', 1, 'address', '<input class="input-sm form-control" type="text" name="var1" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'adresse_zusatz', 'address2', 'person', 1, 'adresse_zusatz REGEXP ''[VAR1]''', '', '', 1, 'address2', '<input class="input-sm form-control" type="text" name="var1" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'plz', 'zip code', 'person', 1, 'plz REGEXP ''[VAR1]''', '', '', 1, 'zip code', '<input class="input-sm form-control" type="text" name="var1" maxlength="10" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ort', 'city', 'person', 1, 'ort REGEXP ''[VAR1]''', '', '', 1, 'city', '<input class="input-sm form-control" type="text" name="var1" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'land', 'country', 'person', 1, 'land REGEXP ''[VAR1]''', '', '', 1, 'country', '<input class="input-sm form-control" type="text" name="var1" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'telp', 'tel p', 'com', 1, 'REPLACE(telp, '' '', '''')  LIKE REPLACE(''%[VAR1]%'', '' '', '''')', '', '', 1, 'tel p', '<input class="input-sm form-control" type="text" name="var1" maxlength="30" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'telg', 'tel b', 'com', 1, 'REPLACE(telg, '' '', '''')  LIKE REPLACE(''%[VAR1]%'', '' '', '''')', '', '', 1, 'tel b', '<input class="input-sm form-control" type="text" name="var1" maxlength="30" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'natel', 'mobile', 'com', 1, 'REPLACE(natel, '' '', '''')  LIKE REPLACE(''%[VAR1]%'', '' '', '''')', '', '', 1, 'mobile', '<input class="input-sm form-control" type="text" name="var1" maxlength="30" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'fax', 'fax', 'com', 1, 'fax REGEXP ''[VAR1]''', '', '', 1, 'fax', '<input class="input-sm form-control" type="text" name="var1" maxlength="30" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'email', 'email', 'com', 1, 'email REGEXP ''[VAR1]''', '', '', 1, 'email', '<input class="input-sm form-control" type="text" name="var1" maxlength="100" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'url', 'url', 'com', 1, 'web REGEXP ''[VAR1]''', '', '', 1, 'url', '<input class="input-sm form-control" type="text" name="var1" maxlength="100" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'zivilstand', 'civil status', 'status', 1, 'kota_filter', '', '', 1, 'civil status', 'FCN:ko_specialfilter_kota:ko_leute:zivilstand', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'geburtsdatum', 'birthdate', 'status', 1, 'DAYOFMONTH(geburtsdatum) LIKE ''[VAR1]''', 'MONTH(geburtsdatum) LIKE ''[VAR2]''', 'YEAR(geburtsdatum) REGEXP ''[VAR3]''', 3, 'day', '<input class="input-sm form-control" type="text" name="var1" maxlength="2" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'month', '<input class="input-sm form-control" type="text" name="var2" maxlength="2" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'year', '<input class="input-sm form-control" type="text" name="var3" maxlength="4" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'geschlecht', 'sex', 'status', 1, 'kota_filter', '', '', 1, 'sex', 'FCN:ko_specialfilter_kota:ko_leute:geschlecht', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', '', 'smallgroup', 'smallgroup', 1, 'smallgroups REGEXP ''[VAR1]''', '', '', 1, 'smallgroup', '<select class="input-sm form-control" name="var1" size="0"><option value=""></option></select>', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', '', 'duplicates', 'misc', 0, '', '', '', 1, 'test field', 'FCN:ko_specialfilter_duplicates', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'memo1', 'memo1', 'misc', 1, 'memo1 REGEXP ''[VAR1]''', '', '', 1, 'memo1', '<input class="input-sm form-control" type="text" name="var1" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'memo2', 'memo2', 'misc', 1, 'memo2 REGEXP ''[VAR1]''', '', '', 1, 'memo2', '<input class="input-sm form-control" type="text" name="var1" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', '', 'group', 'groups', 1, 'groups REGEXP ''[VAR1][g:0-9]*[VAR2]''', '', '', 2, 'group', '<input type="hidden" name="var1"><div class="groupfilter" name="sel1-var1" size="6" data-select="single"></div>', 'role', '<select class="input-sm form-control" name="var2" size="0"></select>', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'geburtsdatum', 'year', 'status', 0, 'YEAR(geburtsdatum) >= [VAR1] && `geburtsdatum` != ''0000-00-00''', 'YEAR(geburtsdatum) <= [VAR2]', '', 2, 'lower limit', '<input class="input-sm form-control" type="text" name="var1" maxlength="4" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'upper limit', '<input class="input-sm form-control" type="text" name="var2" maxlength="4" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', '', 'role', 'groups', 1, 'groups REGEXP ''[VAR1]''', '', '', 1, 'role', '<select class="input-sm form-control" name="var1" size="0"><option value="0"></option></select>', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'kinder', 'children', 'family', 1, 'kinder REGEXP ''[VAR1]''', '', '', 1, 'number of children', '<input class="input-sm form-control" type="text" name="var1" maxlength="50" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'famid', 'family', 'family', 1, 'famid LIKE ''[VAR1]''', '', '', 1, 'family', 'FCN:ko_specialfilter_families', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'famfunction', 'family role', 'family', 1, 'kota_filter', '', '', 1, 'family role', 'FCN:ko_specialfilter_kota:ko_leute:famfunction', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ko_rota_schedulling.event_id', 'rota', 'groups', 1, '`event_id` IN ([VAR1])', '', '', 2, 'rota event', 'FCN:ko_specialfilter_rota', 'teams', 'FCN:ko_specialfilter_rota_teams', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'lastchange', 'last change', 'misc', 1, 'DATE_FORMAT(`lastchange`, ''%Y-%m-%d'') >= ''[VAR1]''', 'DATE_FORMAT(`lastchange`, ''%Y-%m-%d'') <= ''[VAR2]''', '', 2, 'Created after (YYYY-MM-DD)', 'FCN:ko_specialfilter_lastchange:1', 'Created before (YYYY-MM-DD)', 'FCN:ko_specialfilter_lastchange:2', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'geburtsdatum', 'age', 'status', 0, '(YEAR(CURDATE())-YEAR(geburtsdatum))- (RIGHT(CURDATE(),5)<RIGHT(geburtsdatum,5)) >= [VAR1]', '(YEAR(CURDATE())-YEAR(geburtsdatum))- (RIGHT(CURDATE(),5)<RIGHT(geburtsdatum,5)) <= [VAR2]', '', 2, 'lower limit', '<input class="input-sm form-control" type="text" name="var1" maxlength="3" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'upper limit', '<input class="input-sm form-control" type="text" name="var2" maxlength="3" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'firm', 'firm', 'person', 1, 'firm REGEXP ''[VAR1]''', '', '', 1, 'firm', '<input class="input-sm form-control" type="text" name="var1" maxlength="200" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'department', 'department', 'person', 1, 'department REGEXP ''[VAR1]''', '', '', 1, 'department', '<input class="input-sm form-control" type="text" name="var1" maxlength="200" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ko_groups_datafields_data.value', 'grp data', 'groups', 1, 'datafield_id = ''[VAR1]''', 'value LIKE ''%[VAR2]%''', '', 2, 'group datafield', 'FCN:ko_specialfilter_groupdatafields', 'value', '<div name="groups_datafields_filter">\r\n<input class="input-sm form-control" type="text" name="var2" maxlength="200" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />\r\n</div>', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ko_kleingruppen.region', 'sg region', 'smallgroup', 1, 'region REGEXP ''[VAR1]''', '', '', 1, 'small group region', 'FCN:ko_specialfilter_kleingruppen_region', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ko_kleingruppen.type', 'sg type', 'smallgroup', 1, 'type REGEXP ''[VAR1]''', '', '', 1, 'small group type', 'FCN:ko_specialfilter_kleingruppen_type', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ko_kleingruppen.wochentag', 'sg day', 'smallgroup', 1, 'wochentag REGEXP ''[VAR1]''', '', '', 1, 'small group day', 'FCN:ko_specialfilter_select_ll:ko_kleingruppen:wochentag', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', '', 'crdate', 'misc', 0, '`crdate` >= ''[VAR1]''', 'DATE_FORMAT(`crdate`, ''%Y-%m-%d'') <= ''[VAR2]''', '', 2, 'Created after (YYYY-MM-DD)', 'FCN:ko_specialfilter_crdate:1', 'Created before (YYYY-MM-DD)', 'FCN:ko_specialfilter_crdate:2', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ko_donations.date', 'donation', 'misc', 1, '', '', '', 2, 'year', 'FCN:ko_specialfilter_donation', 'account', 'FCN:ko_specialfilter_donation_account', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'famid', 'addchildren', 'family', 0, 'YEAR(CURDATE())-YEAR(`geburtsdatum`) >= ''[VAR1]''', 'YEAR(CURDATE())-YEAR(`geburtsdatum`) <= ''[VAR2]''', '', 3, 'age_min', '<input class="input-sm form-control" type="text" name="var1" maxlength="3" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'age_max', '<input class="input-sm form-control" type="text" name="var2" maxlength="3" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'only_children', '<input class="input-sm form-control" type="checkbox" name="var3" value="1" />', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'famid', 'addparents', 'family', 0, '', '', '', 1, 'only_parents', 'FCN:ko_specialfilter_addparents', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'famid', 'childrencount', 'family', 0, '', '', '', 1, 'childrencount', 'FCN:ko_specialfilter_childrencount', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', '', 'smallgrouproles', 'smallgroup', 1, 'smallgroups REGEXP '':[VAR1]''', '', '', 1, 'smallgroup role', 'FCN:ko_specialfilter_smallgrouproles', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ko_admin.leute_id', 'logins', 'misc', 0, '`admingroups` REGEXP ''(^|,)[VAR1](,|$)'' AND `disabled` = ''''', '', '', 1, 'usergroup', 'FCN:ko_specialfilter_logins', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'geburtsdatum', 'dobrange', 'status', 1, '`geburtsdatum` >= \'[VAR1]\'', '`geburtsdatum` <= \'[VAR2]\'', '', 2, 'lower', 'FCN:ko_specialfilter_dobrange:1', 'upper', 'FCN:ko_specialfilter_dobrange:2', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'id', 'id', 'misc', 0, 'id = ''[VAR1]''', '', '', 1, 'id', '<input class="input-sm form-control" type="text" name="var1" maxlength="11" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', '', '', 1);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'hidden', 'hidden', 'status', 0, '`hidden` = ''1''', '', '', 1, 'hidden', '<input class="input-sm form-control" type="checkbox" name="var1" checked="checked" value="1" disabled="disabled" />', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ko_crm_contacts.project_id', 'crm_project', 'misc', 1, 'c.`project_id` = ''[VAR1]''', 'c.`status_id` = ''[VAR2]''', '', 2, 'project_id', 'FCN:ko_specialfilter_crm_project', 'status_id', 'FCN:ko_specialfilter_crm_status', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ko_crm_contacts.id', 'crm_contact', 'misc', 1, '', '', '', 1, 'id', 'FCN:ko_specialfilter_crm_contact', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'id', 'random_ids', 'misc', 1, '', '', '', 1, 'id', 'FCN:ko_specialfilter_random_ids', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', '', 'fastfilter', 'person', 0, '', '', '', 0, '', '', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', '', 'candidateadults', 'family', 0, '', '', '', 1, 'dummy', 'FCN:ko_specialfilter_candidateadults', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', '', 'groupshistory', 'groups', 0, '', '', '', 4, 'id', 'FCN:ko_specialfilter_groupshistory', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', '', 'groupsanniversary', 'groups', 0, '', '', '', 4, 'id', 'FCN:ko_specialfilter_groupsanniversary', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'geburtsdatum', 'jubilee', 'status', 0, '', '', '', 3, 'minage', 'FCN:ko_specialfilter_text:var1', 'step', 'FCN:ko_specialfilter_jubilee_step', 'yearoffset', 'FCN:ko_specialfilter_jubilee_yearoffset', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'confession', 'mixedhousehold', 'family', 0, '', '', '', 1, 'mode', 'FCN:ko_specialfilter_mixedhousehold', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'geburtsdatum', 'yearage', 'status', 0, '', '', '', 2, 'lower limit', '<input class="input-sm form-control" type="text" name="var1" maxlength="3" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', 'upper limit', '<input class="input-sm form-control" type="text" name="var2" maxlength="3" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ko_tracking_entries.value', 'trackingentries', 'misc', 0, '', '', '', 5, 'tracking_id', 'FCN:ko_specialfilter_trackingentries', '', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'ko_taxonomy_terms.id', 'taxonomy', 'groups', 0, '', '', '', 2, 'taxonomy', 'FCN:ko_specialfilter_taxonomy_term:taxonomy', 'role', '', '', '', 0);
INSERT INTO `ko_filter` VALUES(NULL, 'leute', 'information_lock', 'information_lock', 'status', 0, 'kota_filter', '', '', 1, 'information_lock', 'FCN:ko_specialfilter_kota:ko_leute:information_lock', '', '', '', '', 0);



CREATE TABLE `ko_grouproles` (
  `id` mediumint(6) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` mediumint(8) unsigned NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_groups` (
  `id` mediumint(6) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `pid` mediumint(6) unsigned zerofill DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `start` date NOT NULL,
  `stop` date NOT NULL,
  `deadline` date NOT NULL,
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
	`mailing_prefix` varchar(50) NOT NULL,
	`mailing_rectype` varchar(10) NOT NULL,
	`mailing_crm_project_id` mediumint(6) unsigned NOT NULL,
  `linked_group` mediumint(6) unsigned zerofill DEFAULT NULL,
  `cruser` mediumint(8) unsigned NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `start` (`start`),
  KEY `stop` (`stop`),
  KEY `pid` (`pid`),
  KEY `ezmlm_list` (`ezmlm_list`),
  KEY `mailing_alias` (`mailing_alias`),
  FULLTEXT KEY `roles` (`roles`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_groups_datafields` (
  `id` mediumint(6) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `options` text NOT NULL,
  `reusable` tinyint(2) NOT NULL DEFAULT '0',
  `private` tinyint(2) NOT NULL,
  `preset` tinyint(2) NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` mediumint(8) unsigned NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `reusable` (`reusable`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_groups_assignment_history` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `group_id` mediumint(9) NOT NULL,
  `person_id` mediumint(9) NOT NULL,
  `role_id` mediumint(9) NOT NULL,
  `start` datetime NOT NULL,
  `start_is_exact` tinyint(3) NOT NULL,
  `stop` datetime NOT NULL,
  `stop_is_exact` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `role_id` (`role_id`),
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_help` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(50) NOT NULL,
  `type` varchar(100) NOT NULL,
  `language` varchar(5) NOT NULL,
  `t3_page` mediumint(9) NOT NULL,
  `t3_content` mediumint(9) NOT NULL,
  `text` text NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module` (`module`,`language`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

-- vesr, set_allgemein, set_layout, etiketten/pdf/individual, logs, kg, multiedit, inlineedit
INSERT INTO `ko_help` VALUES(NULL, 'admin', '', 'de', 0, 0, '', 'http://kool.help/admin/benutzerverwaltung');
INSERT INTO `ko_help` VALUES(NULL, 'leute', '', 'de', 0, 0, '', 'http://kool.help/module/adressen');
INSERT INTO `ko_help` VALUES(NULL, 'home', '', 'de', 0, 0, '', 'http://kool.help/');
INSERT INTO `ko_help` VALUES(NULL, 'daten', '', 'de', 0, 0, '', 'http://kool.help/module/termine');
INSERT INTO `ko_help` VALUES(NULL, 'groups', '', 'de', 0, 0, '', 'http://kool.help/module/gruppen');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', '', 'de', 0, 0, '', 'http://kool.help/module/reservationen');
INSERT INTO `ko_help` VALUES(NULL, 'donations', '', 'de', 0, 0, '', 'http://kool.help/module/spenden');
INSERT INTO `ko_help` VALUES(NULL, 'rota', '', 'de', 0, 0, '', 'http://kool.help/module/dienstplan');
INSERT INTO `ko_help` VALUES(NULL, 'subscription', '', 'de', 0, 0, '', 'http://kool.help/module/anmeldung');

INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_logins', 'de', 0, 0, '', 'http://kool.help/admin/benutzerverwaltung');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_new_login', 'de', 0, 0, '', 'http://kool.help/admin/benutzerverwaltung#benutzerverwaltung');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'set_new_admingroup', 'de', 0, 0, '', 'http://kool.help/admin/benutzerverwaltung#benutzergruppen');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'show_admingroups', 'de', 0, 0, '', 'http://kool.help/admin/benutzerverwaltung#benutzergruppen');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'submenu_logins', 'de', 0, 0, '', 'http://kool.help/admin/benutzerverwaltung');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_leute', 'de', 0, 0, '', 'http://kool.help/admin/benutzerverwaltung#berechtigungen_fuer_das_leute-modul');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_itemlist_spalten', 'de', 0, 0, '', 'http://kool.help/module/adressen#spalten');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_filter', 'de', 0, 0, '', 'http://kool.help/module/adressen#filter');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_meine_liste', 'de', 0, 0, '', 'http://kool.help/module/adressen#meine_liste');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_aktionen', 'de', 0, 0, '', 'http://kool.help/module/adressen#exporte_aktionen');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'submenu_schnellfilter', 'de', 0, 0, '', 'http://kool.help/module/adressen#schnellfilter');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'show_all', 'de', 0, 0, '', 'http://kool.help/module/adressen#das_leute-modul');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'version_history', 'de', 0, 0, '', 'http://kool.help/module/adressen#aenderungen_verfolgen');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'merge_duplicates', 'de', 0, 0, '', 'http://kool.help/module/adressen#doppelte_adressen');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'leute_settings', 'de', 0, 0, '', 'http://kool.help/module/adressen#einstellungen');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'filter_link', 'de', 0, 0, '', 'http://kool.help/module/adressen#filter_kombinieren');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'labels', 'de', 0, 0, '', 'http://kool.help/module/adressen#etiketten_erstellen');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'sms', 'de', 0, 0, '', 'http://kool.help/module/adressen#sms_versenden');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'mutationen', 'de', 0, 0, '', 'http://kool.help/module/adressen#mutationen');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'revisions', 'de', 0, 0, '', 'http://kool.help/module/adressen#adressbereinigungen');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'import', 'de', 0, 0, '', 'http://kool.help/module/adressen#import');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'kota.ko_leute.groups', 'de', '', '', '', 'http://kool.help/module/gruppen#adresse_einer_gruppe_zuweisen');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'kota.ko_leute.famid', 'de', '', '', '', 'http://kool.help/module/adressen#haushaltefamilien');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'submenu_termine', 'de', 0, 0, '', 'http://kool.help/module/termine');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'all_events', 'de', 0, 0, '', 'http://kool.help/module/termine');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'submenu_itemlist_termingruppen', 'de', 0, 0, '', 'http://kool.help/module/termine#terminliste');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'submenu_termingruppen', 'de', 0, 0, '', 'http://kool.help/module/termine#termingruppen');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'all_groups', 'de', 0, 0, '', 'http://kool.help/module/termine#termingruppen');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'submenu_reminder', 'de', 0, 0, '', 'http://kool.help/module/termine#erinnerungen');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'submenu_filter', 'de', 0, 0, '', 'http://kool.help/module/termine#terminliste');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'submenu_export', 'de', 0, 0, '', 'http://kool.help/module/termine#exporte');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'daten_settings', 'de', 0, 0, '', 'http://kool.help/module/termine#einstellungen');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'list_events_mod', 'de', 0, 0, '', 'http://kool.help/module/termine#termin-moderation');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'ical_links', 'de', 0, 0, '', 'http://kool.help/module/termine#ical_import_und_export');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'list_absence', 'de', 0, 0, '', 'https://kool.help/module/termine#absenzenferien');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'list_rooms', 'de', 0, 0, '', 'https://kool.help/module/termine#veranstaltungsorte');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'list_reminders', 'de', 0, 0, '', 'https://kool.help/module/termine#erinnerungen');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'ical_links_absence', 'de', 0, 0, '', 'https://kool.help/module/termine#absenzen_aus_outlook_importieren');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'groups_settings', 'de', 0, 0, '', 'http://kool.help/module/gruppen#einstellungen');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'submenu_groups', 'de', 0, 0, '', 'http://kool.help/module/gruppen');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'submenu_roles', 'de', 0, 0, '', 'http://kool.help/module/gruppen#rollen');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'submenu_export', 'de', 0, 0, '', 'http://kool.help/module/gruppen#exporte');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_roles', 'de', 0, 0, '', 'http://kool.help/module/gruppen#rollen');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_datafields', 'de', 0, 0, '', 'http://kool.help/module/gruppen#gruppen_datenfelder');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'list_datafields', 'de', 0, 0, '', 'http://kool.help/module/gruppen#gruppen_datenfelder');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'kota.ko_groups.type', 'de', '', '', '', 'http://kool.help/module/gruppen#platzhalter-gruppen');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'kota.ko_groups.datafields', 'de', '', '', '', 'http://kool.help/module/gruppen#gruppen_datenfelder');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'kota.ko_groups.mailing_alias', 'de', '', '', '', 'http://kool.help/module/gruppen_e-mails#e-mail_alias');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'kota.ko_groups.stop', 'de', '', '', '', 'http://kool.help/module/gruppen#zeitliche_terminierung_der_gruppen');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'kota.ko_groups.pid', 'de', '', '', '', 'http://kool.help/module/gruppen#hierarchie');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'submenu_objekte', 'de', 0, 0, '', 'http://kool.help/module/reservationen#reservations-objekte');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'submenu_export', 'de', 0, 0, '', 'http://kool.help/module/reservationen#exporte');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'neue_reservation', 'de', 0, 0, '', 'http://kool.help/module/reservationen#reservationen_erfassen');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'liste', 'de', 0, 0, '', 'http://kool.help/module/reservationen#liste');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'show_mod_res', 'de', 0, 0, '', 'http://kool.help/module/reservationen#moderation');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'submenu_filter', 'de', 0, 0, '', 'http://kool.help/module/reservationen#liste');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'res_settings', 'de', 0, 0, '', 'http://kool.help/module/reservationen#einstellungen');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'ical_links', 'de', 0, 0, '', 'http://kool.help/module/reservationen#ical-abo');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'list_items', 'de', 0, 0, '', 'http://kool.help/module/reservationen#reservations-objekte');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'settings', 'de', 0, 0, '', 'http://kool.help/module/dienstplan#einstellungen');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'schedule', 'de', 0, 0, '', 'http://kool.help/module/dienstplan#einteilen');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'list_teams', 'de', 0, 0, '', 'http://kool.help/module/dienstplan#dienste');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'ical_links', 'de', 0, 0, '', 'http://kool.help/module/dienstplan#ical-abo');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'kota.ko_rota_teams.rotatype', 'de', 0, 0, '', 'http://kool.help/module/dienstplan#dienstwochen');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'kota.ko_rota_teams.allow_consensus', 'de', 0, 0, '', 'http://kool.help/module/dienstplan#konsensus_doodle-umfrage');
INSERT INTO `ko_help` VALUES(NULL, 'rota', 'submenu_rota', 'de', 0, 0, '', 'http://kool.help/module/dienstplan');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_accounts', 'de', 0, 0, '', 'http://kool.help/module/spenden#konten');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_filter', 'de', 0, 0, '', 'http://kool.help/module/spenden#spendenliste');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'merge', 'de', 0, 0, '', 'http://kool.help/module/spenden#spenden_zusammenfassen');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_export', 'de', 0, 0, '', 'http://kool.help/module/spenden#export');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'list_donations', 'de', 0, 0, '', 'http://kool.help/module/spenden#spendenliste');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'donation_settings', 'de', 0, 0, '', 'http://kool.help/module/spenden#einstellungen');
INSERT INTO `ko_help` VALUES(NULL, 'tracking', '', 'de', 0, 0, '', 'http://kool.help/module/praesenzlisten');
INSERT INTO `ko_help` VALUES(NULL, 'tracking', 'submenu_export', 'de', 0, 0, '', 'http://kool.help/module/praesenzlisten#export');
INSERT INTO `ko_help` VALUES(NULL, 'subscription', 'submenu_forms', 'de', 0, 0, '', 'http://kool.help/module/anmeldung#formulare_erstellen');
INSERT INTO `ko_help` VALUES(NULL, 'subscription', 'submenu_form_groups', 'de', 0, 0, '', 'http://kool.help/module/anmeldung#formulargruppen');
INSERT INTO `ko_help` VALUES(NULL, 'subscription', 'submenu_double_opt_in', 'de', 0, 0, '', 'https://kool.help/module/anmeldung#double-opt-in_aktivieren');

INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_eventgruppen', 'de', 0, 0, '', 'http://kool.help/module/termine#termingruppen');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_resitem', 'de', 0, 0, '', 'http://kool.help/module/reservationen#reservations-objekte');
INSERT INTO `ko_help` VALUES(NULL, 'kota', 'ko_donations', 'de', 0, 0, '', 'http://kool.help/module/spenden#spenden_erfassen');

INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_daten', 'de', 0, 0, '<b>2: </b>Zu den gewählten Termingruppen dürfen neue Termine erfasst, bestehende bearbeitet und gelöscht werden (inkl. Moderation gemäss Einstellung pro Termingruppe)<br />\r\n<b>3: </b>Rechte von 2 aber ohne Moderation auch bei moderierten Termingruppen<br/>\r\n<b>4: </b>Von Stufe-2-Benutzern erfasste Termine moderieren<br />\r\n<b>ALL-Rechte auf 3:</b><br />Termingruppen/Kalender erstellen', '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_reservation', 'de', 0, 0, '<b>2:</b> Neue Reservationen erfassen, die aber je nach Objekt noch moderiert werden.<br /> <b>3: </b>Eigene Reservationen bearbeiten/löschen.<br /> <b>4:</b> Neue Reservationen ohne Moderation erfassen. Alle Reservationen zu den gewählten Objekten bearbeiten/löschen.<br /> <b>5:</b> Moderieren der Reservations-Anfragen<br /> <b>ALL-Rechte auf 4:</b> Neue Reservations-Gruppen und -Objekte erstellen', '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_groups', 'de', 0, 0, '<b>2:</b> Personen Gruppen zuweisen oder Zuweisung aufheben.<br /> <b>3:</b> Gruppen und Rollen bearbeiten.<br /> <b>4:</b> Gruppen und Rollen löschen.', '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_admin', 'de', 0, 0, 'Normalerweise sollte jeder Benutzer Berechtigungsstufe 1 für das Admin-Modul haben, um die eigenen Layout-Einstellungen bearbeiten zu können.', '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_daten', 'en', 0, 0, '<b>2: </b>Add new events, edit or delete events for the selected event groups. Moderation needed if set for the event group.<br />\r\n<b>3: </b>Same as 2 but without moderation even if set for the event group.<br/>\r\n<b>4: </b>Moderate events entered by users with access level 2.<br />\r\n<b>ALL rights set to 3:</b><br />Create new event groups/calendars', '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_reservation', 'en', 0, 0, '<b>2:</b> Add new reservations. Moderation needed for items with moderation.<br /> <b>3: </b>Edit/delete own reservations.<br /> <b>4:</b> Add new reservations without moderation and edit/delete all reservations for the given items.<br /> <b>4:</b> Moderate reservation requests.<br /> <b>ALL rights set to 4:</b> Create new reservation groups and items', '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_groups', 'en', 0, 0, '<b>2:</b> Add people to groups or remove them.<br /> <b>3:</b> Edit groups and add or edit roles.<br /> <b>4:</b> Delete groups and roles.', '');
INSERT INTO `ko_help` VALUES(NULL, 'admin', 'login_rights_admin', 'en', 0, 0, 'Usually every user should have access to the admin module with access level 1 to edit her own layout settings.', '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'ical_links2', 'en', 0, 0, 'Right click on link and select "Copy link" to add the link to your clipboard. Paste it into your calendar application.', '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'ical_links2', 'de', 0, 0, 'Mit der rechten Maustaste auf den gewünschten Link klicken und "Link-Adresse kopieren". Danach im Kalender-Programm als Kalender-URL einfügen.', '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'kota.ko_reminder.text', 'de', 0, 0, 'Im Text der Nachricht können verschiedene Platzhalter eingefügt werden, die anschliessend vom kOOL durch Absender-, Empfänger-, oder Eventspezifische Informationen ersetzt werden.\nAls Beispiel:\n\n###r__salutation_formal_name###\nHier erhalten Sie wichtige Informationen zum Event ###e_title###:\nStartzeit: ###e_startzeit###, Startdatum: ###e_startdatum###.\n\nMit freundlichen Grüssen,\n###s_vorname### ###s_nachname###', '');
INSERT INTO `ko_help` VALUES(NULL, 'daten', 'kota.ko_reminder.text', 'en', 0, 0, 'Several placeholders may be inserted into the text of the reminder. These will then be replaced by the corresponding information of an event, receiver or sender.\nAn Example:\n\n###r__salutation_formal_name###\nHere you get some important information concerning the event ###e_title###:\nStarting time: ###e_startzeit###, Starting date: ###e_startdatum###.\n\nYours sincerely,\n###s_vorname### ###s_nachname###', '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'ical_links2', 'en', 0, 0, 'Right click on link and select "Copy link" to add the link to your clipboard. Paste it into your calendar application.', '');
INSERT INTO `ko_help` VALUES(NULL, 'reservation', 'ical_links2', 'de', 0, 0, 'Mit der rechten Maustaste auf den gewünschten Link klicken und "Link-Adresse kopieren". Danach im Kalender-Programm als Kalender-URL einfügen.', '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_filter_amount', 'en', 0, 0, '<h1>Filter for the amount:</h1><ul><li><b>100-200</b>: Find amount between 100 and 200 (including)</li><li><b>&gt;100</b>: Amount greater than or equal to 100</li><li><b>&lt;100</b>: Amount smaller than or equal to 100</li><li><b>=100</b>: Amount is exactly 100</li><li><b>Other values</b>: Partial matches: "100" finds "100", "1000" and e.g. "1009"</li></ul>', '');
INSERT INTO `ko_help` VALUES(NULL, 'donations', 'submenu_filter_amount', 'de', 0, 0, '<h1>Suche nach Betrag:</h1><ul><li><b>100-200</b>: Betrag zwischen 100 und 200 (inklusive)</li><li><b>&gt;100</b>: Betrag grösser als 100 (inklusive)</li><li><b>&lt;100</b>: Betrag kleiner als 100 (inklusive)</li><li><b>=100</b>: Betrag exakt 100</li><li><b>Sonstige Eingaben</b>: Teilsuche, z.B. "100" findet "100" aber auch "1000" oder "1009"</li></ul>', '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'filter_link_adv', 'de', 0, 0, '<h1>Manuelle Filter-Verknüpfung</h1>Für jeden angewandten Filter erscheint in obiger Liste eine Nummer, über die der Filter im Textfeld referenziert werden kann.<br />Beispiele:<ul><li><b>0 UND 1:</b> Damit wird der erste Filter ("0") mit einem logischen UND mit dem zweiten ("1") verknüpft.</li><li><b>0 UND (1 ODER 2)</b>: Der erste ("0") sowie entweder der zweite oder der dritte Filter ("(1 ODER 2)") müssen zutreffen.</li></ul>', '');
INSERT INTO `ko_help` VALUES(NULL, 'leute', 'filter_link_adv', 'en', 0, 0, '<h1>Manually set filter links</h1>In the list above you can see a number for every currently applied filter. Use these in the input below to add a reference to each filter.<br />Some examples:<ul><li><b>0 AND 1:</b>The first ("0") and second ("1") filter will be linked by a logical AND.</li><li><b>0 AND (1 OR 2)</b>: The first ("0") filter and either the second or third filter ("(1 OR 2)")must match.</li></ul>', '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'kota.ko_groups.linked_group', 'en', '', '', 'When adding an address to the current group this option will also assign this address to the group specified here. This only applies to newly assigned addresses. If the address is removed from this group it will stay in the linked group.', '');
INSERT INTO `ko_help` VALUES(NULL, 'groups', 'kota.ko_groups.linked_group', 'de', '', '', 'Beim Hinzufügen einer Adresse zu dieser Gruppe wird diese Adresse auch der verknüpften Gruppe zugewiesen. Dies gilt nur für Neuzuweisungen von Adressen zu dieser Gruppe. Wird eine Adresse aus dieser Gruppe entfernt, bleibt sie in der verknüpften Gruppe bestehen.', '');

INSERT INTO `ko_help` VALUES(NULL, 'subscription', 'kota.ko_subscription_forms.response_body_subscription', 'de', 0, 0, 'Platzhalter die hier verwendet werden können sind bei den Formularfeldern einsehbar.<br />Weitere Platzhalter:<dl><dt>###EDIT_LINK###</dt><dd>Fügt einen Link zum Bearbeiten der Anmeldung ein. Das funktioniert nur bei deaktivierter Moderation.</dd><dt>###SALUTATION_FORMAL###><dd>Formelle Anrede inkl. Name (z.B. Sehr geehrter Herr Muster)</dd><dt>###SALUTATION_INFORMAL###</dt><dd>Informelle Anrede inkl. Name (z.B. Lieber Peter)</dd></dl>', '');
INSERT INTO `ko_help` VALUES(NULL, 'subscription', 'kota.ko_subscription_forms.response_body_edit', 'de', 0, 0, 'Platzhalter die hier verwendet werden können sind bei den Formularfeldern einsehbar.<br />Weitere Platzhalter:<dl><dt>###EDIT_LINK###</dt><dd>Fügt einen Link zum Bearbeiten der Anmeldung ein. Das funktioniert nur bei deaktivierter Moderation.</dd><dt>###SALUTATION_FORMAL###><dd>Formelle Anrede inkl. Name (z.B. Sehr geehrter Herr Muster)</dd><dt>###SALUTATION_INFORMAL###</dt><dd>Informelle Anrede inkl. Name (z.B. Lieber Peter)</dd></dl>', '');
INSERT INTO `ko_help` VALUES(NULL, 'subscription', 'kota.ko_subscription_forms.moderated', 'de', '', '', 'Moderierte Anmeldungen müssen unter "Leute > Anmeldungen moderiert werden. Sonst wird sofort eine neue Adresse in kOOL gespeichert und ein Adressbereinigungs-Eintrag erstellt.', '');
INSERT INTO `ko_help` VALUES(NULL, 'subscription', 'kota.ko_subscription_forms.protected', 'de', '', '', 'Diesen Link können Sie im Leute-Modul als Spalte einblenden und über den E-Mail Versand den einzelnen eingeladenen Personen zukommen lassen.', '');
INSERT INTO `ko_help` VALUES(NULL, 'subscription', 'kota.ko_subscription_forms.edit_link', 'de', '', '', 'Mit diesem Link können die Benutzer ihre eigene Adresse und die Angaben zur Gruppenanmeldung später wieder bearbeiten.', '');
INSERT INTO `ko_help` VALUES(NULL, 'subscription', 'kota.ko_subscription_forms.url_segment', 'de', '', '', 'Unter diesem Link ist das Anmeldeformular erreichbar. Persönliche Links können Sie aus dem Leute-Modul mit eigener Spalte und über den E-Mail Versand versenden.', '');
INSERT INTO `ko_help` VALUES(NULL, 'subscription', 'kota.ko_subscription_forms.double_opt_in', 'de', '', '', '', 'https://kool.help/module/anmeldung#double-opt-in_aktivieren');


CREATE TABLE `ko_kleingruppen` (
  `id` mediumint(4) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `alter` varchar(20) NOT NULL DEFAULT '',
  `geschlecht` varchar(10) NOT NULL,
  `wochentag` varchar(20) NOT NULL,
  `ort` varchar(250) NOT NULL,
  `zeit` tinytext NOT NULL,
  `treffen` varchar(50) NOT NULL,
  `anz_frei` tinyint(4) NOT NULL DEFAULT '0',
  `kg-gen` mediumint(9) NOT NULL DEFAULT '0',
  `type` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `comments` text NOT NULL,
  `picture` varchar(200) NOT NULL,
  `url` tinytext NOT NULL,
  `eventGroupID` mediumint(9) NOT NULL,
  `mailing_alias` varchar(50) NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` mediumint(9) NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`region`,`eventGroupID`),
  KEY `mailing_alias` (`mailing_alias`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_leute` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `famid` mediumint(9) NOT NULL DEFAULT '0',
  `anrede` varchar(100) NOT NULL,
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
  `telegram_id` int(10) NOT NULL DEFAULT '-1',
  `email` varchar(100) NOT NULL,
  `web` varchar(250) NOT NULL,
  `geburtsdatum` date NOT NULL,
  `zivilstand` varchar(50) NOT NULL,
  `geschlecht` varchar(10) NOT NULL DEFAULT '',
  `memo1` text NOT NULL,
  `memo2` text NOT NULL,
  `father` mediumint(9) NOT NULL DEFAULT '0',
  `mother` mediumint(9) NOT NULL DEFAULT '0',
  `spouse` mediumint(9) NOT NULL DEFAULT '0',
  `kinder` smallint(4) NOT NULL DEFAULT '0',
  `smallgroups` text NOT NULL,
  `lastchange` datetime NOT NULL,
  `famfunction` varchar(20) NOT NULL,
  `groups` text NOT NULL,
  `checkin_number` int(10) NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `crdate` datetime NOT NULL,
  `cruserid` mediumint(9) NOT NULL,
  `rectype` varchar(10) NOT NULL,
  `information_lock` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `nachname` (`nachname`),
  KEY `geburtsdatum` (`geburtsdatum`),
  KEY `famid` (`famid`),
  KEY `deleted` (`deleted`),
  KEY `hidden` (`hidden`),
  KEY `crdate` (`crdate`),
  KEY `lastchange` (`lastchange`),
  KEY `famfunction` (`famfunction`),
  KEY `checkin_number` (`checkin_number`),
  FULLTEXT KEY `groups` (`groups`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_leute_mod` (
  `_id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `_leute_id` mediumint(8) NOT NULL DEFAULT '0',
  `firm` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `anrede` varchar(100) NOT NULL,
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
  `geschlecht` varchar(10) NOT NULL,
	`zivilstand` varchar(50) NOT NULL,
  `memo1` text NOT NULL,
  `memo2` text NOT NULL,
  `father` mediumint(9) NOT NULL DEFAULT '0',
  `mother` mediumint(9) NOT NULL DEFAULT '0',
  `spouse` mediumint(9) NOT NULL DEFAULT '0',
  `famfunction` varchar(20) NOT NULL,
  `rectype` varchar(10) NOT NULL,
  `_bemerkung` text NOT NULL,
  `_group_id` tinytext NOT NULL,
  `_group_datafields` text NOT NULL,
  `_additional_group_ids` text NOT NULL,
  `_additional_group_datafields` text NOT NULL,
  `_crdate` datetime NOT NULL,
  `_cruserid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`_id`),
  KEY `nachname` (`nachname`),
  KEY `geburtsdatum` (`geburtsdatum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_leute_revisions` (
	`id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
	`leute_id` mediumint(9) unsigned NOT NULL,
	`reason` varchar(100) NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` mediumint(9) unsigned NOT NULL,
  `group_id` text NOT NULL,
	`force_keep` tinyint(2) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_leute_preferred_fields` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `lid` mediumint(9) unsigned NOT NULL,
  `field` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `lid` (`lid`),
  KEY `field` (`field`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_event_absence` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `leute_id` mediumint(9) unsigned NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `from_date` datetime NOT NULL,
  `to_date` datetime NOT NULL,
  `ical_id` varchar(200) NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` mediumint(9) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;


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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
  `body` longblob NOT NULL,
  `sender_email` varchar(250) NOT NULL,
  `modify_rcpts` tinyint(1) NOT NULL DEFAULT '1',
	`rectype` varchar(15) NOT NULL,
	`crm_project_ids` varchar(100) NOT NULL,
	`size` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_mailing_recipients` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `mail_id` mediumint(8) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `leute_id` mediumint(9) NOT NULL,
  `placeholder_data` text NOT NULL,
  `delivery_attempts` mediumint(2) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_id` (`mail_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_news` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `type` mediumint(9) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `text` longtext NOT NULL,
	`category` varchar(100) NOT NULL DEFAULT '',
  `author` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL,
  `cdate` date NOT NULL DEFAULT '0000-00-00',
  `cruser` mediumint(9) NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_pdf_layout` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `data` text NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` mediumint(9) NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

INSERT INTO `ko_pdf_layout` VALUES(1, 'leute', 'Layout 1', 'a:8:{s:4:"page";a:5:{s:11:"orientation";s:1:"L";s:11:"margin_left";s:2:"10";s:10:"margin_top";s:2:"15";s:12:"margin_right";s:2:"10";s:13:"margin_bottom";s:2:"10";}s:6:"header";a:3:{s:4:"left";a:3:{s:4:"font";s:6:"arialb";s:8:"fontsize";s:2:"12";s:4:"text";s:22:"kOOL - the church tool";}s:6:"center";a:3:{s:4:"font";s:6:"arialb";s:8:"fontsize";s:2:"12";s:4:"text";s:0:"";}s:5:"right";a:3:{s:4:"font";s:6:"arialb";s:8:"fontsize";s:2:"12";s:4:"text";s:0:"";}}s:6:"footer";a:3:{s:4:"left";a:3:{s:4:"font";s:5:"arial";s:8:"fontsize";s:2:"11";s:4:"text";s:0:"";}s:6:"center";a:3:{s:4:"font";s:5:"arial";s:8:"fontsize";s:2:"11";s:4:"text";s:18:"- [[PageNumber]] -";}s:5:"right";a:3:{s:4:"font";s:5:"arial";s:8:"fontsize";s:2:"11";s:4:"text";s:46:"[[Day]].[[Month]].[[Year]] [[Hour]]:[[Minute]]";}}s:9:"headerrow";a:3:{s:4:"font";s:6:"arialb";s:8:"fontsize";s:2:"11";s:9:"fillcolor";s:3:"204";}s:18:"columns_datafields";b:0;s:4:"sort";s:8:"nachname";s:10:"sort_order";s:3:"ASC";s:12:"col_template";a:1:{s:8:"_default";a:2:{s:4:"font";s:5:"arial";s:8:"fontsize";s:2:"11";}}}', NOW(), 1, NOW(), 1);

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
  `lastchange_user` mediumint(8) unsigned NOT NULL,
  `user_id` mediumint(6) NOT NULL DEFAULT '0',
  `serie_id` mediumint(9) NOT NULL,
  `linked_items` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `serie_id` (`serie_id`),
  KEY `item_id` (`item_id`),
  KEY `user_id` (`user_id`),
  KEY `startdatum` (`startdatum`),
  KEY `last_change` (`last_change`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
  `lastchange_user` mediumint(8) unsigned NOT NULL,
  `user_id` mediumint(6) NOT NULL DEFAULT '0',
  `serie_id` mediumint(9) NOT NULL,
  `linked_items` text NOT NULL,
  `_event_id` mediumint(9) NOT NULL DEFAULT '0',
  `event_id` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `serie_id` (`serie_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_resgruppen` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
  `crdate` datetime NOT NULL,
  `cruser` mediumint(9) NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_rota_schedulling` (
  `team_id` mediumint(8) unsigned NOT NULL,
  `event_id` varchar(11) NOT NULL,
  `schedule` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  KEY `dienst` (`team_id`,`event_id`),
  KEY `event` (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_rota_disable_scheduling` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` mediumint(8) unsigned NOT NULL,
  `event_id` varchar(8) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `team_id_event_id` (`team_id`, `event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_rota_teams` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `rotatype` varchar(20) NOT NULL,
  `group_id` text NOT NULL,
  `eg_id` text NOT NULL,
  `export_eg` mediumint(9) NOT NULL,
  `sort` int(11) NOT NULL,
  `schedule_subgroup_members` tinyint(1) NOT NULL,
  `allow_consensus` tinyint(4) NOT NULL DEFAULT '0',
  `consensus_description` text NOT NULL,
  `consensus_disable_maybe_option` tinyint(3) NOT NULL,
  `consensus_max_promises` mediumint(8) NOT NULL,
  `days_range` varchar(20) NOT NULL DEFAULT '0,1,2,3,4,5,6',
  `farbe` varchar(6) NOT NULL DEFAULT '',
  `crdate` datetime NOT NULL,
  `cruser` mediumint(8) unsigned NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rotatype` (`rotatype`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_rota_consensus` (
  `team_id` mediumint(8) unsigned NOT NULL,
  `event_id` varchar(11) NOT NULL,
  `person_id` mediumint(8) NOT NULL,
  `answer` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  KEY `main` (`team_id`,`event_id`,`person_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;


CREATE TABLE `ko_rota_consensus_comment` (
  `team_id` mediumint(8) unsigned NOT NULL,
  `person_id` mediumint(8) unsigned NOT NULL,
  `comment` text NOT NULL,
  KEY `main` (`team_id`,`person_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_scheduler_tasks` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `crontime` varchar(200) NOT NULL,
  `status` int(10) unsigned NOT NULL,
  `call` text NOT NULL,
  `last_call` datetime NOT NULL,
  `next_call` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'Delete old data (downloads, unused gdf, ko_log, ko_mailing_mails tables)', '47 2 * * *', 0, 'ko_task_delete_old_downloads', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'Mailing', '*/5 * * * *', 0, 'ko_task_mailing', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'iCal import', '*/5 * * * *', 1, 'ko_task_import_events_ical', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'Send Reminders', '*/15 * * * *', 0, 'ko_task_reminder', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'Save group assignments history', CONCAT(10 + FLOOR( RAND( ) * 45 ), ' 23 * * *'), 1, 'ko_task_save_group_assignments', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'Perform vesr import (v11)', '34 */2 * * *', 0, 'ko_task_vesr_import', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'Perform vesr import (CAMT)', '19 */2 * * *', 0, 'ko_task_vesr_camt_import', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'Update Google Cloud Printers', '32 1 * * *', 0, 'ko_task_update_google_cloud_printers', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `ko_scheduler_tasks` VALUES(NULL, 'Statistics (Filter, general)', '45 23 * * *', 1, 'ko_task_create_new_statistics', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

CREATE TABLE `ko_settings` (
  `key` varchar(100) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

INSERT INTO `ko_settings` VALUES('show_leute_cols', 'vorname,nachname,adresse,plz,ort');
INSERT INTO `ko_settings` VALUES('mailing_mails_per_cycle', '30');
INSERT INTO `ko_settings` VALUES('daten_perm_filter_ende', '');
INSERT INTO `ko_settings` VALUES('res_perm_filter_start', '');
INSERT INTO `ko_settings` VALUES('res_perm_filter_ende', '');
INSERT INTO `ko_settings` VALUES('show_limit_groups', '20');
INSERT INTO `ko_settings` VALUES('default_view_groups', 'list_groups');
INSERT INTO `ko_settings` VALUES('show_limit_taxonomy', '20');
INSERT INTO `ko_settings` VALUES('default_view_taxonomy', 'list_terms');
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
INSERT INTO `ko_settings` VALUES('default_view_admin', 'logins');
INSERT INTO `ko_settings` VALUES('default_view_daten', 'show_cal_monat');
INSERT INTO `ko_settings` VALUES('default_view_leute', 'show_all');
INSERT INTO `ko_settings` VALUES('default_view_reservation', 'show_cal_monat');
INSERT INTO `ko_settings` VALUES('default_view_tools', '');
INSERT INTO `ko_settings` VALUES('show_limit_kg', '20');
INSERT INTO `ko_settings` VALUES('login_edit_person', '0');
INSERT INTO `ko_settings` VALUES('familie_col_name', 'a:3:{s:2:"de";a:10:{s:8:"nachname";s:8:"Nachname";s:7:"adresse";s:7:"Adresse";s:14:"adresse_zusatz";s:14:"Adresse Zusatz";s:3:"plz";s:3:"PLZ";s:3:"ort";s:3:"Ort";s:4:"land";s:4:"Land";s:4:"telp";s:5:"Tel P";s:9:"famanrede";s:15:"Haushalt-Anrede";s:12:"famfirstname";s:16:"Haushalt-Vorname";s:11:"famlastname";s:17:"Haushalt-Nachname";s:8:"famemail";s:15:"Haushalt-E-Mail";}s:2:"en";a:10:{s:8:"nachname";s:7:"Surname";s:7:"adresse";s:7:"Address";s:14:"adresse_zusatz";s:14:"Address line 2";s:3:"plz";s:9:"Post code";s:3:"ort";s:9:"Town/City";s:4:"land";s:7:"Country";s:4:"telp";s:10:"Home Phone";s:9:"famanrede";s:17:"Family Salutation";s:12:"famfirstname";s:16:"Family Firstname";s:11:"famlastname";s:15:"Family Lastname";}s:2:"nl";a:10:{s:8:"nachname";s:10:"Achternaam";s:7:"adresse";s:5:"Adres";s:14:"adresse_zusatz";s:12:"Adresregel 2";s:3:"plz";s:8:"Postcode";s:3:"ort";s:10:"Woonplaats";s:4:"land";s:4:"Land";s:4:"telp";s:14:"Telefoon thuis";s:9:"famanrede";s:12:"Aanhef gezin";s:12:"famfirstname";s:0:"";s:11:"famlastname";s:0:"";}}');
INSERT INTO `ko_settings` VALUES('daten_perm_filter_start', '');
INSERT INTO `ko_settings` VALUES('res_mandatory', '');
INSERT INTO `ko_settings` VALUES('res_send_email', '');
INSERT INTO `ko_settings` VALUES('res_allow_multires_for_guest', '0');
INSERT INTO `ko_settings` VALUES('show_limit_donations', '20');
INSERT INTO `ko_settings` VALUES('sms_country_code', '41');
INSERT INTO `ko_settings` VALUES('change_password', '1');
INSERT INTO `ko_settings` VALUES('rota_teamrole', '');
INSERT INTO `ko_settings` VALUES('rota_leaderrole', '');
INSERT INTO `ko_settings` VALUES('daten_access_calendar', '1');
INSERT INTO `ko_settings` VALUES('tracking_add_roles', '0');
INSERT INTO `ko_settings` VALUES('rota_showroles', '0');
INSERT INTO `ko_settings` VALUES('mailing_max_recipients', '0');
INSERT INTO `ko_settings` VALUES('mailing_max_attempts', '5');
INSERT INTO `ko_settings` VALUES('mailing_only_alias', '0');
INSERT INTO `ko_settings` VALUES('daten_gs_pid', '');
INSERT INTO `ko_settings` VALUES('daten_gs_role', '');
INSERT INTO `ko_settings` VALUES('default_view_rota', 'schedule');
INSERT INTO `ko_settings` VALUES('typo3_host', '');
INSERT INTO `ko_settings` VALUES('typo3_db', '');
INSERT INTO `ko_settings` VALUES('typo3_user', '');
INSERT INTO `ko_settings` VALUES('typo3_pwd', '');
INSERT INTO `ko_settings` VALUES('mailing_allow_double', '0');
INSERT INTO `ko_settings` VALUES('leute_real_delete', '0');
INSERT INTO `ko_settings` VALUES('daten_show_mod_to_all', '0');
INSERT INTO `ko_settings` VALUES('res_show_mod_to_all', '0');
INSERT INTO `ko_settings` VALUES('daten_mod_exclude_fields', '');
INSERT INTO `ko_settings` VALUES('leute_assign_global_notification', '');
INSERT INTO `ko_settings` VALUES('res_attach_ics_for_user', '');
INSERT INTO `ko_settings` VALUES('res_access_mode', '0');
INSERT INTO `ko_settings` VALUES('leute_allow_moderation', '1');
INSERT INTO `ko_settings` VALUES('checkin_display_leute_fields', 'vorname,nachname,geburtsdatum');
INSERT INTO `ko_settings` VALUES('checkin_max_results', '50');
INSERT INTO `ko_settings` VALUES('leute_allow_import', '1');
INSERT INTO `ko_settings` VALUES('consensus_ongoing_cal_timespan', '1m');

CREATE TABLE `ko_statistics` (
	`id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `user_id` mediumint(9) NOT NULL,
  `title` varchar(200) NOT NULL,
  `filter_id` mediumint(9) unsigned NOT NULL,
  `filter_hash` bigint NOT NULL,
  `result` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ko_statistics_title` (`title`),
  KEY `idx_ko_statistics_filter_id` (`filter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_tracking` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` mediumint(9) NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `label_value` varchar(200) NOT NULL,
  `types` text NOT NULL,
  `mode` varchar(20) NOT NULL,
  `filter` text NOT NULL,
  `date_eventgroup` text NOT NULL,
  `date_weekdays` varchar(20) NOT NULL,
  `dates` text NOT NULL,
  `description` text NOT NULL,
  `type_multiple` tinyint(4) NOT NULL DEFAULT '0',
	`hidden` tinyint(4) NOT NULL DEFAULT '0',
	`enable_checkin` tinyint(4) NOT NULL,
	`checkin_guest_pass` varchar(40) NOT NULL,
	`checkin_admin_pass` varchar(40) NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` mediumint(9) NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_tracking_groups` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_google_cloud_printers` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `google_id` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `path` varchar(200) NOT NULL,
  `owner_name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `google_id` (`google_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_updates` (
  `id` mediumint(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `crdate` datetime NOT NULL,
  `version` varchar(10) NOT NULL,
  `optional` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `done_date` datetime NOT NULL,
  `module` varchar(100) NOT NULL,
  `plugin` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

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

CREATE TABLE `ko_crm_projects` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`number` varchar(100) NOT NULL,
	`title` varchar(100) NOT NULL,
	`stopdate` date NOT NULL,
	`status_ids` text NOT NULL,
	`project_status` text NOT NULL,
	`crdate` datetime NOT NULL,
	`cruser` mediumint(8) NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_crm_status` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL,
	`default_deadline` smallint(4) NOT NULL,
	`crdate` datetime NOT NULL,
	`cruser` mediumint(8) NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_crm_contacts` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`type` varchar(100) NOT NULL,
	`date` datetime NOT NULL,
	`title` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`status_id` mediumint(8) unsigned NOT NULL,
	`file` varchar(255) NOT NULL,
	`project_id` mediumint(8) unsigned NOT NULL,
	`deadline` DATE NOT NULL,
	`reference` varchar(255) NOT NULL,
	`crdate` datetime NOT NULL,
	`cruser` mediumint(8) NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_crm_mapping` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`contact_id` mediumint(8) unsigned NOT NULL,
	`leute_id` mediumint(8) unsigned NOT NULL,
	PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`,`leute_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_subscription_forms` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`crdate` datetime NOT NULL,
	`cruser` mediumint(8) NOT NULL,
  `lastchange` datetime NOT NULL,
  `lastchange_user` mediumint(8) unsigned NOT NULL,
	`form_group` mediumint(8) NOT NULL,
	`title` varchar(255) NOT NULL,
	`fields` text NOT NULL,
	`groups` text NOT NULL,
	`moderated` tinyint(3) NOT NULL,
	`overflow` tinyint(3) NOT NULL,
	`layout` varchar(255) NOT NULL,
	`notification_to` varchar(255) NOT NULL,
	`notification_subject` varchar(255) NOT NULL,
	`notification_body` text NOT NULL,
	`response` tinyint(1) NOT NULL,
	`response_replyto` varchar(255) NOT NULL,
	`response_subject_subscription` varchar(255) NOT NULL,
	`response_body_subscription` text NOT NULL,
	`response_subject_edit` varchar(255) NOT NULL,
	`response_body_edit` text NOT NULL,
	`confirmation_title` varchar(255) NOT NULL,
	`confirmation_text` text NOT NULL,
	`url_segment` varchar(255) NOT NULL,
	`edit_link` tinyint(1) NOT NULL,
	`protected` tinyint(1) NOT NULL,
	`double_opt_in` tinyint(1) NOT NULL,
	`double_opt_in_title` varchar(255) NOT NULL,
	`double_opt_in_text` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_subscription_form_groups` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`crdate` datetime NOT NULL,
	`cruser` mediumint(8) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_subscription_double_opt_in` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`data` text NOT NULL,
	`form` mediumint(8) NOT NULL,
	`status` tinyint(1) NOT NULL,
	`email` varchar(255) NOT NULL,
	`sent_time` datetime DEFAULT NULL,
	`confirmation_time` datetime DEFAULT NULL,
	`confirmation_userid` mediumint(9) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_leute_revision_trace` (
	`lapsed_id` mediumint(8) unsigned NOT NULL,
	`current_id` mediumint(8) unsigned NOT NULL,
	`user_id` mediumint(8) unsigned NOT NULL,
	`date` datetime NOT NULL,
	PRIMARY KEY (`lapsed_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_taxonomy_terms` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `parent` INT NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_taxonomy_index` (
  `id` INT NOT NULL,
  `node_id` INT NOT NULL,
  `table` VARCHAR(45) NOT NULL,
  `crdate` datetime NOT NULL,
  `cruser` int(11) NOT NULL,
  PRIMARY KEY (`id`, `node_id`, `table`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

CREATE TABLE `ko_payment_transaction` (
	`id` mediumint unsigned NOT NULL AUTO_INCREMENT,
	`provider` varchar(100) NOT NULL,
	`provider_id` varchar(100) NOT NULL DEFAULT '',
	`status` tinyint DEFAULT 0 NOT NULL,
	`user_status` tinyint DEFAULT 0 NOT NULL,
	`crdate` datetime NOT NULL,
	`completion_date` datetime DEFAULT NULL,
	`order` text NOT NULL,
	`order_type` varchar(100) NOT NULL,
	`errors` text NULL,
	`user_language` char(2) NOT NULL DEFAULT '',

	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;
