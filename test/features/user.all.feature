# time:0m23.59s
@api @disablecaptcha
Feature: User

  Background:
    Given pages:
      | name          | url           |
      | Content       | /user         |
      | John          | /users/john   |
      | Katie         | /users/katie  |
    Given users:
      | name    | mail                | roles                | pass     |
      | John    | john@example.com    | site manager         | johnpass |
      | Badmin  | admin@example.com   | site manager         | pass     |
      | Gabriel | gabriel@example.com | content creator      | pass     |
      | Jaz     | jaz@example.com     | editor               | pass     |
      | Katie   | katie@example.com   | content creator      | pass     |
      | Martin  | martin@example.com  | editor               | pass     |
      | Celeste | celeste@example.com | editor               | pass     |
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
      | world   |
      | results |
    And datasets:
      | title      | publisher | author  | published        | tags     | description |
      | Dataset 01 | Group 01  | Katie   | Yes              | world    | Test        |
      | Dataset 02 | Group 01  | Katie   | Yes              | world    | Test        |

  Scenario: Login
    Given I am on the homepage
    When I follow "Log in"
    And I fill in "Username" with "John"
    And I fill in "Password" with "johnpass"
    And I press "Log in"
    Then I should see the "John" user page

  Scenario: Logout
    Given I am logged in as "John"
    And I am on the homepage
    When I follow "Log out"
    Then I should see "Log in"

  @javascript @deleteTempUsers @customizable
  Scenario: Register
    Given I am on the homepage
    When I follow "Register"
    # Needed because honeypot module give error when filling out the register form
    # too quickly, so we need to add a wait.
    And I wait for "6" seconds
    And I fill in "Username" with "tempuser"
    And I fill in "E-mail address" with "tempuser@example.com"
    And I press "Create new account"
    Then I should see "Thank you for applying for an account."
    And I should see "Your account is currently pending approval by the site administrator."

  @mail
  Scenario: Request new password
    Given I am on the homepage
    When I follow "Log in"
    And I follow "Request new password"
    And I fill in "Username or e-mail address" with "john@example.com"
    And I press "E-mail new password"
    Then user "John" should receive an email
    #TODO: Follow reset password link on email?

  Scenario: View user profile
    Given I am on "Group 01" page
    And I follow "Members"
    When I click "Katie"
    Then I should see the "Katie" user page

  Scenario: View list of published datasets created by user on user profile
    Given I am on "Katie" page
    Then I should see "2" items in the "user content" region

  Scenario: Search datasets created by user on user profile
    Given I am on "Katie" page
    When I fill in "Test" for "Search" in the "content search" region
    And I press "Apply"
    Then I should see "2 results" in the "user content" region
    And I should see "2" items in the "user content" region

  Scenario: See list of user memberships on user profile
    Given I am logged in as "Katie"
    And I am on "Katie" page
    Then I should see "Group 01" in the "user profile" region
    And I should not see "Group 02"
