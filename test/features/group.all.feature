# time:1m1.50s
@api @smoketest
Feature: Site Manager administer groups

  Background:
    Given pages:
      | name      | url             |
      | Groups    | /groups         |
    Given users:
      | name    | mail                | roles         |
      | Badmin  | admin@example.com   | site manager  |
      | Celeste | celeste@example.com | editor        |
    Given groups:
      | title    | author | published | description              |
      | Group 01 | Badmin | Yes       | This is the group 1 page |
      | Group 02 | Badmin | Yes       | This is the group 2 page |
      | Group 03 | Badmin | No        | This is the group 3 page |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Celeste | Group 02 | member               | Active            |

  @group_all_01
  Scenario: View the list of published groups
    Given I am on "Groups" page
    Then I should see "Group 01"
    And I should see "Group 02"
    And I should not see "Group 03"

  @group_all_02
  Scenario: View the details of a published group
    Given "Topics" terms:
      | name             |
      | Education02      |
    And Datasets:
      | title      | publisher | author  | published | description                | topics           |
      | Dataset 03 | Group 02  | Celeste | Yes       | Test dataset counts        | Education02      |
    And I am on "Groups" page
    When I follow "Group 02"
    Then I should see "This is the group 2 page"
    And I should see "1 datasets" in the "content" region
    And I should see "Group 02" in the ".group-membership" element
    And I should see "Education02" in the ".name" element

  @group_all_04
  Scenario: View the correct count of datasets
    Given Datasets:
      | title      | publisher | author  | published | description                |
      | Dataset 04 | Group 02  | Celeste | Yes       | Test dataset counts        |
      | Dataset 05 | Group 02  | Celeste | Yes       | Test dataset counts        |
      | Dataset 06 | Group 02  | Celeste | Yes       | Test dataset counts        |
      | Dataset 07 | Group 02  | Celeste | Yes       | Test dataset counts        |
      | Dataset 08 | Group 02  | Celeste | Yes       | Test dataset counts        |
      | Dataset 09 | Group 02  | Celeste | Yes       | Test dataset counts        |
      | Dataset 10 | Group 02  | Celeste | Yes       | Test dataset counts        |
      | Dataset 11 | Group 02  | Celeste | Yes       | Test dataset counts        |
      | Dataset 12 | Group 02  | Celeste | Yes       | Test dataset counts        |
      | Dataset 13 | Group 02  | Celeste | Yes       | Test dataset counts        |
      | Dataset 14 | Group 02  | Celeste | Yes       | Test dataset counts        |
    And I am on "Groups" page
    Then I should see "11 datasets"
    When I click "11 datasets"
    Then I should see "Displaying 1 - 10 of 11 datasets"


