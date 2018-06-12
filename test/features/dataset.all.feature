# time:1m12.16s
@api

Feature: Dataset Features

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
    And datasets:
      | title               | publisher | author  | published        | description |
      | DKANTest Dataset 01 | Group 01  | John    | Yes              | Test        |

  @dataset_all_3
  Scenario: Order datasets by "Date changed" by oldest first.
    Given datasets:
      | title                  |  published | description | date changed  |
      | Dataset 15 years ago   |  Yes       | Test        | -15 year      |
      | Dataset 12 years ago   |  Yes       | Test        | -12 year      |
      | Dataset 11 year ago    |  Yes       | Test        | -11 year      |
      | Dataset 13 years ago   |  Yes       | Test        | -13 year      |
    When I am on "Datasets Search" page
    And I search for "Dataset"
    And I select "Date changed" from "Sort by"
    And I select "Asc" from "Order"
    And I press "Apply"
    And I should see the first "4" dataset items in "Date changed" "Asc" order.

  @dataset_all_4
  Scenario: Order datasets by "Date changed" with newest first.
    Given datasets:
      | title                |  published | description | date changed  |
      | Dataset 15 years +   |  Yes       | Test        | +15 year      |
      | Dataset 12 years +   |  Yes       | Test        | +12 year      |
      | Dataset 13 years +   |  Yes       | Test        | +13 year      |
      | Dataset 11 year +    |  Yes       | Test        | +11 year      |
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
