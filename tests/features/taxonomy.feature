@javascript
Feature: Taxonomy Module
  Add terms, set parent, add them to groups and filter, then cleanup

  Background:
    Given I am logged in as root

  Scenario Outline: Add new terms
    Given I am on "/taxonomy/index.php?action=new_term"
    And I fill in "koi[ko_taxonomy_terms][name][0]" with "<term>"
    And I select "<parent>" from "koi[ko_taxonomy_terms][parent][0]"
    And I click on "Speichern"

    Examples:
      | term       | parent     |
      | Vegetables |            |
      | aubergine  | Vegetables |
      | broccoli   | Vegetables |
      | fennel     | Vegetables |
      | meat       | Vegetables |


  Scenario: Delete term "meat"
    Given I am on "/taxonomy/index.php?action=list_terms"
    Given I accept confirmation dialogs
    And I click on the element with xpath "//*[@id='listh_ko_taxonomy_terms:name']"
    And I fill in "kota_filter[ko_taxonomy_terms:name]" with "meat"
    And I press "Anwenden"
    And I wait "30" Miliseconds
    When I click on the element with css selector "li > button[title='Eintrag löschen']"
    Then I should see "Taxonomie-Stichwort wurde erfolgreich gelöscht"
    And I should not see "meat"


  Scenario Outline: Add terms to groups
    Given I am on "/groups/index.php?action=edit_group&id=<groupid>"
    And I wait "1000" Miliseconds
    Then the "txt_name" field should contain "<groupname>"
    And add "<term>" to taxonomy field
    And I click on "Speichern"
    And make a screenshot
    When I select "<term>" from "searchbox_taxonomy"
    And I wait "30" Miliseconds
    Then I should see "Suchergebnisse für Stichwort: <term>"
    And I should see "<groupname>"

    Examples:
      | groupid | groupname | term |
      | 000287  | Testgruppe | aubergine |
# TODO: write more scenarios to check. Precondition: instance with predefined testdata #
#      | 000001  | nichtvorhanden | broccoli |