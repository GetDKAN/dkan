Feature: Test

  @javascript
  Scenario: test-submit
    Given I am on the homepage
    And I wait and press "edit-submit--2"

  @api
  Scenario: test-groups
    Given users:
      | name     | mail            | status |
      | teo      | teo@rocks.com   | 1      |
    Given groups:
      | title    | author | published |
      | Group 01 | Admin  | Yes       |
