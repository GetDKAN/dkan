@javascript
Feature: Resource

  Background:
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | administrator        |
      | Badmin  | admin@example.com   | administrator        |
      | Gabriel | gabriel@example.com | authenticated user   |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | authenticated user   |
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
    # License information should be shown
    Then I am on the "Resource 01" page

  # TODO: DKAN Extension does not currently support visualization entities for resources
  #       and should be added in later

  @api @fixme
  Scenario: View published resource data as a Graph
    Given I am on "Resource 01" page
    When I click "Graph"
    Then I should view the "resource" content as "graph"

  # TODO: DKAN Extension does not currently support visualization entities for resources
  #       and should be added in later

  @api @fixme
  Scenario: View published resource data as a Grid
    Given I am on "Resource 01" page
    When I click "Grid"
    Then I should view the "resource" content as "grid"

  # TODO: DKAN Extension does not currently support visualization entities for resources
  #       and should be added in later

  @api @fixme
  Scenario: View published resource data as Map
    Given I am on "Resource 01" page
    When I click "Map"
    Then I should view the "resource" content as "map"

  # TODO: DKAN Extension does not currently support visualization entities for resources
  #       and should be added in later

  @api @fixme
  Scenario: View the Data API information for a published resource
    Given I am on "Resource 01" page
    When I press "Data API"
    Then I should see "The Resource ID for this resource is"
    And I should see "Example Query"


  # TODO: Permissions for anonymous users to view revisions are not set (they cannot access revisions)

  @api @fixme
  Scenario: View previous revisions of published resource
    Given I am on "Resource 01" page
    When I click "Revisions"
    Then I should see the list of revisions

  # TODO: Permissions for anonymous users to view revisions are not set (they cannot access revisions)

  @api @fixme
  Scenario: Compare revisions of published resource
    Given I am on "Resource 01" page
    And I press "Revisions"
    When I select "revision 1"
    And I select "revision 2"
    And I press "Compare"
    Then I should see the revisions diff

  # TODO: Needs definition.

  @api @fixme
  Scenario: View resource data on map automatically if lat and long info is present
    Given I am on the homepage
