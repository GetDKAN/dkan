@api
# features/search.feature
Feature: Search
  In order to see a dataset
  As a website user
  I need to be able to search for a word

  Background:
    Given I am on the homepage
    And pages:
    | title          | url                        |
    | Dataset Search | /dataset |

  Scenario: Searching datasets
    Given datasets:
      | title           |
      | Dataset 01      |
    When I search for "Dataset 01"
    Then I should be on the "Dataset Search" page
    And I should see "Dataset 01"
    
  Scenario: See number of datasets on search page
    Given I am on the "Dataset Search" page
    Given I search for " "
    Then I should see "10" search results shown on the page
    And I should see "11 datasets"

  Scenario: Filter by facet tag
    Given "tags" terms:
      | name      |
      | something   |
    Given datasets:
      | title           | tags      |
      | Dataset 01      | something |
      | Dataset 02      | politics  |

    And I search for " "
    When I click "politics"
    Then I should not see "Dataset 01"
    But I should see "Dataset 02"

  Scenario: Filter by facet group
    Given groups:
      | title    |
      | Group 01 |
    Given datasets:
      | title           | publisher|
      | Dataset 01      |          |
      | Dataset 02      | Group 01 |
    And I search for " "
    When I click "Group 01"
    Then I should not see "Dataset 01"
    But I should see "Dataset 02"
