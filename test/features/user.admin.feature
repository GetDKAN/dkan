@api @javascript
Feature: User

  Background:
    Given pages:
      | title         | url           |
      | Content       | /user         |
      | Users         | /admin/people |
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
      | Dataset 02 | Group 01  | Katie   | No               | Health   | Test        |
      | Dataset 03 | Group 01  | Gabriel | Yes              | Gov      | Test        |
      | Dataset 04 | Group 01  | Katie   | Yes              | Health   | Test        |


  Scenario: Edit any user account
    Given I am logged in as "John"
    And I am on "Users" page
    When I click "edit" in the "Katie" row
    And I fill in "About" with "This is Katie!"
    And I press "Save"
    Then I should see "The changes have been saved"
    When I am on "Katie" page
    And I click "About" in the "tabs" region
    Then I should see "This is Katie!"

  @fixme
     # TODO: There is an error submitting the form for a new user, so test is not passing currently
  Scenario: Create user
    Given I am logged in as "John"
    And I am on "Users" page
    When I follow "Add user"
    And I fill in the following:
      | Username          | Micaela             |
      | E-mail address    | micaela@example.com |
      | Password          | mic123              |
      | Confirm password  | mic123              |
    And I press "Create new account"
    Then I should see "Created a new user account for Micaela."

  Scenario: Block user
    Given I am logged in as "John"
    And I am on "Users" page
    When I click "edit" in the "Katie" row
    And I select the radio button "Blocked"
    And I press "Save"
    Then I should see "The changes have been saved"
    When I am on "Users" page
    Then I should see "blocked" in the "Katie" row

  Scenario: Disable user
    Given I am logged in as "John"
    And I am on "Users" page
    When I click "edit" in the "Katie" row
    And I press "Cancel account"
    And I select the radio button "Disable the account and keep its content."
    And I press "Cancel account"
    Then I wait for "Katie has been disabled"

  Scenario: Modify user roles
    Given I am logged in as "John"
    And I am on "Users" page
    When I click "edit" in the "Jaz" row
    And I uncheck "editor"
    And I check "storyteller"
    And I press "Save"
    Then I should see "The changes have been saved"
    When I am on "Users" page
    Then I should see "storyteller" in the "Jaz" row

  @fixme
    # TODO: Needs definition
  Scenario: Search users
    Given I am on the homepage






