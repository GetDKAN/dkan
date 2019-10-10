# time:0m18.79s
@api @disablecaptcha @smoketest
Feature: Group membership

  Background:
    Given users:
      | name    | mail                | roles                |
      | Juan    | juan@example.com    | site manager         |
      | Katie   | katie@example.com   | content creator      |
    Given groups:
      | title    | author | published |
      | Group 01 | Juan   | Yes       |
      | Group 02 | Juan   | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Katie   | Group 01 | member               | Active            |

  Scenario: Request group membership and cancel membership request
    Given I am logged in as "Katie"
    And I am on "Group 02" page
    When I click "Request group membership"
    And I fill in "Request message" with "Please let me join!"
    And I press "Join"
    Then I should see "Your membership is pending approval." in the "group block" region
    And I should see "Remove pending membership request" in the "group block" region
    When I click "Remove pending membership request" in the "group block" region
    And I press "Unsubscribe"
    Then I should be on the "Group 02" page
    And I should see "Request group membership" in the "group block" region

  Scenario: Leave group
    Given I am logged in as "Katie"
    And I am on "Group 01" page
    When I click "Unsubscribe from group"
    And I press "Unsubscribe"
    Then I should be on the "Group 01" page
    And I should see "Request group membership" in the "group block" region

