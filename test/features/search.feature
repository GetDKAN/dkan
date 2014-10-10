# features/search.feature
Feature: Search
  In order to see a dataset
  As a website user
  I need to be able to search for a word

  Scenario: Searching for a dataset
    Given I am on "/about"
    When I fill in "search" with "Madison" in the "header" region
      And I press "edit-submit"
    Then I should see "Wisconsin Polling Places"

  Scenario: See number of datasets on search page
    Given I am on "/dataset"
    Then I should see "4 datasets"
      And I should see "US National Foreclosure Statistics January 2012"

  Scenario: Filter by facet tag
    Given I am on "/dataset"
    When I click "politics"
    Then I should not see "Wisconsin Polling Places"

  Scenario: Filter by facet group
    Given I am on "/dataset"
    When I click "Data Explorer Examples"
    Then I should see "US National Foreclosure Statistics January 2012"
    But I should not see "Wisconsin Polling Places"
