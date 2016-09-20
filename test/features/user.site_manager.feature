@api @javascript
Feature: User command center links for site manager role.

  Background:
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |


  Scenario: Site manager role can view admin menu links under Add Content
    Given I am logged in as "John"
    When I click "Add content" in the "admin menu" region
    Then I should see "Add content"
    When I hover over the admin menu item "Add content"
    And I click "Dataset"
    Then I should see "Create dataset"
    When I hover over the admin menu item "Add content"
    And I click "Resource"
    Then I should see "Add resource"
    When I hover over the admin menu item "Add content"
    And I click "Group"
    Then I should see "Create Group"
    When I hover over the admin menu item "Add content"
    And I click "Page"
    Then I should see "Create Page"
    When I hover over the admin menu item "Add content"
    And I click "Data Story"
    Then I should see "Create Data Story"
    When I hover over the admin menu item "Add content"
    And I click "Data Dashboard"
    Then I should see "Create Data Dashboard"
    When I hover over the admin menu item "Add content"
    Then I hover over the admin menu item "Visualization"
    And I click "Chart"
    Then I should see "Add Chart"

  Scenario: Site manager role can view admin menu link Content
    Given I am logged in as "John"
    When I click "Content" in the "admin menu" region
    Then I should see "Update options"

  Scenario: Site manager role can view admin menu links under Visualizations
    Given I am logged in as "John"
    When I click "Visualizations" in the "admin menu" region
    Then I should see "Visualization"
    When I hover over the admin menu item "Visualizations"
    And I click "Charts"
    Then I should see "Chart"

  Scenario: Site manager role can view admin menu links under People
    Given I am logged in as "John"
    When I click "People" in the "admin menu" region
    Then I should see "Show only users where"
    When I hover over the admin menu item "People"
    And I click "Create user"
    Then I should see "This web page allows administrators to register new users."
    When I hover over the admin menu item "People"
    And I click "Manage Users"
    Then I should see "Show only users where"

  Scenario: Site manager role can view admin menu links under Site Configuration
    Given I am logged in as "John"
    When I hover over the admin menu item "Site Configuration"
    And I click "Fonts"
    Then I should see "No fonts enabled yet, please enable some fonts first."
    When I hover over the admin menu item "Site Configuration"
    And I click "Taxonomy"
    Then I should see "Taxonomy"
    When I hover over the admin menu item "Site Configuration"
    Then I hover over the admin menu item "Taxonomy"
    And I click "Format"
    Then I should see "Format"
    When I hover over the admin menu item "Site Configuration"
    Then I hover over the admin menu item "Taxonomy"
    And I click "Tags"
    Then I should see "Tags"
    When I hover over the admin menu item "Site Configuration"
    Then I hover over the admin menu item "Taxonomy"
    And I click "Topics"
    Then I should see "Topics"
