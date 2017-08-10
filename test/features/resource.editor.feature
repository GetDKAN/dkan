# time:3m12.22s
@api @disablecaptcha
Feature: Resource

  Background:
    Given pages:
      | name          | url   |
      | Content       | /user       |
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
      | Badmin  | admin@example.com   | site manager         |
      | Gabriel | gabriel@example.com | content creator      |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | content creator      |
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
      | Celeste | Group 01 | administrator member | Active            |
      | John    | Group 01 | member               | Active            |
    And "Tags" terms:
      | name    |
      | world   |
      | results |
    And datasets:
      | title      | publisher | author  | published        | tags     | description |
      | Dataset 01 | Group 01  | Gabriel | Yes              | world    | Test        |
      | Dataset 02 | Group 02  | Celeste | Yes              | results  | Test        |
    And resources:
      | title       | publisher | format | dataset    | author   | published | description |
      | Resource 01 | Group 01  | csv    | Dataset 01 | Katie    | Yes       | No          |
      | Resource 02 | Group 01  | csv    | Dataset 01 | Katie    | Yes       | No          |
      | Resource 03 | Group 02  | csv    | Dataset 02 | Celeste  | No        | Yes         |
      | Resource 04 | Group 01  | csv    | Dataset 01 | Katie    | No        | Yes         |
      | Resource 05 | Group 02  | csv    | Dataset 02 | Celeste  | Yes       | Yes         |

  # TODO: Change to use Workbench instead of /content
  @noworkflow
  Scenario: Edit resources associated with groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "title" with "Resource 01 edited"
    And I press "Save"
    Then I should see "Resource Resource 01 edited has been updated"
    When I am on "Dataset 01" page
    Then I should see "Resource 01 edited"

  @noworkflow
  Scenario: I should not be able to edit resources of groups that I am not a member of
    Given I am logged in as "Gabriel"
    And I am on "Resource 05" page
    Then I should not see "Edit"

  @fixme @dkanBug @noworkflow
    # TODO: Permissions are not set so that a group member can publish any resources of their group,
    #       this test will need to wait until that is set
  Scenario: Publish resources associated with groups that I am a member of
    Given I am logged in as "Celeste"
    And I am on "Resource 04" page
    When I click "Edit"
    ## If you use selenium uncomment this
    # When I click "Publishing options"
    And I check "Published"
    And I press "Save"
    Then I should see "Resource Resource 04 edited has been updated"

  @noworkflow
  Scenario: Delete resources associated with groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Resource 01" page
    When I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Resource Resource 01 has been deleted"

  @noworkflow
  Scenario: Manage datastore of resources associated with groups that I am a member of
    Given I am logged in as "Celeste"
    And I am on "Resource 01" page
    When I click "Manage Datastore"
    Then I should see "There is nothing to manage! You need to upload or link to a file in order to use the datastore."

  @noworkflow @javascript
  Scenario: Import items on datastore of resources associated with groups that I am a member of
    Given I am logged in as "John"
    And I am on "Resource 01" page
    And I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/files/district_centerpoints_small.csv"
    And I press "Save"
    Given I am logged in as "Celeste"
    And I am on "Resource 01" page
    When I click "Manage Datastore"
    And I wait for "Import"
    And I press "Import"
    And I wait for "Delete Items"
    Then I should see "Last import"
    And I should see "imported items total"

  @noworkflow @javascript
  Scenario: Delete items on datastore of resources associated with groups that I am a member of
    Given I am logged in as "John"
    And I am on "Resource 01" page
    And I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/files/district_centerpoints_small.csv"
    And I press "Save"
    Given I am logged in as "Celeste"
    When I am on "Resource 01" page
    When I click "Manage Datastore"
    And I press "Import"
    And I wait for "Delete Items"
    And I click "Delete items"
    And I press "Delete"
    And I wait for "items have been deleted"
    And I am on "Resource 01" page
    When I click "Manage Datastore"
    And I wait for "No imported items."

  @noworkflow @javascript
  Scenario: Drop datastore of resources associated with groups that I am a member of
    Given I am logged in as "John"
    And I am on "Resource 01" page
    And I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/files/district_centerpoints_small.csv"
    And I press "Save"
    Given I am logged in as "Celeste"
    And I am on "Resource 01" page
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
  Scenario: Add revision to resources associated with groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "title" with "Resource 01 edited"
    And I press "Save"
    Then I should see "Resource Resource 01 edited has been updated"
    When I click "Revisions"
    And I press "Compare"
    Then I should see "Resource 01 edited"

  @fixme @dkanBug @noworkflow
    #TODO: Currently content creators do not have access to revert any resource
    #       That they are a group member for. Does this need to be tested then?
  Scenario: Revert resource revision of any resource associated with groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "title" with "Resource 01 edited"
    And I press "Save"
    Then I should see "Resource Resource 01 edited has been updated"
    When I click "Revisions"
    And I click "Revert"
    And I press "Revert"
    Then I should see "Resource Resource 01 has been reverted"
