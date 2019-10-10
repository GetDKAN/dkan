# time:1m15.66s
@api @disablecaptcha
Feature: Recline
  In order to know the recline preview is working
  As a website user
  I need to be able to view the recline previews

  Background:
    Given users:
      | name    | mail                | roles                |
      | Katie   | katie@example.com   | content creator      |
    And datasets:
      | title      | author  | published        | description |
      | Dataset 01 | Katie   | Yes              | Test 01     |
    And resources:
      | title       | format | author | published | dataset    | description |
      | Resource 01 | csv    | Katie  | Yes       | Dataset 01 | Test R1     |
      | Resource 02 | csv    | Katie  | Yes       | Dataset 01 | Test R2     |

# Don't remove! This is for avoiding issues when other scenarios are disabled.
  Scenario: Dumb test
    Given I am on the homepage

  @javascript
  Scenario: Viewing map preview
    Given I am logged in as "Katie"
    And I am on "/dataset/dataset-01"
    Then I should see "Resource 01"
    When I click "Resource 01"
    Then I should see "Test R1"
    When I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/files/Polling_Places_Madison_0.csv"
    And I press "edit-submit"
    Then I should see "Polling_Places_Madison_0.csv"
    And I wait for "Map"
    Given I press "Map"
    Then I should see "Latitude field"
    Then I wait for "3" seconds
    Given I click map icon number "88"
    And I wait for "Alicia Ashman Branch Library"

  @javascript @api
  Scenario: Viewing graph preview
    Given I am logged in as "Katie"
    And I am on "/dataset/dataset-01"
    Then I should see "Test 01"
    Given I click "Resource 02"
    Then I should see "Test R2"
    When I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/files/data_0.csv"
    And I press "edit-submit"
    Then I should see "data_0.csv"
    Then I wait for "3" seconds
    And I should see "748 records"
    And I wait for "Graph"
    Given I press "Graph"
    Then I should see "There's no graph here yet"

  @javascript
  Scenario: Searching data
    Given I am logged in as "Katie"
    And I am on "/dataset/dataset-01"
    Then I should see "Resource 01"
    When I click "Resource 01"
    Then I should see "Test R1"
    When I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/files/Polling_Places_Madison_0.csv"
    And I press "edit-submit"
    And I wait for "Loading" to disappear
    And I wait for "1" seconds
    Then I should see "Polling_Places_Madison_0.csv"
    And I wait for "1" seconds
    When I click "»"
    And I wait for "Madison Ice Arena"
    And I wait for "1" seconds
    And I click "«"
    And I wait for "1" seconds
    And I wait for "Blackhawk Middle School"
    And I fill in "q" with "Glendale"
    And I press "Go"
    Then I should see "1201 Tompkins Dr"
