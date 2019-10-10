# time:4m30.30s
@api @disablecaptcha
Feature: Resource

  Background:
    Given pages:
      | name          | url           |
      | Content       | /node/add     |
      | User          | /user         |
    Given users:
      | name    | mail                | roles             |
      | John    | john@example.com    | site manager      |
      | Katie   | katie@example.com   | content creator   |
      | Celeste | celeste@example.com | editor            |

  @resource_cc_01 @smoketest_noworkflow
  Scenario: Create resource
    Given I am logged in as a user with the "content creator" role
    And I am on the "Content" page
    And I click "Resource"
    ## If you use selenium uncomment this
    # And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/files/datastore-simple.csv"
    When I fill in "Title" with "Resource 06"
    And I press "Save"
    Then I should see "Resource Resource 06 has been created"

  @resource_cc_02
  Scenario: See warning if full url not given when using the api/url option.
    Given I am logged in as a user with the "content creator" role
    And I am on the "Content" page
    And I click "Resource"
    And I fill in "edit-field-link-api-und-0-url" with "api.tiles.mapbox.com/v3/tmcw.map-gdv4cswo/markers.geojson"
    When I fill in "Title" with "Resource api"
    And I press "Save"
    Then I should see "Please enter a full url"

  @resource_cc_03 @javascript
  Scenario: Create resource with too many sources.
    Given I am logged in as a user with the "content creator" role
    And I am on the "Content" page
    And I click "Resource"
    And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/files/datastore-simple3.csv"
    And I click "API or Website URL"
    And I fill in "edit-field-link-api-und-0-url" with "http://earthquake.usgs.gov/fdsnws/event/1/query?format=geojson&starttime=2014-01-01&endtime=2014-01-02"
    When I fill in "Title" with "Resource 06"
    And I press "Save"
    Then I should see "Remote file is populated - only one resource type can be used at a time"
    And I should see "API or Website URL is populated - only one resource type can be used at a time"

  @resource_cc_04 @javascript
  Scenario: Edit own resource as content creator
    Given resources:
      | title       | author   | published | description |
      | Resource 01 | Katie    | Yes       | No content  |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "Title" with "Resource 01 edited"
    And I press "Save"
    Then I should see "Resource Resource 01 edited has been updated"
    When I am on "User" page
    Then I should see "Resource 01 edited"

  @resource_cc_05
  Scenario: Delete own resource
    Given resources:
      | title       | format | author   | published | description |
      | Resource 01 | csv    | Katie    | Yes       | No content  |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Resource 01 has been deleted"

  @resource_cc_06
  Scenario: Change dataset on resource
    Given groups:
      | title    | author  | published |
      | Group 01 | John    | Yes       |
    And group memberships:
      | user    | group    | role on group  | membership status |
      | Katie   | Group 01 | member         | Active            |
    And datasets:
      | title      | publisher | author  | published    | description |
      | Dataset 01 | Group 01  | John    | Yes          | Test        |
      | Dataset 02 | Group 01  | John    | Yes          | Test        |
    And resources:
      | title       | publisher | format | dataset    | author   | published | description |
      | Resource 01 | Group 01  | csv    | Dataset 01 | Katie    | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "field_dataset_ref[und][0][target_id]" with "Dataset 02"
    And I press "Save"
    Then I should see "Resource 01 has been updated"
    When I click "Back to dataset"
    Then I should see "Dataset 02" in the "dataset title" region
    And I should see "Resource 01" in the "dataset resource list" region

  @resource_cc_07
  Scenario: Add a resource with no datasets to a dataset with no resource
    Given groups:
      | title    | author  | published |
      | Group 01 | John    | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | John    | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
    And datasets:
      | title      | publisher | author  | published  | description |
      | Dataset 01 | Group 01  | John    | Yes        | Test        |
    And resources:
      | title       | author   | published | description |
      | Resource 01 | Katie    | Yes       | No content  |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "field_dataset_ref[und][0][target_id]" with "Dataset 01"
    And I press "Save"
    Then I should see "Resource 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I click "Back to dataset"
    Then I should see "Dataset 01" in the "dataset title" region
    And I should see "Resource 01" in the "dataset resource list" region

  @resource_cc_08
  Scenario: Remove a resource with only one dataset from the dataset
    Given groups:
      | title    | author  | published |
      | Group 01 | John    | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | John    | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
    And datasets:
      | title      | publisher | author  | published    | description |
      | Dataset 01 | Group 01  | John    | Yes          | Test        |
    And resources:
      | title       | publisher | format | dataset    | author   | published | description |
      | Resource 01 | Group 01  | csv    | Dataset 01 | Katie    | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "edit-field-dataset-ref-und-0-target-id" with ""
    And I press "Save"
    Then I should see "Resource 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    And I should not see the link "Back to dataset"

  @resource_cc_09
  Scenario: Add a resource with no group to a dataset with group
    Given groups:
      | title    | author  | published |
      | Group 01 | John    | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | John    | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
    And datasets:
      | title      | publisher | author  | published    | description |
      | Dataset 01 | Group 01  | John    | Yes          | Test        |
    And resources:
      | title       | format | author   | published | description |
      | Resource 01 | csv    | Katie    | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "field_dataset_ref[und][0][target_id]" with "Dataset 01"
    And I press "Save"
    Then I should see "Resource 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"

  @resource_cc_10
  Scenario: Remove a resource from a dataset with group
    Given groups:
      | title    | author  | published |
      | Group 01 | John    | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | John    | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
    And datasets:
      | title      | publisher | author  | published    | description |
      | Dataset 01 | Group 01  | John    | Yes          | Test        |
    And resources:
      | title       | publisher | format | dataset    | author   | published | description |
      | Resource 01 | Group 01  | csv    | Dataset 01 | Katie    | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "edit-field-dataset-ref-und-0-target-id" with ""
    And I press "Save"
    Then I should see "Resource 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I am on "Dataset 01" page
    Then I should not see "Resource 01" in the "dataset resource list" region

  @resource_cc_11
  Scenario: Add a resource to multiple datasets with groups
    Given groups:
      | title    | author  | published |
      | Group 01 | John    | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | John    | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
    And datasets:
      | title      | publisher | author  | published    | description |
      | Dataset 01 | Group 01  | John    | Yes          | Test        |
      | Dataset 02 | Group 01  | John    | Yes          | Test        |
    And resources:
      | title       | format | author   | published | description |
      | Resource 01 | csv    | Katie    | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "field_dataset_ref[und][0][target_id]" with "Dataset 01"
    And I press "Add another item"
    And I fill in "field_dataset_ref[und][0][target_id]" with "Dataset 02"
    And I press "Save"
    Then I should see "Resource 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"

  @resource_cc_12
  Scenario: Remove one dataset with group from resource with multiple datasets
    Given groups:
      | title    | author  | published |
      | Group 01 | John    | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | John    | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
    And datasets:
      | title      | publisher | author  | published    | description |
      | Dataset 01 | Group 01  | John    | Yes          | Test        |
      | Dataset 02 | Group 01  | John    | Yes          | Test        |
    And resources:
      | title       | format | author   | published | description |
      | Resource 01 | csv    | Katie    | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "field_dataset_ref[und][0][target_id]" with "Dataset 01"
    And I press "Add another item"
    And I fill in "field_dataset_ref[und][0][target_id]" with "Dataset 02"
    And I press "Save"
    Then I should see "Resource 01 has been updated"
    When I click "Edit"
    And I fill in "edit-field-dataset-ref-und-0-target-id" with ""
    And I press "Save"
    Then I should see "Resource 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"

  @resource_cc_13
  Scenario: Remove all datasets with groups from resource
    Given groups:
      | title    | author  | published |
      | Group 01 | John    | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | John    | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
    And datasets:
      | title      | publisher | author  | published    | description |
      | Dataset 01 | Group 01  | John    | Yes          | Test        |
      | Dataset 02 | Group 01  | John    | Yes          | Test        |
    And resources:
      | title       | format | author   | published | description |
      | Resource 01 | csv    | Katie    | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "field_dataset_ref[und][0][target_id]" with "Dataset 01"
    And I press "Add another item"
    And I fill in "field_dataset_ref[und][0][target_id]" with "Dataset 02"
    And I press "Save"
    Then I should see "Resource 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I click "Edit"
    And I fill in "edit-field-dataset-ref-und-0-target-id" with ""
    And I fill in "edit-field-dataset-ref-und-1-target-id" with ""
    And I press "Save"
    Then I should see "Resource 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"

  @resource_cc_18
  Scenario: Add revision to own resource
    Given resources:
      | title       | author   | published | description |
      | Resource 01 | Katie    | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I fill in "title" with "Resource 01 edited"
    And I press "Save"
    Then I should see "Resource Resource 01 edited has been updated"
    When I click "Revisions"
    Then I should see "by Katie"

  # @todo Add test for URL w/o .csv
  # We need to edit and save to trigger auto type discover
  @resource_cc_19 @javascript
  Scenario: Remote CSV preview
    Given resources:
      | title       | author   | published | description | link file |
      | Resource 01 | Katie    | Yes       | Test        | https://s3.amazonaws.com/dkan-default-content-files/files/district_centerpoints_0.csv |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I press "Save"
    Then I should see a recline preview

  @resource_cc_20 @javascript
  Scenario: Image preview
    Given resources:
      | title       | author   | published | description | link file |
      | Resource 01 | Katie    | Yes       | Test        | https://s3.amazonaws.com/dkan-default-content-files/files/geography.png |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I press "Save"
    Then I should see a image preview

  @resource_cc_21
  Scenario: ZIP preview
    Given resources:
      | title       | author   | published | description | link file |
      | Resource 01 | Katie    | Yes       | Test        | https://s3.amazonaws.com/dkan-default-content-files/files/metadata.zip |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I press "Save"
    Then I should see a zip preview

  @resource_cc_22 @javascript
  Scenario: XML preview
    Given resources:
      | title       | author   | published | description | link file |
      | Resource 01 | Katie    | Yes       | Test        | https://s3.amazonaws.com/dkan-default-content-files/files/catalog.xml |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I press "Save"
    Then I should see a xml preview

  @resource_cc_23
  Scenario: JSON preview
    Given resources:
      | title       | author   | published | description | link file |
      | Resource 01 | Katie    | Yes       | Test        | https://s3.amazonaws.com/dkan-default-content-files/files/data.json |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I press "Save"
    Then I should see a json preview

  @resource_cc_24
  Scenario: GEOJSON preview
    Given resources:
      | title       | author   | published | description | link file |
      | Resource 01 | Katie    | Yes       | Test        | https://s3.amazonaws.com/dkan-default-content-files/files/USA.geo.json |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I press "Save"
    Then I should see a geojson preview

  @resource_cc_25 @javascript
  Scenario: Generated CSV preview
    Given resources:
      | title       | author   | published | description | link file |
      | Resource 01 | Katie    | Yes       | Test        | https://data.wa.gov/api/views/mu24-67ke/rows.csv?accessType=DOWNLOAD |
    Given I am logged in as "Katie"
    And I am on "Resource 01" page
    When I click "Edit"
    And I press "Save"
    Then I should see a recline preview

  @resource_cc_26 @javascript @fixme
  # Can not choose delimiter for remote files, only uploads allow this.
  Scenario: Create resource with a tsv file
    Given I am logged in as "John"
    And I am on the "Content" page
    And I click "Resource"
    And I attach the drupal file "dkan/TAB_delimiter_large_raw_number.tsv" to "files[field_upload_und_0]"
    When I fill in "Title" with "Resource TSV"
    # See if tab is an option in the delimiter dropdown.
    And I select "tab" from "Delimiter"
    And I press "Save"
    Then I should see "Resource Resource TSV has been created"
    Then I should see a recline preview
    # Make sure it autodetects the format.
    When I click "Edit"
    Then the "field_format[und][textfield]" field should contain "tsv"

  @resource_cc_27 @javascript @fixme
  # Can not choose delimiter for remote files, only uploads allow this.
  Scenario: Create resource with a tab file
    Given I am logged in as "John"
    And I am on the "Content" page
    And I click "Resource"
    And I attach the drupal file "dkan/TAB_delimiter_large_raw_number.tab" to "files[field_upload_und_0]"
    When I fill in "Title" with "Resource TAB"
    # See if tab is an option in the delimiter dropdown.
    And I select "tab" from "Delimiter"
    And I press "Save"
    Then I should see "Resource Resource TAB has been created"
    Then I should see a recline preview
    # Make sure it autodetects the format.
    When I click "Edit"
    Then the "field_format[und][textfield]" field should contain "tsv"

  @resource_cc_28
  Scenario: Content Creators and Editors should see publishing options tab but not authored by tab
    Given I am logged in as a user with the "content creator" role
    When I visit "node/add/resource"
    Then I should not see "Authoring information"
    Then I should see "Publishing options"
    Given I am logged in as a user with the "editor" role
    When I visit "node/add/resource"
    Then I should not see "Authoring information"
    Then I should see "Publishing options"

  @resource_cc_29 @noworkflow @javascript
  Scenario: Add a data dictionary
    Given resources:
      | title       | author   | published | description |
      | Resource 09 | Katie    | Yes       | Test        |
    Given I am logged in as "John"
    And I am on "Resource 09" page
    And I click "Edit"
    And I click "JSON Schema"
    And I enter '{"fields":[{"name":"Lorem Ipsum","type":"string","description":"Dolor sit amet"}]}' into the JSONEditor
    And I press "Save"
    Then I should see "Dolor sit amet"
    When I click "Edit"
    And I click "JSON Schema"
    Then I should see "Dolor sit amet"
