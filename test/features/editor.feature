Feature: Datasets

  @api
  Scenario: Edit any group content
    Given I am logged in as a user with the "editor" role
      And I am on "/group/data-explorer-examples"
    Then I should see "US National Foreclosure Statistics January 2012"
      And I should see "Data Explorer Examples"
      And I should see "Filter by tags"
    When I click "Edit"
    Then I should see "Edit Data Explorer Examples"

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
    Then I should see "Add Resource"
    When I click "Edit"
    Then I should see "What are datasets?"
