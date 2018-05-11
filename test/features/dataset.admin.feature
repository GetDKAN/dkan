# time:0m44.59s
@api @disablecaptcha
Feature: Dataset Features
  In order to realize a named business value
  As an explicit system actor
  I want to gain some beneficial outcome which furthers the goal

  Additional text...


  Background:
    Given users:
      | name    | mail                | roles                |
      | John002    | john@example.com    | site manager         |
      | Badmin002  | admin@example.com   | site manager         |
      | Gabriel002 | gabriel@example.com | editor               |
      | Katie002   | katie@example.com   | content creator      |
    Given groups:
      | title    | author  | published |
      | Group 01 | Badmin002  | Yes       |
      | Group 02 | Badmin002  | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel002 | Group 01 | administrator member | Active            |
      | Katie002   | Group 01 | member               | Active            |
      | Admin   | Group 02 | administrator member | Active            |
    And "Tags" terms:
      | name     |
      | price    |
      | election |
    And datasets:
      | title      | publisher | author  | published | tags     | description |
      | Dataset 01 | Group 01  | Gabriel002 | Yes       | price    | Test 01     |
      | Dataset 03 | Group 01  | Katie002   | Yes       | price    | Test 03     |
      | Dataset 05 | Group 01  | Katie002   | No        | election | Test 05     |
    And "format" terms:
      | name |
      | csv  |
      | html |
    And resources:
      | title       | publisher | format | author | published | dataset    | description |
      | Resource 01 | Group 01  | csv    | Katie002  | Yes       | Dataset 01 | Test R1     |
      | Resource 02 | Group 01  | html   | Katie002  | Yes       | Dataset 01 | Test R2     |

  @dataset_admin_01 @noworkflow
  Scenario: Edit any dataset
    Given I am logged in as "John002"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I fill in "title" with "Dataset 03 edited"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 edited has been updated"

  @dataset_admin_02 @noworkflow
  Scenario: Delete any dataset
    Given I am logged in as "John002"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Dataset Dataset 03 has been deleted"

  @dataset_admin_03 @noworkflow
  Scenario: Publish any dataset
    Given I am logged in as "John002"
    And I am on "Dataset 05" page
    When I click "Edit"
    ## If you use selenium uncomment this
    # When I click "Publishing options"
    And I check the box "Published"
    And I press "Finish"
    Then I should see "Dataset Dataset 05 has been updated"

  @dataset_admin_04 @javascript
  Scenario: See all dataset fields
    Given I am logged in as "Gabriel002"
    And I am on "Dataset 01" page
    When I click "Edit"
    Then I should see all the dataset fields in the form
    And I should not see "Rights on Project Open Data"
    Then I select "Restricted" from "edit-field-public-access-level-und"
    And I should see "Rights on Project Open Data"

  @dataset_admin_05 @javascript
  Scenario: Should not see Rights field if public access level = none
    Given I am logged in as "Gabriel002"
    And I am on "Dataset 01" page
    When I click "Edit"
    Then I select "- None -" from "edit-field-public-access-level-und"
    And I should not see "Rights on Project Open Data"

  @dataset_admin_06 @api @fixme
  Scenario: ODSM data.json 1.1 mapping
    Given I am logged in as a user with the "administrator" role
    And I go to "admin/config/services/odsm/edit/data_json_1_1"
    Then the "Homepage URL (landingPage)" field should contain "[node:field-landing-page:url] || [node:url]"

