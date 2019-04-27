@api @disablecaptcha @smoketest
Feature: Content creator and groups

  Background:
    Given users:
      | name    | mail                | roles                   |
      | Cara    | cara@example.com    | content creator         |
      | Eddy    | eddy@example.com    | editor, content creator |
      | Shay    | shay@example.com    | site manager            |
    Given groups:
      | title      | author  | published |
      | Test Group | Shay    | Yes       |

  @group_cc_01
  Scenario: Content creators should not be able to edit groups
    Given I am logged in as "Cara"
    When I am on "Test Group" page
    Then I should not see the link "Edit"
    And I should not see the link "fa-users"
