# time:0m18.42s
##
# Refactor this to populate all the pages using the background
# and visiting them using the visit step.
# In that way we can remove the javascript part.
##
@api @javascript @disablecaptcha @smoketest
Feature: User command center links for site manager role.

  Background:
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |

  Scenario: Site manager role can view admin menu links under Add Content, DKAN, Visualizations, People and Site Configuration
    Given I am logged in as "John"
    #Add Content
    When I hover over the admin menu item "Add content"
    And I hover over the admin menu item "Dataset"
    And I hover over the admin menu item "Resource"
    And I hover over the admin menu item "Page"
    And I hover over the admin menu item "Data Story"
    And I hover over the admin menu item "Data Dashboard"
    And I hover over the admin menu item "Visualization"
    And I hover over the admin menu item "Harvest Source"
    And I hover over the admin menu item "Group"
    #DKAN
    When I hover over the admin menu item "DKAN"
    Then I should see the admin menu item "Data Previews"
    Then I should see the admin menu item "DKAN Dataset API"
    Then I should see the admin menu item "DKAN Dataset Forms"
    Then I should see the admin menu item "DKAN Harvest Dashboard"
    Then I should see the admin menu item "Recline Configuration"
    #Visualization
    When I hover over the admin menu item "Visualizations"
    Then I hover over the admin menu item "Charts"
    #People
    When I hover over the admin menu item "People"
    Then I hover over the admin menu item "Manage Users"
    #Site Configuration
    When I hover over the admin menu item "Site Configuration"
    Then I should see the admin menu item "Menus"
    Then I should see the admin menu item "Taxonomy"
    Then I should see the admin menu item "Format"
    Then I should see the admin menu item "Tags"
    Then I should see the admin menu item "Topics"
    Then I should see the admin menu item "Open Data Schema Mapper"
    Then I should see the admin menu item "Appearance"
