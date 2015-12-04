Feature: Gravatar

  @api @javascript @debugEach
  Scenario: Test gravatar in user pictures.
    Given I am logged in as a user with the "authenticated user" role
    When I visit "user/"
    Then I should see "Edit"
    And I should see a gravatar link in the "content" region
    And I should see a gravatar link in the "header" region
    When I click "Edit"
    Then I should see "Upload picture"
    When I check "gravatar"
    And I attach the drupal file "dkan_logo.png" to "files[picture_upload]"
    And I scroll to the top
    And grab a screenshot
    And I press "edit-submit"
    And I scroll to the top
    And grab a screenshot
    And I wait for "The changes have been saved."
    And I should not see a gravatar link in the "header" region
    When I visit "user/"
    Then I should not see a gravatar link in the "content" region
    When I click "Edit"
    Then I should see "Delete picture"
    When I check "edit-picture-delete"
    And I press "edit-submit"
    And I wait for "3" seconds
    Then I should see "The changes have been saved."
    And I should not see "Delete picture"
    And I should see a gravatar link in the "content" region
    And I should see a gravatar link in the "header" region
