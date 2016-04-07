Feature: DKAN Dataset REST API

  Scenario: Creating a Dataset
    Given I am on "/search/type/dataset"
      Then I should not see "test dataset"
    Given I use the Dataset REST API to create:
      | title        | body       | status |
      | test dataset | body text  | 1      |
      When I am on "/search/type/dataset"
      Then I should see "test dataset"
    Given I use the Dataset REST API to update "test dataset":
      | title        | body       | status |
      | test dataset | body text  | 0      |
      When I am on "/search/type/dataset"
      Then I should not see "test dataset"
    Given I use the Dataset REST API to update "test dataset":
      | title                | body       | status |
      | Dataset updated | body text  | 1      |
      When I am on "/search/type/dataset"
      Then I should see "Dataset updated"
    Given I use the Dataset REST API to delete "Dataset updated":
      When I am on "/search/type/dataset"
      Then I should not see "test dataset updated"
