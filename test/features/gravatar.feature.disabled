Feature: Gravatar

  @api @javascript
  Scenario: Test gravatar in user pictures.
    Given I am logged in as a user with the "content creator" role
    When I visit "user/"
    Then I should see "Edit"
    And I should see a gravatar image in the "content" region
    And I should see a gravatar image in the "header" region
    When I click "Edit"
    Then I should see "Upload picture"
    When I check "gravatar"
    And I attach the file "6944276022_06ea83e528_0.jpg" to "files[picture_upload]"
    And I press "edit-submit"
    And I wait for "3" seconds
    Then I should see "The changes have been saved."
    And I should not see a gravatar image in the "header" region
    When I visit "user/"
    Then I should not see a gravatar image in the "content" region
    When I click "Edit"
    Then I should see "Delete picture"
    When I check "edit-picture-delete"
    And I press "edit-submit"
    And I wait for "3" seconds
    Then I should see "The changes have been saved."
    And I should not see "Delete picture"
    And I should see a gravatar image in the "content" region
    And I should see a gravatar image in the "header" region
