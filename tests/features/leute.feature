@javascript
Feature: Leute Module
  Add new persons and modify, search and filter.

  Background:
    Given I am logged in as root

  Scenario: Add new person and search for it
    Given I am on "/leute/index.php"
    And I hover over "#ko_menu > ul > li.dropdown.active > a"
    And I follow "Adresse hinzufügen"
    Then I should see "Adresse"
    And I fill in "koi[ko_leute][firm][0]" with "Lauper Computing"
    And I click on "Speichern"

  Scenario: View and download general statistics of addresses
    Given I am on "/leute/index.php?action=leute_chart"
    And I select "addresses" from "sel_leute_chart_statistics"
    And I wait "300" Miliseconds
    Then I should be able to download chart.png
