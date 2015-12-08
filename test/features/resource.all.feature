@javascript
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
      | Health  |
      | Gov     |
    And datasets:
      | title      | publisher | author  | published        | tags     | description |
      | Dataset 01 | Group 01  | Gabriel | Yes              | Health   | Test        |
      | Dataset 02 | Group 01  | Gabriel | Yes              | Gov      | Test        |
    And "Format" terms:
      | name    |
      | cvs     |
      | xls     |
    And resources:
      | title       | publisher | format | dataset    | author   | published | description |
      | Resource 01 | Group 01  | cvs    | Dataset 01 | Katie    | Yes       | No          |
      | Resource 02 | Group 01  | xls    | Dataset 01 | Katie    | Yes       | No          |
      | Resource 03 | Group 01  | xls    | Dataset 02 | Celeste  | No        | Yes         |
      | Resource 04 | Group 01  | cvs    | Dataset 01 | Katie    | No        | Yes         |
      | Resource 05 | Group 01  | xls    | Dataset 02 | Celeste  | Yes       | Yes         |

  @api
  Scenario: View published resource
    Given I am on the homepage
    And I follow "Datasets"
    And I click "Dataset 01"
    When I click "Resource 01"
    Then I am on the "Resource 01" page

  @api @fixme @testBug
    # TODO: Checking the visualization of a resource being correct is not yet defined, need feedback
  Scenario: View published resource data as a Graph
    Given I am on "Resource 01" page
    When I click "Graph"
    Then I should view the "resource" content as "graph"

  @api @fixme @testBug
    # TODO: Checking the visualization of a resource being correct is not yet defined, need feedback
  Scenario: View published resource data as a Grid
    Given I am on "Resource 01" page
    When I click "Grid"
    Then I should view the "resource" content as "grid"

  @api @fixme @testBug
    # TODO: Checking the visualization of a resource being correct is not yet defined, need feedback
  Scenario: View published resource data as Map
    Given I am on "Resource 01" page
    When I click "Map"
    Then I should view the "resource" content as "map"

  @api @fixme @testBug
    #TODO: NEED TO have test data api set up for new resources
  Scenario: View the Data API information for a published resource
    Given I am on "Resource 02" page
    When I click "Data API"
    Then I should see "The Resource ID for this resource is"
    And I should see "Example Query"

  @api @fixme @dkanBug
    #TODO: Permissions for anon users to see revisions to be added in future release'
    # See NuCivic/pluto/issues/132#issuecomment-161743679
  Scenario: View previous revisions of published resource
    Given I am on "Resource 01" page
    When I click "Revisions"
    Then I should see the list of revisions

  @api @fixme @dkanBug
    #TODO: Permissions for anon users to see revisions to be added in future release'
    # See NuCivic/pluto/issues/132#issuecomment-161743679
  Scenario: Compare revisions of published resource
    Given I am on "Resource 01" page
    And I press "Revisions"
    When I select "revision 1"
    And I select "revision 2"
    And I press "Compare"
    Then I should see the revisions diff

  @api @fixme @testBug
    #TODO: Needs definition and feedback
  Scenario: View resource data on map automatically if lat and long info is present
    Given I am on the homepage
