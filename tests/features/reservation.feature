@javascript
Feature: Reservation Module
  CRUD Reservations, export and import

  Background:
    Given I am logged in as root

  Scenario: Export all current reservations with iCal
    Given I am on "/reservation/index.php?action=ical_links"
    And I copy the iCal-Link "Alle"
    And download the file
    Then I open the file and find "BEGIN:VCALENDAR"

  Scenario: Revoke iCal hash and try to download iCal-File with invalid hash
    Given I am on "/reservation/index.php?action=ical_links"
    Given I accept confirmation dialogs
    And I copy the iCal-Link "Alle"
    Then I click on "Links neu generieren"
    And download the file
    Then the file is not there

  Scenario: Revoke iCal hash and download iCal-File with old hash (backwards compatibility)
    Given I am on "/reservation/index.php?action=ical_links"
    Given I accept confirmation dialogs
    And I use an old iCal-Link for "reservations"
    Then I click on "Links neu generieren"
    And download the file
    Then I open the file and find "BEGIN:VCALENDAR"

