Feature: Topics

  Background:
    Given "dkan_topics" terms:
      | name   | Icon Type | Icon   |
      | Topic1 | font      | xe977  |
      | Topic2 | font      | xe97b  |

  @api @Topics
  Scenario: See topics on the homepage as anonymous user
    When I am on the homepage
    Then I should see "Topic1"
    And I should see "Topic2"
    And I should see "icon" in the ".font-icon-select-1-e977" element

  @api @Topics
  Scenario: See topic in the main menu
    When I am on the homepage
    Then I click on the text "Topics"
    Then I should see "Topic1"
