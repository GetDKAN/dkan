# time:0m7s
@api @disablecaptcha
Feature: User command center links for content creator role.

  Background:
    Given pages:
      | name               | url                           |
      | Add Dataset        | /node/add/dataset             |
      | Add Resource       | /node/add/resource            |
      | Add Data Story     | /node/add/dkan-data-story     |
      | Add Data Dashboard | /node/add/data-dashboard      |
    Given users:
      | name    | mail                | roles                |
      | Gabriel | gabriel@example.com | content creator      |

  Scenario: Content creator role can view admin menu links under Add Content
    Given I am logged in as "Gabriel"
    
    When I am on "Add Dataset" page    
    Then I should see "Create dataset"

    When I am on "Add Resource" page    
    Then I should see "Add resource"

    When I am on "Add Data Story" page    
    Then I should see "Create Data Story"

    When I am on "Add Data Dashboard" page    
    Then I should see "Create Data Dashboard"

  Scenario: Editor role can view admin menu link Content
    Given I am logged in as "Gabriel"
    When I click "Content" in the "admin menu" region
    Then I should see "Operations"

