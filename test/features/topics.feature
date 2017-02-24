# time:0m2.37s
Feature: Topics

  Background:
    Given "dkan_topics" terms:
      | name         | field_icon_type  | field_topic_icon   |
      | Topic1       | font             | xe904              |
      | Topic2 & $p@ | font             | xe97b              |
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
    Given pages:
      | name         | url                                        |
      | Add Topic    | /admin/structure/taxonomy/dkan_topics/add  |

  @api @Topics @defaultHomepage
  Scenario: See topics on the homepage as anonymous user
    When I am on the homepage
    Then I should see "Topic1"
    And I should see "Topic2 & $p@"
    And I should see "icon" in the ".font-icon-select-1-e904" element

  @api @Topics
  Scenario: See topic in the main menu
    When I am on the homepage
    Then I click on the text "Topics"
    Then I should see "Topic1"

  @api @Topics
  Scenario: Check topic facet link
    When I am on the homepage
    Then I click on the text "Topics"
    Then I should see "Topic1"
    When I click on the text "Topic2 & $p@"
    Then I should see "results"

  @api @Topics
  Scenario: Site manager role can create new topic term
    Given I am logged in as "John"
    When I am on "Add Topic" page
    And I fill in "name" with "Abibliophobia"
    And I check the box "edit-field-topic-icon-und-xe909"
    And I press "Save"
    Then I should see "Created new term Abibliophobia."
    
