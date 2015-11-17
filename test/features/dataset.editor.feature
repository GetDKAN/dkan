@javascript @api
Feature: Dataset Features
  In order to realize a named business value
  As an explicit system actor
  I want to gain some beneficial outcome which furthers the goal

  Additional text...


Background:
  Given pages:
    | title     | url       |
    | Datasets  | /dataset |
  Given users:
    | name    | mail             | roles                |
    | John    | john@example.com    | administrator        |
    | Badmin  | admin@example.com   | administrator        |
    | Gabriel | gabriel@example.com | authenticated user     |
    | Jaz     | jaz@example.com     | editor               |
    | Katie   | katie@example.com   | authenticated user   |
    | Martin  | martin@example.com  | editor               |
    | Celeste | celeste@example.com | editor               |
  Given groups:
    | title    | author  | published |
    | Group 01 | Badmin  | Yes       |
    | Group 02 | Badmin  | Yes       |
    | Group 03 | Badmin  | No        |
  And group memberships:
    | user    | group    | role on group        | membership status |
    | Gabriel | Group 01 | administrator member | Active            |
    | Katie   | Group 01 | member               | Active            |
    | Jaz     | Group 01 | member               | Pending           |
    | Admin   | Group 02 | administrator member | Active            |
    | Celeste | Group 02 | member               | Active            |
  And "Tags" terms:
    | name    |
    | Health  |
    | Gov     |
  And datasets:
    | title      | publisher | author  | published        | tags     | description |
    | Dataset 01 | Group 01  | Gabriel | Yes              | Health   | Test        |
    | Dataset 02 | Group 01  | Gabriel | Yes              | Gov      | Test        |
    | Dataset 03 | Group 01  | Katie   | Yes              | Health   | Test        |
    | Dataset 04 | Group 02  | Celeste | No               | Gov      | Test        |
    | Dataset 05 | Group 01  | Katie   | No               | Gov      | Test        |
  And "Format" terms:
    | name    |
    | xls     |
  And resources:
    | title       | publisher | format | author | published | dataset    | description |
    | Resource 01 | Group 01  | csv    | Katie  | Yes       | Dataset 01 |             |
    | Resource 02 | Group 01  | xls    | Katie  | Yes       | Dataset 01 |             |
    | Resource 03 | Group 01  | xls    | Katie  | Yes       | Dataset 02 |             |

  Scenario: Edit any dataset associated with the groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I fill in "title" with "Dataset 03 edited"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 edited has been updated"

  @fixme
    # TODO: Requires workbench to be in place, not installed in data_starter at this time
  Scenario: Review any dataset associated with a group that I am a member of
    Given I am logged in as "Gabriel"
    When I am on "Needs Review" page
    Then I should see "Dataset 05"
    When I click "Change to Published" in the "Dataset 05" row
    Then I should see "Email notifications sent"
    When I am on "Needs Review" page
    Then I should not see "Dataset 05"

  @fixme
    #  And I select "Published" - undefined
  Scenario: Publish any dataset associated with the groups I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Dataset 05" page
    When I click "Edit"
    When I click "Publishing options"
    And I select "Published"
    And I press "Finish"
    Then I should see "Dataset Dataset 05 has been updated"

  @fixme
    # TODO: Requires workbench to be in place, not installed in data_starter at this time
  Scenario: Receive a notification when a dataset is created by a member of the groups that I am a member of
    Given I am logged in as "Celeste"
    And I am on "My drafts" page
    Then I should see "Dataset 04"
    And I should see "Change to Needs Review" in the "Dataset 04" row
    When I click "Change to Needs Review" in the "Dataset 04" row
    Then I should see "Needs Review" as "Moderation state" in the "Dataset 04" row
    And user "Admin" should receive an email

  @fixme
    # TODO: Requires workbench to be in place, not installed in data_starter at this time
  Scenario: I should not receive notifications of content created outside of the groups that I am a member of
    Given I am logged in as "Celeste"
    And I am on "My drafts" page
    Then I should see "Dataset 04"
    And I should see "Change to Needs Review" in the "Dataset 04" row
    When I click "Change to Needs Review" in the "Dataset 04" row
    Then I should see "Needs Review" as "Moderation state" in the "Dataset 04" row
    And "Gabriel" should not receive an email

  Scenario: I should not be able to edit datasets of groups that I am not a member of
    Given I am logged in as "Gabriel"
    When I am on "Dataset 04" page
    Then I should not see the link "Edit"

  Scenario: Delete any dataset associated with the groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Dataset 03" page
    When I click "Edit"
    When I press "Delete"
    And I press "Delete"
    Then I should see "Dataset Dataset 03 has been deleted"
