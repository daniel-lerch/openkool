@javascript
Feature: Homepage
  In order to make me sure that Behat is configured properly.
  As a anonymous user, I need to be able to see login text somewhere

  Scenario: Visit the homepage and look for specific text
    Given I am on "/"
    And I click on the element with css selector "#lang-select > button"
    And I follow "DE"
    Then I should see "Anmelden"

  Scenario: Visit the homepage in english and look for specific text
    Given I am on "/"
    And I click on the element with css selector "#lang-select > button"
    And I follow "EN"
    Then I should see "login"

  Scenario: Visit the homepage in french and look for specific text
    Given I am on "/"
    And I click on the element with css selector "#lang-select > button"
    And I follow "FR"
    Then I should see "Connexion"