# time:0m39s
##
# Refactor this to populate all the pages using the background
# and visiting them using the visit step.
# In that way we can remove the javascript part.
##
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
    When I hover over the admin menu item "Add content"
    And I click "Harvest Source"
    Then I should see "Create Harvest Source"

  Scenario: Site manager role can view admin menu links under DKAN
    Given I am logged in as "John"
    When I click "DKAN" in the "admin menu" region
    Then I should see "DKAN"
    When I hover over the admin menu item "DKAN"
    And I click "Data Dashboards" in the "admin menu" region
    Then I should see "Data Dashboards"
    When I hover over the admin menu item "DKAN"
    And I click "DKAN Dataset Forms" in the "admin menu" region
    Then I should see "DKAN Dataset Forms"
    When I hover over the admin menu item "DKAN"
    And I click "Data Previews" in the "admin menu" region
    Then I should see "DKAN Dataset Previews"
    When I hover over the admin menu item "DKAN"
    And I click "DKAN Harvest Dashboard" in the "admin menu" region
    Then I should see "DKAN Harvest Dashboard"
    When I hover over the admin menu item "DKAN"
    And I click "Featured Groups Sort Order" in the "admin menu" region
    Then I should see "Featured Groups Sort Order"
    When I hover over the admin menu item "DKAN"
    And I click "Recline Configuration" in the "admin menu" region
    Then I should see "Recline Configuration"

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
    And I click "Open Data Schema Mapper"
    Then I should see "Open Data Schema Mapper"
    When I hover over the admin menu item "Site Configuration"
    And I hover over the admin menu item "Open Data Schema Mapper"
    And I click "DCAT validation"
    Then I should see "DCAT validation"
    When I hover over the admin menu item "Site Configuration"
    And I click "Colorizer"
    Then I should see "Color Scheme Settings"
    When I hover over the admin menu item "Site Configuration"
    And I click "Theme Settings"
    Then I should see "Appearance"
    When I hover over the admin menu item "Site Configuration"
    And I click "Menus"
    Then I should see "Main menu"
    When I hover over the admin menu item "Site Configuration"
    And I click "Taxonomy"
    Then I should see "Taxonomy"
    When I hover over the admin menu item "Site Configuration"
    Then I hover over the admin menu item "Taxonomy"
    And I click "Format"
    Then I should see "Format"
    Then I wait for "Site Configuration"
    When I hover over the admin menu item "Site Configuration"
    Then I hover over the admin menu item "Taxonomy"
    And I click "Tags"
    Then I should see "Tags"
    Then I wait for "Site Configuration"
    When I hover over the admin menu item "Site Configuration"
    Then I hover over the admin menu item "Taxonomy"
    And I click "Topics" in the "admin menu" region
    Then I should see "Topics"

  @customizable
  Scenario: Site manager role can configure custom fonts
    Given I am logged in as "John"
    When I hover over the admin menu item "Site Configuration"
    And I click "Fonts"
    Then I should see "No fonts enabled yet, please enable some fonts first."
