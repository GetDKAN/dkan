@api @collections
Feature: Dataset Collections
  For testing dataset collections functionality.

  Additional text...

  Background:
    Given pages:
      | name        | url               |
      | Datasets    | /dataset          |
      | Add Dataset | /node/add/dataset |
      | Search      | /search           |
    Given users:
      | name  | mail              | roles           |
      | Katie | katie@example.com | content creator |
    Given groups:
      | title    | author | published |
      | Group 01 | Katie  | Yes       |
    And group memberships:
      | user  | group    | role on group | membership status |
      | Katie | Group 01 | member        | Active            |

  @collection
  Scenario: Create dataset as content creator
    # Create collection parent
    Given I am logged in as Katie
    And I am on "Add Dataset" page
    And I fill-in the following:
      | title       | Test Dataset1    |
      | description | Test description |
      | publisher   | Group 01         |
    And I press "Next: Add data"
    And I fill in "title" with "Test Resource Link File1"
    And I press "Save"
    Then I should see "Test Dataset1"

    # Associate child 1 with parent and make sure it links
    Then I am on "Add Dataset" page
    And I fill-in the following:
      | title       | Test Dataset2     |
      | description | Test description2 |
      | publisher   | Group 01          |
    And I fill in "field_dkan_ispartof_ref[und][0][target_id]" with "Test Dataset1"
    And I press "Next: Add data"
    And I fill in "title" with "Test Resource Link File2"
    And I press "Save"
    Then I click "Test Dataset2"
    Then I should see "Test Dataset2"
    And I should see "Test Dataset1"

    # Associate child 2 with parent and make sure it links
    Then I am on "Add Dataset" page
    And I fill-in the following:
      | title       | Test Dataset3     |
      | description | Test description3 |
      | publisher   | Group 01          |
    And I fill in "field_dkan_ispartof_ref[und][0][target_id]" with "Test Dataset1"
    And I press "Next: Add data"
    And I fill in "title" with "Test Resource Link File3"
    And I press "Save"
    Then I click "Test Dataset3"
    Then I should see "Test Dataset3"
    And I should see "Test Dataset1"

    # Make sure parent has children
    Then I click "Test Dataset1"
    Then I should see "Collection"
    And I should see "Test Dataset2"
    And I should see "Test Dataset3"

    # Check search facet & view presentation
    Then I am on "Search"
    Then I should see "Filter by Collection"
    And I click "Test Dataset1"
    And I should see "Test Dataset2"
    And I should see "Test Dataset3"





