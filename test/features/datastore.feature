# time:1m55.91s
@api @disablecaptcha @datastore
Feature: Datastore
  In order to know the datastore is working
  As a website user
  I need to be able to add and remove items from the datastore

  Background:
    Given users:
      | name    | mail                | roles           |
      | Gabriel | gabriel@example.com | content creator |
      | Katie   | katie@example.com   | site manager    |
      | Daniel  | daniel@example.com  | content creator |
      | Jaz     | editor@example.com  | editor          |
    Given groups:
      | title    | author  | published |
      | Group 01 | Katie   | Yes       |
      | Group 02 | Katie   | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | member               | Active            |
      | Jaz     | Group 01 | administrator member | Active            |
      | Daniel  | Group 02 | member               | Active            |
    Given datasets:
      | title      | publisher | author  | published | description |
      | Dataset 01 | Group 01  | Gabriel | Yes       | Test        |
      | Dataset 02 | Group 02  | Daniel  | Yes       | Test        |
    And "Format" terms:
      | name    |
      | cvs     |
      | csv     |
    And resources:
      | title       | publisher | format | dataset    | author  | published | description               |
      | Resource 01 | Group 01  | cvs    | Dataset 01 | Gabriel | Yes       | The resource description. |
      | Resource 02 | Group 02  | cvs    | Dataset 02 | Daniel  | Yes       | The resource description. |
      | Resource 03 | Group 02  | csv    | Dataset 02 | Katie   | Yes       | The resource description. |

  # Don't remove! This is for avoiding issues when other scenarios are disabled (because of @noworkflow tag).
  Scenario: Dumb test
    Given I am on the homepage

  @api @javascript @noworkflow @datastore
  Scenario: Adding and Removing items from the datastore
    Given I am logged in as a user with the "site manager" role
    And I am on "dataset/dataset-01"
    When I click "Resource 01"
    And I click "Edit"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/district_centerpoints_small.csv"
    And I press "Save"
    And I am on "dataset/dataset-01"
    And I click "Resource 01"
    And I wait for "The resource description."
    And I click "Manage Datastore"
    And I press "Save"
    And I wait for "Status:"
    And I press "Import"
    Then I wait for "Data Importing: Done"
    When I click "Drop Datastore"
    And I wait for "This operation will destroy the db table"
    And I press "Drop"
    And I wait for "has been successfully dropped"
    Then I should see "Records Imported: 0"
    And I should see "Data Importing: Ready"

  @api @noworkflow @datastore @javascript @fixme
  Scenario: Import a csv tab delimited file.
    Given endpoints:
      | name             | path                   |
      | dataset rest api | /api/dataset           |
    And I use the "dataset rest api" endpoint to login with user "admin" and pass "admin"
    And I use the "dataset rest api" endpoint to attach the file "dkan/TAB_delimiter_large_raw_number.csv" to "Resource 03"
    And I am logged in as a user with the "site manager" role
    When I am on "dataset/dataset-02"
    And I click "Resource 03"
    And I click "Edit"
    And I press "Save"
    Then I should see "Resource Resource 03 has been updated"
    And I click "Manage Datastore"
    #Then I wait for "DKAN Datastore File: Status"
    And I select "TAB" from "edit-feedscsvparser-delimiter"
    And I press "Import"
    And I wait for "5" seconds
    Then I should see "5 imported items total."
    When I click "View"
    And I wait for "5" seconds
    Then I should see exactly "30" ".slick-cell" in region "recline preview"
