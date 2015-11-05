Feature: Panels

  @api @javascript
  Scenario: Adds "New Custom Item" block to home page using panels ipe editor
    Given I am logged in as a user with the "administrator" role
      And I am on the homepage
      Then I should see "Customize this page"
    When I click "Customize this page"
    And I wait for "Add new pane"
      And I click "Add new pane"
      And I wait for "Please select a category from the left"
    When I click "New custom content" in the "modal" region
      And I wait for "2" seconds
      And I fill in "edit-title" with "New Custom Item"
      And I scroll to the top
      And I fill in "edit-body-value" with "Custom item body."
      And I press "Finish"
      And I wait and press "Save"
      And I wait for "New Custom Item"

  @api @javascript
  Scenario: Updating front page as authenticated user
    Given I am logged in as a user with the "authenticated user" role
      And I am on the homepage
      Then I should not see "Customize this page"
