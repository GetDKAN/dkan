# time:0m44.59s
@api @disablecaptcha @smoketest_noworkflow
Feature: Dataset Features

  Background:
    Given users:
      | name       | mail                | roles                |
      | John002    | john@example.com    | site manager         |
      | Gabriel002 | gabriel@example.com | editor               |
      | Katie002   | katie@example.com   | content creator      |
    And datasets:
      | title      | author     | published | description |
      | Dataset 01 | Gabriel002 | Yes       | Test 01     |
      | Dataset 03 | Katie002   | No        | Test 03     |

  @dataset_sm_01
  Scenario: Edit any dataset as a site manager
    Given I am logged in as "John002"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I fill in "title" with "Dataset 03 edited"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 edited has been updated"

  @dataset_sm_02
  Scenario: Delete any dataset as a site manager
    Given I am logged in as "John002"
    And I am on "Dataset 01" page
    When I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Dataset Dataset 01 has been deleted"

  @dataset_sm_03
  Scenario: Publish any dataset as a site manager
    Given I am logged in as "John002"
    And I am on "Dataset 03" page
    When I click "Edit"
    ## If you use selenium uncomment this
    # When I click "Publishing options"
    And I check the box "Published"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 has been updated"

  @dataset_sm_04 @javascript
  Scenario: See all dataset fields as a site manager
    Given I am logged in as "John002"
    And I am on "Dataset 01" page
    When I click "Edit"
    Then I should see all the dataset fields in the form
    And I should not see "Rights on Project Open Data"
    Then I select "Restricted" from "edit-field-public-access-level-und"
    And I should see "Rights on Project Open Data"

  @dataset_sm_05 @javascript
  Scenario: Should not see Rights field if public access level = none
    Given I am logged in as "John002"
    And I am on "Dataset 01" page
    When I click "Edit"
    Then I select "- None -" from "edit-field-public-access-level-und"
    And I should not see "Rights on Project Open Data"

