# time:1m55.91s
@api @disablecaptcha @datastore
Feature: Datastore
  In order to know the datastore is working
  As a website user
  I need to be able to add and remove items from the datastore

  Background:
    Given users:
      | name    | mail                | roles           |
      | Katie   | katie@example.com   | site manager    |
    Given groups:
      | title    | author  | published |
      | Group 01 | Katie   | Yes       |
    Given datasets:
      | title      | publisher | author  | published | description |
      | Dataset 01 | Group 01  | Katie   | Yes       | Test        |
    And "Format" terms:
      | name    |
      | csv     |
    And resources:
      | title       | publisher | format | dataset    | author  | published | description               |
      | Resource 01 | Group 01  | csv    | Dataset 01 | Katie   | Yes       | The resource description. |
  # Don't remove! This is for avoiding issues when other scenarios are disabled (because of @noworkflow tag).
  Scenario: Dumb test
    Given I am on the homepage

  @api @datastore @javascript
  Scenario: Adding and Removing items from the datastore

    Given endpoints:
      | name             | path                   |
      | dataset rest api | /api/dataset           |
    And I use the "dataset rest api" endpoint to login with user "admin" and pass "admin"
    And I use the "dataset rest api" endpoint to attach the file "dkan/district_centerpoints_0.csv" to "Resource 01"

    When I am logged in as "Katie"
    And I am on "dataset/dataset-01"
    And I click "Resource 01"
    And I click "Manage Datastore"
    And I select "Simple Import" from "edit-datastore-managers-selection"
    And I press "Save"
    Then I should see "Status:"

    When I press "Import"
    And I wait for "Status:"
    Then I should see "Records Imported: 2"

    When I click "Drop Datastore"
    And I press "Drop"
    Then I should see "Records Imported: 0"
    Then I should see "Storage: Uninitialized"
    Then I should see "Data Importing: Ready"

  @api @datastore @javascript
  Scenario: Import a csv tab delimited file.
    Given endpoints:
      | name             | path                   |
      | dataset rest api | /api/dataset           |
    And I use the "dataset rest api" endpoint to login with user "admin" and pass "admin"
    And I use the "dataset rest api" endpoint to attach the file "dkan/TAB_delimiter_large_raw_number.csv" to "Resource 01"

    When I am logged in as "Katie"
    And I am on "dataset/dataset-01"
    And I click "Resource 01"
    And I click "Manage Datastore"
    And I select "Simple Import" from "edit-datastore-managers-selection"
    And I press "Save"
    Then I should see "Status:"

    When I select "TAB" from "delimiter"
    When I press "Import"
    And I wait for "Status:"
    Then I should see "Records Imported: 5"

    When I click "Drop Datastore"
    And I press "Drop"
    Then I should see "Records Imported: 0"
    Then I should see "Storage: Uninitialized"
    Then I should see "Data Importing: Ready"