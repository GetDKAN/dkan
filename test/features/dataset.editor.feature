# time:0m39.30s
# The first scenario requires that the timezone be set to UTC.
# @timezone will set the timezone for tests and restore the timezone afterwards.
@api @timezone @disablecaptcha @smoketest_noworkflow
Feature: Dataset Features

Background:
  Given users:
    | name    | mail                | roles                |
    | Gabriel | gabriel@example.com | editor               |
    | Katie   | katie@example.com   | content creator      |
    | Celeste | celeste@example.com | editor               |
  Given groups:
    | title    | author  | published |
    | Group 01 | Gabriel | Yes       |
    | Group 02 | Celeste | Yes       |
  And group memberships:
    | user    | group    | role on group        | membership status |
    | Gabriel | Group 01 | administrator member | Active            |
    | Katie   | Group 01 | member               | Active            |
    | Celeste | Group 02 | administrator member | Active            |
  And datasets:
    | title      | publisher | author  | published | description | harvest source modified | date changed |
    | Dataset 01 | Group 01  | Gabriel | Yes       | Test        | 2015-01-02              | -5 year      |
    | Dataset 03 | Group 01  | Katie   | No        | Test        | 2015-01-02              | -5 year      |
    | Dataset 04 | Group 02  | Celeste | Yes       | Test        | 2015-01-02              | -5 year      |


  @dataset_editor_01
  Scenario: Replace node changed date with harvest source modified for harvested datasets
    Given I am logged in as "Gabriel"
    And I am on "Dataset 01" page
    When I click "Edit"
    ## If you use selenium uncomment this
    # When I click "Publishing options"
    And I check the box "Published"
    And I press "Finish"
    Then I should see "Dataset Dataset 01 has been updated"
    And I should see "2015-01-02"

  @dataset_editor_02
  Scenario: Edit any dataset associated with the groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I fill in "title" with "Dataset 03 edited"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 edited has been updated"

  @dataset_editor_03
  Scenario: Publish any dataset associated with the groups I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Dataset 03" page
    When I click "Edit"
    ## If you use selenium uncomment this
    # When I click "Publishing options"
    And I check the box "Published"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 has been updated"

  @dataset_editor_03 @javascript
  Scenario: I should not be able to edit datasets of groups that I am not a member of
    Given I am logged in as "Gabriel"
    When I am on "Dataset 04" page
    And I hide the admin menu
    Then I should not see "Edit"

  @dataset_editor_04
  Scenario: Delete any dataset associated with the groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Dataset 03" page
    When I click "Edit"
    When I press "Delete"
    And I press "Delete"
    Then I should see "Dataset Dataset 03 has been deleted"
