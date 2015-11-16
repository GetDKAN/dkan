
Feature: Portal Administrators administer groups
  In order to manage site organization
  As a Portal Administrator
  I want to administer groups

  Portal administrators needs to be able to create, edit, and delete
  groups. They need to be able to set group membership by adding and removing
  users and setting group roles and permissions.


  Background:
    Given pages:
      | title     | url             |
      | Groups    | /groups         |
      | Content   | /admin/content/ |
    Given users:
      | name    | mail             | roles                |
      | John    | john@example.com    | administrator        |
      | Badmin  | admin@example.com   | administrator        |
      | Gabriel | gabriel@example.com | editor               |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | editor               |
      | Martin  | martin@example.com  | editor               |
      | Celeste | celeste@example.com | editor               |
    Given groups:
      | title    | author | published |
      | Group 01 | Badmin | Yes       |
      | Group 02 | Badmin | Yes       |
      | Group 03 | Badmin | No        |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
      | Jaz     | Group 01 | member               | Pending           |
      | Celeste | Group 02 | member               | Active            |
    And datasets:
      | title      | publisher | tags       | author  | published | description                |
      | Dataset 01 | Group 01  | price      | Katie   | Yes       | Increase of toy prices     |
      | Dataset 02 | Group 01  | price      | Katie   | No        | Cost of oil in January     |
      | Dataset 03 | Group 01  | election   | Gabriel | Yes       | Election districts         |
    And resources:
      | title       | publisher | format | author | published | dataset    | description |
      | Resource 01 | Group 01  | csv    | Katie  | Yes       | Dataset 01 |             |
      | Resource 02 | Group 01  | html   | Katie  | Yes       | Dataset 01 |             |

  @api
  Scenario: View the list of published groups
    Given I am on the homepage
    When I follow "Groups"
    Then I should see "2" items in the "groups" region
    And I should not see "Group 03"

  @api
  Scenario: View the details of a published group
    Given I am on "Groups" page
    When I follow "Group 01"
    Then I should see the "Group 01" detail page

  @api @javascript
  Scenario: View the list of datasets on a group
    Given I am on "Group 01" page
    When I click "Datasets" in the "group information" region
    Then I should see "2" items in the "group datasets" region

  @api @javascript
  Scenario: View the number of datasets on group
    Given I am on "Group 01" page
    When I click "Datasets" in the "group information" region
    Then I should see "2 datasets" in the "group datasets" region

  @api @javascript
  Scenario: View the list of group members
    Given I am on "Group 01" page
    When I click "Members" in the "group information" region
    Then I should see "Gabriel" in the "group members" region
    And I should see "Katie" in the "group members" region
    And I should not see "Jaz" in the "group members" region
    And I should not see "John" in the "group members" region

  @api @javascript
  Scenario: Search datasets on group
    Given I am on "Group 01" page
    And I click "Datasets" in the "group information" region
    When I fill in "toy" for "Search" in the "group datasets" region
    And I press "Apply"
    Then I should see "1 datasets" in the "group datasets" region
    And I should see "1" items in the "groups datasets" region

  # TODO: The filter for resource formats does not appear on the dataset listing page for
  #       datasets created by behat despite having a resource with a valid format,
  #       perhaps an indexing issue with resources?

  @api @javascript @fixme
  Scenario: View available "resource format" filters after search
    Given I am on "Group 01" page
    And I click "Datasets" in the "group information" region
    When I fill in "Dataset" for "Search" in the "group datasets" region
    And I press "Apply"
    Then I should see "csv (1)" in the "filter by resource format" region
    And I should see "html (1)" in the "filter by resource format" region

  @api @javascript
  Scenario: View available "author" filters after search
    Given I am on "Group 01" page
    And I click "Datasets" in the "group information" region
    When I fill in "Dataset" for "Search" in the "group datasets" region
    And I press "Apply"
    Then I should see "Katie (1)" in the "filter by author" region
    And I should see "Gabriel (1)" in the "filter by author" region

  @api @javascript
  Scenario: View available "tag" filters after search
    Given I am on "Group 01" page
    And I click "Datasets" in the "group information" region
    When I fill in "Dataset" for "Search" in the "group datasets" region
    And I press "Apply"
    Then I should see "price (1)" in the "filter by tag" region
    And I should see "election (1)" in the "filter by tag" region


  # TODO: The filter for resource formats does not appear on the dataset listing page for
  #       datasets created by behat despite having a resource with a valid format,
  #       perhaps an indexing issue with resources?

  @api @javascript @fixme
  Scenario: Filter datasets on group by resource format
    Given I am on "Group 01" page
    And I click "Datasets" in the "group information" region
    When I fill in "Dataset" for "Search" in the "group datasets" region
    And I press "Apply"
    When I click "csv (1)" in the "filter by resource format" region
    Then I should see "1 datasets" in the "group datasets" region
    And I should see "1" items in the "groups datasets" region

  @api @javascript
  Scenario: Filter datasets on group by author
    Given I am on "Group 01" page
    And I click "Datasets" in the "group information" region
    When I fill in "Dataset" for "Search" in the "group datasets" region
    And I press "Apply"
    When I click "Katie" in the "filter by author" region
    Then I should see "1 datasets" in the "group datasets" region
    And I should see "1" items in the "groups datasets" region

  @api @javascript
  Scenario: Filter datasets on group by tags
    Given I am on "Group 01" page
    And I click "Datasets" in the "group information" region
    When I fill in "Dataset" for "Search" in the "group datasets" region
    And I press "Apply"
    When I click "price" in the "filter by tag" region
    Then I should see "1 datasets" in the "group datasets" region
    And I should see "1" items in the "groups datasets" region
