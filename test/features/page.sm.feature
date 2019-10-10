# time:0m11s
@disablecaptcha
Feature: Page

  Background:
    Given pages:
      | name          | url             |
      | Add Page      | /node/add/page  |

  @api @javascript
  Scenario: Site manager role should not see Customize Display link

    Given I am logged in as a user with the "site manager" role
    And I am on the "Add Page" page
    Then I should see "Create Page"
    When I fill in "title" with "My new page"
    And I hide the admin menu
    And I select the radio button "Boxton" with the id "edit-layout-radix-boxton"
    And I press "Save"
    And I wait for "View"
    Then I should not see "Customize Display"
