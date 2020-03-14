# features/vesr_import.feature
@javascript @notesting
Feature: Import new vesr payments
  We will get notified about new payments via mail.
  Therefore we need to send a mail with .v11 file and import it into kOOL.

  Scenario: Send v11 file to Mailbox
    Given there is a new v11 file
    And I send this file via mail
    And I wait "2000" Miliseconds
    Then I am logged in as root
    And I am on "/tools/index.php"
    And hover over "#ko_menu > ul > li.dropdown.active > a"
    And follow "Tasks anzeigen"
    Then execute task "Perform vesr import (v11)"
    And I wait "5000" Miliseconds
    When I load new mails
    Then find mail with Subject "Report vom automatischen ESR-Import"
    And find mail with attachment ".pdf"