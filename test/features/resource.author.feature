@api
Feature: Resource

  Background:
    Given pages:
      | name          | url         |
      | Content       | /node/add  |
      | User          | /user       |
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
      | Badmin  | admin@example.com   | site manager         |
      | Gabriel | gabriel@example.com | content creator   |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | content creator   |
      | Martin  | martin@example.com  | editor               |
      | Celeste | celeste@example.com | editor               |
    Given groups:
      | title    | author  | published |
      | Group 01 | Badmin  | Yes       |
      | Group 02 | Badmin  | Yes       |
      | Group 03 | Badmin  | No        |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
      | Jaz     | Group 01 | member               | Pending           |
      | Admin   | Group 02 | administrator member | Active            |
      | Celeste | Group 01 | member               | Active            |
      | Katie   | Group 02 | member               | Active            |
    And "Tags" terms:
      | name    |
      | Health  |
      | Gov     |
    And datasets:
      | title      | publisher | author  | published        | tags     | description |
      | Dataset 01 | Group 01  | Gabriel | Yes              | Health   | Test        |
      | Dataset 02 | Group 01  | Gabriel | Yes              | Gov      | Test        |
      | Dataset 03 |           | Katie   | Yes              | Health   | Test        |
      | Dataset 04 |           | Katie   | Yes              | Gov      | Test        |
      | Dataset 05 | Group 01  | Katie   | Yes              | Gov      | Test        |
      | Dataset 06 | Group 02  | Katie   | Yes              | Gov      | Test        |
    And "Format" terms:
      | name    |
      | cvs     |
      | xls     |
    And resources:
      | title       | publisher | format | dataset    | author   | published | description |
      | Resource 01 | Group 01  | cvs    | Dataset 01 | Katie    | Yes       | No          |
      | Resource 02 | Group 01  | xls    | Dataset 01 | Katie    | Yes       | No          |
      | Resource 03 | Group 01  | xls    | Dataset 02 | Celeste  | No        | Yes         |
      | Resource 04 | Group 01  | cvs    | Dataset 01 | Katie    | No        | Yes         |
      | Resource 05 | Group 01  | xls    | Dataset 02 | Celeste  | Yes       | Yes         |
      | Resource 06 |           | csv    |            | Katie    | Yes       | Test        |
      | Resource 07 |           | csv    | Dataset 04 | Katie    | Yes       | Test        |
      | Resource 08 | Group 01  | csv    | Dataset 05 | Katie    | Yes       | Test        |

  @noworkflow
  Scenario: Create resource
    Given I am logged in as "Katie"
    And I am on the "Content" page
    And I click "Resource"
    And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "http://s3.amazonaws.com/dkan-default-content-files/files/district_centerpoints_short_version.csv"
    When I fill in "Title" with "Resource 06"
    And I press "Save"
    Then I should see "Resource Resource 06 has been created"

  @noworkflow @javascript
  Scenario: Create resource with too many sources.
    Given I am logged in as "Katie"
    And I am on the "Content" page
    And I click "Resource"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "http://s3.amazonaws.com/dkan-default-content-files/files/district_centerpoints_short_version.csv"
    And I click "API or Website URL"
    And I fill in "edit-field-link-api-und-0-url" with "http://earthquake.usgs.gov/fdsnws/event/1/query?format=geojson&starttime=2014-01-01&endtime=2014-01-02"
    When I fill in "Title" with "Resource 06"
    And I press "Save"
    Then I should see "Remote file is populated - only one resource type can be used at a time"
    And I should see "API or Website URL is populated - only one resource type can be used at a time"

  @noworkflow
  #TODO: Content creator will be a role added later, but for now we stick with authenticated user
  Scenario: Edit own resource as content creator
    Given I am logged in as "Katie"
    And I am on "Resource 02" page
    When I click "Edit"
    And I fill in "Title" with "Resource 02 edited"
    And I press "Save"
    Then I should see "Resource Resource 02 edited has been updated"
    When I am on "User" page
    Then I should see "Resource 02 edited"

  @noworkflow
  #TODO: Content creator will be a role added later, but for now we stick with authenticated user
  Scenario: Delete own resource
    Given I am logged in as "Katie"
    And I am on "Resource 02" page
    When I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Resource 02 has been deleted"

  @dkanBug @noworkflow
  Scenario: Change dataset on resource
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I select "Dataset 02" from "Dataset"
    And I press "Save"
    Then I should see "Resource 01 has been updated"
    When I click "Back to dataset"
    Then I should see "Dataset 02" in the "dataset title" region
    And I should see "Resource 01" in the "dataset resource list" region

  @noworkflow
  Scenario: Add a resource with no datasets to a dataset with no resource
    Given I am logged in as "Katie"
    And I am on "Resource 06" page
    When I click "Edit"
    And I select "Dataset 03" from "Dataset"
    And I press "Save"
    Then I should see "Resource 06 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I click "Back to dataset"
    Then I should see "Dataset 03" in the "dataset title" region
    And I should see "Resource 06" in the "dataset resource list" region

  @noworkflow
  Scenario: Remove a resource with only one dataset from the dataset
    Given I am logged in as "Katie"
    And I am on "Resource 07" page
    When I click "Edit"
    And I select "- None -" from "Dataset"
    And I press "Save"
    Then I should see "Resource 07 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I click "Back to dataset"
    Then I should see "There is no dataset associated with this resource"
    Given I am on "Dataset 04" page
    Then I should not see "Resource 07" in the "dataset resource list" region

  @noworkflow
  Scenario: Add a resource with no group to a dataset with group
    Given I am logged in as "Katie"
    And I am on "Resource 06" page
    When I click "Edit"
    And I select "Dataset 05" from "Dataset"
    And I press "Save"
    Then I should see "Resource 06 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I click "Edit"
    Then I should see "Group 01" in the "resource groups" region

  @noworkflow
  Scenario: Remove a resource from a dataset with group
    Given I am logged in as "Katie"
    And I am on "Resource 08" page
    When I click "Edit"
    Then I should see "Group 01" in the "resource groups" region
    When I select "- None -" from "Dataset"
    And I press "Save"
    Then I should see "Resource 08 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I click "Edit"
    Then I should not see "Group 01" in the "resource groups" region
    When I am on "Dataset 05" page
    Then I should not see "Resource 08" in the "dataset resource list" region

  @noworkflow
  Scenario: Add a resource to multiple datasets with groups
    Given I am logged in as "Katie"
    And I am on "Resource 06" page
    When I click "Edit"
    And I select "Dataset 05" from "Dataset"
    And I additionally select "Dataset 06" from "Dataset"
    And I press "Save"
    Then I should see "Resource 06 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I click "Edit"
    Then I should see "Group 01" in the "resource groups" region
    And I should see "Group 02" in the "resource groups" region

  @noworkflow
  Scenario: Remove one dataset with group from resource with multiple datasets
    Given I am logged in as "Katie"
    And I am on "Resource 06" page
    When I click "Edit"
    And I select "Dataset 05" from "Dataset"
    And I additionally select "Dataset 06" from "Dataset"
    And I press "Save"
    Then I should see "Resource 06 has been updated"
    When I click "Edit"
    And I select "Dataset 05" from "Dataset"
    And I press "Save"
    Then I should see "Resource 06 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I click "Edit"
    Then I should see "Group 01" in the "resource groups" region
    And I should not see "Group 02" in the "resource groups" region

  @noworkflow
  Scenario: Remove all datasets with groups from resource
    Given I am logged in as "Katie"
    And I am on "Resource 06" page
    When I click "Edit"
    And I select "Dataset 05" from "Dataset"
    And I additionally select "Dataset 06" from "Dataset"
    And I press "Save"
    Then I should see "Resource 06 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I click "Edit"
    And I select "- None -" from "Dataset"
    And I press "Save"
    Then I should see "Resource 06 has been updated"
    When I click "Edit"
    Then I should not see "Group 01" in the "resource groups" region
    And I should not see "Group 02" in the "resource groups" region

  @dkanBug @noworkflow
    # TODO: Managing own datastore not currently supported for authenticated users
    # TODO: Permissions for a user to manage the datastore of their own resource are not set (they can't access)
  Scenario: Manage datastore of own resource
    Given I am logged in as "Celeste"
    And I am on "Resource 05" page
    When I click "Edit"
    And I click "Manage Datastore"
    Then I should see "There is nothing to manage! You need to upload or link to a file in order to use the datastore."

  @noworkflow @javascript
  Scenario: Import items on datastore of own resource
    Given I am logged in as "Celeste"
    And I am on "Resource 05" page
    And I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "http://s3.amazonaws.com/dkan-default-content-files/files/district_centerpoints_short_version.csv"
    And I press "Save"
    And I am on "Resource 05" page
    When I click "Manage Datastore"
    And I press "Import"
    And I wait for "Delete items"
    Then I should see "Last import"
    And I should see "imported items total"

  @noworkflow @javascript
  Scenario: DEBUG
    Given I am logged in as "John"
    And I am on "Resource 03" page
    And I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "http://s3.amazonaws.com/dkan-default-content-files/files/district_centerpoints_short_version.csv"
    And I press "Save"
    Given I am logged in as "Celeste"
    And I am on "Resource 03" page
    When I click "Manage Datastore"
    And I press "Import"
    And I wait for "Delete Items"
    And I click "Delete items"
    And I press "Delete"
    And I wait for "items have been deleted"
    And I am on "Resource 03" page
    When I click "Manage Datastore"
    Then I should see "No imported items."

  @noworkflow @javascript
  Scenario: Drop datastore of own resource
    Given I am logged in as "John"
    And I am on "Resource 03" page
    And I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "http://s3.amazonaws.com/dkan-default-content-files/files/district_centerpoints_short_version.csv"
    And I press "Save"
    Given I am logged in as "Celeste"
    And I am on "Resource 03" page
    When I click "Manage Datastore"
    And I press "Import"
    And I wait for "Delete Items"
    When I click "Drop Datastore"
    And I press "Drop"
    Then I should see "Datastore dropped!"
    And I should see "Your file for this resource is not added to the datastore"
    When I click "Manage Datastore"
    Then I should see "No imported items."

  @noworkflow
  Scenario: Add revision to own resource
    Given I am logged in as "Katie"
    And I am on "Resource 02" page
    When I click "Edit"
    And I fill in "title" with "Resource 02 edited"
    And I press "Save"
    Then I should see "Resource Resource 02 edited has been updated"
    When I click "Revisions"
    Then I should see "by Katie"

  # https://github.com/Behat/Behat/issues/834
  @dummy
  Scenario: Dummy test
    Given I am on "/"
