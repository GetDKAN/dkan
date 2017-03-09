# time:0m53.16s
@api

  # TODO: 5 datasets are created in the test but the DKAN site has 4 datasets pre-made,
  #       with 2 of the datasets created are unpublished so the
  #       default search page will have 7 datasets instead of 3
  #       the expected number of datasets are increased to reflect this, but should be fixed later

Feature: Dataset Features
  In order to realize a named business value
  As an explicit system actor
  I want to gain some beneficial outcome which furthers the goal

  Additional text...


  Background:
    Given pages:
      | name             | url                        |
      | Datasets Search  | /search/type/dataset       |
    Given users:
      | name    | mail             | roles                |
      | John    | john@example.com    | site manager         |
      | Badmin  | admin@example.com   | site manager         |
      | Gabriel | gabriel@example.com | editor               |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | content creator      |
      | Martin  | martin@example.com  | editor               |
      | Celeste | celeste@example.com | editor               |
    Given groups:
      | title    | author  | published |
      | Group 01 | Badmin  | Yes       |
      | Group 02 | Badmin  | Yes       |
      | Group 03 | Badmin  | No        |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
      | Jaz     | Group 01 | member               | Pending           |
      | Admin   | Group 02 | administrator member | Active            |
      | Celeste | Group 02 | member               | Active            |
    And "Tags" terms:
      | name     |
      | Health 2 |
      | Gov 2    |
    And "Format" terms:
      | name   |
      | csv 2  |
      | html 2 |
    And datasets:
      | title                | publisher | author  | published        | tags     | description |
      | DKAN Test Dataset 01 | Group 01  | Gabriel | Yes              | Health 2 | Test        |
      | DKAN Test Dataset 02 | Group 01  | Gabriel | Yes              | Gov 2    | Test        |
      | DKAN Test Dataset 03 | Group 01  | Katie   | Yes              | Health 2 | Test        |
      | DKAN Test Dataset 04 | Group 02  | Celeste | No               | Gov 2    | Test        |
      | DKAN Test Dataset 05 | Group 01  | Katie   | No               | Gov 2    | Test        |
    And resources:
      | title       | publisher | format | author | published | dataset              | description |
      | Resource 01 | Group 01  | csv 2  | Katie  | Yes       | DKAN Test Dataset 01 |             |
      | Resource 02 | Group 01  | html 2 | Katie  | Yes       | DKAN Test Dataset 01 |             |
      | Resource 03 | Group 01  | html 2 | Katie  | Yes       | DKAN Test Dataset 02 |             |

   @fixme @dkanBug
    # TODO: Datasets not shown on homepage currently
     #      Will they be added to the homepage later?
  Scenario: View list of most recent published datasets (on homepage)
    When I am on the homepage
    Then I should see "19" items in the "datasets" region
    And I should see the first "3" dataset items in "Date changed" "Desc" order.

  @no-main-menu
  Scenario: View list of published datasets
    When I am on the homepage
    And I click "Datasets"
    And I search for "DKAN Test"
    Then I should see "3 results"
    And I should see "3" items in the "datasets" region

  Scenario: Order datasets by "Date changed" by oldest first.
    Given datasets:
      | title                 |  published | description | date changed |
      | Dataset 5 years ago   |  Yes       | Test        | -5 year      |
      | Dataset 2 years ago   |  Yes       | Test        | -2 year      |
      | Dataset 1 year ago    |  Yes       | Test        | -1 year      |
      | Dataset 3 years ago   |  Yes       | Test        | -3 year      |
    When I am on "Datasets Search" page
    And I search for "Dataset"
    And I select "Date changed" from "Sort by"
    And I select "Asc" from "Order"
    And I press "Apply"
    And I should see the first "4" dataset items in "Date changed" "Asc" order.


  Scenario: Order datasets by "Date changed" with newest first.
    Given datasets:
      | title               |  published | description | date changed |
      | Dataset 5 years +   |  Yes       | Test        | +5 year      |
      | Dataset 2 years +   |  Yes       | Test        | +2 year      |
      | Dataset 3 years +   |  Yes       | Test        | +3 year      |
      | Dataset 1 year +    |  Yes       | Test        | +1 year      |
    When I am on "Datasets Search" page
    And I search for "Dataset"
    And I select "Date changed" from "Sort by"
    And I select "Desc" from "Order"
    And I press "Apply"
    And I should see the first "4" dataset items in "Date changed" "Desc" order.

  Scenario: Search datasets by "title" with "Asc" order
    When I am on "Datasets Search" page
    And I select "Title" from "Sort by"
    And I select "Asc" from "Order"
    And I press "Apply"
    Then I should see the first "3" dataset items in "Title" "Asc" order.

  Scenario: Search datasets by "title" with "Desc" order
    When I am on "Datasets Search" page
    And I select "Title" from "Sort by"
    And I select "Desc" from "Order"
    And I press "Apply"
    Then I should see the first "3" dataset items in "Title" "Desc" order.

    # TODO : Reseting the search will make all the datasets appear in the results including pre-made
    #        datasets, should be fixed

  Scenario: Reset dataset search filters
    When I am on "Datasets Search" page
    And I fill in "DKAN Test" for "Search" in the "datasets" region
    And I press "Apply"
    Then I should see "3 results"
    And I should see "3" items in the "datasets" region
    When I press "Reset"
    Then I should see all published datasets
    # Then I should see "7 results"
    # And I should see "7" items in the "datasets" region

  # TODO: make sure it works when we don't have default content on.
  Scenario: View available tag filters for datasets
    When I am on "Datasets Search" page
    ## Uncomment this if you wanna use selenium.
    # Then I click on the text "Tags"
    # And I wait for "1" seconds
    Then I should see "Health 2 (2)" in the "filter by tag" region
    Then I should see "Gov 2 (1)" in the "filter by tag" region

  # TODO: make sure it works when we don't have default content on.
  Scenario: View available resource format filters for datasets
    When I am on "Datasets Search" page
    ## Uncomment this if you wanna use selenium.
    # When I click on the text "Format"
    # And I wait for "1" seconds
    Then I should see "csv 2 (1)" in the "filter by resource format" region
    And I should see "html 2 (2)" in the "filter by resource format" region

  Scenario: View available author filters for datasets
    When I am on "Datasets Search" page
    And I wait for "Author"
    ## Uncomment this if you wanna use selenium.    
    # When I click on the text "Author"
    # And I wait for "1" seconds
    Then I should see "Gabriel (2)" in the "filter by author" region
    Then I should see "Katie (1)" in the "filter by author" region

  # TODO: make sure it works when we don't have default content on.
  Scenario: Filter dataset search results by tags
    When I am on "Datasets Search" page
    And I search for "DKAN Test"
    And I press "Apply"
    Then I should see "3 results"
    And I should see "3" items in the "datasets" region
    ## Uncomment this if you wanna use selenium.
    # Then I click on the text "Tags"
    When I click "Health 2" in the "filter by tag" region
    Then I should see "2 results"
    And I should see "2" items in the "datasets" region

  # TODO: make sure it works when we don't have default content on.
  Scenario: Filter dataset search results by resource format
    When I am on "Datasets Search" page
    And I search for "DKAN Test"
    And I press "Apply"
    Then I should see "3 results"
    And I should see "3" items in the "datasets" region
    ## Uncomment this if you wanna use selenium.
    # Then I click on the text "Format"
    # Then I wait for "1" seconds
    When I click "csv 2" in the "filter by resource format" region
    Then I should see "1 results"
    And I should see "1" items in the "datasets" region

  # TODO: make sure it works when we don't have default content on.
  Scenario: Filter dataset search results by author
    When I am on "Datasets Search" page
    And I search for "DKAN Test"
    And I press "Apply"
    Then I should see "3 results"
    And I should see "3" items in the "datasets" region
    ## Uncomment this if you wanna use selenium.
    # Then I click on the text "Author"
    # Then I wait for "1" seconds
    When I click "Gabriel" in the "filter by author" region
    Then I should see "2 results"
    And I should see "2" items in the "datasets" region

  Scenario: View published dataset
    When I am on "Datasets Search" page
    And I click "DKAN Test Dataset 01"
    # I should see the license information
    Then I should be on "DKAN Test Dataset 01" page

  Scenario: Share published dataset on Google+
    When I am on "DKAN Test Dataset 01" page
    Then I should see the redirect button for "Google+"

  Scenario: Share published dataset on Twitter
    When I am on "DKAN Test Dataset 01" page
    Then I should see the redirect button for "Twitter"

  Scenario: Share published dataset on Facebook
    When I am on "DKAN Test Dataset 01" page
    Then I should see the redirect button for "Facebook"

  @fixme @testBug
    #TODO: This is currently not working on CircleCI due to a memory issue
    #      but is passing locally
    #      The default PHP limits are not enough on CI, and thus
    #      the test errors out due to insufficient memory space to allocate.
  Scenario: View published dataset information as JSON
    When I am on "Dataset 01" page
    Then I should get "JSON" content from the "JSON" button

  @fixme @testBug
    #TODO: Need to know how to check and confirm RDF format with PHP
    #      Currently there is a step for checking JSON format (scenario above this one)
    #      When you click the JSON button on a dataset
    #      Solution is to use that custom step but have it check for RDF format instead
  Scenario: View published dataset information as RDF
    When I am on "Dataset 01" page
    When I click "RDF" in the "other access" region
    Then I should see the content in "RDF" format

  @fixme @testBug
    #TODO: There is an issue where downloaded files in the browser container
    #       Are not seen by other containers, and thus can't be tested to see if they exist.
    #       A solution is to try to have files shared across containers.
  Scenario: Download file from published dataset
    When I am on "Dataset 01" page
    When I press "Download" in the "Resource 01" row
    Then A file should be downloaded

  @fixme @testBug
    # TODO: Get feedback if this is still needed, since suggested datasets are not currently viewable
    #       Will that be added later?
    # TODO: Needs definition
  Scenario: View a list of suggested datasets when viewing a dataset
    When I am on the homepage
