@api @enableDKAN_Linkchecker @disablecaptcha
Feature:
  Linkchecker tests for DKAN Linkchecker Module

  Background:
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |

  @linkchecker_01
  Scenario: As a site manager I should have access to the link checker config and report pages.
    Given I am logged in as "John"
    When I click "Link Checker Settings" in the "admin menu" region
    Then I should see "General settings"
    When I click "Broken Links Report" in the "admin menu" region
    Then I should see "Broken Links Report" in the "page header" region
