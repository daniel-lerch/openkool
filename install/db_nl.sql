ALTER TABLE `ko_leute` CHANGE `anrede` `anrede` ENUM('', 'Mijnheer', 'Mevrouw');



UPDATE `ko_settings` SET `value` = 'Dienstrooster <MAAND> <JAAR> voor <TEAMNAAM>\n\nWijs a.u.b. teamleden toe voor de volgende Activiteiten tot uiterlijk <DEADLINE> toevoegen resp. corrigeren:\n<ACTIVITEITEN>\n\nHartelijk bedankt' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'dp_mailtext_1';

UPDATE `ko_settings` SET `value` = 'Hierbij ontvangt u het rooster voor de komende maand. \nBedankt voor uw inzet!\n\nVriendelijke groet' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'dp_mailtext_2';

UPDATE `ko_settings` SET `value` = 'Dienstrooster' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'dp_titel';

UPDATE `ko_settings` SET `value` = 'Hallo!\n\n<ABSENDER> (<ABSENDEREMAIL>) heeft u een bestand toegestuurd. De volgende opmerkingen zijn daaraan toegevoegd:\n---\n<TEXT>\n---\nHet bestand kunt u hier downloaden: <LINK>' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'fileshare_mailtext';


UPDATE `ko_settings` SET `value` = '31' WHERE `key` = 'sms_country_code';



UPDATE `ko_tapes_printlayout` SET `name` = 'Lijst' WHERE  `ko_tapes_printlayout`.`id` = '1';
UPDATE `ko_tapes_printlayout` SET `name` = '6x2 Tapes' WHERE  `ko_tapes_printlayout`.`id` = '2';
UPDATE `ko_tapes_printlayout` SET `name` = '6x1 Tapes' WHERE  `ko_tapes_printlayout`.`id` = '3';



UPDATE `ko_pdf_layout` SET `name` = 'Lay-out 1' WHERE `ko_pdf_layout`.`id` = '1';
