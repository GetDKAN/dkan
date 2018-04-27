# time:1m12.16s
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
      | name             | url                    |
      | Datasets Search  | /search/type/dataset   |
    Given users:
      | name    | mail             | roles        |
      | John    | john@example.com | site manager |
    Given groups:
      | title    | author  | published |
      | Group 01 | John    | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | John    | Group 01 | administrator member | Active            |
    And "Tags" terms:
      | name         |
      | gobbledygook |
      | gibberish    |
    And "Format" terms:
      | name   |
      | csv 2  |
      | html 2 |
    And datasets:
      | title               | publisher | author  | published        | tags         | description |
      | DKANTest Dataset 01 | Group 01  | John    | Yes              | gobbledygook | Test        |
      | DKANTest Dataset 02 | Group 01  | John    | Yes              | gibberish    | Test        |
    And resources:
      | title       | publisher | format | author | published | dataset             | description |
      | Resource 01 | Group 01  | csv 2  | John   | Yes       | DKANTest Dataset 01 |             |
      | Resource 02 | Group 01  | html 2 | John   | Yes       | DKANTest Dataset 02 |             |

   @fixme @dkanBug
    # TODO: Datasets not shown on homepage currently
     #      Will they be added to the homepage later?
  @dataset_all_1
  Scenario: View list of most recent published datasets (on homepage)
    When I am on the homepage
    Then I should see "19" items in the "datasets" region
    And I should see the first "3" dataset items in "Date changed" "Desc" order.

  @dataset_all_2  @no-main-menu
  Scenario: View list of published datasets
    When I am on the homepage
    And I click "Datasets"
    And I search for "DKANTest"
    Then I should see "2 results"
    And I should see "2" items in the "datasets" region

  @dataset_all_3
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


  @dataset_all_4
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

  @dataset_all_5
  Scenario: Search datasets by "title" with "Asc" order
    When I am on "Datasets Search" page
    And I select "Title" from "Sort by"
    And I select "Asc" from "Order"
    And I press "Apply"
    Then I should see the first "3" dataset items in "Title" "Asc" order.

  @dataset_all_6
  Scenario: Search datasets by "title" with "Desc" order
    When I am on "Datasets Search" page
    And I select "Title" from "Sort by"
    And I select "Desc" from "Order"
    And I press "Apply"
    Then I should see the first "3" dataset items in "Title" "Desc" order.

    # TODO : Reseting the search will make all the datasets appear in the results including pre-made
    #        datasets, should be fixed

  @dataset_all_7
  Scenario: Reset dataset search filters
    When I am on "Datasets Search" page
    And I fill in "DKANTest" for "Search" in the "datasets" region
    And I press "Apply"
    Then I should see "2 results"
    And I should see "2" items in the "datasets" region
    When I press "Reset"
    Then I should see all published search content
    # Then I should see "7 results"
    # And I should see "7" items in the "datasets" region

  # TODO: make sure it works when we don't have default content on.
  @dataset_all_8
  Scenario: View available tag filters for datasets
    When I am on "Datasets Search" page
    # Sites with long lists of tags will fail unless you filter first.
    And I fill in "gobbledygook" for "Search" in the "datasets" region
    And I press "Apply"
    ## Uncomment this if you wanna use selenium.
    # Then I click on the text "Tags"
    # And I wait for "1" seconds
    Then I should see "gobbledygook (1)" in the "filter by tag" region
    And I fill in "gibberish" for "Search" in the "datasets" region
    And I press "Apply"
    ## Uncomment this if you wanna use selenium.
    # Then I click on the text "Tags"
    # And I wait for "1" seconds
    Then I should see "gibberish (1)" in the "filter by tag" region

  # TODO: make sure it works when we don't have default content on.
  @dataset_all_9
  Scenario: View available resource format filters for datasets
    When I am on "Datasets Search" page
    ## Uncomment this if you wanna use selenium.
    # When I click on the text "Format"
    # And I wait for "1" seconds
    Then I should see "csv 2 (1)" in the "filter by resource format" region
    And I should see "html 2 (1)" in the "filter by resource format" region

  # dataset_all_10/author facet removed. See GetDKAN/dkan#2033

  # TODO: make sure it works when we don't have default content on.
  @dataset_all_11
  Scenario: Filter dataset search results by tags
    When I am on "Datasets Search" page
    And I search for "DKANTest"
    And I press "Apply"
    Then I should see "2 results"
    And I should see "2" items in the "datasets" region
    ## Uncomment this if you wanna use selenium.
    # Then I click on the text "Tags"
    When I click "gobbledygook" in the "filter by tag" region
    Then I should see "1 results"
    And I should see "1" items in the "datasets" region

  # TODO: make sure it works when we don't have default content on.
  @dataset_all_12
  Scenario: Filter dataset search results by resource format
    When I am on "Datasets Search" page
    And I search for "DKANTest"
    And I press "Apply"
    Then I should see "2 results"
    And I should see "2" items in the "datasets" region
    ## Uncomment this if you wanna use selenium.
    # Then I click on the text "Format"
    # Then I wait for "1" seconds
    When I click "csv 2" in the "filter by resource format" region
    Then I should see "1 results"
    And I should see "1" items in the "datasets" region

  # dataset_all_13/author facet removed. See GetDKAN/dkan#2033

  @dataset_all_14
  Scenario: View published dataset
    When I am on "Datasets Search" page
    And I click "DKANTest Dataset 01"
    # I should see the license information
    Then I should be on "DKANTest Dataset 01" page

  @dataset_all_15
  Scenario: Share published dataset on Google+
    When I am on "DKANTest Dataset 01" page
    Then I should see the redirect button for "Google+"

  @dataset_all_16
  Scenario: Share published dataset on Twitter
    When I am on "DKANTest Dataset 01" page
    Then I should see the redirect button for "Twitter"

  @dataset_all_17
  Scenario: Share published dataset on Facebook
    When I am on "DKANTest Dataset 01" page
    Then I should see the redirect button for "Facebook"

  @dataset_all_18 @fixme @testBug
    #TODO: This is currently not working on CircleCI due to a memory issue
    #      but is passing locally
    #      The default PHP limits are not enough on CI, and thus
    #      the test errors out due to insufficient memory space to allocate.
  Scenario: View published dataset information as JSON
    When I am on "Dataset 01" page
    Then I should get "JSON" content from the "JSON" button

  @dataset_all_19 @fixme @testBug
    #TODO: Need to know how to check and confirm RDF format with PHP
    #      Currently there is a step for checking JSON format (scenario above this one)
    #      When you click the JSON button on a dataset
    #      Solution is to use that custom step but have it check for RDF format instead
  Scenario: View published dataset information as RDF
    When I am on "Dataset 01" page
    When I click "RDF" in the "other access" region
    Then I should see the content in "RDF" format

  @dataset_all_20 @fixme @testBug
    #TODO: There is an issue where downloaded files in the browser container
    #       Are not seen by other containers, and thus can't be tested to see if they exist.
    #       A solution is to try to have files shared across containers.
  Scenario: Download file from published dataset
    When I am on "Dataset 01" page
    When I press "Download" in the "Resource 01" row
    Then A file should be downloaded

  @dataset_all_21 @fixme @testBug
    # TODO: Get feedback if this is still needed, since suggested datasets are not currently viewable
    #       Will that be added later?
    # TODO: Needs definition
  Scenario: View a list of suggested datasets when viewing a dataset
    When I am on the homepage
