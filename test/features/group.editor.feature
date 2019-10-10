# time:1m17.36s
@api @disablecaptcha
Feature: Editor administer groups

  Background:
    Given pages:
      | name      | url             |
      | Groups    | /groups         |
      | Content   | /admin/content/ |
    Given users:
      | name    | mail                | roles            |
      | Gabriel | gabriel@example.com | editor           |
      | Jaz     | jaz@example.com     | editor           |
      | Eddie   | eddie@example.com   | editor           |
      | Katie   | katie@example.com   | content creator  |
      | Carla   | carla@example.com   | content creator  |
    Given groups:
      | title    | author  | published |
      | Group A  | Gabriel | Yes       |
      | Group B  | Katie   | Yes       |
    And group memberships:
      | user    | group   | role on group        | membership status |
      | Gabriel | Group A | administrator member | Active            |
      | Katie   | Group A | member               | Active            |
      | Jaz     | Group A | member               | Pending           |

  @group_editor_01 @smoketest
  Scenario: Edit group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    When I click "Edit"
    And I fill in "Description" with "Edited page"
    And I press "Save"
    Then I should see "Group Group A has been updated"
    And I should be on the "Group A" page

  @group_editor_02 @smoketest
  Scenario: Add group member on a group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    And I click "Add people"
    #When I fill in the "member" form for "Eddie"
    When I fill in "Eddie" for "User name"
    And I press "Add users"
    Then I should see "Eddie has been added to the group Group A"
    When I am on "Group A" page
    And I click "Group"
    And I click "People"
    Then I should see "Eddie"

  @group_editor_03 @smoketest
  Scenario: Remove group member from a group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    And I click "People"
    And I click "remove" in the "Katie" row
    And I press "Remove"
    Then I should see "The membership was removed"
    And I click "Group"
    And I click "People"
    Then I should not see "Katie"

  @group_editor_04 @javascript
   Scenario: I should not be able to edit a group that I am not a member of
    Given I am logged in as "Gabriel"
    When I am on "Group B" page
    Then I should not see "Edit"
    And I should not see the link "fa-users"

  @group_editor_05
  Scenario: Edit membership status of group member as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    And I click "People"
    And I click "edit" in the "Katie" row
    When I select "Blocked" from "Status"
    And I press "Update membership"
    Then I should see "The membership has been updated"

  @group_editor_06
  Scenario: Edit group roles of group member as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    And I click "People"
    And I click "edit" in the "Katie" row
    When I check "administrator member"
    And I press "Update membership"
    Then I should see "The membership has been updated"

  @group_editor_07
  Scenario: View permissions of group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    When I click "Permissions (read-only)"
    Then I should see the list of permissions for the group

  @group_editor_08
  Scenario: View group roles of group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    When I click "Roles (read-only)"
    Then I should see the list of roles for the group "Group A"

  @group_editor_09
  Scenario Outline: View group role permissions of group as administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    When I click "Permissions (read-only)"
    Then I should see the list of permissions for "<role name>" role

  Examples:
    | role name            |
    | non-member           |
    | member               |
    | administrator member |

  @group_editor_10 @smoketest
  Scenario: Approve new group members as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    And I click "People"
    And I click "edit" in the "Jaz" row
    When I select "Active" from "Status"
    And I press "Update membership"
    And I wait for "Group overview"
    Then I should see "The membership has been updated"

  @group_editor_11 @smoketest
  Scenario: View the number of members and view list of member names as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    When I click "People"
    Then I should see "Total members: 3"
    And I should see "Gabriel"
    And I should see "Katie"
    And I should see "Jaz" in the "Pending" row
    And I should not see "John"

  @group_editor_12
  Scenario: View the number of content on group as group administrator
    Given resources:
      | title        | publisher | format | author | published | description |
      | resourceTest | Group A   | csv    | Katie  | Yes       | blurb       |
    And I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    When I click "People"
    Then I should see "Total content: 1"

  @group_editor_13
  Scenario: Edit resource content created by others on group as editor
    Given resources:
      | title    | publisher | format | author | published | description |
      | content1 | Group A   | csv    | Katie  | Yes       |             |
    And I am logged in as "Gabriel"
    And I am on "content1" page
    When I click "Edit"
    And I fill in "Title" with "content1 edited"
    And I press "Save"
    Then I should see "Resource content1 edited has been updated"

  @group_editor_14 @javascript
  Scenario: Edit dataset content created by others on group as editor
    Given datasets:
      | title    | publisher | author | published | description |
      | dataset1 | Group A   | Katie  | Yes       |             |
    And I am logged in as "Gabriel"
    And I am on "dataset1" page
    When I click "Edit"
    And I press "Finish"
    Then I should see "Dataset dataset1 has been updated"

  @group_editor_15
  Scenario: Show correct number of groups to which user belongs
    Given I am logged in as "Katie"
    When I am on "user"
    Then I should see "2 Groups" in the "content" region

  @group_editor_16 @smoketest
  Scenario: Assign a content creator the group administrator role
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    When I click "Group"
    And I click "Add people"
    And I fill in "edit-name" with "Carla"
    And I check the box "administrator member"
    And I press "Add users"
    Then I should see "Carla was a content creator. In order to be an administrator of this group, and edit content created by other users, Carla was also given the editor role."

  @group_editor_17 @smoketest
    Scenario: Assign an editor the group administrator role
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    When I click "Group"
    And I click "Add people"
    And I fill in "edit-name" with "Eddie"
    And I press "Add users"
    Then I should see "Eddie has the editor role, so has also been granted the group administrator role in this group."

