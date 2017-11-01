# time:0m21.63s
@disablecaptcha
Feature: Resource

  Background:
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
      | Badmin  | admin@example.com   | site manager         |
      | Gabriel | gabriel@example.com | content creator      |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | content creator      |
      | Martin  | martin@example.com  | editor               |
      | Celeste | celeste@example.com | editor               |
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
      | title       | publisher | format | dataset    | author   | published | description |
      | Resource 01 | Group 01  | csv    | Dataset 01 | Katie    | Yes       | Old Body    |
      | Resource 02 | Group 01  | csv    | Dataset 01 | Katie    | Yes       | No          |
      | Resource 03 | Group 01  | csv    | Dataset 02 | Celeste  | No        | Yes         |
      | Resource 04 | Group 01  | csv    | Dataset 01 | Katie    | No        | Yes         |
      | Resource 05 | Group 01  | csv    | Dataset 02 | Celeste  | Yes       | Yes         |

  @resource_all_01 @api
  Scenario: View published resource
    Given I am on "Dataset 01" page
    When I click "Resource 01"
    Then I am on the "Resource 01" page

  @resource_all_02 @api @fixme @testBug
    # TODO: Visualization for resources is added, and accessible on the resource's page
    #       Checking the visualization of a resource being correct is not yet defined, need feedback
    #       For example, how should the graph be verified that it is a graph and correctly displaying the data?
    #       Check against a base graph and see if it matches? Seems like it would be complicated to verify
  Scenario: View published resource data as a Graph
    Given I am on "Resource 01" page
    When I click "Graph"
    Then I should view the "resource" content as "graph"

  @resource_all_03 @api @fixme @testBug
    # TODO: Visualization for resources is added, and accessible on the resource's page
    #       Checking the visualization of a resource being correct is not yet defined, need feedback
    #       For example, how should the grid be verified that it is a grid and correctly displaying the data?
    #       Check against a base grid and see if it matches? Seems like it would be complicated to verify
  Scenario: View published resource data as a Grid
    Given I am on "Resource 01" page
    When I click "Grid"
    Then I should view the "resource" content as "grid"

  @resource_all_04 @api @fixme @testBug
    # TODO: Visualization for resources is added, and accessible on the resource's page
    #       Checking the visualization of a resource being correct is not yet defined, need feedback
    #       For example, how should the map be verified that it is a map and correctly displaying the data?
    #       Check against a base map and see if it matches? Seems like it would be complicated to verify
  Scenario: View published resource data as Map
    Given I am on "Resource 01" page
    When I click "Map"
    Then I should view the "resource" content as "map"

  @resource_all_05 @api @fixme @testBug
    #TODO: Need to have test data api set up for new resources for this test
    #      This functionality is tested in another module, test again here?
    #      See:     https://github.com/GetDKAN/dkan_datastore/blob/7.x-1.x/tests/dkan_datastore.test
    #      And See: https://github.com/GetDKAN/dkan_dataset/compare/310_dataset_rest_api
  Scenario: View the Data API information for a published resource
    Given I am on "Resource 02" page
    When I click "Data API"
    Then I should see "The Resource ID for this resource is"
    And I should see "Example Query"

  @resource_all_06 @api @noworkflow 
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

  @resource_all_07 @api @noworkflow
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

  @resource_all_08 @api @fixme @testBug
    #TODO: Needs definition and feedback, not sure where to test this
    #       Does this get tested with the visualization tests for maps?
  Scenario: View resource data on map automatically if lat and long info is present
    Given I am on the homepage

  @resource_all_09 @api
  Scenario: View dataset reference on Resource teaser
    Given I am on "/search"
    And I click "Resource"
    Then I should see "Dataset 01"

  @resource_all_10 @api @noworkflow
  Scenario: Data previews when only local enabled
    Given cartodb previews are disabled for csv resources
    And I am on "Dataset 01" page
    Then I should see "Preview"
    And I should not see "Open with"

  @resource_all_011 @api @noworkflow @fixme
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
      | title                    | publisher | format | dataset | author | published | description |
      | Resource Without Dataset | Group 01  | csv    |         | Katie  | Yes       | Old Body    |
    And I am on "Resource Without Dataset" page
    Then I should not see the link "Back to dataset"
    And I should see "Groups"

