# time:1m15.22s
Feature: Project Open Data + Open Data Federal Extras
  In order to meet POD
  As a dataset creator
  I want to create datasets with POD fields and publish them with data.json

  @pod_json_valid @api @noworkflow
  Scenario: Data.json should be valid
    Given I am on the homepage
    Then I "should" see a valid data.json

  @pod_json_odfe @api @noworkflow @add_ODFE @remove_ODFE
  Scenario: Data.json validation should fail if ODFE enabled
    Given I am on the homepage
    Then I "should not" see a valid data.json

  @api @noworkflow @add_ODFE @remove_ODFE
  Scenario: See Federal Extras fields on the Dataset form
    Given I am logged in as a user with the "editor" role
    When I visit "node/add/dataset"
    Then I should see all of the Federal Extras fields

  @api @noworkflow @add_ODFE @remove_ODFE
  Scenario: See all POD required fields marked as required
    # Enable POD validation + Groups validation
    Given I "enable" the "Strict POD validation" on DKAN Dataset Forms
    And I "enable" the "Groups validation" on DKAN Dataset Forms
    # Log in as a non-admin user.
    When I am logged in as a user with the "content creator" role
    And I visit "node/add/dataset"
    Then I should see all POD required fields
    Then I should see "A dataset must be added to a specific group, but you don’t belong to any groups."
    And I should see "Ask a supervisor or site administrator to add you to a group or promote you to site manager to add datasets."
    When I select "- None -" from "edit-field-public-access-level-und"
    And I press "Next: Add data"
    Then I should see an error for POD required fields
    And I should see "Error: You tried to submit a dataset with no groups assigned."
    # Cleanup configuration.
    Then I "disable" the "Strict POD validation" on DKAN Dataset Forms
    Then I "disable" the "Groups validation" on DKAN Dataset Forms

  @api @noworkflow @add_ODFE @remove_ODFE
  Scenario: See all POD required fields marked as required except for Groups
    # Enable POD validation only.
    Given I "enable" the "Strict POD validation" on DKAN Dataset Forms
    # Log in as a non-admin user.
    When I am logged in as a user with the "content creator" role
    And I visit "node/add/dataset"
    Then I should see all POD required fields
    And I should not see "A dataset must be added to a specific group, but you don’t belong to any groups."
    And I should not see "Ask a supervisor or site administrator to add you to a group or promote you to site manager to add datasets."
    When I select "- None -" from "edit-field-public-access-level-und"
    And I press "Next: Add data"
    Then I should see an error for POD required fields
    And I should not see "Error: You tried to submit a dataset with no groups assigned."
    # Cleanup configuration.
    Then I "disable" the "Strict POD validation" on DKAN Dataset Forms

  @api @noworkflow @add_ODFE @remove_ODFE
  Scenario: See all license values if POD validation is not enabled
    Given I am logged in as a user with the "content creator" role
    When I visit "node/add/dataset"
    Then I should see "all" license values

  @api @noworkflow @add_ODFE @remove_ODFE
  Scenario: See only POD valid licenses if POD validation is enabled
    # Enable POD validation only.
    Given I "enable" the "Strict POD validation" on DKAN Dataset Forms
    # Log in as a non-admin user.
    When I am logged in as a user with the "content creator" role
    And I visit "node/add/dataset"
    Then I should see "POD valid" license values
    #Cleanup configuration
    Given I "disable" the "Strict POD validation" on DKAN Dataset Forms

  @api @here
  Scenario: Site Manager role should have access to the validation page
    Given pages:
      | name           | url                                     |
      | POD Validation | /admin/config/services/odsm/validate/pod |
    And I am logged in as a user with the "site manager" role
    When I am on the "POD Validation" page
    Then I should not see "Access Denied"
