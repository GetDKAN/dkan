Feature: Open Data Federal Extras
  In order to meet Open Data Federal Extras
  As a dataset creator
  I want to create datasets with ODFE fields and publish them with data.json

  @api
  Scenario: Data.json should remain valid when ODFE is enabled
    Given I enable the module "open_data_federal_extras"
    Then I should see a valid data.json

  @api
  Scenario: See Federal Extras fields on the Dataset form
    Given I am logged in as a user with the "editor" role
    When I visit "node/add/dataset"
    Then I should see all of the Federal Extras fields

  @api
  Scenario: See all POD required fields marked as required
    Given I am logged in as a user with the "administrator" role
    When I visit "node/add/dataset"
    Then I should see all POD required fields
    When I press "Next: Add data"
    Then I should see an error for POD required fields

  @api
  Scenario: Disable the default content module
    Given I am logged in as a user with the "administrator" role
    Then I disable the module "open_data_federal_extras"