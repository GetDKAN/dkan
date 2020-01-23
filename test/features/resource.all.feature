# time:0m31.63s
@disablecaptcha @smoketest_noworkflow
Feature: Resource

  Background:
    Given users:
      | name    | mail                | roles             |
      | Katie   | katie@example.com   | content creator   |
    And datasets:
      | title      | author  | published    | description |
      | Dataset 01 | Katie   | Yes          | Test        |
    And resources:
      | title       | format | dataset    | author   | published | description |
      | Resource 01 | csv    | Dataset 01 | Katie    | Yes       | Old Body    |

  @resource_all_01 @api
  Scenario: View published resource
    Given I am on "Dataset 01" page
    When I click "Resource 01"
    Then I am on the "Resource 01" page

  @resource_all_06 @api
  Scenario: View previous revisions of published resource
    Given I am logged in as a user with the "administrator" role
    And I am on "Resource 01" page
    And I click "Edit"
    And I should not see "Groups" in the "content" region
    And I fill in "Test" for "Description"
    And I press "Save"
    And I am an anonymous user
    Given I am on "Resource 01" page
    When I click "Revisions"
    Then I should see "This is the published revision"

  @resource_all_07 @api
  Scenario: Compare revisions of published resource
    Given I am logged in as a user with the "administrator" role
    And I am on "Resource 01" page
    And I click "Edit"
    And I fill in "Test" for "Description"
    And I press "Save"
    And I am an anonymous user
    Given I am on "Resource 01" page
    And I click "Revisions"
    And I press "Compare"
    Then I should see "Old Body"

  @resource_all_09 @api
  Scenario: View dataset reference on Resource teaser
    Given I am on "/search"
    And I click "Resource" in the "facet container" region
    And I fill in "edit-query" with "Resource 01"
    And I press "Apply"
    Then I should see "Dataset 01"

  @resource_all_10 @api
  Scenario: Data previews when only local enabled
    Given cartodb previews are disabled for csv resources
    And I am on "Dataset 01" page
    Then I should see "Preview"
    And I should not see "Open with"

  @resource_all_011 @api @fixme
  #TODO: This test was relying on default dkan content so we needed to fix it, in the next lines there is
  #      an approach but it doesn't work because of a bug in which the carto db previews are not working
  #      for resources which uses linked files.
  Scenario: Open data previews in external services
    Given cartodb previews are enabled for csv resources
    And I am logged in as a user with the "site manager" role
    And I am on "/dataset/dataset-01"
    When I click "Resource 01"
    Then I should see "Edit"
    When I click "Edit"
    ## If you use selenium uncomment this
    # And I click "Remote file"
    And I fill in "edit-field-link-remote-file-und-0-filefield-dkan-remotefile-url" with "https://s3.amazonaws.com/dkan-default-content-files/files/district_centerpoints_0.csv"
    And I press "edit-submit"
    When I am on "/dataset/dataset-01"
    Then I should see "Open With"
    When I press "Open With"
    Then I should see the local preview link
    And I should see "CartoDB"

  @resource_all_12 @api
  Scenario: Hide "Back to dataset" button AND show the groups field on resources that do not belong to a dataset
    Given resources:
      | title                    | format | dataset | author | published | description |
      | Resource Without Dataset | csv    |         | Katie  | Yes       | Old Body    |
    And I am logged in as a user with the "site manager" role
    And I am on "Resource Without Dataset" page
    Then I should not see the link "Back to dataset"
    When I click "Edit"
    Then I should see "Groups" in the "content" region

  @resource_all_13 @api @add_filehash @remove_filehash
  Scenario: View SHA-512 for resource with uploaded file when filehash is enabled and set create SHA-512
    Given I am logged in as a user with the "content creator" role
    And I am on "/node/add"
    And I click "Resource"
    And I attach the drupal file "dkan/TAB_delimiter_large_raw_number.tsv" to "files[field_upload_und_0]"
    When I fill in "Title" with "Resource TSV"
    And I press "Save"
    Then I should see "SHA512"
