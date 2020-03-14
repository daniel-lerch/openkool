UPDATE `ko_settings` SET `value` = 'Dienstplan <DIENSTNAME> für <MONAT> <JAHR>\n\nBitte Daten für folgende Anlässe bis spätestens <DEADLINE> erfassen resp. korrigieren:\n<ANLASSLISTE>\n\nVielen Dank und liebe Grüsse' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'dp_mailtext_1';

UPDATE `ko_settings` SET `value` = 'Hier bekommst du den Dienstplan für den nächsten Monat.\nVielen Dank für deinen Einsatz!\n\nLiebe Grüsse' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'dp_mailtext_2';

UPDATE `ko_settings` SET `value` = 'Dienstplan' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'dp_titel';

UPDATE `ko_settings` SET `value` = '41' WHERE `key` = 'sms_country_code';



UPDATE `ko_pdf_layout` SET `name` = 'Layout 1' WHERE `ko_pdf_layout`.`id` = '1';
