# time:5m51.88s
@api @enableDKAN_Linkchecker @disablecaptcha
Feature:
  Linkchecker tests for DKAN Linkchecker Module

  Background:
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |

    Given groups:
      | title    | author  | published |
      | Group 01 | John    | Yes       |

    And group memberships:
      | user    | group    | role on group        | membership status |
      | John    | Group 01 | administrator member | Active            |

  @linkchecker_01
  Scenario: As a site manager I should have access to the link checker config and report pages.
    Given I am logged in as "John"
    When I click "Link Checker Settings" in the "admin menu" region
    Then I should see "General settings"
    When I click "Broken Links Report" in the "admin menu" region
    Then I should see "Broken Links Report" in the "page header" region

  @linkchecker_02 @fixme
  Scenario: If a user creates content with bad links the links should show up in the report.
    Given I am logged in as "John"
    And I am on the "Linkchecker Resource 01" page
    Then I should see "Edit"
    When I click "Edit"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://nope.com/file_does_not_exist.csv"
    And I press "edit-submit"
    Then I run linkchecker-analyze
    And I run cron
    When I click "Broken Links Report" in the "admin menu" region
    And I should see "https://nope.com/file_does_not_exist.csv"
    And I should see "http://badlinktest.com/this/will/fail"
