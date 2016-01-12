 @api @javascript
Feature: Dataset Features
  In order to realize a named business value
  As an explicit system actor
  I want to gain some beneficial outcome which furthers the goal

  Additional text...


  Background:
    Given pages:
      | title        | url                          |
      | Datasets     | /dataset                     |
      | My Content   | /user                        |
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
      | Badmin  | admin@example.com   | site manager         |
      | Gabriel | gabriel@example.com | editor               |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | content creator      |
      | Martin  | martin@example.com  | authenticated user   |
      | Celeste | celeste@example.com | authenticated user   |
    Given groups:
      | title    | author | published |
      | Group 01 | Admin  | Yes       |
      | Group 02 | Admin  | Yes       |
      | Group 03 | Admin  | No        |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
      | Jaz     | Group 01 | member               | Pending           |
      | Celeste | Group 02 | member               | Active            |
    And "Tags" terms:
      | name   |
      | Health |
      | Gov    |
    And datasets:
      | title      | publisher | author  | published        | tags     | description |
      | Dataset 01 | Group 01  | Gabriel | Yes              | price    |             |
      | Dataset 02 | Group 01  | Gabriel | Yes              | election |             |
      | Dataset 03 |           | Katie   | Yes              | price    |             |
      | Dataset 04 | Group 02  | Celeste | No               | election |             |
      | Dataset 05 | Group 01  | Katie   | No               | election |             |
    And resources:
      | title       | publisher | format | author | published | dataset    | description |
      | Resource 01 | Group 01  | csv    | Katie  | Yes       | Dataset 01 |             |
      | Resource 02 | Group 01  | html   | Katie  | Yes       | Dataset 01 |             |
      | Resource 03 | Group 01  | html   | Katie  | Yes       | Dataset 02 |             |

  Scenario: Create dataset as content creator
    Given I am logged in as "Katie"
    And I am on "Datasets" page
    When I click "Add Dataset"
    And I fill in the following:
      | Title           | Test Dataset      |
      | Description     | Test description  |
    And I fill in the chosen field "Choose some options" with "Group 01"
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"

  Scenario: Edit own dataset as a content creator
    Given I am logged in as "Katie"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I fill in "edit-title" with "Dataset 03 edited"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 edited has been updated"
    When I am on "My Content" page
    Then I should see "Dataset 03 edited"

  @fixme
    # TODO: Needs definition. How can a data contributor unpublish content?
  Scenario: Unpublish own dataset as a content creator
    Given I am on the homepage

  Scenario: Delete own dataset as content creator
    Given I am logged in as "Katie"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Dataset 03 has been deleted"

  Scenario: Add a dataset to group that I am a member of
    Given I am logged in as "Katie"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I fill in the chosen field "Choose some options" with "Group 01"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 has been updated"
    When I am on "Group 01" page
    Then I should see "Dataset 03" in the "content" region
