Feature: Homepage
  In order to know the website is running
  As a website user
  I need to be able to view the site title and login
  
  @customizable
  Scenario: Viewing the site title
    Given I am on the homepage
    Then I should see "Welcome to the DKAN Demo"

  @customizable
  Scenario: Viewing default content
    Given I am on the homepage
    Then I should see "Geospatial Data Explorer Examples"

  @customizable
  Scenario: Viewing top menu
    Given I am on the homepage
    Then I should see "Datasets"
    Then I should see "Groups"
    Then I should see "About"
    Then I should see "Topics"

  @customizable
  Scenario: Viewing footer
    Given I am on the homepage
    Then I should see "Powered by DKAN, a project of Granicus"

  @customizable
  Scenario: Viewing tags
    Given I am on the homepage
    When I click "politics"
    Then I should see "Afghanistan Election Districts"

  @customizable
  Scenario: Viewing topics
    Given I am on the homepage
    When I click "Public Safety"
    Then I should see "Wisconsin Polling Places"

  @api @javascript @customizable
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
