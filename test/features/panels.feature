# time:0m21.02s
@disablecaptcha
Feature: Panels

  @api @javascript
  Scenario: Adds "New Custom Item" block to home page using panels ipe editor
    Given I am logged in as a user with the "site manager" role
      And I am on the homepage
      Then I wait for "Customize this page"
    When I click "Customize this page"
    And I wait for "Add new pane"
      And I click "Add new pane"
      And I wait for "Add content"
    When I follow "New custom content"
      And I wait for "5" seconds
      And I fill in "override-title-textfield" with "New Custom Item"
      And I scroll to the top
      And I fill in "edit-body-value" with "Custom item body."
      And I press "Finish"
      And I wait and press "Save"
      # Avoid unexpected alert open: with next two lines.
      And I wait for "5" seconds
      And I wait for "New Custom Item"

  @api @javascript
  Scenario: Updating front page as Content Creator
    Given I am logged in as a user with the "content creator" role
      And I am on the homepage
      Then I should not see "Customize this page"
