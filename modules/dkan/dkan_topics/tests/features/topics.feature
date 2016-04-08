Feature: Topics

  Background:
    Given "dkan_topics_term" terms:
      | name   |
      | Topic1 |
      | Topic2 |

  @api @FeaturedTopics
  Scenario: See Featured Topics on the homepage as anonymous user
    When I am on the homepage
    Then I should see "Topic1"
    And I should see "Topic2"
