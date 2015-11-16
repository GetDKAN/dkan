@javascript
Feature: Resource

  Background:
    Given pages:
      | title         | url         |
      | My Workbench  | /node/add/  |
      | Content       | /user       |
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | administrator        |
      | Badmin  | admin@example.com   | administrator        |
      | Gabriel | gabriel@example.com | authenticated user   |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | authenticated user   |
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
      | CVS     |
      | XLS     |
    And resources:
      | title       | publisher | resource format | dataset    | author   | published | description |
      | Resource 01 | Group 01  | CVS             | Dataset 01 | Katie    | Yes       | No          |
      | Resource 02 | Group 01  | XLS             | Dataset 01 | Katie    | Yes       | No          |
      | Resource 03 | Group 01  | XLS             | Dataset 02 | Celeste  | No        | Yes         |
      | Resource 04 | Group 01  | CVS             | Dataset 01 | Katie    | No        | Yes         |
      | Resource 05 | Group 01  | XLS             | Dataset 02 | Celeste  | Yes       | Yes         |

  # TODO: Change to use Workbench instead of /content

  @api
  Scenario: Create resource
    Given I am logged in as "Katie"
    And I am on "My Workbench" page
    #And I click "Add Content"
    And I click "Resource"
    When I fill in "Resource 06" for "Title"
    And I press "Save"
    Then I should see "Resource Resource 06 has been created"
    When I am on "Content" page
    Then I should see "Resource 06"

  # TODO: Needs definition.

  @api @fixme
  Scenario: Create resources with GeoJSON data
    Given I am on the homepage

  # TODO: Needs definition.

  @api @fixme
  Scenario: Bureau & Program Code are auto populated on creation
    Given I am on the homepage

  # TODO: Change to use Workbench instead of /content

  @api
  Scenario: Edit own resource
    Given I am logged in as "Katie"
    And I am on "Resource 02" page
    When I click "Edit"
    And I fill in "Title" with "Resource 02 edited"
    And I press "Save"
    Then I should see "Resource Resource 02 edited has been updated"
    When I am on "Content" page
    Then I should see "Resource 02 edited"

  @api
  Scenario: A data contributor should not be able to publish resources
    Given I am logged in as "Katie"
    And I am on "Resource 02" page
    When I click "Edit"
    Then I should not see "Publishing options"

  # TODO: Needs definition. How can a data contributor unpublish content?

  @api @fixme
  Scenario: Unpublish own resource
    Given I am on the homepage

  # TODO: Managing own datastore not currently supported for authenticated users
  # TODO: Permissions for a user to manage the datastore of their own resource are not set (they can't access)

  @api @fixme
  Scenario: Manage datastore of own resource
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I click "Manage Datastore"
    Then I should see "There is nothing to manage! You need to upload or link to a file in order to use the datastore."

  # TODO: Add manage datastore support for created resources in DKAN Extension

  @api @fixme
  Scenario: Import items on datastore of own resource
    Given I am logged in as "Katie"
    And I am on "Resource 02" page
    When I click "Manage Datastore"
    And I press "Import"
    And I press "Import"
    And I wait
    Then I should see "Last import"
    And I should see "imported items total"

  # TODO: Add manage datastore support for created resources in DKAN Extension

  @api @fixme
  Scenario: Delete items on datastore of own resource
    Given I am logged in as "Celeste"
    And I am on "Resource 03" page
    When I click "Manage Datastore"
    And I press "Delete items"
    And I press "Delete"
    And I wait
    Then I should see "items have been deleted."
    When I click "Manage Datastore"
    Then I should see "No imported items."

  # TODO: Add manage datastore support for created resources in DKAN Extension

  @api @fixme
  Scenario: Drop datastore of own resource
    Given I am logged in as "Celeste"
    And I am on "Resource 03" page
    And I click "Manage Datastore"
    When I press "Drop datastore"
    And I press "Drop"
    Then I should see "Datastore dropped!"
    And I should see "Your file for this resource is not added to the datastore"
    When I click "Manage Datastore"
    Then I should see "No imported items."

  @api
  Scenario: Add revision to own resource
    Given I am logged in as "Katie"
    And I am on "Resource 02" page
    When I click "Edit"
    And I fill in "title" with "Resource 02 edited"
    #And I check "Create new revision"
    And I press "Save"
    Then I should see "Resource Resource 02 edited has been updated"
    When I click "Revisions"
    Then I should see "current revision"
