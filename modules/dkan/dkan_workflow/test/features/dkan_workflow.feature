Feature: Workbench

  Background:
    Given pages:
      | title        | url                          |
      | Datasets     | dataset                      |
      | Needs Review | admin/workbench/needs-review |
      | My drafts    | admin/workbench/drafts       |
    Given users:
      | name    | mail             | roles                |
      | Jeff    | jeff@test.com    | portal administrator |
      | Gabriel | gabriel@test.com | content editor       |
      | Katie   | katie@test.com   | data contributor     |
      | Celeste | celeste@test.com | data contributor     |
      | Jaz     | jaz@test.com     | data contributor     |
    And "tags" terms:
      | name   |
      | Health |
      | Gov    |
    And datasets:
      | title      | author  | moderation   | date         | tags   |
      | Dataset 01 | Katie   | draft        | Feb 01, 2015 | Health |
      | Dataset 02 | Gabriel | published    | Mar 13, 2015 | Gov    |
      | Dataset 03 | Katie   | published    | Feb 17, 2013 | Health |
      | Dataset 04 | Celeste | draft        | Jun 21, 2015 | Gov    |
      | Dataset 05 | Katie   | needs_review | Jun 21, 2015 | Gov    |
    And "Format" terms:
      | name  |
      | csv   |
    And resources:
      | title        | author  | dataset    | moderation | format |
      | Resource 041 | Celeste | Dataset 04 | published  | csv    |
      | Resource 042 | Celeste | Dataset 04 | published  | csv    |
      | Resource 051 | Katie   | Dataset 05 | published  | csv    |

  @api
  Scenario: As a Data Contributor I want to moderate my own Datasets
    Given I am logged in as "Katie"
    And I am on "Dataset 01" page
    When I follow "Moderate"
    Then I should see "Needs Review" in the "#edit-state" element
    And I should not see "Published" in the "#edit-state" element
    And I press "Apply"
    And I should see "Draft --> Needs Review"

  @api
  Scenario: As a Content Editor I want to Publish datasets posted by a Data Contributor
    Given I am logged in as "Gabriel"
    And I am on "Dataset 01" page
    When I follow "Moderate"
    Then I should see "Needs Review" in the "#edit-state" element
    When I press "Apply"
    Then I should see "Draft --> Needs Review"
    And I should see "Published" in the "#edit-state" element
    When I press "Apply"
    Then I should see "Needs Review --> Published"
    Given I am an anonymous user
    And I am on "Dataset 01" page
    Given I should not see the error message "Access denied. You must log in to view this page."

  @api
  Scenario: As a Portal Administrator I want to moderate all content
    Given I am logged in as "Jeff"
    And I am on "Dataset 01" page
    When I follow "Moderate"
    Then I should see "Needs Review" in the "#edit-state" element
    And I should see "Published" in the "#edit-state" element
    When I follow "Edit draft"
    And I fill in "Description" with "Dataset 01 edited"
    And I press "Finish"
    Then I should see "Dataset Dataset 01 has been updated"
    Given I am an anonymous user
    And I am on "Dataset 01" page
    Given I should not see the error message "Access denied. You must log in to view this page."

  @api
  Scenario Outline: View 'My workbench' page
    Given I am logged in as a user with the "<role name>" role
    Then I should see the link "My Workbench" in the navigation region
    When I follow "My Workbench"
    Then I should see "My Content"
    And I should see "Create content"
    And I should see "My drafts"
    And I should see an ".link-badge" element
    And I should see "Needs review"
    And I should see an ".link-badge" element

    Examples:
      | role name                 |
      | portal administrator      |
      | content editor            |

  @api
  Scenario: View 'My workbench' page for "data contributor" role
    Given I am logged in as a user with the "data contributor" role
    Then I should see the link "My Workbench" in the navigation region
    When I follow "My Workbench"
    Then I should see "My Content"
    And I should see "Create content"
    And I should see "My drafts"
    And I should see "Needs review"

  @api
  Scenario: View 'Stale drafts' menu item for "portal administrator" role
    Given I am logged in as a user with the "portal administrator" role
    Then I should see the link "My Workbench" in the navigation region
    When I follow "My Workbench"
    Then I should see "Stale drafts"
    And I should see an ".link-badge" element

  @api
  Scenario: View 'Stale reviews' menu item for "portal administrator" role
    Given I am logged in as a user with the "portal administrator" role
    Then I should see the link "My Workbench" in the navigation region
    When I follow "My Workbench"
    Then I should see "Stale reviews"
    And I should see an ".link-badge" element

  @api @mail
  Scenario: As a Content Editor I want to receive an email notification when "Data Contributor" add a Dataset that "Needs Review".
    Given I am logged in as "Katie"
    And I am on "Datasets" page
    When I click "Add Dataset"
    And I fill in the following:
      | Title                     | Dataset That Needs Review |
      | Description               | Test Behat Dataset 06     |
      | autocomplete-deluxe-input | Health                    |
    And I press "Next: Add data"
    And I fill in the following:
      | Title                     | Resource 061            |
      | Description               | Test Behat Resource 061 |
      | autocomplete-deluxe-input | CSV                     |
    And I press "Save"
    Then I should see the success message "Resource Resource 061 has been created."
    And I click "Back to dataset"
    Then I follow "Moderate"
    Then I should see "Needs Review" in the "#edit-state" element
    And I should not see "Published" in the "#edit-state" element
    And I press "Apply"
    And I should see "Draft --> Needs Review"
    And user Gabriel should receive an email containing "Please review the recent update at"

  @api @mail @javascript
  Scenario: Request dataset review (Change dataset status from 'Draft' to 'Needs review')
    Given I am logged in as "Celeste"
    And I follow "My Workbench"
    And I follow "My drafts"
    Then I should see "Dataset 04"
    Given I click "Submit for Review" for "Dataset 04" in the workbench tree
    Then I should not see "Dataset 04"
    And the moderation state of node "Dataset 04" of type "Dataset" should be "Needs review"
    And user Gabriel should receive an email containing "Please review the recent update at"

  @api @mail @javascript
  Scenario: As Content Editor Review Dataset (Change dataset status from 'Needs review' to 'Published')
    Given I am logged in as "Gabriel"
    And I follow "My Workbench"
    And I follow "Needs review"
    And I should see "Dataset 05"
    Given I click "Publish" for "Dataset 05" in the workbench tree
    Then I should not see "Dataset 05"
    And the moderation state of node "Dataset 05" of type "Dataset" should be "Published"
    And user Katie should receive an email containing "Please review the recent update at"

  @api @mail @javascript
  Scenario: Bulk dataset review requests (Change multiple datasets status from 'Draft' to 'Needs review')
    Given datasets:
      | title                  | author | moderation | date         | tags   |
      | Dataset Bulk update 01 | Jaz    | draft      | Feb 01, 2015 | Health |
      | Dataset Bulk update 02 | Jaz    | draft      | Mar 13, 2015 | Gov    |
      | Dataset Bulk update 03 | Jaz    | draft      | Feb 17, 2013 | Health |
    And resources:
      | title                    | author | dataset                | moderation | format |
      | Resource Bulk update 011 | Jaz    | Dataset Bulk update 01 | draft  | csv    |
      | Resource Bulk update 012 | Jaz    | Dataset Bulk update 01 | draft  | csv    |
      | Resource Bulk update 021 | Jaz    | Dataset Bulk update 02 | draft  | csv    |
      | Resource Bulk update 022 | Jaz    | Dataset Bulk update 02 | draft  | csv    |
      | Resource Bulk update 031 | Jaz    | Dataset Bulk update 03 | draft  | csv    |
      | Resource Bulk update 032 | Jaz    | Dataset Bulk update 03 | draft  | csv    |
    Given I am logged in as "Jaz"
    And I follow "My Workbench"
    And I follow "My drafts"
    Then the workbench tree should contain 9 elements
    Given I set all the elements in the workbench tree to "Submit for review"
    And I wait for '5' seconds
    Then I should see the success message "Performed Submit for review on 9 items."
    And the workbench tree should contain 0 elements

