# time:0m39.30s
# The first scenario requires that the timezone be set to UTC.
# @timezone will set the timezone for tests and restore the timezone afterwards.
@api @timezone @disablecaptcha
Feature: Dataset Features
  In order to realize a named business value
  As an explicit system actor
  I want to gain some beneficial outcome which furthers the goal

  Additional text...


Background:
  Given users:
    | name    | mail                | roles                |
    | John    | john@example.com    | site manager         |
    | Badmin  | admin@example.com   | site manager         |
    | Gabriel | gabriel@example.com | editor               |
    | Katie   | katie@example.com   | content creator      |
    | Celeste | celeste@example.com | editor               |
  Given groups:
    | title    | author  | published |
    | Group 01 | Badmin  | Yes       |
    | Group 02 | Badmin  | Yes       |
  And group memberships:
    | user    | group    | role on group        | membership status |
    | Gabriel | Group 01 | administrator member | Active            |
    | Katie   | Group 01 | member               | Active            |
    | Celeste | Group 02 | member               | Active            |
  And datasets:
    | title      | publisher | author  | published | description | harvest source modified | date changed |
    | Dataset 01 | Group 01  | Gabriel | Yes       | Test        | 2015-01-02              | -5 year      |
    | Dataset 02 | Group 01  | Gabriel | Yes       | Test        | 2015-01-02              | -5 year      |
    | Dataset 03 | Group 01  | Katie   | Yes       | Test        | 2015-01-02              | -5 year      |
    | Dataset 04 | Group 02  | Celeste | Yes       | Test        | 2015-01-02              | -5 year      |
    | Dataset 05 | Group 01  | Katie   | No        | Test        | 2015-01-02              | -5 year      |

  @noworkflow
  Scenario: Replace node changed date with harvest source modified for harvested datasets
    Given I am logged in as "Gabriel"
    And I am on "Dataset 05" page
    When I click "Edit"
    ## If you use selenium uncomment this
    # When I click "Publishing options"
    And I check the box "Published"
    And I press "Finish"
    Then I should see "Dataset Dataset 05 has been updated"
    And I should see "2015-01-02"

  @noworkflow
  Scenario: Edit any dataset associated with the groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I fill in "title" with "Dataset 03 edited"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 edited has been updated"

  @noworkflow
  Scenario: Publish any dataset associated with the groups I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Dataset 05" page
    When I click "Edit"
    ## If you use selenium uncomment this
    # When I click "Publishing options"
    And I check the box "Published"
    And I press "Finish"
    Then I should see "Dataset Dataset 05 has been updated"

  @noworkflow @javascript
  Scenario: I should not be able to edit datasets of groups that I am not a member of
    Given I am logged in as "Gabriel"
    When I am on "Dataset 04" page
    And I hide the admin menu
    Then I should not see "Edit"

  @noworkflow
  Scenario: Delete any dataset associated with the groups that I am a member of
    Given I am logged in as "Gabriel"
    And I am on "Dataset 03" page
    When I click "Edit"
    When I press "Delete"
    And I press "Delete"
    Then I should see "Dataset Dataset 03 has been deleted"
