# time:0m39s
##
# Refactor this to populate all the pages using the background
# and visiting them using the visit step.
# In that way we can remove the javascript part.
##
@api @javascript @disablecaptcha
Feature: User command center links for site manager role.

  Background:
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |

  Scenario: Site manager role can view admin menu links under Add Content, DKAN, Visualizations, People and Site Configuration
    Given I am logged in as "John"
    #Add Content
    When I hover over the admin menu item "Add content"
    Then I hover over the admin menu item "Page"
    And I hover over the admin menu item "Data Story"
    And I hover over the admin menu item "Data Dashboard"
    #DKAN
    When I hover over the admin menu item "DKAN"
    Then I hover over the admin menu item "DKAN Dataset Forms"
    And I hover over the admin menu item "Data Dashboards"
    And I hover over the admin menu item "Recline Configuration"
    #Visualization
    When I hover over the admin menu item "Visualizations"
    Then I hover over the admin menu item "Charts"
    #People
    When I hover over the admin menu item "People"
    Then I hover over the admin menu item "Manage Users"
    #Site Configuration
    When I hover over the admin menu item "Site Configuration"
    Then I hover over the admin menu item "Open Data Schema Mapper"
    And I hover over the admin menu item "Taxonomy"
    And I hover over the admin menu item "Tags"
    Then I hover over the admin menu item "Add term"


  Scenario: Site manager role should not see Customize Display link
    Given I am logged in as "John"
    When I hover over the admin menu item "Add content"
    And I click "Page"
    Then I should see "Create Page"
    When I fill in "title" with "My new page"
    And I select the radio button "Boxton" with the id "edit-layout-radix-boxton"
    And I press "Save"
    And I wait for "View"
    Then I should not see "Customize Display"

  @customizable
  Scenario: Site manager role can configure custom fonts
    Given I am logged in as "John"
    When I hover over the admin menu item "Site Configuration"
    And I click "Fonts"
    Then I should see "No fonts enabled yet, please enable some fonts first."
