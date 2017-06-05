# time:0m25.25s
@api @disablecaptcha
Feature: User command center links for editor role.

  Background:
    Given users:
      | name    | mail                | roles                |
      | Jaz     | jaz@example.com     | editor               |

  @javascript
  Scenario: Editor role can view admin menu links under Add Content
    Given I am logged in as "Jaz"
    When I click "Add content" in the "admin menu" region
    Then I should see "Add content"
    When I hover over the admin menu item "Add content"
    And I click "Dataset"
    Then I should see "Create dataset"
    When I hover over the admin menu item "Add content"
    And I click "Resource"
    Then I should see "Add resource"
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
  
  Scenario: Editor role can view admin menu link Content
    Given I am logged in as "Jaz"
    When I click "Content" in the "admin menu" region
    Then I should see "Show only items where"

  @javascript
  Scenario: Editor role can view admin menu links under Visualizations
    Given I am logged in as "Jaz"
    When I click "Visualizations" in the "admin menu" region
    Then I should see "Visualization"
    When I hover over the admin menu item "Visualizations"
    And I click "Charts"
    Then I should see "Chart"
  
  @javascript
  Scenario: Editor role can view admin menu links under Site Configuration
    Given I am logged in as "Jaz"
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
