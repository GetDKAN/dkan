# time:1m17.36s
@api @disablecaptcha
Feature: Site Manager administer groups
  In order to manage site organization
  As a Site Manager
  I want to administer groups

  Portal administrators needs to be able to create, edit, and delete
  groups. They need to be able to set group membership by adding and removing
  users and setting group roles and permissions.


  Background:
    Given pages:
      | name      | url             |
      | Groups    | /groups         |
      | Content   | /admin/content/ |
    Given users:
      | name    | mail                | roles                |
      | Gabriel | gabriel@example.com | content creator      |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | content creator      |
      | Martin  | martin@example.com  | editor               |
    Given groups:
      | title    | author  | published |
      | Group A  | Gabriel | Yes       |
      | Group B  | Katie   | Yes       |
      | Group C  | Gabriel | No        |
    And group memberships:
      | user    | group   | role on group        | membership status |
      | Gabriel | Group A | administrator member | Active            |
      | Katie   | Group A | member               | Active            |
      | Jaz     | Group A | member               | Pending           |
    And datasets:
      | title  | publisher | author  | published | description |
      | GC1    | Group A   | Gabriel | Yes       | --test--    |

  Scenario: Edit group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    When I click "Edit"
    And I fill in "Description" with "Edited page"
    And I press "Save"
    Then I should see "Group Group A has been updated"
    And I should be on the "Group A" page

  Scenario: Add group member on a group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    And I click "Add people"
    #When I fill in the "member" form for "Martin"
    When I fill in "Martin" for "User name"
    And I press "Add users"
    Then I should see "Martin has been added to the group Group A"
    When I am on "Group A" page
    And I click "Members"
    Then I should see "Martin" in the "group members" region

  Scenario: Remove group member from a group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    And I click "People"
    And I click "remove" in the "Katie" row
    And I press "Remove"
    Then I should see "The membership was removed"
    When I am on "Group A" page
    And I click "Members"
    Then I should not see "Katie" in the "group members" region

  Scenario: I should not be able to edit a group that I am not a member of
    Given I am logged in as "Gabriel"
    When I am on "Group B" page
    Then I should not see the link "Edit"
    And I should not see the link "fa-users"

  Scenario: Edit membership status of group member as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    And I click "People"
    And I click "edit" in the "Katie" row
    When I select "Blocked" from "Status"
    And I press "Update membership"
    Then I should see "The membership has been updated"

  Scenario: Edit group roles of group member as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    And I click "People"
    And I click "edit" in the "Katie" row
    When I check "administrator member"
    And I press "Update membership"
    Then I should see "The membership has been updated"

  Scenario: View permissions of group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    When I click "Permissions (read-only)"
    Then I should see the list of permissions for the group

  Scenario: View group roles of group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    When I click "Roles (read-only)"
    Then I should see the list of roles for the group "Group A"

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

  Scenario: View the number of members on group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    When I click "People"
    Then I should see "Total members: 3"

  Scenario: View the number of content on group as group administrator
    Given I am logged in as "Gabriel"
    And I am on "Group A" page
    And I click "Group"
    When I click "People"
    Then I should see "Total content: 1"

  Scenario: Edit dataset content created by others on group as editor
    Given I am logged in as "Martin"
    And I am on "GC1" page
    Then I should see "Edit"

  Scenario: Show correct number of groups to which user belongs
    Given I am logged in as "Katie"
    When I am on "user"
    Then I should see "2 Groups" in the "content" region

