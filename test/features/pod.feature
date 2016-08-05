Feature: Project Open Data + Open Data Federal Extras
  In order to meet POD
  As a dataset creator
  I want to create datasets with POD fields and publish them with data.json

  @api
  Scenario: Data.json should be valid
    Given I am on the homepage
    Then I should see a valid data.json

  @api @add_ODFE @remove_ODFE
  Scenario: Data.json should remain valid when ODFE is enabled
    Given I am on the homepage
    Then I should see a valid data.json

  @api @add_ODFE @remove_ODFE
  Scenario: See Federal Extras fields on the Dataset form
    Given I am logged in as a user with the "editor" role
    When I visit "node/add/dataset"
    Then I should see all of the Federal Extras fields

  @api @add_ODFE @remove_ODFE
  Scenario: See all POD required fields marked as required
    Given I am logged in as a user with the "administrator" role
    When I visit "admin/dkan/dataset_forms"
    And I check the box "Validate dataset form according to Project Open Data (more strict than default DKAN validation)."
    And I press "Save configuration"
    When I visit "node/add/dataset"
    Then I should see all POD required fields
    When I press "Next: Add data"
    Then I should see an error for POD required fields
    Given I visit "admin/dkan/dataset_forms"
    Then I uncheck the box "Validate dataset form according to Project Open Data (more strict than default DKAN validation)."
    And I press "Save configuration"
