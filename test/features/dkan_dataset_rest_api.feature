Feature: DKAN Dataset REST API

  Background:
    Given pages:
      | name             | url                    |
      | Search Resources | /search/type/resource  |
      | Search Datasets  | /search/type/dataset   |
    And resources:
      | title       | published | description     |
      | Resource 01 | Yes       | The description |
    And datasets:
      | title       | published | description     |
      | Dataset 01  | Yes       | The description |
    And groups:
      | title    | published |
      | Group 01 | Yes       |

  Scenario: Create a Resource using the Dataset REST API
    Given I am on "Search Resources" page
    Then I should not see "Resource 02"
    Given I use the Dataset REST API to create the nodes:
      | type     | title            | body            | status |
      | resource | Resource 02      | The description | 1      |
    And I am on "Search Resources" page
    Then I should see "Resource 02"

  Scenario: Attach a file to a Resource using the Dataset REST API
    Given I am on "Resource 01" page
    Then I should not see "Polling_Places_Madison"
    Given I use the Dataset REST API to attach the file "Polling_Places_Madison.csv" to "Resource 01"
    And I am on "Resource 01" page
    Then I should see "Polling_Places_Madison"


  Scenario: Update a Resource using the Dataset REST API
    Given I am on "Resource 01" page
    Then I should not see "The description was modified"
    When I use the Dataset REST API to update the node "Resource 01" with:
      | body                         |
      | The description was modified |
    And I am on "Resource 01" page
    Then I should see "The description was modified"

  Scenario: Delete a Resource using the Dataset REST API
    Given I am on "Search Resources" page
    Then I should see "Resource 01"
    When I use the Dataset REST API to delete the node "Resource 01"
    And I am on "Search Resources" page
    Then I should not see "Resource 01"

  Scenario: Create a Dataset using the Dataset REST API
    Given I am on "Search Datasets" page
    Then I should not see "Dataset 02"
    Given I use the Dataset REST API to create the nodes:
      | type     | title            | body            | status | resource    |
      | dataset  | Dataset 02       | The description | 1      | Resource 01 |
    When I am on "Search Datasets" page
    Then I should see "Dataset 02"
    When I am on "/dataset/dataset-02"
    Then I should see "Resource 01"

  Scenario: Update a Dataset using the Dataset REST API.
    Given I am on "Dataset 01" page
    Then I should not see "The description was modified"
    When I use the Dataset REST API to update the node "Dataset 01" with:
      | body                         |
      | The description was modified |
    And I am on "Dataset 01" page
    Then I should see "The description was modified"

  Scenario: Delete a Dataset using the REST API
    Given I am on "Search Datasets" page
    Then I should see "Dataset 01"
    When I use the Dataset REST API to delete the node "Dataset 01"
    And I am on "Search Datasets" page
    Then I should not see "Dataset 01"

