Feature: Project Open Data Feature
  In order to meet POD requirements
  As a dataset creator
  I want to create datasets with POD fields and publish them with data.json

  @api
  Scenario: Know that data.json file is valid
    Given I am an anonymous user
    Then I should find a data.json file that passes POD 1.1 schema validator

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



