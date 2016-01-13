@javascript @api
Feature: Resource

  Background:
    Given pages:
      | title         | url         |
      | Content       | /node/add/  |
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
      | Celeste | Group 02 | member               | Active            |
    And "Tags" terms:
      | name    |
      | Health  |
      | Gov     |
    And datasets:
      | title      | publisher | author  | published        | tags     | description |
      | Dataset 01 | Group 01  | Gabriel | Yes              | Health   | Test        |
      | Dataset 02 | Group 01  | Gabriel | Yes              | Gov      | Test        |
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

  Scenario: Create resource
    Given I am logged in as "Katie"
    And I am on the "Content" page
    And I click "Resource"
    When I fill in "Title" with "Resource 06"
    And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "http://demo.getdkan.com/sites/default/files/district_centerpoints_0.csv"
    And I press "Save"
    Then I should see "Resource Resource 06 has been created"

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

  Scenario: A data contributor should not be able to publish resources
    Given I am logged in as "Katie"
    And I am on "Resource 02" page
    When I click "Edit"
    Then I should not see "Publishing options"

  #TODO: Content creator will be a role added later, but for now we stick with authenticated user
  Scenario: Delete own resource
    Given I am logged in as "Katie"
    And I am on "Resource 02" page
    When I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Resource 02 has been deleted"

  @dkanBug
    # TODO: Managing own datastore not currently supported for authenticated users
    # TODO: Permissions for a user to manage the datastore of their own resource are not set (they can't access)
  Scenario: Manage datastore of own resource
    Given I am logged in as "Celeste"
    And I am on "Resource 05" page
    When I click "Edit"
    And I click "Manage Datastore"
    Then I should see "There is nothing to manage! You need to upload or link to a file in order to use the datastore."

  Scenario: Import items on datastore of own resource
    Given I am logged in as "Celeste"
    And I am on "Resource 05" page
    And I click "Edit"
    And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "http://demo.getdkan.com/sites/default/files/district_centerpoints_0.csv"
    And I press "Save"
    And I am on "Resource 05" page
    When I click "Manage Datastore"
    And I press "Import"
    And I wait for "Delete items"
    Then I should see "Last import"
    And I should see "imported items total"

  Scenario: Delete items on datastore of own resource
    Given I am logged in as "John"
    And I am on "Resource 03" page
    And I click "Edit"
    And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "http://demo.getdkan.com/sites/default/files/district_centerpoints_0.csv"
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

  Scenario: Drop datastore of own resource
    Given I am logged in as "John"
    And I am on "Resource 03" page
    And I click "Edit"
    And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "http://demo.getdkan.com/sites/default/files/district_centerpoints_0.csv"
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

  Scenario: Add revision to own resource
    Given I am logged in as "Katie"
    And I am on "Resource 02" page
    When I click "Edit"
    And I fill in "title" with "Resource 02 edited"
    And I press "Save"
    Then I should see "Resource Resource 02 edited has been updated"
    When I click "Revisions"
    Then I should see "by Katie"
