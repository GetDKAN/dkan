@javascript @api
Feature: Portal Administrators administer groups
  In order to manage site organization
  As a Portal Administrator
  I want to administer groups

  Portal administrators needs to be able to create, edit, and delete
  groups. They need to be able to set group membership by adding and removing
  users and setting group roles and permissions.


  Background:
    Given pages:
      | title     | url             |
      | Groups    | /groups         |
      | Content   | /admin/content/ |
    Given users:
      | name    | mail             | roles                |
      | John    | john@example.com    | administrator        |
      | Badmin  | admin@example.com   | administrator        |
      | Gabriel | gabriel@example.com | authenticated user   |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | authenticated user   |
      | Martin  | martin@example.com  | editor               |
      | Celeste | celeste@example.com | editor               |
    Given groups:
      | title    | author | published |
      | Group 01 | Badmin | Yes       |
      | Group 02 | Badmin | Yes       |
      | Group 03 | Badmin | No        |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
      | Jaz     | Group 01 | member               | Pending           |
      | Celeste | Group 02 | member               | Active            |
    And datasets:
      | title      | publisher | tags       | author  | published | description                |
      | Dataset 01 | Group 01  | price      | Katie   | Yes       | Increase of toy prices     |
      | Dataset 02 | Group 01  | price      | Katie   | No        | Cost of oil in January     |
      | Dataset 03 | Group 01  | election   | Gabriel | Yes       | Election districts         |
    And resources:
      | title       | publisher | format | author | published | dataset    | description |
      | Resource 01 | Group 01  | csv    | Katie  | Yes       | Dataset 01 |             |
      | Resource 02 | Group 01  | html   | Katie  | Yes       | Dataset 01 |             |

  @fixme
    # And I should see the "Group 01 edited" detail page - undefined
  Scenario: Edit group as administrator
    Given I am logged in as "Gabriel"
    And I am on "Group 01" page
    When I click "Edit"
    And I fill in "title" with "Goup 01 edited"
    And I press "Save"
    Then I should see "Group Goup 01 edited has been updated"
    And I should see the "Group 01 edited" detail page

  Scenario: Add group member on a group as administrator
    Given I am logged in as "Gabriel"
    And I am on "Group 01" page
    And I click "Group"
    And I click "Add people"
    #When I fill in the "member" form for "Martin"
    When I fill in "Martin" for "User name"
    And I press "Add users"
    Then I should see "Martin has been added to the group Group 01"
    When I am on "Group 01" page
    And I click "Members" in the "group information" region
    Then I should see "Martin" in the "group information" region

  Scenario: Remove group member from a group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group 01" page
    And I click "Group"
    And I click "People"
    And I click "remove" in the "Katie" row
    And I press "Remove"
    Then I should see "The membership was removed"
    When I am on "Group 01" page
    And I click "Members" in the "group information" region
    Then I should not see "Katie" in the "group information" region

  Scenario: I should not be able to edit a group that I am not a member of
    Given I am logged in as "Gabriel"
    When I am on "Group 02" page
    Then I should not see the link "Edit" in the "primary tabs" region
    And I should not see the link "Group" in the "primary tabs" region

  Scenario: Edit membership status of group member as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group 01" page
    And I click "Group"
    And I click "People"
    And I click "edit" in the "Katie" row
    When I select "Blocked" from "Status"
    And I press "Update membership"
    Then I should see "The membership has been updated"

  Scenario: Edit group roles of group member as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group 01" page
    And I click "Group"
    And I click "People"
    And I click "edit" in the "Katie" row
    When I check "administrator member"
    And I press "Update membership"
    Then I should see "The membership has been updated"

  @fixme
   # Then I should see the list of permissions for the group - undefined
  Scenario: View permissions of group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group 01" page
    And I click "Group"
    When I click "Permissions (read-only)"
    Then I should see the list of permissions for the group

  @fixme
     # Then I should see the list of roles for the group - undefined
  Scenario: View group roles of group as administrator
    Given I am logged in as "Gabriel"
    And I am on "Group 01" page
    And I click "Group"
    When I click "Roles (read-only)"
    Then I should see the list of roles for the group

  @fixme
     # Then I should see the list of permissions for "<role name>" role - undefined
  Scenario Outline: View group role permissions of group as administrator
    Given I am logged in as "Gabriel"
    And I am on "Group 01" page
    And I click "Group"
    And I click "Roles (read-only)"
    When I click "view permissions" in the "<role name>" row
    Then I should see the list of permissions for "<role name>" role

  Examples:
    | role name            |
    | non-member           |
    | member               |
    | administrator member |

  Scenario: Approve new group members as administrator
    Given I am logged in as "Gabriel"
    And I am on "Group 01" page
    And I click "Group"
    And I click "People"
    And I click "edit" in the "Jaz" row
    When I select "Active" from "Status"
    And I press "Update membership"
    Then I should see "The membership has been updated"

  Scenario: View the number of members on group as administrator
    Given I am logged in as "Gabriel"
    And I am on "Group 01" page
    And I click "Group"
    When I click "People"
    Then I should see "Total members: 4"

  Scenario: View the number of content on group as administrator
    Given I am logged in as "Gabriel"
    And I am on "Group 01" page
    And I click "Group"
    When I click "People"
    Then I should see "Total content: 4"
