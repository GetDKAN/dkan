# time:0m13.79s
@disablecaptcha
Feature: Site Manager administer groups
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
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
      | Badmin  | admin@example.com   | site manager         |
      | Gabriel | gabriel@example.com | editor               |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | content creator      |
      | Martin  | martin@example.com  | editor               |
      | Celeste | celeste@example.com | editor               |
    Given groups:
      | title    | author | published |
      | Group 01 | Badmin | Yes       |
      | Group 02 | Badmin | Yes       |
      | Group 03 | Badmin | No        |
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

  @api
  Scenario: Request group membership
    Given I am logged in as "Katie"
    And I am on "Group 02" page
    When I click "Request group membership"
    And I fill in "Request message" with "Please let me join!"
    And I press "Join"
    Then I should see "Your membership is pending approval." in the "group block" region
    And I should see "Remove pending membership request" in the "group block" region

  @api
  Scenario: Cancel membership request
    Given I am logged in as "Katie"
    And I am on "Group 02" page
    When I click "Request group membership"
    And I fill in "Request message" with "Please let me join!"
    And I press "Join"
    Then I should see "Remove pending membership request" in the "group block" region
    When I click "Remove pending membership request" in the "group block" region
    And I press "Remove"
    Then I should be on the "Group 02" page
    And I should see "Request group membership" in the "group block" region

  @api
  Scenario: Leave group
    Given I am logged in as "Katie"
    And I am on "Group 01" page
    When I click "Unsubscribe from group"
    And I press "Remove"
    Then I should be on the "Group 01" page
    And I should see "Request group membership" in the "group block" region

  @api
  Scenario: I should not be able to edit groups
    Given I am logged in as "Katie"
    When I am on "Group 01" page
    Then I should not see the link "Edit"
    And I should not see the link "fa-users"
