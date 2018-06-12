# time:0m22.96s
@api
# features/search.feature
Feature: Search

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
    And "Format" terms:
      | name   |
      | csv 2  |
      | html 2 |
    And "Tags" terms:
      | name         |
      | something01  |
      | politics01   |
    And "Topics" terms:
      | name         |
      | edumication  |
      | dazzling     |
    And datasets:
      | title               | publisher | author  | published | tags         | topics      | description |
      | DKANTest Dataset 01 | Group 02  | Gabriel | Yes       | something01  | edumication | Test 01     |
      | DKANTest Dataset 02 | Group 01  | Gabriel | Yes       | politics01   | dazzling    | Test 02     |
    And resources:
      | title       | publisher | format | author | published | dataset             | description |
      | Resource 01 | Group 01  | csv 2  | Badmin | Yes       | DKANTest Dataset 01 |             |
      | Resource 02 | Group 01  | html 2 | Badmin | Yes       | DKANTest Dataset 02 |             |

  @search_01
  Scenario: Searching datasets
    Given I am on the "Dataset Search" page
    When I search for "Dataset 01"
    Then I should be on the "Dataset Results" page
    And I should see "Dataset 01"

  @search_02
  Scenario: See number of datasets on search page and Reset dataset search filters
    Given I am on the "Dataset Search" page
    When I search for "DKANTest"
    Then I should see "2 results"
    And I should see "2" items in the "datasets" region
    When I press "Reset"
    Then I should see all published search content

  @search_03
  # Sites with long lists of facet items will fail unless you filter first.
  Scenario: Filter by facets
    # Tag
    Given I am on the "Dataset Search" page
    And I fill in "something01" for "Search" in the "datasets" region
    And I press "Apply"
    Then I should see "something01 (1)" in the "filter by tag" region
    And I fill in "politics01" for "Search" in the "datasets" region
    And I press "Apply"
    Then I should see "politics01 (1)" in the "filter by tag" region

    # Group
    Given I press "Reset"
    When I search for "Test"
    Then I click "Group 01"
    And I should not see "Dataset 01"
    But I should see "Dataset 02"

    # Topics
    Given I am on the "Topics Search" page
    Then I should see "edumication" in the "filter by topics" region
    When I click "edumication"
    Then I should not see "Dataset 02"
    But I should see "Dataset 01"
    # Topics redirect
    Given I visit "topics"
    Then I should see "Search"
    And I should not see "Page not found"

    # Format
    Given I am on the "Dataset Search" page
    And I fill in "csv 2" for "Search" in the "datasets" region
    Then I should see "csv 2 (1)" in the "filter by resource format" region
    And I fill in "html 2" for "Search" in the "datasets" region
    And I should see "html 2 (1)" in the "filter by resource format" region

  @search_04
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
