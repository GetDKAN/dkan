# time:0m23.59s

@api
Feature: Dkan Dash
  Background:
    Given react dashboards:
      | title        | settings |
      | Dashboard 01 | {"title":"Demo Dashboard","regions":[{"id":"first-region","children":[{"type":"Markup","data":"<h5>Take a look at this</h5>"}]}]} |
    And I am logged in as a user with the "site manager" role

  @javascript
  Scenario: Check simple dashboard
    When I am on "/dashboard/dashboard-01"
      And I wait for "3" seconds
      Then I should see "Take a look at this"