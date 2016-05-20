Feature: Homepage
  In order to know the website is running
  As a website user
  I need to be able to view the site title and login

  Scenario: Viewing the site title
    Given I am on the homepage
    Then I should see "Welcome to the DKAN Demo"

  Scenario: Viewing default groups
    Given I am on the homepage
    Then I should see "Committee on International Affairs"
    And I should see "State Economic Council"
    And I should see "Wisconsin Parks and Rec Commission"
    And I should see "Advisory Council for Infectious Disease"

  Scenario: Viewing top menu
    Given I am on the homepage
    Then I should see "Datasets"
    Then I should see "Groups"
    Then I should see "About"
    Then I should see "Topics"

  Scenario: Viewing footer
    Given I am on the homepage
    Then I should see "Powered by DKAN, a project of NuCivic"

  Scenario: Viewing tags
    Given I am on the homepage
    When I click "demographics"
    Then I should see "London Deprivation Index"

  @fixme
  Scenario: Viewing topics
    Given I am on the homepage
    When I click "Public Safety"
    Then I should see "Wisconsin Polling Places"

  @api @javascript
  Scenario: See "Add Dataset"
    Given I am logged in as a user with the "content creator" role
    And I am on the homepage
    Then I hover over the admin menu item "Content"
    Then I hover over the admin menu item "Add content"
    Then I should see the admin menu item "Dataset"

  @api @javascript
  Scenario: See "Dataset Form"
    Given I am logged in as a user with the "content creator" role
    And I am on the homepage
    Then I hover over the admin menu item "Content"
    Then I hover over the admin menu item "Add content"
    Then I click "Dataset"
    Then I should see "Create Dataset"
