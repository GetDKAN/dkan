@javascript @api

  # TODO: 5 datasets are created in the test but the DKAN site has 4 datasets pre-made,
  #       so the default search page will have 9 datasets instead of 5
  #       the expected number of datasets are increased to reflect this, but should be fixed later

Feature: Dataset Features
  In order to realize a named business value
  As an explicit system actor
  I want to gain some beneficial outcome which furthers the goal

  Additional text...


  Background:
    Given pages:
      | title     | url       |
      | Datasets  | /dataset |
    Given users:
      | name    | mail             | roles                |
      | John    | john@example.com    | administrator        |
      | Badmin  | admin@example.com   | administrator        |
      | Gabriel | gabriel@example.com | editor               |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | authenticated user   |
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
      | name    |
      | Health  |
      | Gov     |
    And datasets:
      | title      | publisher | author  | published        | tags     | description |
      | Dataset 01 | Group 01  | Gabriel | Yes              | Health   | Test        |
      | Dataset 02 | Group 01  | Gabriel | Yes              | Gov      | Test        |
      | Dataset 03 | Group 01  | Katie   | Yes              | Health   | Test        |
      | Dataset 04 | Group 02  | Celeste | No               | Gov      | Test        |
      | Dataset 05 | Group 01  | Katie   | No               | Gov      | Test        |
    And "Format" terms:
      | name    |
      | csv     |
      | xls     |
    And resources:
      | title       | publisher | format | author | published | dataset    | description |
      | Resource 01 | Group 01  | csv    | Katie  | Yes       | Dataset 01 |             |
      | Resource 02 | Group 01  | xls    | Katie  | Yes       | Dataset 01 |             |
      | Resource 03 | Group 01  | xls    | Katie  | Yes       | Dataset 02 |             |

  # TODO: Since there is content already created in dkan profile,
  #       care must be taken when trying to filter the datasets in the site's search engine
  #       as the pre-created datasets will also be included and should be accounted for
  #
  #       Currently it searches for 'Test' keyword to prevent any pre-made datasets from appearing

   @fixme
    # WIP: 'And I should see the list with "Desc" order by "Date changed"' is undefined.
  Scenario: View list of most recent published datasets (on homepage)
    Given I am on the homepage
    Then I should see "9" items in the "datasets" region
    And I should see the list with "Desc" order by "Date changed"

  Scenario: View list of published datasets
    Given I am on the homepage
    When I click "Datasets"
    Then I should see "9 datasets"
    And I should see "9" items in the "datasets" region

  @fixme
     # WIP: And I should see the list with "Asc" order by "Date changed" - undefined
  Scenario: Search datasets by "Date changed" with "Asc" order
    Given I am on "Datasets" page
    When I fill in "Test" for "Search" in the "datasets" region
    And I select "Date changed" from "Sort by"
    And I select "Asc" from "Order"
    And I press "Apply"
    Then I should see "5 datasets"
    And I should see "5" items in the "datasets" region
    And I should see the list with "Asc" order by "Date changed"

  @fixme
    # WIP: And I should see the list with "Desc" order by "Date changed" - undefined
  Scenario: Search datasets by "Date changed" with "Desc" order
    Given I am on "Datasets" page
    When I fill in "Test" for "Search" in the "datasets" region
    And I select "Date changed" from "Sort by"
    And I select "Desc" from "Order"
    And I press "Apply"
    Then I should see "5 datasets"
    And I should see "5" items in the "datasets" region
    And I should see the list with "Desc" order by "Date changed"

  @fixme
    # WIP: And I should see the list with "Asc" order by "title" - undefined
  Scenario: Search datasets by "title" with "Asc" order
    Given I am on "Datasets" page
    When I fill in "Test" for "Search" in the "datasets" region
    And I select "Title" from "Sort by"
    And I select "Asc" from "Order"
    And I press "Apply"
    Then I should see "5 datasets"
    And I should see "5" items in the "datasets" region
    And I should see the list with "Asc" order by "title"

  @fixme
    # WIP: And I should see the list with "Asc" order by "title" - undefined
    # WIP: Then I should see "3 datasets" - not found on page
  Scenario: Search datasets by "title" with "Desc" order
    Given I am on "Datasets" page
    When I fill in "Test" for "Search" in the "datasets" region
    And I select "Title" from "Sort by"
    And I select "Desc" from "Order"
    And I press "Apply"
    Then I should see "5 datasets"
    And I should see "5" items in the "datasets" region
    And I should see the list with "Desc" order by "title"

  @fixme
    # TODO : Reseting the search will make all the datasets appear in the results including pre-made
    #        datasets, should be fixed
  Scenario: Reset dataset search filters
    Given I am on "Datasets" page
    When I fill in "Dataset 01" for "Search" in the "datasets" region
    And I press "Apply"
    Then I should see "1 datasets"
    And I should see "1" items in the "datasets" region
    When I press "Reset"
    Then I should see "9 datasets"
    And I should see "9" items in the "datasets" region

  @fixme
    # Then I should see "Gov (1)" in the "filter by tag" region -- not found
  Scenario: View available tag filters for datasets
    Given I am on "Datasets" page
    Then I should see "Health (2)" in the "filter by tag" region
    Then I should see "Gov (1)" in the "filter by tag" region


  @fixme
    # TODO: their resource format is not being indexed properly by datasets,
    #       so newly created datasets will not be filterable by resource format
  Scenario: View available resource format filters for datasets
    Given I am on "Datasets" page
    Then I should see "CVS (2)" in the "filter by resource format" region
    Then I should see "XLS (1)" in the "filter by resource format" region

  @fixme
    # Then I should see "Katie (1)" in the "filter by author" region - not found in region
  Scenario: View available author filters for datasets
    Given I am on "Datasets" page
    Then I should see "Gabriel (2)" in the "filter by author" region
    Then I should see "Katie (1)" in the "filter by author" region


  Scenario: Filter dataset search results by tags
    Given I am on "Datasets" page
    Then I should see "9 datasets"
    And I should see "9" items in the "datasets" region
    When I click "Health" in the "filter by tag" region
    Then I should see "2 datasets"
    And I should see "2" items in the "datasets" region
  @fixme
    #  When I click "CVS" in the "filter by resource format" region - not found in region
  Scenario: Filter dataset search results by resource format
    Given I am on "Datasets" page
    Then I should see "9 datasets"
    And I should see "9" items in the "datasets" region
    When I click "CVS" in the "filter by resource format" region
    Then I should see "2 datasets"
    And I should see "2" items in the "datasets" region

  Scenario: Filter dataset search results by author
    Given I am on "Datasets" page
    Then I should see "9 datasets"
    And I should see "9" items in the "datasets" region
    When I click "Gabriel" in the "filter by author" region
    Then I should see "2 datasets"
    And I should see "2" items in the "datasets" region

  @fixme
    # Then I should see "Dataset 01" detail page - undefined
  Scenario: View published dataset
    Given I am on "Datasets" page
    When I click "Dataset 01"
    # I should see the license information
    Then I should see "Dataset 01" detail page

  @fixme
    # Then I should be redirected to "Google+" sharing page for "Dataset 01"
  Scenario: Share published dataset on Google +
    Given I am on "Dataset 01" page
    When I click "Google+" in the "social" region
    Then I should be redirected to "Google+" sharing page for "Dataset 01"

  @fixme
    #  Then I should be redirected to "Twitter" sharing page for "Dataset 01" - undefined
  Scenario: Share published dataset on Twitter
    Given I am on "Dataset 01" page
    When I click "Twitter" in the "social" region
    Then I should be redirected to "Twitter" sharing page for "Dataset 01"

  @fixme
    #  Then I should be redirected to "Facebook" sharing page for "Dataset 01" - undefined
  Scenario: Share published dataset on Facebook
    Given I am on "Dataset 01" page
    When I click "Facebook" in the "social" region
    Then I should be redirected to "Facebook" sharing page for "Dataset 01"

  @fixme
    # Then I should see the content in "JSON" format - undefined
  Scenario: View published dataset information as JSON
    Given I am on "Dataset 01" page
    When I click "JSON" in the "other access" region
    Then I should see the content in "JSON" format

  @fixme
    # Then I should see the content in "RDF" format - undefined
  Scenario: View published dataset information as RDF
    Given I am on "Dataset 01" page
    When I click "RDF" in the "other access" region
    Then I should see the content in "RDF" format

  @fixme
    #  When I press "Download" in the "Resource 01" row - undefined
    #  Then A file should be downloaded - undefined
  Scenario: Download file from published dataset
    Given I am on "Dataset 01" page
    When I press "Download" in the "Resource 01" row
    Then A file should be downloaded

  @fixme
    # TODO: Needs definition
  Scenario: View a list of suggested datasets when viewing a dataset
    Given I am on the homepage
