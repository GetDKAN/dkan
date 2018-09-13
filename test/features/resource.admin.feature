# time:1m32.08s
@api @disablecaptcha
# in the resource tests, when it uses "Given resources:" it defines a property called 'datastore created' with either a 'yes' or 'no', which is used in some tests -  should I try to map that when creating the resource in resourceContext? @Frank
Feature: Resource

  Background:
    Given pages:
      | name      | url             |
      | Content   | /admin/content  |
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
      | Gabriel | gabriel@example.com | content creator      |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | content creator      |
      | Martin  | martin@example.com  | editor               |
      | Celeste | celeste@example.com | editor               |
      | Badmin  | admin@example.com   | site manager         |
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
      | world   |
      | results |
    And datasets:
      | title      | publisher | author  | published        | tags     | description |
      | Dataset 01 | Group 01  | Gabriel | Yes              | world    | Test        |
      | Dataset 02 | Group 01  | Gabriel | Yes              | results  | Test        |
    And resources:
      | title       | publisher | dataset    | author   | published | description |
      | Resource 01 | Group 01  | Dataset 01 | Katie    | Yes       | No          |
      | Resource 02 | Group 01  | Dataset 01 | Katie    | Yes       | No          |
      | Resource 03 | Group 01  | Dataset 02 | Celeste  | No        | Yes         |
      | Resource 04 | Group 01  | Dataset 01 | Katie    | No        | Yes         |
      | Resource 05 | Group 01  | Dataset 02 | Celeste  | Yes       | Yes         |

  @resource_admin_1 @noworkflow
  Scenario: Edit any resource
    Given I am logged in as "John"
    And I am on "Resource 02" page
    When I click "Edit"
    And I fill in "title" with "Resource 02 edited"
    And I press "Save"
    Then I should see "Resource Resource 02 edited has been updated"
    When I am on "Content" page
    Then I should see "Resource 02 edited"

  @resource_admin_2 @noworkflow
  Scenario: Publish any resource
    Given I am logged in as "John"
    And I am on "Resource 04" page
    When I click "Edit"
    ## If you use selenium uncomment this
    # When I click "Publishing options"
    And I check "Published"
    And I press "Save"
    Then I should see "Resource Resource 04 has been updated"

  @resource_admin_3 @noworkflow
  Scenario: Delete any resource
    Given I am logged in as "John"
    And I am on "Resource 02" page
    When I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Resource 02 has been deleted"

  @resource_admin_4 @noworkflow @javascript
  Scenario: Manage Datastore of any resource
    Given I am logged in as "John"
    And I am on "Resource 01" page
    And I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/files/datastore-simple.csv"
    And I press "Save"
    When I click "Manage Datastore"
    Then I should see "Datastore"

  @resource_admin_5 @noworkflow @datastore @javascript
  Scenario: Import items on datastore of any resource and drop
    Given I am logged in as "John"
    And I am on "Resource 02" page
    And I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/files/datastore-simple.csv"
    And I press "Save"
    When I click "Manage Datastore"
    Then I should see "Status"
    When I press "Import"
    And I wait for "Done"
    And I press "Drop"
    And I press "Drop"
    Then I should see "Records Imported"
    And I should see "0"
    And I should see "Data Importing"
    And I should see "Ready"

  @resource_admin_6 @noworkflow
  Scenario: Add revision to any resource
    Given I am logged in as "John"
    And I am on "Resource 02" page
    When I click "Edit"
    And I fill in "title" with "Resource 02 edited"
    And I check "Create new revision"
    And I press "Save"
    Then I should see "Resource Resource 02 edited has been updated"
    When I click "Revisions"
    Then I should see "This is the published revision"

  @fixme @dkanBug @noworkflow
    #TODO: There is an issue where an admin, when clicking revert, gets a access unauthorized response.
    #     See: https://github.com/GetDKAN/dkan/issues/793
  Scenario: Revert any resource revision
    Given I am logged in as "John"
    And I am on "Resource 02" page
    When I click "Edit"
    And I fill in "title" with "Resource 02 edited"
    And I press "Save"
    Then I should see "Resource Resource 02 edited has been updated"
    When I click "Revisions"
    And I click "Revert"
    And I press "Revert"
    Then I should see "Resource 02"
    And I should not see "Resource 02 edited"
