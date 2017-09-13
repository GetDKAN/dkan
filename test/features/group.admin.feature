# time:1m2.37s
@api @disablecaptcha
Feature: Site managers administer groups
  In order to manage site organization
  As a Site Manager
  I want to administer groups

  Site managers needs to be able to create, edit, and delete
  groups. They need to be able to set group membership by adding and removing
  users and setting group roles and permissions.


  Background:
    Given pages:
      | name      | url             |
      | Groups    | /groups         |
      | Content   | /admin/content/ |
    Given users:
      | name    | mail                | roles        |
      | John    | john@example.com    | site manager |
      | Badmin  | admin@example.com   | site manager |
      | Gabriel | gabriel@example.com | editor       |
      | Jaz     | jaz@example.com     | editor       |
      | Katie   | katie@example.com   | editor       |
      | Martin  | martin@example.com  | editor       |
      | Celeste | celeste@example.com | editor       |
    Given groups:
      | title    | author | published |
      | Group 01 | John   | Yes       |
      | Group 02 | John   | Yes       |
      | Group 03 | John   | No        |
    And "Tags" terms:
      | name    |
      | world   |
      | results |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
      | Jaz     | Group 01 | member               | Pending           |
      | Celeste | Group 02 | member               | Active            |
    And "Tags" terms:
      | name     |
      | price    |
      | election |
    And datasets:
      | title      | publisher | tags       | author  | published | description                |
      | Dataset 01 | Group 01  | world      | Katie   | Yes       | Increase of toy prices     |
      | Dataset 02 | Group 01  | world      | Katie   | No        | Cost of oil in January     |
      | Dataset 03 | Group 01  | results    | Gabriel | Yes       | Election results           |
    And "format" terms:
      | name |
      | csv  |
      | zip |
    And resources:
      | title       | publisher | format | author | published | dataset    | description |
      | Resource 01 | Group 01  | csv    | Katie  | Yes       | Dataset 01 |             |
      | Resource 02 | Group 01  | zip    | Katie  | Yes       | Dataset 01 |             |

  @group_admin_01
  Scenario: Create group
    Given I am logged in as "John"
    And I am on "Groups" page
    And I follow "Add Group"
    When I fill in the following:
      | Title         | My group       |
      | Description   | This is a body |
    And I press "Save"
    Then I should see the success message "Group My group has been created"
    And I should see the heading "My group"
    And I should see "This is a body"
    And I should see the "img" element in the "group block" region

  @group_admin_02
  Scenario: Create group with previous same title
    Given I am logged in as "John"
    And I am on "Groups" page
    And I follow "Add Group"
    When I fill in the following:
      | Title       | Group 01       |
      | Description | This is a body |
    And I press "Save"
    Then I should see "A group with title Group 01 exists on the site. Please use another title."

  @group_admin_03
  Scenario: Add a group member on any group
    Given I am logged in as "John"
    And I am on "Group 02" page
    And I click "Group"
    And I click "Add people"
    And I fill in "Katie" for "User name"
    And I press "Add users"
    Then I should see "Katie has been added to the group Group 02"
    When I am on "Group 02" page
    And I click "Members"
    Then I should see "Katie" in the "group members" region

  @group_admin_04
  Scenario: Remove a group member from any group
    Given I am logged in as "John"
    And I am on "Group 01" page
    And I click "Group"
    And I click "People"
    When I click "remove" in the "Katie" row
    And I press "Remove"
    Then I should see "The membership was removed"
    And I am on "Group 01" page
    And I click "Members"
    And I should not see "Katie" in the "group members" region

  @group_admin_05
  Scenario: Delete any group
    Given I am logged in as "John"
    And I am on "Group 02" page
    When I click "Edit"
    Then I should see the button "Delete"
    When I press "Delete"
    Then I should see "Are you sure you want to delete"
    When I press "Delete"
    Then I should see "Group Group 02 has been deleted"

  @group_admin_06
  Scenario: Edit any group
    Given I am logged in as "John"
    And I am on "Group 02" page
    When I click "Edit"
    And I fill in "Description" with "Group 02 edited"
    And I press "Save"
    Then I should see "Group Group 02 has been updated"
    And I should be on the "Group 02" page

  @group_admin_07
  Scenario: Edit membership status of group member on any group
    Given I am logged in as "John"
    And I am on "Group 01" page
    And I click "Group"
    And I click "People"
    And I click "edit" in the "Katie" row
    When I select "Blocked" from "Status"
    And I press "Update membership"
    Then I should see "The membership has been updated"

  @group_admin_08
  Scenario: Edit group roles of group member on any group
    Given I am logged in as "John"
    And I am on "Group 01" page
    And I click "Group"
    And I click "People"
    And I click "edit" in the "Katie" row
    When I check "administrator member"
    And I press "Update membership"
    Then I should see "The membership has been updated"

  @group_admin_09
  Scenario: View permissions of any group
    Given I am logged in as "John"
    And I am on "Group 01" page
    And I click "Group"
    When I click "Permissions (read-only)"
    Then I should see the list of permissions for the group

  @group_admin_10
  Scenario: View group roles of any group
    Given I am logged in as "John"
    And I am on "Group 01" page
    And I click "Group"
    When I click "Roles (read-only)"
    Then I should see the list of roles for the group "Group 01"

  @group_admin_11
  Scenario Outline: View group role permissions of any group
    Given I am logged in as "John"
    And I am on "Group 01" page
    And I click "Group"
    And I click "Permissions (read-only)"
    Then I should see the list of permissions for "<role name>" role

    Examples:
      | role name            |
      | non-member           |
      | member               |
      | administrator member |

  @group_admin_12
  Scenario: View the number of members on any group
    Given I am logged in as "John"
    And I am on "Group 01" page
    And I click "Group"
    When I click "People"
    Then I should see "Total members: 4"

  @group_admin_13
  Scenario: View the number of content on any group
    Given I am logged in as "John"
    And I am on "Group 01" page
    And I click "Group"
    When I click "People"
    Then I should see "Total content: 5"

  @group_admin_14
  Scenario: View list of unpublished groups
    Given I am logged in as "John"
    And I am on "Content" page
    When I select "No" from "status"
    And I select "group" from "type"
    And I press "Apply"
    Then I should see "Group 03"

  @group_admin_15
  Scenario: View the details of an unpublished group
    Given I am logged in as "John"
    When I am on "Group 03" page
    #TODO: What should actually be tested as far as details?
    Then I should be on the "Group 03" page

