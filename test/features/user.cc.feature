# time:0m9.21s
@disablecaptcha @api @smoketest
Feature: User

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

  @javascript
  Scenario: Content creator role can view admin menu links under Add Content
    Given I am logged in as "Gabriel"
    And I am on the homepage
    And I hover over the admin menu item "Content"
    And I hover over the admin menu item "Add content"
    Then I should see the admin menu item "Dataset"
    Then I should see the admin menu item "Resource"
    Then I should see the admin menu item "Data Story"
    Then I should see the admin menu item "Data Dashboard"
    And I hover over the admin menu item "Visualizations"
    Then I should see the admin menu item "Charts"

    When I am on "Add Dataset" page
    Then I should see "Create dataset"

    When I am on "Add Resource" page
    Then I should see "Add resource"

    When I am on "Add Data Story" page
    Then I should see "Create Data Story"

    When I am on "Add Data Dashboard" page
    Then I should see "Create Data Dashboard"

  @api @fixme @testBug
    # TODO: Needs definition.
    #       This would take a long time to test manually, having to wait N minutes each time it's run.
    #       A possible solution to this would be to edit the cookies directly and speed up the waiting time
    #       that way. That would take time to figure out, would this test be worth the time?
  Scenario: User should be logged out automatically after N minutes
    Given I am on the homepage
