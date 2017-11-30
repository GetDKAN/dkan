# time:0m54.15s
@api @disablecaptcha
Feature: Markdown Editor
  In order to create content
  As a user with edition permissions
  I should be able to use markdown when editing content

  Background:
    Given pages:
    | name          | url                |
    | Add Dataset   | /node/add/dataset  |
    Given users:
    | name    | mail                | roles                |
    | John    | john@example.com    | site manager         |
    | Jaz     | jaz@example.com     | editor               |
    | Gabriel | gabriel@example.com | content creator      |
    Given groups:
    | title    | author | published |
    | Group 01 | Admin  | Yes       |
    And group memberships:
    | user    | group    | role on group     | membership status |
    | Gabriel | Group 01 | member            | Active            |
    | Jaz     | Group 01 | member            | Active            |


  @markdown_editor_1 @javascript
  Scenario: Seeing 'Markdown' text format and toolbar as a Content Creator
    Given I am logged in as "Gabriel"
    When I am on "Add Dataset" page
    # Check available text formats ('Markdown HTML' option value is 'html')
    Then I should have an "html" text format option
    And I should have an "plain_text" text format option
    # Buttons that the user should see on the toolbar
    And I should see the button "Make selected text into a header" in the "dataset edit body"
    And I should see the button "Italics: Make selected text emphasized" in the "dataset edit body"
    And I should see the button "Bold: Make selected text strong" in the "dataset edit body"
    And I should see the button "Make selected text into a block quote" in the "dataset edit body"
    And I should see the button "Make selected text into an ordered list (numbered)" in the "dataset edit body"
    And I should see the button "Make selected text into an unordered list (bullets)" in the "dataset edit body"
    And I should see the button "Make text into an autolink (turns URLs in links, turns words into section identifiers for navigating the document)" in the "dataset edit body"
    And I should see the button "Make text into a link (turns text into a link with more options)" in the "dataset edit body"
    And I should see the button "Insert an image" in the "dataset edit body"
    And I should see the button "Insert a line break" in the "dataset edit body"
    And I should see the button "Help" in the "dataset edit body"
    # Buttons that the user should not see on the toolbar
    And I should not see the button "Insert a table" in the "dataset edit body"
    And I should not see the button "Insert an abbreviation (word or acronym with definition)" in the "dataset edit body"
    And I should not see the button "Insert a footnote" in the "dataset edit body"
    And I should not see the button "Insert a horizontal ruler (horizontal line)" in the "dataset edit body"
    And I should not see the button "Teaser break" in the "dataset edit body"
    And I should not see the button "Insert a definition list" in the "dataset edit body"
    And I should not see the button "Format selected text as code" in the "dataset edit body"
    And I should not see the button "Format selected text as a code block" in the "dataset edit body"

  @markdown_editor_2 @javascript 
  Scenario: Seeing 'Markdown' text format and toolbar as an Editor
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    # Check available text formats ('Markdown HTML' option value is 'html')
    Then I should have an "html" text format option
    And I should have an "plain_text" text format option
    # Buttons that the user should see on the toolbar
    And I should see the button "Make selected text into a header" in the "dataset edit body"
    # Buttons that the user should not see on the toolbar
    And I should not see the button "Insert a table" in the "dataset edit body"

  
  @markdown_editor_3 @javascript
  Scenario: Seeing 'Markdown' text format and toolbar as a Site Manager
    Given I am logged in as "John"
    When I am on "Add Dataset" page
    # Check available text formats ('Markdown HTML' option value is 'html')
    Then I should have an "html" text format option
    And I should have an "plain_text" text format option
    # Buttons that the user should see on the toolbar
    And I should see the button "Make selected text into a header" in the "dataset edit body"
    # Buttons that the user should not see on the toolbar
    And I should not see the button "Insert a table" in the "dataset edit body"

  @markdown_editor_4
  Scenario: Add headers using markdown
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    And I fill-in the following:
      | title | Test Dataset |
      | description | <h2>First subtitle</h2><h3>Second subtitle</h3> |
      | publisher | Group 01 |
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I am on "dataset/test-dataset"
    Then I should see the "h2" element in the "dataset body" region
    And I should see the "h3" element in the "dataset body" region

  @markdown_editor_5
  Scenario: Add italic text using markdown
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    And I fill-in the following:
      | title | Test Dataset |
      | description | *Some text* |
      | publisher | Group 01 |
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I am on "dataset/test-dataset"
    Then I should see the "em" element in the "dataset body" region

  @markdown_editor_6
  Scenario: Add bold text using markdown
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    And I fill-in the following:
      | title | Test Dataset |
      | description | **Some text** |
      | publisher | Group 01 |
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I am on "dataset/test-dataset"
    Then I should see the "strong" element in the "dataset body" region

  @markdown_editor_7
  Scenario: Add code block using markdown
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    And I fill-in the following:
      | title | Test Dataset |
      | description | `Some code` |
      | publisher | Group 01 |
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I am on "dataset/test-dataset"
    Then I should see the "code" element in the "dataset body" region

  @markdown_editor_8
  Scenario: Add quote block using markdown
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    And I fill-in the following:
      | title | Test Dataset |
      | description | > Some quote |
      | publisher | Group 01 |
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I am on "dataset/test-dataset"
    Then I should see the "blockquote" element in the "dataset body" region

  @markdown_editor_9
  Scenario: Add ordered list using markdown
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    And I fill-in the following:
      | title | Test Dataset |
      | description | 1. Some item |
      | publisher | Group 01 |
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I am on "dataset/test-dataset"
    Then I should see the "ol" element in the "dataset body" region

  @markdown_editor_10
  Scenario: Add unordered list using markdown
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    And I fill-in the following:
      | title | Test Dataset |
      | description | * Some item |
      | publisher | Group 01 |
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I am on "dataset/test-dataset"
    Then I should see the "ul" element in the "dataset body" region

  @markdown_editor_11
  Scenario: Add link using markdown
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    And I fill-in the following:
      | title | Test Dataset |
      | description | [Link](http://www.google.com) |
      | publisher | Group 01 |
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I am on "dataset/test-dataset"
    Then I should see the "a" element in the "dataset body" region

  @markdown_editor_12
  Scenario: Add image using markdown
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    And I fill-in the following:
      | title | Test Dataset |
      | description | ![alt text]('/the/url' 'Image Title') |
      | publisher | Group 01 |
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I am on "dataset/test-dataset"
    Then I should see the "img" element in the "dataset body" region

  @markdown_editor_13
  Scenario: Add line break using markdown
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    And I fill-in the following:
      | title | Test Dataset |
      | description | This<br>that |
      | publisher | Group 01 |
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I am on "dataset/test-dataset"
    Then I should see the "br" element in the "dataset body" region

  @markdown_editor_14
  Scenario: Add iframe break using markdown
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    And I fill-in the following:
      | title | Test Dataset |
      | description | <iframe src=\"http://www.w3schools.com\"></iframe> |
      | publisher | Group 01 |
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I am on "dataset/test-dataset"
    Then I should see the "iframe" element in the "dataset body" region

  @markdown_editor_15 @javascript
  Scenario: Don't see markdown toolbar when using 'Plain text' text format
    Given I am logged in as "Jaz"
    When I am on "Add Dataset" page
    And I select "Plain text" from "edit-body-und-0-format--2" chosen.js select box
    Then I should not see the button "Make selected text into a header" in the "dataset edit body"
    And I should not see the button "Italics: Make selected text emphasized" in the "dataset edit body"
