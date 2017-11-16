# time:0m22.96s
@api
# features/search.feature
Feature: Search
  In order to see a dataset
  As a website user
  I need to be able to search for a word

  Background:
    Given I am on the homepage
    And pages:
      | name                      | url                                                |
      | Dataset Search            | /search/type/dataset                               |
      | Dataset Results           | /search/type/dataset?query=Dataset%2001            |
      | Topics Search             | /search/field_topics                               |
      | Topics Redirect           | /topics                                            |
      | Not valid type search     | /search/type/notvalid                              |
      | Not valid tags search     | /search/field_tags/notvalid                        |
      | Not valid topics search   | /search/field_topic/notvalid                       |
      | Not valid resource search | /search/field_resources%253Afield_format/notvalid  |
      | Not valid license search  | /search/field_license/notvalid                     |
    Given users:
      | name    | mail                | roles                |
      | Badmin  | admin@example.com   | site manager         |
      | Gabriel | gabriel@example.com | editor               |
    Given groups:
      | title    | author  | published |
      | Group 01 | Badmin  | Yes       |
      | Group 02 | Gabriel | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
    And "Tags" terms:
      | name         |
      | something01  |
      | politics01   |
    And "Topics" terms:
      | name         |
      | edumication  |
      | dazzling     |
    And datasets:
      | title              | publisher | author  | published | tags         | topics      | description |
      | ooftaya Dataset 01 | Group 02  | Gabriel | Yes       | something01  | edumication | Test 01     |
      | ooftaya Dataset 02 | Group 01  | Gabriel | Yes       | politics01   | dazzling    | Test 02     |

  Scenario: Searching datasets
    Given I am on the "Dataset Search" page
    When I search for "Dataset 01"
    Then I should be on the "Dataset Results" page
    And I should see "Dataset 01"

  Scenario: See number of datasets on search page
    Given I am on the "Dataset Search" page
    When I search for "ooftaya"
    Then I should see "2" search results shown on the page
    And I should see "2 results"

  Scenario: Filter by facet tag
    Given I am on the "Dataset Search" page
    When I search for "Test"
    Then I click "politics01"
    And I should not see "Dataset 01"
    But I should see "Dataset 02"

  Scenario: Filter by facet group
    Given I am on the "Dataset Search" page
    When I search for "Test"
    Then I click "Group 01"
    And I should not see "Dataset 01"
    But I should see "Dataset 02"

  Scenario: View Topics Search Page
    Given I am on the "Topics Search" page
    Then I should see "edumication" in the "filter by topics" region
    When I click "edumication"
    Then I should not see "Dataset 02"
    But I should see "Dataset 01"

  Scenario: Topics redirect
    Given I visit "topics"
    Then I should see "Search"
    And I should not see "Page not found"

  Scenario Outline: Forbid XSS injection in search
    Given I am on the "<page>" page
    Then I should see "Page not found"
    Examples:
    | page                      |
    | Not valid type search     |
    | Not valid tags search     |
    | Not valid topics search   |
    | Not valid resource search |
    | Not valid license search  |
