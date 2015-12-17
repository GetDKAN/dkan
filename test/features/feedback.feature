Feature: Tests for the "Feedback" features.

  @api
  Scenario: As a user, I should see the "Feedback" link in the menu
    Given I am an Anonymous User
    And I am on the homepage
    Then I should see the link "Feedback"

  @api @disablecaptcha
  Scenario: As an Anonymous user, I should be able to add a feeback from the Feedback page.
    Given pages:
      | title    | url       |
      | Feedback | feedback  |
    And a feedback_type term with the name "Test feedback type"
    And a tags term with the name "Test feedback tag"
    And I am an anonymous user
    And I am on "Feedback" page
    When I click "Add Feedback"
    And I fill in the following:
      | Title                     | Test Feedback from Anonymous                   |
      | Email                     | anonymous@example.com                          |
      | Feedback                  | This is a feedback                             |
    And select "Test feedback type" from "Feedback Type"
    And I press the "Save" button
    Then I should see the success message "Feedback Test Feedback from Anonymous has been created."
    And The feedback "Test Feedback from Anonymous" is in "needs_review" moderation state

  @api @disablecaptcha
  Scenario: As an Anonymous user, I should  be able to add a feedback from a Dataset page.
    Given groups:
      | title    |
      | Group 01 |
    And Users:
      | name    | mail                  | status | roles                |
      | AUTH-WC | AUTH-WC@fakeemail.com | 1      | Workflow Contributor |
    And datasets:
      | title      | author  | published | moderation | date created | publisher |
      | Dataset 01 | AUTH-WC | Yes       | published  | Jul 21, 2015 | Group 01  |
    And I am an anonymous user
    And a feedback_type term with the name "Test feedback type"
    When I am on the "Dataset 01" page
    And I click "Add Feedback"
    And I fill in the following:
      | Title                     | Test Feedback from a Workflow Contributor      |
      | Email                     | AUTH-WC@fakeemail.com                          |
      | Feedback                  | This is a feedback                             |
      | autocomplete-deluxe-input | Test feedback tag                              |
    And select "Test feedback type" from "edit-field-feedback-type-und"
    And I press the "Save" button
    Then I should see the success message "Feedback Test Feedback from a Workflow Contributor has been created."
    And The feedback "Test Feedback from a Workflow Contributor" is in "needs_review" moderation state

  @api @disablecaptcha
  Scenario: As Anonymous, I should be able to search for published feedback
    Given users:
      | name     | role                                 | status |
      | AUTH-DC  | Authenticated User, Data Contributor | 1      |
      | AUTH     | Authenticated User                   | 1      |
    And datasets:
      | title      | author  | moderation | date         | published |
      | Dataset 01 | AUTH-DC | published  | July 5, 2015 | yes       |
    And feedback:
      | title       | moderation | author | associated content | published |
      | Feedback 01 | published  | AUTH   | Dataset 01         | yes       |
    And I am an anonymous user
    When I go to "/feedback"
    And I search for "Feedback 01" in the "feedback" search form
    And I should see "1" search results shown on the page
    And I should see "Feedback 01" in the search results

  @api
  Scenario: As a user, I should be able to filter feedback in the Feedback page.
    Given groups:
      | title    |
      | Group 01 |
      | Group 02 |
      | Group 03 |
    And users:
      | name     | role                                 | status |
      | AUTH-DC  | Authenticated User, Data Contributor | 1      |
      | AUTH     | Authenticated User                   | 1      |
    And datasets:
      | title      | author  | moderation | date created | published | publisher |
      | Dataset 01 | AUTH-DC | published  | July 5, 2015 | yes       | Group 01  |
      | Dataset 02 | AUTH-DC | published  | July 5, 2015 | yes       | Group 02  |
      | Dataset 03 | AUTH-DC | published  | July 5, 2015 | yes       | Group 03  |
    And a feedback_type term with the name "Test feedback type 1"
    And a feedback_type term with the name "Test feedback type 2"
    And a feedback_type term with the name "Test feedback type 3"
    And feedback:
      | title       | moderation | author | associated content | published | feedback type        | date created |
      | Feedback 01 | published  | AUTH   | Dataset 01         | yes       | Test feedback type 1 | July 5, 2015 |
      | Feedback 02 | published  | AUTH   | Dataset 02         | yes       | Test feedback type 2 | July 6, 2015 |
      | Feedback 03 | published  | AUTH   | Dataset 03         | yes       | Test feedback type 3 | July 7, 2015 |
    And I am an anonymous user
    When I go to "/feedback"
    And I search for "Feedback 01" in the "feedback" search form
    And I should see "1" search results shown on the page
    And I should see "Feedback 01" in the search results
    When I go to "/feedback"
    And I select "Group 02" from "Agency"
    And I press the "Apply" button
    And I should see "1" search results shown on the page
    And I should see "Feedback 02" in the search results
    When I go to "/feedback"
    And I select "Test feedback type 3" from "Feedback Type"
    And I press the "Apply" button
    And I should see "1" search results shown on the page
    And I should see "Feedback 03" in the search results

  @api @javascript
  Scenario: As Anonymous, I should be able to vote up or vote down Feedback
    Given groups:
      | title    |
      | Group 01 |
    And users:
      | name     | role                                 | status |
      | AUTH-DC  | Authenticated User, Data Contributor | 1      |
      | AUTH     | Authenticated User                   | 1      |
    And datasets:
      | title      | author  | moderation | date created | published | publisher |
      | Dataset 01 | AUTH-DC | published  | July 5, 2015 | yes       | Group 01  |
    And a feedback_type term with the name "Test feedback type 1"
    And feedback:
      | title       | moderation | author | associated content | published | feedback type        | date created |
      | Feedback 01 | published  | AUTH   | Dataset 01         | yes       | Test feedback type 1 | July 5, 2015 |
    And I am an Anonymous user
    When I go to "/feedback"
    And I vote up the feedback "Feedback 01"
    And I wait for "1" seconds
    Then The feedback "Feedback 01" should be rated "+1"
    When I vote down the feedback "Feedback 01"
    And I wait for "1" seconds
    Then The feedback "Feedback 01" should be rated "-1"

  @api @javascript
  Scenario: As a user, I should be able to sort by date in the Feedback page.
    Given groups:
      | title    |
      | Group 01 |
      | Group 02 |
      | Group 03 |
    And users:
      | name     | role                                 | status |
      | AUTH-DC  | Authenticated User, Data Contributor | 1      |
      | AUTH     | Authenticated User                   | 1      |
    And datasets:
      | title      | author  | moderation | date created | published | publisher |
      | Dataset 01 | AUTH-DC | published  | July 5, 2015 | yes       | Group 01  |
      | Dataset 02 | AUTH-DC | published  | July 5, 2015 | yes       | Group 02  |
      | Dataset 03 | AUTH-DC | published  | July 5, 2015 | yes       | Group 03  |
    And a feedback_type term with the name "Test feedback type 1"
    And a feedback_type term with the name "Test feedback type 2"
    And a feedback_type term with the name "Test feedback type 3"
    And feedback:
      | title       | moderation | author | associated content | published | feedback type        | date created | rating |
      | Feedback 01 | published  | AUTH   | Dataset 01         | yes       | Test feedback type 1 | July 5, 2015 | 1      |
      | Feedback 02 | published  | AUTH   | Dataset 02         | yes       | Test feedback type 2 | July 6, 2015 | 2      |
      | Feedback 03 | published  | AUTH   | Dataset 03         | yes       | Test feedback type 3 | July 7, 2015 | 3      |
    And I am an anonymous user
    When I go to "/feedback"
    And I select "Most Recent" from "Sort by"
    And I select "Desc" from "Order"
    And I press the "Apply" button
    Then the ".views-table tr td a" elements should be sorted in this order "Feedback 03 > Feedback 02 > Feedback 01"
    When I go to "/feedback"
    And I select "Most Recent" from "Sort by"
    And I select "Asc" from "Order"
    And I press the "Apply" button
    Then the ".views-table tr td a" elements should be sorted in this order "Feedback 01 > Feedback 02 > Feedback 03"

  @api @javascript
  Scenario: As a user, I should be able to sort by rating in the Feedback page.
    Given groups:
      | title    |
      | Group 01 |
      | Group 02 |
      | Group 03 |
    And users:
      | name     | role                                 | status |
      | AUTH-DC  | Authenticated User, Data Contributor | 1      |
      | AUTH     | Authenticated User                   | 1      |
    And datasets:
      | title      | author  | moderation | date created | published | publisher |
      | Dataset 01 | AUTH-DC | published  | July 5, 2015 | yes       | Group 01  |
      | Dataset 02 | AUTH-DC | published  | July 5, 2015 | yes       | Group 02  |
      | Dataset 03 | AUTH-DC | published  | July 5, 2015 | yes       | Group 03  |
    And a feedback_type term with the name "Test feedback type 1"
    And a feedback_type term with the name "Test feedback type 2"
    And a feedback_type term with the name "Test feedback type 3"
    And feedback:
      | title       | moderation | author | associated content | published | feedback type        | date created | rating |
      | Feedback 01 | published  | AUTH   | Dataset 01         | yes       | Test feedback type 1 | July 5, 2015 | 1      |
      | Feedback 02 | published  | AUTH   | Dataset 02         | yes       | Test feedback type 2 | July 6, 2015 | 2      |
      | Feedback 03 | published  | AUTH   | Dataset 03         | yes       | Test feedback type 3 | July 7, 2015 | 3      |
    And I am an anonymous user
    When I go to "/feedback"
    And I select "Most Voted" from "Sort by"
    And I select "Desc" from "Order"
    And I press the "Apply" button
    Then the ".views-table tr td a" elements should be sorted in this order "Feedback 03 > Feedback 02 > Feedback 01"
    When I go to "/feedback"
    And I select "Most Voted" from "Sort by"
    And I select "Asc" from "Order"
    And I press the "Apply" button
    Then the ".views-table tr td a" elements should be sorted in this order "Feedback 01 > Feedback 02 > Feedback 03"

  @api @disablecaptcha
  Scenario: As an Authenticated User, I should be able to comment on a feedback
    Given groups:
      | title    |
      | Group 01 |
    And users:
      | name   | role               | status |
      | AUTH-1 | Authenticated User | 1      |
      | AUTH-2 | Authenticated User | 1      |
    And datasets:
      | title      | author | moderation | date created | published | publisher |
      | Dataset 01 | AUTH-1 | published  | July 5, 2015 | yes       | Group 01  |
    And a feedback_type term with the name "Test feedback type 1"
    And feedback:
      | title       | moderation | author | associated content | published | feedback type        | date created |
      | Feedback 01 | published  | AUTH-2 | Dataset 01         | yes       | Test feedback type 1 | July 5, 2015 |
    And I am logged in as a user with the "authenticated user" role
    When I am on the "Feedback 01" page
    And I fill in "Subject" with "Subject 1"
    And I fill in "Comment" with "Comment 1"
    And I press the "Save" button
    Then I should see the success message "Your comment has been posted."
    And I should see the text "Subject 1"
    And I should see the text "Comment 1"

  @api @disablecaptcha
  Scenario: As an Authenticated User, I should be able to delete my comments on feedback
    Given groups:
      | title    |
      | Group 01 |
    And users:
      | name   | role               | status |
      | AUTH-1 | Authenticated User | 1      |
      | AUTH-2 | Authenticated User | 1      |
    And datasets:
      | title      | author | moderation | date created | published | publisher |
      | Dataset 01 | AUTH-1 | published  | July 5, 2015 | yes       | Group 01  |
    And a feedback_type term with the name "Test feedback type 1"
    And feedback:
      | title       | moderation | author | associated content | published | feedback type        | date created |
      | Feedback 01 | published  | AUTH-2 | Dataset 01         | yes       | Test feedback type 1 | July 5, 2015 |
    When I am logged in as a user with the "authenticated user" role
    And I am on the "Feedback 01" page
    And I fill in "Subject" with "Subject 1"
    And I fill in "Comment" with "Comment 1"
    And I press the "Save" button
    And I visit the link "#comments .links.inline .comment-delete.first a"
    And I press the "Delete" button
    Then I should see the success message "The comment and all its replies have been deleted."

  @api @disablecaptcha
  Scenario: As Anonymous, I should see a badge/icon next to feedback submitted by Authenticated Users
    Given groups:
      | title    |
      | Group 01 |
    And users:
      | name   | role               | status |
      | AUTH-1 | authenticated user | 1      |
      | AUTH-2 | authenticated user | 1      |
    And datasets:
      | title      | author | moderation | date created | published | publisher |
      | Dataset 01 | AUTH-1 | published  | July 5, 2015 | yes       | Group 01  |
    And a feedback_type term with the name "Test feedback type 1"
    And feedback:
      | title       | moderation | author | associated content | published | feedback type        | date created |
      | Feedback 01 | published  | AUTH-2 | Dataset 01         | yes       | Test feedback type 1 | July 5, 2015 |
      | Feedback 02 | published  |        | Dataset 01         | yes       | Test feedback type 1 | July 5, 2015 |
    And I am an anonymous user
    When I go to "/feedback"
    Then I should see a badge next to feedback "Feedback 01"
    And I should not see a badge next to feedback "Feedback 02"
