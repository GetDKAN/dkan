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
    | Dataset Results| /search/type/dataset?query=Dataset%2001  |
    Given users:
      | name    | mail                | roles                |
      | Badmin  | admin@example.com   | site manager         |
      | Gabriel | gabriel@example.com | editor               |
    Given groups:
      | title    | author  | published |
      | Group 01 | Badmin  | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
    And "Tags" terms:
      | name      |
      | something |
      | politics  |
    And datasets:
      | title           | publisher | author  | published | tags      | description |
      | Test Dataset 01 |           | Gabriel | Yes       | something | Test 01     |
      | Test Dataset 02 | Group 01  | Gabriel | Yes       | politics  | Test 02     |

  Scenario: Searching datasets
    When I search for "Dataset 01"
    Then I should be on the "Dataset Results" page
    And I should see "Dataset 01"

  Scenario: See number of datasets on search page
    Given I am on the "Dataset Search" page
    Given I search for "Test"
    Then I should see "2" search results shown on the page
    And I should see "2 results"

  Scenario: Filter by facet tag
    Given I search for "Test"
    When I click "politics"
    Then I should not see "Dataset 01"
    But I should see "Dataset 02"

  Scenario: Filter by facet group
    Given I search for "Test"
    When I click "Group 01"
    Then I should not see "Dataset 01"
    But I should see "Dataset 02"
