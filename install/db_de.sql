ALTER TABLE `ko_leute` CHANGE `anrede` `anrede` ENUM('', 'Herr', 'Frau');



UPDATE `ko_settings` SET `value` = 'Dienstplan <DIENSTNAME> für <MONAT> <JAHR>\n\nBitte Daten für folgende Anlässe bis spätestens <DEADLINE> erfassen resp. korrigieren:\n<ANLASSLISTE>\n\nVielen Dank und liebe Grüsse' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'dp_mailtext_1';

UPDATE `ko_settings` SET `value` = 'Hier bekommst du den Dienstplan für den nächsten Monat.\nVielen Dank für deinen Einsatz!\n\nLiebe Grüsse' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'dp_mailtext_2';

UPDATE `ko_settings` SET `value` = 'Dienstplan' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'dp_titel';

UPDATE `ko_settings` SET `value` = 'Guten Tag\n\n<ABSENDER> (<ABSENDEREMAIL>) hat Ihnen eine Datei geschickt und folgendes dazu geschrieben:\n---\n<TEXT>\n---\nDie Datei finden Sie unter: <LINK>' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'fileshare_mailtext';


UPDATE `ko_settings` SET `value` = '41' WHERE `key` = 'sms_country_code';



UPDATE `ko_tapes_printlayout` SET `name` = 'Liste' WHERE  `ko_tapes_printlayout`.`id` = '1';
UPDATE `ko_tapes_printlayout` SET `name` = '6x2 Tapes' WHERE  `ko_tapes_printlayout`.`id` = '2';
UPDATE `ko_tapes_printlayout` SET `name` = '6x1 Tapes' WHERE  `ko_tapes_printlayout`.`id` = '3';



UPDATE `ko_pdf_layout` SET `name` = 'Layout 1' WHERE `ko_pdf_layout`.`id` = '1';
