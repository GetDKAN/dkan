# time:0m23.59s
@disablecaptcha
Feature: Data Dashboard

  Background:
    Given data dashboards:
      | title        |
      | Dashboard 01 |
      | Dashboard 02 |
      | Afghan Dashboard |
    And I am logged in as a user with the "site manager" role

  @api
  Scenario: See the list of data dashboards
    When I am on "admin/dkan/data-dashboards"
    Then I should see the text "Dashboard 01" in the "dashboards" region
    And I should see the text "Dashboard 02" in the "dashboards" region

  @api
  Scenario: Creation of data dashboard
    And I am on "admin/dkan/data-dashboards"
    And I click "Create Dashboard"
    Then I should see "Create Data Dashboard"
    When I fill in "title" with "My new dashboard"
    And I select the radio button "Boxton" with the id "edit-layout-radix-boxton"
    And I press "Save"
    Then I should see "Your Data Dashboard 'My new dashboard' has been created"
    And I should see "Start adding content by clicking on the + sign on each panel"

  @api
  Scenario: Edition of data dashboard
    And I am on "/dashboard-01"
    When I click "Edit"
    And I fill in "title" with "Edited Dashboard"
    And I press "Save"
    Then I should see "Data Dashboard Edited Dashboard has been updated"
    When I am on "admin/dkan/data-dashboards"
    Then I should see the text "Edited Dashboard" in the "dashboards" region
    And I should not see the text "Dashboard 01" in the "dashboards" region

  @api
  Scenario: Deletion of data dashboard
    And I am on "/dashboard-01"
    When I click "Edit"
    And I press "Delete"
    Then I should see "Are you sure you want to delete Dashboard 01?"
    When I press "Delete"
    Then I should see "Data Dashboard Dashboard 01 has been deleted"
    When I am on "admin/dkan/data-dashboards"
    Then I should not see the text "Dashboard 01" in the "dashboards" region

  @api @javascript
  Scenario: Dashboards have Panels IPE available
    Given I am on "/afghan-dashboard"
    And I wait for "Customize this page"
    And I click "Customize this page"
    And I wait for "Add new pane"
    Then I should see "Add new pane"

