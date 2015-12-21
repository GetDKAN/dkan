@api
Feature: Data Stories

  Background:
    Given users:
      | name    | mail                | roles           |
      | Jaz     | jaz@example.com     | content creator |
    And "dkan_data_story" content:
      | title                           | author      | status   |
      | DKAN Data Story Test Story Post | Jaz         | 1        |
      | Unpublished Test Story Post     | Jaz         | 1        |

  Scenario: Menu Item
    Given I am on the homepage
    Then I should see "Stories"

  @fixme
  Scenario: Create Story Content
    And I am logged in as "Jaz"
    When I am on "/node/add/dkan-data-story"
    And I fill in "edit-title" with "Creating Data Story Test Story Post"
    And I fill in "body[und][0][value]" with "Test description"
    And I press "Save"
    Then I should see "Your DKAN Data Story 'DKAN Data Story Test Story Post' has been created"
    Then I should be able to use the Panels In Place Editor

  @javascript
  Scenario: Edit own story content
    And I am logged in as "Jaz"
    When I am on "story/dkan-data-story-test-story-post"
    And I click "Edit"
    And I fill in "body[und][0][value]" with "Test description Update"
    And I press "Save"
    Then I should see "DKAN Data Story Test Story Post has been updated"

  @javascript
  Scenario: Delete own story content
    And I am logged in as "Jaz"
    When I am on "story/dkan-data-story-test-story-post"
    And I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "DKAN Data Story Test Story Post has been deleted"
