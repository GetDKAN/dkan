# features/profile.feature
Feature: Profile
  Check a user profile as an authenticated user.

  @api @fixme
  Scenario: Check profile menu
    Given I am logged in as a user with the "authenticated user" role
    And I am on "/user"
    Then I should see "Content"
    Then I should see "Datasets"
    Then I should see "Groups"
    Then I should see "Visualizations"
    Then I should see "Users"
    Then I should see "Site Preferences"
    Then I should see "Profile Settings"
