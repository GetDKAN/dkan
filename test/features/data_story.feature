@api
Feature: Data Stories

  Background:
    Given users:
      | name    | mail                | roles                |
      | Jaz     | jaz@example.com     | editor               |
    And "dkan_data_story" content:
      | title                           | author      | status   |
      | DKAN Data Story Test Story Post | Jaz         | 1        |
      | test Story Post                 | Jaz         | 0        |

  Scenario: Menu Item
    Given I am on the homepage
    Then I should see "Stories"

  Scenario: Stories Index
    Given I am on "/stories"
    Then I should see "No stories were found."

  Scenario: Can see the administration menu
    And I am logged in as "Jaz"
    When I am on the homepage
    Then I should see the administration menu

  Scenario: Can see administration pages
    And I am logged in as "Jaz"
    When I am on "/admin"
    Then I should see "Content"

  Scenario: Access content overview
    And I am logged in as "Jaz"
    When I am on "/admin/content"
    Then I should see "About"

  Scenario: Create Story Content
    And I am logged in as "Jaz"
    When I am on "/node/add/dkan-data-story"
    And I fill in "edit-title" with "DKAN Data Story Test Story Post"
    And I fill in "body[und][0][value]" with "Test description"
    And I press "Save"
    Then I should see "Your DKAN Data Story 'DKAN Data Story Test Story Post' has been created"

  Scenario: Delete own story content
    And I am logged in as "Jaz"
    When I am on "admin/content"
    And I click "delete"
    And I press "Delete"
    Then I should see "DKAN Data Story Test Story Post has been deleted"

  Scenario: Edit own story content
    And I am logged in as "Jaz"
    When I am on "story/dkan-data-story-test-story-post"
    When I click "Edit"
    And I fill in "body[und][0][value]" with "Test description Update"
    And I press "Save"
    Then I should see "DKAN Data Story Test Story Post has been updated"

  @javascript
  Scenario: Use text format filtered_html
    And I am logged in as "Jaz"
    When I am on "/node/add/dkan-data-story"
    Then I should have an "html" text format option

  Scenario: View own unpublished content
    And I am logged in as "Jaz"
    When I am on "/admin/content"
    Then I should see "test Story Post"