@api
# features/search.feature
Feature: Search
  In order to see a dataset
  As a website user
  I need to be able to search for a word

  Background:
    Given I am on the homepage
    And pages:
    | name           | url                                      |
    | Dataset Search | /search/type/dataset                     |
    | Dataset Results| /search/type/dataset?query=Dataset%2001 |

  Scenario: Searching datasets
    Given datasets:
      | title      | published        | description |
      | Dataset 01 | Yes              | Test        |

    When I search for "Dataset 01"
    Then I should be on the "Dataset Results" page
    And I should see "Dataset 01"

  Scenario: See number of datasets on search page
    Given I am on the "Dataset Search" page
    Given I search for " "
    Then I should see "4" search results shown on the page
    And I should see "4 results"

  Scenario: Filter by facet tag
    Given "tags" terms:
      | name        |
      | something   |
    Given datasets:
      | title           | published  | tags      |
      | Dataset 01      | Yes        | something |
      | Dataset 02      | Yes        | politics  |

    And I search for " "
    When I click "politics"
    Then I should not see "Dataset 01"
    But I should see "Dataset 02"

  Scenario: Filter by facet group
    Given groups:
      | title    |
      | Group 01 |
    Given datasets:
      | title           | publisher| published  |
      | Dataset 01      |          | Yes        |
      | Dataset 02      | Group 01 | Yes        |
    And I search for " "
    When I click "Group 01"
    Then I should not see "Dataset 01"
    But I should see "Dataset 02"
