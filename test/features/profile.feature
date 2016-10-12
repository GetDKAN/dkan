# features/profile.feature
Feature: Profile
  Check a user profile as an content creator.

  @api @javascript
  Scenario: Check profile menu
    Given I am logged in as a user with the "content creator" role
    And I am on "/user"
    Then I should see "Dataset" in the ".horizontal-tabs" element
    Then I should see "About" in the ".horizontal-tabs" element
    Then I should see "Search" in the ".horizontal-tabs-pane" element
