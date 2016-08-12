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
    And I check the box "Require datasets to have a group."
    And I press "Save configuration"
    When I visit "node/add/dataset"
    Then I should see all POD required fields
    And I should see "A dataset must be added to a specific group, but you don’t belong to any groups."
    And I should see "Ask a supervisor or site administrator to add you to a group or promote you to site manager to add datasets."
    When I select "- None -" from "edit-field-public-access-level-und"
    And I press "Next: Add data"
    Then I should see an error for POD required fields
    And I should see "Error: You tried to submit a dataset with no groups assigned."
    # Cleanup configuration.
    Given I visit "admin/dkan/dataset_forms"
    Then I uncheck the box "Validate dataset form according to Project Open Data (more strict than default DKAN validation)."
    Then I uncheck the box "Require datasets to have a group."
    And I press "Save configuration"

  @api @add_ODFE @remove_ODFE
  Scenario: See all POD required fields marked as required except for Groups
    Given I am logged in as a user with the "administrator" role
    When I visit "admin/dkan/dataset_forms"
    And I check the box "Validate dataset form according to Project Open Data (more strict than default DKAN validation)."
    And I press "Save configuration"
    When I visit "node/add/dataset"
    Then I should see all POD required fields
    And I should not see "A dataset must be added to a specific group, but you don’t belong to any groups."
    And I should not see "Ask a supervisor or site administrator to add you to a group or promote you to site manager to add datasets."
    When I select "- None -" from "edit-field-public-access-level-und"
    And I press "Next: Add data"
    Then I should see an error for POD required fields
    And I should not see "Error: You tried to submit a dataset with no groups assigned."
    # Cleanup configuration.
    Given I visit "admin/dkan/dataset_forms"
    Then I uncheck the box "Validate dataset form according to Project Open Data (more strict than default DKAN validation)."
    And I press "Save configuration"
