Feature: Testing module UI

  @api
  Scenario: Menu Item
    Given I am on the homepage
    Then I should see "Stories"

  @api
  Scenario: Stories Index
    Given I am on "/stories"
    Then I should see "No stories were found."
