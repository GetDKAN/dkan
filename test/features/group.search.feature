# time:1m1.50s
@api
Feature: Group home page provides searching within the group.


  Background:
    Given pages:
      | name      | url             |
      | Groups    | /groups         |
    Given users:
      | name    | mail                | roles         |
      | Badmin  | admin@example.com   | site manager  |
      | Gabriel | gabriel@example.com | editor        |
      | Katie   | katie@example.com   | editor        |
    Given groups:
      | title    | author | published | description              |
      | Group 01 | Badmin | Yes       | This is the group 1 page |
      | Group 02 | Badmin | Yes       | This is the group 2 page |
      | Group 03 | Badmin | No        | This is the group 3 page |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
    And "Tags" terms:
      | name             |
      | Health 2         |
      | Gov 2            |
      | Count 2          |
    And "Topics" terms:
      | name             |
      | Education02      |
      | Transportation02 |
    And datasets:
      | title      | publisher | tags         | author  | published | description                | date changed      | topics           |
      | Dataset 01 | Group 01  | Health 2     | Katie   | Yes       | Increase of toy prices     | 10 September 2015 | Education02      |
      | Dataset 02 | Group 01  | Health 2     | Katie   | No        | Cost of oil in January     | 10 September 2015 | Education02      |
      | Dataset 03 | Group 01  | Gov 2        | Gabriel | Yes       | Election districts         | 17 October 2014   | Education02      |
    And "format" terms:
      | name   |
      | csv 2  |
      | html 2 |
    And resources:
      | title       | publisher | format | author | published | dataset    | description |
      | Resource 01 | Group 01  | csv 2  | Katie  | Yes       | Dataset 01 |             |
      | Resource 02 | Group 01  | html 2 | Katie  | Yes       | Dataset 03 |             |

  @group_search_01 @smoketest
  Scenario: Search datasets on group
    Given I am on "Group 01" page
    When I fill in "toy" for "Search" in the "content" region
    And I press "Apply"
    Then I wait for "1 datasets"

  @group_search_02
  Scenario: View available "date changed" filters after search
    Given Datasets:
      | title     | publisher | author  | published | description | date changed      |
      | Dataset a | Group 02  | Katie   | Yes       | Test        | 10 September 2013 |
      | Dataset b | Group 02  | Katie   | Yes       | Test        | 21 September 2013 |
      | Dataset c | Group 02  | Katie   | Yes       | Test        | 14 October 2014   |
    Given I am on "Group 02" page
    Then I should see "2014 (1)" in the "filter by date changed" region
    And I should see "2013 (2)" in the "filter by date changed" region

  @group_search_03 @smoketest
  Scenario: Filter datasets on group page
    # By resource format.
    Given I am on "Group 01" page
    When I fill in "Dataset" for "Search" in the "content" region
    And I press "Apply"
    Then I should see "csv 2 (1)" in the "filter by resource format" region
    And I should see "html 2 (1)" in the "filter by resource format" region

    # By author.
    Given I am on "Group 01" page
    When I fill in "Dataset" for "Search" in the "content" region
    And I press "Apply"
    Then I should see "Katie (1)" in the "filter by author" region
    And I should see "Gabriel (1)" in the "filter by author" region

    # By tags.
    Given I am on "Group 01" page
    When I fill in "Dataset" for "Search" in the "content" region
    And I press "Apply"
    Then I should see "Health 2 (1)" in the "filter by tag" region
    And I should see "Gov 2 (1)" in the "filter by tag" region
