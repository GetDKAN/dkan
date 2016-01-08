Feature: Administrator role

  @api
  Scenario: Administrator has all permissions
    Given I am logged in as a user with the "administrator" role
      Then the administrator role should have all permissions