# time:0m28.91s
@api @disablecaptcha @datastore
Feature: Datastore
  In order to know the datastore is working
  As a website user
  I need to be able to add and remove items from the datastore

  Background:
    Given users:
      | name    | mail                | roles           |
      | Gabriel | gabriel@example.com | content creator |
      | Katie   | katie@example.com   | site manager    |
      | Daniel  | daniel@example.com  | content creator |
      | Jaz     | editor@example.com  | editor          |
    Given groups:
      | title    | author  | published |
      | Group 01 | Katie   | Yes       |
      | Group 02 | Katie   | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | member               | Active            |
      | Jaz     | Group 01 | administrator member | Active            |
      | Daniel  | Group 02 | member               | Active            |
    Given datasets:
      | title      | publisher | author  | published | description |
      | Dataset 01 | Group 01  | Gabriel | Yes       | Test        |
      | Dataset 02 | Group 02  | Daniel  | Yes       | Test        |
    And "Format" terms:
      | name    |
      | cvs     |
    And resources:
      | title       | publisher | format | dataset    | author  | published | description               |
      | Resource 01 | Group 01  | cvs    | Dataset 01 | Gabriel | Yes       | The resource description. |
      | Resource 02 | Group 02  | cvs    | Dataset 02 | Daniel  | Yes       | The resource description. |

  # Don't remove! This is for avoiding issues when other scenarios are disabled (because of @noworkflow tag).
  Scenario: Dumb test
        Given I am on the homepage

  @api @javascript @noworkflow
  Scenario: Adding and Removing items from the datastore
    Given I am logged in as a user with the "site manager" role
    And I am on "dataset/dataset-01"
    And I click "Resource 01"
    And I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/district_centerpoints_small.csv"
    And I press "Save"
    And I am on "dataset/dataset-01"
    And I click "Resource 01"
    Then I wait for "The resource description."
    When I click "Manage Datastore"
    Then I wait for "DKAN Datastore Link Importer: Status"
    When I press "Import"
    And I wait for "2 imported items total."
    When I click "Data API" in the "primary tabs" region
    Then I wait for "Example Query"
    When I click "Manage Datastore"
    Then I wait for "DKAN Datastore Link Importer: Status"
    When I click "Delete items"
    Then I wait for "DKAN Datastore Link Importer: Status"
    When I press "Delete"
    And I wait for "2 items have been deleted."
    When I click "Manage Datastore"
    And I wait for "Drop Datastore"
    And I click "Drop Datastore"
    And I wait and press "Drop"
    Then I wait for "Datastore dropped!"
    And I should see "Your file for this resource is not added to the datastore."

  @api
  Scenario: Anonymous users should not be able to manage datastores
    Given I am an anonymous user
    Then I "should not" be able to manage the "Resource 01" datastore

  @api
  Scenario: Content Creators should be able to manage only datastores
    associated with the resources they own
    Given I am logged in as "Gabriel"
    Then I "should" be able to manage the "Resource 01" datastore
    Given I am logged in as "Daniel"
    Then I "should not" be able to manage the "Resource 01" datastore

  @api
  Scenario: Editors should be able to manage only datastores associated with
    resources created by members of their groups
    Given I am logged in as "Jaz"
    Then I "should" be able to manage the "Resource 01" datastore
    And I "should not" be able to manage the "Resource 02" datastore

  @api
  Scenario: Site Managers should be able to manage any datastore
    Given I am logged in as "Katie"
    Then I "should" be able to manage the "Resource 01" datastore
    And I "should" be able to manage the "Resource 02" datastore
