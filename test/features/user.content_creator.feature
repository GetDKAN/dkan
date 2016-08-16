@api @javascript
Feature: User command center links for content creator role.

  Background:
    Given users:
      | name    | mail                | roles                |
      | Gabriel | gabriel@example.com | content creator      |


  Scenario: Content creator role can view admin menu links under Add Content
    Given I am logged in as "Gabriel"
    When I click "Add content" in the "admin menu" region
    Then I should see "Add content"
    When I hover over the admin menu item "Add content"
    And I click "Dataset"
    Then I should see "Create dataset"
    When I hover over the admin menu item "Add content"
    And I click "Resource"
    Then I should see "Add resource"
    When I hover over the admin menu item "Add content"
    And I click "Data Story"
    Then I should see "Create Data Story"
    When I hover over the admin menu item "Add content"
    And I click "Data Dashboard"
    Then I should see "Create Data Dashboard"

  Scenario: Editor role can view admin menu link Content
    Given I am logged in as "Gabriel"
    When I click "Content" in the "admin menu" region
    Then I should see "Show only items where"

