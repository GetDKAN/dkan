@api @javascript
Feature: User

  Background:
    Given pages:
      | title         | url           |
      | Content       | /user         |
      | John          | /users/john   |
      | Katie         | /users/katie  |
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | administrator        |
      | Badmin  | admin@example.com   | administrator        |
      | Gabriel | gabriel@example.com | authenticated user   |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | authenticated user   |
      | Martin  | martin@example.com  | editor               |
      | Celeste | celeste@example.com | editor               |
    Given groups:
      | title    | author  | published |
      | Group 01 | Badmin  | Yes       |
      | Group 02 | Badmin  | Yes       |
      | Group 03 | Badmin  | No        |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
      | Jaz     | Group 01 | member               | Pending           |
      | Admin   | Group 02 | administrator member | Active            |
      | Celeste | Group 02 | member               | Active            |
    And "Tags" terms:
      | name    |
      | Health  |
      | Gov     |
    And datasets:
      | title      | publisher | author  | published        | tags     | description |
      | Dataset 01 | Group 01  | Katie   | Yes              | Health   | Test        |
      | Dataset 02 | Group 01  | Katie   | Yes              | Health   | Test        |

  @fixme
    # Then I should see the "John" page - undefined
  Scenario: Login
    Given I am on the homepage
    When I follow "Log in"
    And I fill in "Username" with "john"
    And I fill in "Password" with "johnpass"
    Then I should see the "John" page


  # TODO: Anonymous users can see profiles of logged in users (though can't edit), is that intended?

  Scenario: Logout
    Given I am logged in as "John"
    And I am on the homepage
    When I follow "Log out"
    Then I should see "Log in"
    #When I am on "John" page
    #Then I should see "Page not found"


  # TODO: Currently receiving an error page upon pressing "Create new account", upon which the user is not properly created
  #       and you cannot log in as them, so this should be fixed
  Scenario: Register
    Given I am on the homepage
    When I follow "Register"
    And I fill in "Username" with "newuser"
    And I fill in "E-mail address" with "newuser@example.com"
    And I press "Create new account"
    #Then I should see "Thank you for applying for an account."
    #And I should see "Your account is currently pending approval by the site administrator."

  @mail
  Scenario: Request new password
    Given I am on the homepage
    When I follow "Log in"
    And I follow "Request new password"
    And I fill in "Username or e-mail address" with "john@example.com"
    And I press "E-mail new password"
    Then user "John" should receive an email
    #TODO: Follow reset password link on email?

  @fixme
    # Then I should see the "Katie" page - undefined
  Scenario: View user profile
    Given I am on "Group 01" page
    And I follow "Members"
    When I click "Katie"
    Then I should see the "Katie" page

  Scenario: View list of published datasets created by user on user profile
    Given I am on "Katie" page
    And I click "Datasets" in the "tabs" region
    Then I should see "2" items in the "user content" region

  Scenario: Search datasets created by user on user profile
    Given I am on "Katie" page
    And I click "Datasets" in the "tabs" region
    When I fill in "Test" for "Search" in the "content search" region
    And I press "Apply"
    Then I should see "2 datasets" in the "user content" region
    And I should see "2" items in the "user content" region

  Scenario: See list of user memberships on user profile
    Given I am on "Katie" page
    And I click "Groups" in the "tabs" region
    Then I should see "Group membership:"
    Then I should see "Group 01"
    And I should not see "Group 02"
