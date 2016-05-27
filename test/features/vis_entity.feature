@javascript
Feature: Visualization Entities

  Background:
    Given pages:
      | name        | url                                                  |
      | Add Chart   | /admin/structure/entity-type/visualization/ve_chart  |
      | Chart       | /visualization/ve_chart/1                            |
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
      | Gabriel | gabriel@example.com | content creator      |
      | Jaz     | jaz@example.com     | editor               |
    Given "ve_chart" content:
      | title             | author      | Existing resource      | description |
      | Chart 01          | John        | Table of Gold Prices   | testing     |

  @api
  Scenario: Create chart entity
    Given I am logged in as "John"
    And I am on the "Add Chart" page
    And I click "Add Chart"
    When I fill in "Title" with "Chart 01"
    And I attach the drupal file "gold_prices.csv" to "files[field_file_und_0]"
    And I press "Upload"
    #And I wait for "Remove"
    And I wait and press "Next"
    Then I should see "Define Variables"
    And I fill in the chosen field "control_chart_series_chosen" with "price"
    And I press "Next"
    And I select "lineChart" from "chart-selector"
    And I press "Next"
    And I press "Finish"
    Then I should see "Chart 01"

  #  @api
  # Scenario: View chart entity
  #   Given I am logged in as "Gabriel"
  #   When I am on the "Chart" page
  #   Then I should see "Chart 01"
