# time:0m11s
@disablecaptcha
Feature: Page

  Background:
    Given pages:
      | name          | url             |
      | Add Page      | /node/add/page  |

  @api @javascript
  Scenario: Add new page content as Editor

    Given I am logged in as a user with the "editor" role
    And I am on the "Add Page" page
    #   When I hover over the admin menu item "Add content"
    #     And I click "Page"
      Then I should see "Create Page"
      When I fill in "Title" with "New Sample Page"
      And I press "Save"
      Then I should see "New Sample Page"
      #  When I wait for "Loading" to disappear
      And I wait for "1" seconds
      And I press "Save"
      And I wait for "Customize this page"
      Then I should see "Customize this page"
