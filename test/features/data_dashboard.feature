Feature: Data Dashboard

  Background:
    Given data_dashboards:
      | title            |
      | Dashboard 01     |
      | Dashboard 02     |
      | Afghan Dashboard |
    And I am logged in as a user with the "administrator" role

  @api
  Scenario: See the list of data dashboards
    When I am on "admin/dkan/data-dashboards"
    Then I should see the text "Dashboard 01" in the "dashboards" region
      And I should see the text "Dashboard 02" in the "dashboards" region

  @api
  Scenario: Creation of data dashboard
    Given I am on "/admin/dkan/data-dashboards"
      And I click "Create Dashboard"
      Then I should see "Create Data Dashboard"
    When I fill in "title" with "My new dashboard"
      And I select the radio button "Radix Boxton" with the id "edit-layout-radix-boxton"
      And I press "Save"
    Then I should see "Your Data Dashboard 'My new dashboard' has been created"
      And I should see "Start adding content by clicking on the + sign on each panel"

  @api
  Scenario: Edition of data dashboard
    Given I am on "/dashboard-01"
    When I click "Edit"
      And I fill in "title" with "Edited Dashboard"
      And I press "Save"
    Then I should see "Data Dashboard Edited Dashboard has been updated"
    When I am on "admin/dkan/data-dashboards"
    Then I should see the text "Edited Dashboard" in the "dashboards" region
      And I should not see the text "Dashboard 01" in the "dashboards" region

  @api
  Scenario: Deletion of data dashboard
    Given I am on "/dashboard-01"
    When I click "Edit"
      And I press "Delete"
    Then I should see "Are you sure you want to delete Dashboard 01?"
    When I press "Delete"
    Then I should see "Data Dashboard Dashboard 01 has been deleted"
    When I am on "admin/dkan/data-dashboards"
    Then I should not see the text "Dashboard 01" in the "dashboards" region

  @api @javascript
  Scenario: Create a dashboard with a set of filtered views
    Given I am on "/afghan-dashboard"
      And I click "Customize this page"
      And I click "Add new pane"
      And I click "View panes" in the "modal" region
      And I add a Dataset Filtered List
      And I wait for "2" seconds
      And I check "override-title-checkbox"
      And I fill in "override-title-textfield" with "Afghan Datasets"
      And I select "country-afghanistan" from "edit-exposed-field-tags-tid"
    When I press "Finish"
      And I wait for "10" seconds
      And I press "Save as custom"
    Then I should see "Afghan Datasets"
      And I should see "Afghanistan Election Districts"
