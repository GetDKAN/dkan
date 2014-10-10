Feature: Homepage
  In order to know the website is running
  As a website user
  I need to be able to view the site title and login

  Scenario: Viewing the site title
    Given I am on the homepage
    Then I should see "Welcome to the DKAN Demo"

  Scenario: Viewing default content
    Given I am on the homepage
    Then I should see "Geospatial Data Explorer Examples"

  Scenario: Viewing top menu
    Given I am on the homepage
    Then I should see "Datasets"
    Then I should see "Groups"
    Then I should see "About"

  Scenario: Viewing footer
    Given I am on the homepage
    Then I should see "Powered by DKAN, a project of NuCivic"

  Scenario: Viewing tags
    Given I am on the homepage
    When I click "politics"
    Then I should see "Afghanistan Election Districts"

  @api @javascript
  Scenario: See "Add Dataset"
    Given I am logged in as a user with the "authenticated user" role
    And I am on the homepage
    Then I should see "Add Dataset"

  @api @javascript
  Scenario: See "Dataset Form"
    Given I am logged in as a user with the "authenticated user" role
    And I am on the homepage
    And I click "Add Dataset"
    Then I should see "Create Dataset"

  Scenario: Test header region
    Given I am on the homepage
    Then I should see "Welcome"
    And I should see "Login" in the "header" region
