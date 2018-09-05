@api @disablecaptcha
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
  Scenario: Assign a content creator the group administrator role
    Given I am logged in as "Shay"
    And I am on "Test Group" page
    When I click "Group"
    And I click "Add people"
    And I fill in "edit-name" with "Cara"
    And I check the box "administrator member"
    And I press "Add users"
    Then I should see "Cara was a content creator. In order to be an administrator of this group, and edit content created by other users, Cara was also given the editor role."

  @group_cc_02
    Scenario: Assign an editor the group administrator role
    Given I am logged in as "Shay"
    And I am on "Test Group" page
    When I click "Group"
    And I click "Add people"
    And I fill in "edit-name" with "Eddy"
    And I press "Add users"
    Then I should see "Eddy has the editor role, so has also been granted the group administrator role in this group."
