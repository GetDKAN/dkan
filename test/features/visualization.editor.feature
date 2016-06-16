@api @javascript
Feature: Visualization entity editor tests

  Scenario: Visualization links appear in command center menu
      Given I am logged in as a user with the "editor" role
      And I am on the homepage
      When I click "Visualizations"
      I should see "Chart"
      When I hover over the admin menu item "Visualizations"
      Then I should see "Charts"
