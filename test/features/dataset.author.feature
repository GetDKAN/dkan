@javascript
Feature: Dataset Features
  In order to realize a named business value
  As an explicit system actor
  I want to gain some beneficial outcome which furthers the goal

  Additional text...


  Background:
    Given pages:
      | title        | url                          |
      | Datasets     | dataset                      |
      | Needs Review | admin/workbench/needs-review |
      | My drafts    | admin/workbench/drafts       |
    Given users:
      | name    | mail             | roles                |
      | John    | john@example.com    | administrator        |
      | Badmin  | admin@example.com   | administrator        |
      | Gabriel | gabriel@example.com | editor               |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | editor   |
      | Martin  | martin@example.com  | editor               |
      | Celeste | celeste@example.com | editor               |
    Given groups:
      | title    | author | published |
      | Group 01 | Admin  | Yes       |
      | Group 02 | Admin  | Yes       |
      | Group 03 | Admin  | No        |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
      | Jaz     | Group 01 | member               | Pending           |
      | Admin   | Group 02 | administrator member | Active            |
      | Celeste | Group 02 | member               | Active            |
    And "Tags" terms:
      | name   |
      | Health |
      | Gov    |
    And datasets:
      | title      | publisher | author  | published        | tags     | description |
      | Dataset 01 | Group 01  | Gabriel | Yes              | price    |             |
      | Dataset 02 | Group 01  | Gabriel | Yes              | election |             |
      | Dataset 03 | Group 01  | Katie   | Yes              | price    |             |
      | Dataset 04 | Group 02  | Celeste | No               | election |             |
      | Dataset 05 | Group 01  | Katie   | No               | election |             |
    And resources:
      | title       | publisher | format | author | published | dataset    | description |
      | Resource 01 | Group 01  | csv    | Katie  | Yes       | Dataset 01 |             |
      | Resource 02 | Group 01  | html   | Katie  | Yes       | Dataset 01 |             |
      | Resource 03 | Group 01  | html   | Katie  | Yes       | Dataset 02 |             |

  # TODO: Requires workbench to be in place, not installed in data_starter at this time
  @api @fixme
  Scenario: Create dataset as draft
    Given I am logged in as "Katie"
    And I am on "Datasets" page
    When I click "Add Dataset"
    And I fill in the "dataset" form for "Dataset 06"
    And I press "Next: Add data"
    And I fill in the "resource" form for "Resource 06"
    And I press "Save"
    Then I should see "Resource Resource 05 has been created"
    When I press "Back to dataset"
    Then I should see "Revision state: Draft"


  #TODO: Data contributor role does not exist at this current time
  @api @fixme
  Scenario: A data contributor should not be able to publish datasets
    Given I am logged in as "Celeste"
    And I am on "Dataset 04" page
    When I follow "Edit"
    Then I should not see "Publishing options"

  # TODO: Requires workbench to be in place, not installed in data_starter at this time
  @api @fixme
  Scenario: Edit own dataset
    Given I am logged in as "Katie"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I fill in "title" with "Dataset 03 edited"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 edited has been updated"
    When I am on "My drafts" page
    Then I should see "Dataset 03 edited"
    And I should see "Draft" as "Moderation state" in the "Dataset 03 edited" row

  # TODO: Needs definition. How can a data contributor unpublish content?
  @api  
  Scenario: Unpublish own dataset
    Given I am on the homepage

  # TODO: Requires workbench to be in place, not installed in data_starter at this time
  @api @fixme
  Scenario: Revert review request (Change dataset status from 'Needs review' to 'Draft')
    Given I am logged in as "Katie"
    And I am on "My drafts" page
    Then I should see "Dataset 05"
    And I should see "Change to Draft" in the "Dataset 05" row
    When I click "Change to Draft" in the "Dataset 05" row
    Then I should see "Draft" as "Moderation state" in the "Dataset 05" row

  # TODO: Requires workbench to be in place, not installed in data_starter at this time
  @api @fixme
  Scenario: Receive a notification when a content editor publishes content I created
    Given I am logged in as "John"
    And I am on "Needs Review" page
    When I click "Change to Published" in the "Dataset 05" row
    Then I should see "Email notification sent"
    And user "Katie" should receive an email

  # TODO: Your groups field is not being found
  @api  @fixme
  Scenario: Add a dataset to group that I am a member of
    Given I am logged in as "Katie"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I fill in "Your groups" with "Group 01"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 has been updated"
    When I am on "Group 01" page
    And I click "Datasets" in the "group information" region
    Then I should see "Dataset 03" in the "group information" region
