# features/authentication.feature
@javascript
Feature: Authentication
  In order to gain access to kool as an admin
  I need to be able to login and logout

  Scenario: Logging in with wrong credential
    Given I log in as "root" with "WRONG PASSWORD"
    Then I should see "Login fehlgeschlagen. Benutzername oder Passwort ist falsch."

  Scenario: Logging in
    Given I log in as "root" with "test23"
    And I click on the element with css selector "#btn__logout"
    Then I should see "Abmelden"

  Scenario: Logging in and logging out
    Given I log in as "root" with "test23"
    And I click on the element with css selector "#btn__logout"
    Then I follow "Abmelden"
    Then I should see "Anmelden"
