# time:1m38.22s
@api @disablecaptcha
Feature: Resource

  Background:
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
      | Gabriel | gabriel@example.com | editor               |
      | Katie   | katie@example.com   | content creator      |
    Given groups:
      | title    | author  | published |
      | Group 01 | John    | Yes       |
      | Group 02 | John    | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |

  @resource_editor_1
  Scenario: Edit resources associated with groups that I am a member of
    Given datasets:
      | title      | publisher | author  | published        |description |
      | Dataset 01 | Group 01  | Gabriel | Yes              |Test        |
    Given resources:
      | title       | publisher | format | dataset    | author   | published | description |
      | Resource 01 | Group 01  | csv    | Dataset 01 | Katie    | Yes       | Test        |
    And I am logged in as "Gabriel"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "title" with "Resource 01 edited"
    And I press "Save"
    Then I should see "Resource Resource 01 edited has been updated"
    When I am on "Dataset 01" page
    Then I should see "Resource 01 edited"

  @resource_editor_2 @javascript
  Scenario: I should not be able to edit resources of groups that I am not a member of
    Given resources:
      | title       | publisher | format | author   | published | description |
      | Resource 02 | Group 02  | csv    | John     | Yes       | Test        |
    Given I am logged in as "Gabriel"
    And I am on "Resource 02" page
    And I hide the admin menu
    Then I should not see "Edit"

  @resource_editor_3
  Scenario: Publish resources associated with groups that I am a member of
    Given resources:
      | title       | publisher | format | author   | published | description |
      | Resource 03 | Group 01  | csv    | Katie    | No        | Test        |
    Given I am logged in as "Gabriel"
    And I am on "Resource 03" page
    When I click "Edit"
    ## If you use selenium uncomment this
    # When I click "Publishing options"
    And I check "Published"
    And I press "Save"
    Then I should see "Resource Resource 03 has been updated"

  @resource_editor_4
  Scenario: Delete resources associated with groups that I am a member of
    Given resources:
      | title       | publisher | format | author   | published | description |
      | Resource 04 | Group 01  | csv    | Katie    | No        | Test        |
    Given I am logged in as "Gabriel"
    And I am on "Resource 04" page
    When I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Resource Resource 04 has been deleted"

  @resource_editor_6 @datastore @javascript
  Scenario: Manage the datastore of resources associated with groups that I am a member of
    Given resources:
      | title       | publisher | format | author   | published | description |
      | Resource 06 | Group 01  | csv    | Katie    | Yes       | Test        |
    Given I am logged in as "Gabriel"
    And I am on "Resource 06" page
    And I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/files/datastore-simple7.csv"
    And I press "Save"
    And I am on "Resource 06" page
    When I follow "View"
    When I click "Manage Datastore"
    And I wait for "Import"
    And I press "Import"
    And I wait for "Done"
    When I press "Drop"
    And I press "Drop"
    Then I wait for "Ready"

  @resource_editor_9
  Scenario: Add revision to resources associated with groups that I am a member of
    Given resources:
      | title       | publisher | format | author   | published | description |
      | Resource 09 | Group 01  | csv    | Katie    | Yes       | Test        |
    Given I am logged in as "Gabriel"
    And I am on "Resource 09" page
    When I click "Edit"
    And I fill in "title" with "Resource 09 edited"
    And I press "Save"
    Then I should see "Resource Resource 09 edited has been updated"
    When I click "Revisions"
    And I press "Compare"
    Then I should see "Resource 09 edited"

  @resource_editor_10
  Scenario: Revert resource revision of any resource associated with groups that I am a member of
    Given resources:
      | title       | publisher | format | author   | published | description |
      | Resource 10 | Group 01  | csv    | Katie    | Yes       | Test        |
    Given I am logged in as "Gabriel"
    And I am on "Resource 10" page
    When I click "Edit"
    And I fill in "title" with "Resource 10 edited"
    And I press "Save"
    Then I should see "Resource Resource 10 edited has been updated"
    When I click "Revisions"
    And I click "Revert"
    And I press "Revert"
    Then I should see "Resource Resource 10 has been reverted"
