Feature: Datasets

  @api
  Scenario: Edit any page content
    Given I am logged in as a user with the "editor" role
      And I am on "/about"
    Then I should see "DKAN is the Drupal-based version of CKAN"
    When I click "Edit"
    Then I should see "Edit About"

  @api
  Scenario: Edit any dataset content
    Given I am logged in as a user with the "editor" role
      And I am on "/dataset/us-national-foreclosure-statistics-january-2012"
    When I click "Edit"
    Then I should see "What are datasets?"
