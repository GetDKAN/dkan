Feature: Topics

  Background:
    Given "dkan_topics" terms:
      | name         | field_icon_type  | field_topic_icon   |
      | Topic1       | font             | xe904              |
      | Topic2 & $p@ | font             | xe97b              |

  @api @Topics
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
