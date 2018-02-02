# time:0m50.62s
@api @disablecaptcha
Feature: User

  Background:
    Given pages:
      | name          | url           |
      | Content       | /user         |
      | Users         | /admin/people |
      | John          | /users/john   |
      | Katie         | /users/katie  |
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
      | Badmin  | admin@example.com   | site manager         |
      | aadmin  | admin@example.com   | administrator        |
      | Gabriel | gabriel@example.com | content creator      |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | content creator      |
      | Martin  | martin@example.com  | editor               |
      | Celeste | celeste@example.com | editor               |
    # Given groups:
    #   | title    | author  | published |
    #   | Group 01 | Badmin  | Yes       |
    #   | Group 02 | Badmin  | Yes       |
    #   | Group 03 | Badmin  | No        |
    # And group memberships:
    #   | user    | group    | role on group        | membership status |
    #   | Gabriel | Group 01 | administrator member | Active            |
    #   | Katie   | Group 01 | member               | Active            |
    #   | Jaz     | Group 01 | member               | Pending           |
    #   | Admin   | Group 02 | administrator member | Active            |
    #   | Celeste | Group 02 | member               | Active            |
    # And "Tags" terms:
    #   | name    |
    #   | world   |
    #   | results |
    # And datasets:
    #   | title      | publisher | author  | published        | tags     | description |
    #   | Dataset 01 | Group 01  | Katie   | Yes              | world    | Test        |
    #   | Dataset 02 | Group 01  | Katie   | No               | world    | Test        |
    #   | Dataset 03 | Group 01  | Gabriel | Yes              | results  | Test        |
    #   | Dataset 04 | Group 01  | Katie   | Yes              | world    | Test        |


  Scenario: Edit any user account
    Given I am logged in as "John"
    And I am on "Users" page
    And I fill in "edit-name" with "Katie"
    And I press "Apply"
    When I click "edit" in the "Katie" row
    And I fill in "About" with "This is Katie!"
    And I press "Save"
    Then I should see "The changes have been saved"
    When I am on "Katie" page
    Then I should see "This is Katie!" in the "user profile" region

  @dkanBug @deleteTempUsers @javascript
    # Site managers trigger honeypot when creating users.
    # See https://github.com/GetDKAN/dkan/issues/811
    # Workaround: Wait for 6 seconds so that honeypot doesn't overreact
  Scenario: Create user and assign role as site manager
    Given I am logged in as "John"
    And I am on "Users" page
    When I follow "Add user"
    And I fill in the following:
      | Username          | tempuser             |
      | E-mail address    | tempuser@example.com |
      | Password          | temp123              |
      | Confirm password  | temp123              |
    And I check "editor"
    And I wait for "6" seconds
    And I press "Create new account"
    Then I should see "Created a new user account for tempuser."
    When I am on "Users" page
    And I fill in "edit-name" with "tempuser"
    And I press "Apply"
    And I wait for "tempuser"
    Then I should see "editor" in the "tempuser" row

  Scenario: Block user
    Given I am logged in as "John"
    And I am on "Users" page
    And I fill in "edit-name" with "Katie"
    And I press "Apply"
    When I click "edit" in the "Katie" row
    And I select the radio button "Blocked"
    And I press "Save"
    Then I should see "The changes have been saved"
    When I am on "Users" page
    And I fill in "edit-name" with "Katie"
    And I press "Apply"
    Then I should see "No" in the "Katie" row

  @javascript
  Scenario: Disable user
    Given I am logged in as "John"
    And I am on "Users" page
    And I fill in "edit-name" with "Katie"
    And I press "Apply"
    And I wait for "edit"
    When I click "edit" in the "Katie" row
    And I press "Cancel account"
    And I select the radio button "Disable the account and keep its content."
    And I press "Cancel account"
    Then I wait for "Katie has been disabled"

  Scenario: Modify user roles as administrator
    Given I am logged in as "aadmin"
    And I am on "Users" page
    And I fill in "edit-name" with "Jaz"
    And I press "Apply"
    When I click "edit" in the "Jaz" row
    And I uncheck "editor"
    And I check "content creator"
    And I press "Save"
    Then I should see "The changes have been saved"
    When I am on "Users" page
    Then I should see "content creator" in the "Jaz" row

  Scenario: Modify user roles as site manager
    Given I am logged in as "John"
    And I am on "Users" page
    And I fill in "edit-name" with "Jaz"
    And I press "Apply"
    When I click "edit" in the "Jaz" row
    And I uncheck "content creator"
    And I check "site manager"
    And I press "Save"
    Then I should see "The changes have been saved"
    When I am on "Users" page
    And I fill in "edit-name" with "Jaz"
    And I press "Apply"
    Then I should see "site manager" in the "Jaz" row

  Scenario: Modify user as editor
    Given I am logged in as "Jaz"
    And I am on "Users" page
    Then I should see "Access denied"
