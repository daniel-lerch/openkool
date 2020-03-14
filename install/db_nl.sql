UPDATE `ko_settings` SET `value` = 'Dienstrooster <MAAND> <JAAR> voor <TEAMNAAM>\n\nWijs a.u.b. teamleden toe voor de volgende Activiteiten tot uiterlijk <DEADLINE> toevoegen resp. corrigeren:\n<ACTIVITEITEN>\n\nHartelijk bedankt' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'dp_mailtext_1';

UPDATE `ko_settings` SET `value` = 'Hierbij ontvangt u het rooster voor de komende maand. \nBedankt voor uw inzet!\n\nVriendelijke groet' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'dp_mailtext_2';

UPDATE `ko_settings` SET `value` = 'Dienstrooster' WHERE  CONVERT(`ko_settings`.`key` USING utf8) = 'dp_titel';

UPDATE `ko_settings` SET `value` = '31' WHERE `key` = 'sms_country_code';



UPDATE `ko_pdf_layout` SET `name` = 'Lay-out 1' WHERE `ko_pdf_layout`.`id` = '1';
