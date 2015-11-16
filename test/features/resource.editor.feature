@javascript
Feature: Resource

  Background:
    Given pages:
      | title         | url   |
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
      | Resource 05 | Group 02  | XLS             | Dataset 02 | Celeste  | Yes       | Yes         |

  # TODO: Change to use Workbench instead of /content

  @api
  Scenario: Edit resources associated with groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "title" with "Resource 01 edited"
    And I press "Save"
    Then I should see "Resource Resource 01 edited has been updated"
    When I am on "Dataset 01" page
    Then I should see "Resource 01 edited"

  @api
  Scenario: I should not be able to edit resources of groups that I am not a member of
    Given I am logged in as "Gabriel"
    And I am on "Resource 05" page
    Then I should not see "Edit"

  # TODO: Permissions are not set so that a group member can publish any resources of their group,
  #       this test will need to wait until that is set

  @api @fixme
  Scenario: Publish resources associated with groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Resource 04" page
    When I click "Edit"
    And I select "published" for "publishing options"
    And I press "Save"
    Then I should see "Resource Resource 04 edited has been updated"

  @api
  Scenario: Delete resources associated with groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Resource 01" page
    When I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Resource Resource 01 has been deleted"

  @api
  Scenario: Manage datastore of resources associated with groups that I am a member of
    Given I am logged in as "Celeste"
    And I am on "Resource 01" page
    When I click "Manage Datastore"
    Then I should see "There is nothing to manage! You need to upload or link to a file in order to use the datastore."

  # TODO:  Make possible to add items to datastore upon resource creation with extension to test
  #         deleting  items and dropping datastore. Currently tests for empty items only (check commented for original)

  @api
  Scenario: Import items on datastore of resources associated with groups that I am a member of
    Given I am logged in as "Celeste"
    And I am on "Resource 01" page
    When I click "Manage Datastore"
    And I click "Import"
    #And I press "Import"
    #And I wait
    #Then I should see "Last import"
    #And I should see "imported items total"
    Then I should see "There is nothing to manage!"

  # TODO:  Make possible to add items to datastore upon resource creation with extension to test
  #         deleting  items and dropping datastore. Currently tests for empty items only (check commented for original)
  #

  @api @fixme
  Scenario: Delete items on datastore of resources associated with groups that I am a member of
    Given I am logged in as "Celeste"
    And I am on "Resource 04" page
    When I click "Manage Datastore"
    And I click "Delete items"
    And I press "Delete"
    And I wait
    #Then I should see "items were deleted"
    Then I should see "no items to delete"
    When I click "Manage Datastore"
    Then I should see "No imported items."

  # TODO:  Make possible to add items to datastore upon resource creation with extension to test
  #         deleting  items and dropping datastore. Currently tests for empty items only (check commented for original)

  @api 
  Scenario: Drop datastore of resources associated with groups that I am a member of
    Given I am logged in as "Celeste"
    And I am on "Resource 04" page
    And I click "Manage Datastore"
    When I click "Drop Datastore"
    #And I press "Drop"
    #Then I should see "Datastore dropped!"
    #And I should see "Your file for this resource is not added to the datastore"
    Then I should see "You need to have a file or link imported to the datastore in order to drop it."
    When I click "Manage Datastore"
    #Then I should see "No imported items."
    Then I should see "There is nothing to manage!"

  @api @fixme
  Scenario: Add revision to resources associated with groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "title" with "Resource 01 edited"
    And I check "Create new revision"
    And I press "Save"
    Then I should see "Resource Resource 01 edited has been updated"
    When I press "Revisions"
    And I click "first" revision
    Then I should see "Resource 01 edited"

  @api @fixme
  Scenario: Revert resource revision of any resource associated with groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "title" with "Resource 01 edited"
    And I check "Create new revision"
    And I press "Save"
    Then I should see "Resource Resource 01 edited has been updated"
    When I press "Revisions"
    And I click "Revert" in the "second" row
    # TODO: This is NOT working. Throws "You are not authorized to access this page"
    Then the resource should be reverted