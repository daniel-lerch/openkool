<?php
/*******************************************************************************
*
*    OpenKool - Online church organization tool
*
*    Copyright © 2019      Daniel Lerch
*
*    This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*******************************************************************************/

namespace OpenKool\Migrations;

class M1 extends Migration {

    public function __construct(\mysqli $db_connection) {
        parent::__construct($db_connection, 1);
    }

    protected function apply_internal() {
        $this->query('ALTER TABLE `ko_admin` ALTER `leute_admin_filter` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_admin` ALTER `leute_admin_spalten` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_admin` ALTER `leute_admin_groups` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_admin` ALTER `leute_admin_assign` SET DEFAULT \'0\'');
        $this->query('ALTER TABLE `ko_admin` ALTER `disabled` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_admin` ALTER `admingroups` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_admin` ALTER `email` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_admin` ALTER `mobile` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_admin` ALTER `kota_columns_ko_kleingruppen` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_admin` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_admingroups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_donations` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_donations_accounts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_etiketten` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_event` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_event_calendar` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_event_mod` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_event_program` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_eventgruppen` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_eventgruppen_program` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_familie` ALTER `famemail` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_familie` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_fileshare` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_fileshare_folders` ALTER `share_users` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_fileshare_folders` ALTER `comment` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_fileshare_folders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_fileshare_sent` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_filter` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_grouproles` ALTER `name` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_grouproles` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_groups` ALTER `count` SET DEFAULT \'0\'');
        $this->query('ALTER TABLE `ko_groups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_groups_datafields` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_groups_datafields_data` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_help` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `name` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `geschlecht` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `wochentag` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `ort` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `zeit` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `treffen` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `type` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `region` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `comments` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `picture` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `url` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `eventGroupID` SET DEFAULT \'0\'');
        $this->query('ALTER TABLE `ko_kleingruppen` ALTER `mailing_alias` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_kleingruppen` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_leute` ALTER `anrede` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_leute` ALTER `zivilstand` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_leute` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_leute_changes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_leute_mod` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_leute_preferred_fields` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_log` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_mailing_mails` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_mailing_recipients` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_mailmerge` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_news` MODIFY `text` mediumtext');
        $this->query('ALTER TABLE `ko_news` ALTER `text` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_news` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_pdf_layout` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_reminder` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_reminder_mapping` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_reservation` ALTER `serie_id` SET DEFAULT \'0\'');
        $this->query('ALTER TABLE `ko_reservation` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_reservation_mod` ALTER `serie_id` SET DEFAULT \'0\'');
        $this->query('ALTER TABLE `ko_reservation_mod` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_resgruppen` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_resitem` ALTER `name` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_resitem` ALTER `beschreibung` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_resitem` ALTER `linked_items` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_resitem` ALTER `email_recipient` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_resitem` ALTER `email_text` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_resitem` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_rota_consensus` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_rota_schedulling` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_rota_teams` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_scheduler_tasks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_settings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('INSERT INTO `ko_settings` VALUES(\'db_migration\', \'1\')');
        $this->query('ALTER TABLE `ko_tapes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_tapes_groups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_tapes_printlayout` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_tapes_series` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_tracking` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_tracking_entries` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_tracking_groups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->query('ALTER TABLE `ko_userprefs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }
}
