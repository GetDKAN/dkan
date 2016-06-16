Feature: Homepage
  In order to know the website is running
  As a website user
  I need to be able to view the site title and login

  Scenario: Viewing the site title
    Given I am on the homepage
    Then I should see "DKAN"

  Scenario: Viewing default content
    Given I am on the homepage
    Then I should see "Welcome to DKAN"
    Then I should see "Latest Data Stories"
    Then I should see "State Economic Council"
    Then I should see "Dynamic Transportation Visualizations"

  Scenario: Viewing top menu
    Given I am on the homepage
    Then I should see "Datasets"
    Then I should see "Groups"
    Then I should see "About"
    Then I should see "Topics"

  Scenario: Viewing footer
    Given I am on the homepage
    Then I should see "Powered by DKAN, a project of NuCivic"

  # Scenario: Viewing tags
    # Given I am on the homepage
    # When I click "politics"
    # Then I should see "Afghanistan Election Districts"

  Scenario: Viewing topics
    Given I am on the homepage
    Then I should see "Transportation"
    When I click "Public Safety"
    Then I should see "Crime in America's Top 10 Most Populous Cities"

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
