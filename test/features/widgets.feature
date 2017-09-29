# time:0m40.08s
@api @javascript @disablecaptcha
Feature: Widgets

  Scenario: Make sure IPE comes up and shows all options

    Given I am logged in as a user with the "site manager" role
    And I am on the homepage
    And I wait for "Customize this page"
    When I click "Customize this page"
    And I wait for "Add new pane"
    And I click "Add new pane"
    And I wait for "Add content to"
    Then I should see "Link"
    And I should see "File"
    And I should see "Image"
    And I should see "Text"
    And I should see "Map"
    And I should see "Table"
    And I should see "Video"
    And I should see "Slideshow"
    And I should see "Submenu"
    And I should see "Content List"
    And I should see "Existing content"
    And I should see "Visualization"
    Then I press "Close Window"
    Then I press "Cancel"
