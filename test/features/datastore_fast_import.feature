@api @enableFastImport
Feature: DKAN Datastore Fast Import
  Background:
  Given pages:
    | name                | url                    |
    | Datastore Settings  | /admin/dkan/datastore  |

  @datastore @javascript
  Scenario: As user I want to import files using batch imports
    Given I am logged in as a user with the "site manager" role
      And I am on "Datastore Settings" page
      And I select the radio button "Use fast import for files with a weight over:"
      And I press "Save configuration"
    Given I am on the resource "District Names"
     When I click "Manage Datastore"
     Then I wait for "DKAN Datastore File: Status"
     When I press "Import"
      Then I should see "Importing"
      And I wait for "399 imported items total."

  @datastore
  Scenario: As user I want to import files using fast imports
    Given I am logged in as a user with the "site manager" role
      And I am on the resource "District Names"
     When I click "Manage Datastore"
     Then I wait for "DKAN Datastore File: Status"
      And I check the box "Use Fast Import"
     When I press "Import"
      Then I should not see "Importing"
      And I wait for "399 imported items total."

  @datastore
  Scenario: As user I want to set fast imports as default for all the resource with a size over a threshold
    Given I am logged in as a user with the "site manager" role
      And I am on "Datastore Settings" page
      And I select the radio button "Use fast import for files with a weight over:"
      And I fill in "dkan_datastore_fast_import_selection_threshold" with "1KB"
      And I press "Save configuration"
    Given I am on the resource "District Names"
    When I click "Manage Datastore"
      Then I wait for "DKAN Datastore File: Status"
      And the "Use Fast Import" checkbox should be checked
    When I press "Import"
      Then I should not see "Importing"
      And I wait for "399 imported items total."

  @datastore @javascript
  Scenario: As user I want to enqueue all the imports of resource with a size over a threshold
    Given I am logged in as a user with the "site manager" role
      And I am on "Datastore Settings" page
      And I select the radio button "Use fast import as default (LOAD DATA)"
      And I fill in "queue_filesize_threshold" with "1KB"
      And I press "Save configuration"
    Given I am on the resource "District Names"
     When I click "Manage Datastore"
     Then I wait for "DKAN Datastore File: Status"
      And the "Use Fast Import" checkbox should be checked
     When I press "Import"
     Then I should not see "Importing"
      And I wait for "File was succesfully enqueued to be imported and will be available in the datastore in a few minutes"

  @datastore
  Scenario: As user I want to import resources using "LOAD DATA INFILE"
    Given I am logged in as a user with the "site manager" role
      And I am on "Datastore Settings" page
      And I select the radio button "Use fast import as default (LOAD DATA)"
      And I select the radio button "LOAD DATA INFILE"
      And I press "Save configuration"
    Given I am on the resource "District Names"
     When I click "Manage Datastore"
     Then I wait for "DKAN Datastore File: Status"
      And the "Use Fast Import" checkbox should be checked
     When I press "Import"
     Then I should not see "Importing"
      And I wait for "399 imported items total."

  @datastore
  Scenario: As user I want to import resources using "LOAD DATA LOCAL INFILE"
    Given I am logged in as a user with the "site manager" role
      And I am on "Datastore Settings" page
      And I select the radio button "Use fast import as default (LOAD DATA)"
      And I select the radio button "LOAD DATA LOCAL INFILE"
      And I press "Save configuration"
    Given I am on the resource "District Names"
     When I click "Manage Datastore"
     Then I wait for "DKAN Datastore File: Status"
      And the "Use Fast Import" checkbox should be checked
     When I press "Import"
     Then I should not see "Importing"
      And I wait for "399 imported items total."
