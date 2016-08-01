Feature: Datastore
  In order to know the datastore is working
  As a website user
  I need to be able to add and remove items from the datastore

  Background:
    Given users:
      | name    | mail                | roles           |
      | Gabriel | gabriel@example.com | content creator |
    Given groups:
      | title    | author  | published |
      | Group 01 | Gabriel | Yes       |
    Given datasets:
      | title      | publisher | author  | published | description |
      | Dataset 01 | Group 01  | Gabriel | Yes       | Test        |
    And "Format" terms:
      | name    |
      | cvs     |
    And resources:
      | title       | publisher | format | dataset    | author  | published | description               |
      | Resource 01 | Group 01  | cvs    | Dataset 01 | Gabriel | Yes       | The resource description. |

  @api @javascript
  Scenario: Adding and Removing items from the datastore
      Given I am logged in as a user with the "site manager" role
      And I am on "dataset/dataset-01"
      And I click "Resource 01"
      And I click "Edit"
      And I click "Remote file"
      And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/district_centerpoints_small.csv"
      And I press "Save"
      And I am on "dataset/dataset-01"
      And I click "Resource 01"
      Then I wait for "The resource description."
    When I click "Manage Datastore"
      Then I wait for "DKAN Datastore Link Importer: Status"
    When I press "Import"
      And I wait for "2 imported items total."
    When I click "Data API"
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
