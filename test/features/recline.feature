# time:1m5.66s
@api
Feature: Recline
  In order to know the recline preview is working
  As a website user
  I need to be able to view the recline previews

  Background:
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
      | name     |
      | price    |
      | election |
    And datasets:
      | title      | publisher | author  | published        | tags     | description |
      | Dataset 01 | Group 01  | Gabriel | Yes              | price    | Test 01     |
      | Dataset 02 | Group 01  | Gabriel | Yes              | election | Test 02     |
      | Dataset 03 | Group 01  | Katie   | Yes              | price    | Test 03     |
      | Dataset 04 | Group 02  | Celeste | No               | election | Test 04     |
    And resources:
      | title       | publisher | format | author | published | dataset    | description |
      | Resource 01 | Group 01  | csv    | Katie  | Yes       | Dataset 01 | Test R1     |
      | Resource 02 | Group 01  | csv    | Katie  | Yes       | Dataset 01 | Test R2     |
      | Resource 03 | Group 01  | zip    | Katie  | Yes       | Dataset 02 | Test R3     |

# Don't remove! This is for avoiding issues when other scenarios are disabled (because of @noworkflow tag).
  Scenario: Dumb test
    Given I am on the homepage

  @javascript
  Scenario: Viewing map preview
    Given I am logged in as "John"
    And I am on "/dataset/dataset-01"
    Then I should see "Resource 01"
    When I click "Resource 01"
    Then I should see "Test R1"
    When I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "http://s3.amazonaws.com/dkan-default-content-files/files/Polling_Places_Madison_0.csv"
    And I press "edit-submit"
    Then I should see "Polling_Places_Madison_0.csv"
    And I wait for "Map"
    Given I press "Map"
    Then I should see "Latitude field"
    Then I wait for "3" seconds
    Given I click map icon number "88"
    And I wait for "Alicia Ashman Branch Library"

  @javascript @api @noworkflow
  Scenario: Viewing a resource with API field should show a iframe
    Given I am logged in as "John"
    And I am on "/dataset/dataset-01"
    Then I should see "Test 01"
    Given I click "Resource 02"
    Then I should see "Test R2"
    When I click "Edit"
    And I click "API or Website URL"
    And I fill in "edit-field-link-api-und-0-url" with "https://data.wa.gov/api/views/mu24-67ke/rows.csv?accessType=DOWNLOAD"
    And I press "edit-submit"
    Then I should not see "File was too large or unavailable for preview."

  @javascript @api @noworkflow
  Scenario: Viewing graph preview
    Given I am logged in as "John"
    And I am on "/dataset/dataset-01"
    Then I should see "Test 01"
    Given I click "Resource 02"
    Then I should see "Test R2"
    When I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "http://s3.amazonaws.com/dkan-default-content-files/files/data_0.csv"
    And I press "edit-submit"
    Then I should see "data_0.csv"
    Then I wait for "3" seconds
    And I should see "748 records"
    And I wait for "Graph"
    Given I press "Graph"
    Then I should see "There's no graph here yet"

  @javascript @noworkflow
  Scenario: Searching data
    Given I am logged in as "John"
    And I am on "/dataset/dataset-01"
    Then I should see "Resource 01"
    When I click "Resource 01"
    Then I should see "Test R1"
    When I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "http://s3.amazonaws.com/dkan-default-content-files/files/Polling_Places_Madison_0.csv"
    And I press "edit-submit"
    And I wait for "Loading" to disappear
    And I wait for "1" seconds
    Then I should see "Polling_Places_Madison_0.csv"
    And I wait for "1" seconds
    When I click "»"
    And I wait for "Our"
    And I wait for "1" seconds
    And I click "«"
    And I wait for "1" seconds
    And I wait for "East"
    And I fill in "q" with "Glendale"
    And I press "Go"
    Then I should see "Tompkins"
