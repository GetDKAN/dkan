# time:0m32.86s
@disablecaptcha @api
Feature: DKAN Dataset REST API

  Background:
    Given pages:
      | name             | url                    |
      | Search Resources | /search/type/resource  |
      | Search Datasets  | /search/type/dataset   |
    And endpoints:
      | name             | path                   |
      | dataset rest api | /api/dataset           |
    And resources:
      | title       | published | description     |
      | Resource 01 | Yes       | The description |
    And datasets:
      | title       | published | description     |
      | Dataset 01  | Yes       | The description |
    And groups:
      | title    | published |
      | Group 01 | Yes       |

  @dataset_rest_api_01
  Scenario: Create a Resource using the 'Dataset REST API' endpoint
    Given I am on "Search Resources" page
    Then I should not see "Resource 02"
    Given I use the "dataset rest api" endpoint to login with user "admin" and pass "admin"
    And I use the "dataset rest api" endpoint to create the nodes:
      | type     | title            | body            | status |
      | resource | Resource 02      | The description | 1      |
    When I am on "Search Resources" page
    Then I should see "Resource 02"

  @dataset_rest_api_02 @api
  Scenario: Attach files to Resources using the 'Dataset REST API' endpoint
    Given I am on "Resource 01" page
    Then I should not see "Polling_Places_Madison"
    Given I use the "dataset rest api" endpoint to login with user "admin" and pass "admin"
    And I use the "dataset rest api" endpoint to attach the file "dkan/Polling_Places_Madison_test.csv" to "Resource 01"
    When I am on "Resource 01" page
    Then I should see "Polling_Places_Madison"
    Given I use the "dataset rest api" endpoint to attach the file "dkan/Afghanistan_Election_Districts_test.csv" to "Resource 01"
    When I am on "Resource 01" page
    Then I should not see "Polling_Places_Madison"
    And I should see "Afghanistan_Election_Districts"
    And I run cron
    And I am logged in as a user with the "administrator" role
    And I am on "Resource 01" page
    And I click "Manage Datastore"
    Then I should not see "No imported items"

  @dataset_rest_api_03
  Scenario: Update a Resource using the 'Dataset REST API' endpoint
    Given I am on "Resource 01" page
    Then I should not see "The description was modified"
    Given I use the "dataset rest api" endpoint to login with user "admin" and pass "admin"
    And I use the "dataset rest api" endpoint to update the node "Resource 01" with:
      | body                         |
      | The description was modified |
    When I am on "Resource 01" page
    Then I should see "The description was modified"

  @dataset_rest_api_04
  Scenario: Delete a Resource using the 'Dataset REST API' endpoint
    Given I am on "Search Resources" page
    Then I should see "Resource 01"
    Given I use the "dataset rest api" endpoint to login with user "admin" and pass "admin"
    And I use the "dataset rest api" endpoint to delete the node "Resource 01"
    When I am on "Search Resources" page
    Then I should not see "Resource 01"

  @dataset_rest_api_05
  Scenario: Create a Dataset using the 'Dataset REST API' endpoint
    Given I am on "Search Datasets" page
    Then I should not see "Dataset 02"
    Given I use the "dataset rest api" endpoint to login with user "admin" and pass "admin"
    And I use the "dataset rest api" endpoint to create the nodes:
      | type     | title            | body            | status | resource    |
      | dataset  | Dataset 02       | The description | 1      | Resource 01 |
    When I am on "Search Datasets" page
    Then I should see "Dataset 02"
    And I am on "/dataset/dataset-02"
    Then I should see "Resource 01"

  @dataset_rest_api_06
  Scenario: Update a Dataset using the 'Dataset REST API' endpoint
    Given I am on "Dataset 01" page
    Then I should not see "The description was modified"
    Given I use the "dataset rest api" endpoint to login with user "admin" and pass "admin"
    And I use the "dataset rest api" endpoint to update the node "Dataset 01" with:
      | body                         |
      | The description was modified |
    When I am on "Dataset 01" page
    Then I should see "The description was modified"

  @dataset_rest_api_07
  Scenario: Delete a Dataset using the 'Dataset REST API' endpoint
    Given I am on "Search Datasets" page
    Then I should see "Dataset 01"
    Given I use the "dataset rest api" endpoint to login with user "admin" and pass "admin"
    And I use the "dataset rest api" endpoint to delete the node "Dataset 01"
    When I am on "Search Datasets" page
    Then I should not see "Dataset 01"
