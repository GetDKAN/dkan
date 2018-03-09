# time:0m9.35s
@api @disablecaptcha
Feature: Data Stories

  Background:
    Given users:
      | name    | mail                | roles           |
      | Jaz001     | jaz@example.com     | content creator |
    And "dkan_data_story" content:
      | title                           | author      | status   |
      | DKAN Data Story Test Story Post | Jaz001         | 1        |
      | Unpublished Test Story Post     | Jaz001         | 1        |

  @customizable
  Scenario: Menu Item
    Given I am on the homepage
    Then I should see "Stories"

  Scenario: Create Story Content
    And I am logged in as "Jaz001"
    When I am on "/node/add/dkan-data-story"
    And I fill in "edit-title" with "Test Post"
    And I fill in "body[und][0][value]" with "Test description"
    And I press "Save"
    Then I should see "Your Data Story 'Test Post' has been created"
    Then I should not see "panels-ipe-region"

  Scenario: Edit own story content
    And I am logged in as "Jaz001"
    When I am on "story/dkan-data-story-test-story-post"
    And I click "Edit"
    And I fill in "body[und][0][value]" with "Test description Update"
    And I press "Save"
    Then I should see "DKAN Data Story Test Story Post has been updated"

  # @javascript @wip
  # Scenario: Add topic to a story
  #   And I am logged in as "Jaz"
  #   When I am on "story/dkan-data-story-test-story-post"
  #   And I click "Edit"
  #   And I wait for "Topics"
  #   And I fill in the chosen field "edit_field_topic_und_chosen" with "Education"
  #   And I press "Save"
  #   Then I should see "Education"

  Scenario: Delete own story content
    And I am logged in as "Jaz001"
    When I am on "story/dkan-data-story-test-story-post"
    And I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Data Story Test Story Post has been deleted"
