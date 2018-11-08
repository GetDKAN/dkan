# time:2m56.53s
@api @disablecaptcha
Feature: Dataset Features
  In order to realize a named business value
  As an explicit system actor
  I want to gain some beneficial outcome which furthers the goal

  Additional text...


  Background:
    Given pages:
      | name                | url                          |
      | Datasets            | /dataset                     |
      | Datasets Search     | /search/type/dataset         |
      | Add Dataset         | /node/add/dataset            |
      | My Content          | /user                        |
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | content creator      |
    Given groups:
      | title    | author | published |
      | Group 01 | Jaz    | Yes       |
      | Group 02 | Jaz    | Yes       |
      | Group 03 | John   | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Katie   | Group 01 | member               | Active            |
      | Katie   | Group 02 | member               | Active            |
      | Jaz     | Group 01 | member               | Active            |
      | Jaz     | Group 02 | member               | Active            |

  @dataset_author_1 @noworkflow
  Scenario: Create dataset as content creator
    Given I am logged in as "Katie"
    And I am on "Add Dataset" page
    Then I should not see "Authoring information"
    And I fill-in the following:
      | title           | Test Dataset      |
      | description     | Test description  |
      | publisher       | Group 01          |
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"

  @dataset_author_2 @noworkflow
  Scenario: Save using Additional Info
    Given I am logged in as "Katie"
    And I am on "Add Dataset" page
    And I fill-in the following:
      | title           | Test Dataset      |
      | description     | Test description  |
      | publisher       | Group 01          |
    And I press "Next: Add data"
    Then I should not see "Groups" in the "content" region
    And I fill in "title" with "Test Resource Link File"
    And I press "Next: Additional Info"
    And I press "Save"
    Then I should see "Test Dataset"
    And I should see "Test description"

  @dataset_author_3 @noworkflow
  Scenario: Edit own dataset as a content creator
    Given datasets:
      | title      | author  | published | description | publisher |
      | Dataset 01 | Katie   | Yes       |             | Group 01  |
    Given I am logged in as "Katie"
    And I am on "Dataset 01" page
    When I click "Edit"
    And I fill in "edit-title" with "Dataset 01 edited"
    And I press "Finish"
    Then I should see "Dataset Dataset 01 edited has been updated"
    When I am on "My Content" page
    Then I should see "Dataset 01 edited"

  @dataset_author_4 @noworkflow
  Scenario: Seeing the License
    Given datasets:
      | title      | author  | published | description | publisher |
      | Dataset 01 | Katie   | Yes       |             | Group 01  |
    Given I am logged in as "Katie"
    And I am on "Dataset 01" page
    When I click "Edit"
    Given I select "Creative Commons Attribution" from "edit-field-license-und-select"
    And I press "edit-submit"
    And I click "Log out"
    When I am on "Dataset 01" page
    And I click "Creative Commons Attribution"
    Then I should see "The Creative Commons Attribution license allows re-distribution and re-use of a licensed work"

  @dataset_author_5 @fixme @noworkflow
  # TODO: Needs definition. How can a data contributor unpublish content?
  Scenario: Unpublish own dataset as a content creator
    Given I am on the homepage

  @dataset_author_6 @noworkflow
  Scenario: Delete own dataset as content creator
    Given datasets:
      | title      | author  | published | description | publisher |
      | Dataset 01 | Katie   | Yes       |             | Group 01  |
    Given I am logged in as "Katie"
    And I am on "Dataset 01" page
    When I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Dataset 01 has been deleted"

  @dataset_author_7 @noworkflow
  Scenario: Add a dataset to group that I am a member of
    Given datasets:
      | title      | author  | published | description | publisher |
      | Dataset 01 | Katie   | Yes       |             | Group 02  |
    Given I am logged in as "Katie"
    And I am on "Dataset 01" page
    When I click "Edit"
    And I select "Group 01" from "og_group_ref[und][]"
    And I press "Finish"
    Then I should see "Dataset Dataset 01 has been updated"
    When I am on "Group 01" page
    Then I should see "Dataset 01" in the "content" region

  @dataset_author_8 @noworkflow
  Scenario: Add a resource with no dataset to a dataset with no resource
    Given datasets:
      | title      | author  | published | description | publisher |
      | Dataset 01 | Katie   | Yes       |             | Group 01  |
    Given resources:
      | title       | author   | published | description |
      | Resource 01 | Jaz      | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Dataset 01" page
    When I click "Edit"
    And I fill in "field_resources[und][0][target_id]" with "Resource 01"
    And I press "Finish"
    Then I should see "Dataset 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    And I should see "Resource 01" in the "dataset resource list" region
    When I click "Resource 01"
    Then I should see "Resource 01" in the "resource title" region

  # NOTE: Datasets and resources associated through the 'Background' steps cannot be used here
  #       because the URL of the resources change based on the datasets where they are added
  #       so going back to a resource page after the dataset association is modified throws an error.
  @dataset_author_9 @noworkflow
  Scenario: Remove a resource with only one dataset from the dataset
    Given datasets:
      | title      | author  | published | description | publisher |
      | Dataset 01 | Katie   | Yes       |             | Group 01  |
    Given resources:
      | title       | author   | published | description |
      | Resource 01 | Jaz      | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Dataset 01" page
    When I click "Edit"
    And I fill in "field_resources[und][0][target_id]" with "Resource 01"
    And I press "Finish"
    Then I should see "Dataset 01 has been updated"
    And I should see "Resource 01" in the "dataset resource list" region
    When I click "Edit"
    And I empty the field "edit-field-resources-und-0-target-id"
    And I press "Finish"
    Then I should see "Dataset 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    And I should not see "Resource 01" in the "resource title" region
    When I am on "Resource 01" page
    Then I should not see the link "Back to dataset"

  @dataset_author_10 @noworkflow
  Scenario: Add a resource with no group to a dataset with group
    Given datasets:
      | title      | author  | published | description | publisher |
      | Dataset 01 | Katie   | Yes       |             | Group 01  |
    Given resources:
      | title       | author   | published | description |
      | Resource 01 | Jaz      | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Dataset 01" page
    When I click "Edit"
    And I fill in "field_resources[und][0][target_id]" with "Resource 01"
    And I press "Finish"
    Then I should see "Dataset 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"

  # NOTE: Datasets and resources associated through the 'Background' steps cannot be used here
  #       because the URL of the resources change based on the datasets where they are added
  #       so going back to a resource page after the dataset association is modified throws an error.
  @dataset_author_11 @noworkflow
  Scenario: Remove a resource from a dataset with group
    Given datasets:
      | title      | author  | published | description | publisher |
      | Dataset 01 | Katie   | Yes       |             | Group 01  |
    Given resources:
      | title       | author   | published | description |
      | Resource 01 | Jaz      | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Dataset 01" page
    When I click "Edit"
    And I fill in "field_resources[und][0][target_id]" with "Resource 01"
    And I press "Finish"
    Then I should see "Dataset 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I am on "Dataset 01" page
    And I click "Edit"
    And I empty the field "edit-field-resources-und-0-target-id"
    And I press "Finish"
    Then I should see "Dataset 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"

  @dataset_author_12 @noworkflow
  Scenario: Add group to a dataset with resources
    Given datasets:
      | title      | author  | published | description | publisher |
      | Dataset 01 | Katie   | Yes       |             | Group 01  |
    Given resources:
      | title       | author   | published | description | dataset    |
      | Resource 01 | Jaz      | Yes       | No          | Dataset 01 |
    Given I am logged in as "Katie"
    And I am on "Dataset 01" page
    When I click "Edit"
    And I select "Group 02" from "og_group_ref[und][]"
    And I press "Finish"
    Then I should see "Dataset 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"

  @dataset_author_13 @noworkflow
  Scenario: Remove group from dataset with resources
    Given datasets:
      | title      | author  | published | description | publisher           |
      | Dataset 01 | Katie   | Yes       |             | Group 01, Group 02  |
    Given resources:
      | title       | author   | published | description | dataset    |
      | Resource 01 | Jaz      | Yes       | No          | Dataset 01 |
    Given I am logged in as "Katie"
    And I am on "Dataset 01" page
    When I click "Edit"
    And I select "" from "og_group_ref[und][]"
    And I press "Finish"
    Then I should see "Dataset 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"

  @dataset_author_14 @noworkflow @javascript
  Scenario: Add group and resource to a dataset on the same edition
    Given datasets:
      | title      | author  | published | description | publisher |
      | Dataset 01 | Katie   | Yes       |             | Group 01  |
    Given resources:
      | title       | author   | published | description |
      | Resource 01 | Jaz      | Yes       | No          |
    Given I am logged in as "Katie"
    And I am on "Dataset 01" page
    When I click "Edit"
    And I fill in the chosen field "edit_og_group_ref_und_chosen" with "Group 02"
    And I fill in "field_resources[und][0][target_id]" with "Resource 01"
    And I press "Finish"
    Then I should see "Dataset 01 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    And I should see "Resource 01" in the "dataset resource list" region

  @dataset_author_15 @noworkflow
  Scenario: Site Managers should see groups they are not member of
    Given I am logged in as "John"
    When I visit "node/add/dataset"
    Then I should see the "Group 01" groups option
    And I should see the "Group 02" groups option

  @dataset_author_16 @noworkflow
  Scenario: Content Creators should only see the groups they are member of
    Given I am logged in as "Katie"
    When I visit "node/add/dataset"
    Then I should see the "Group 02" groups option
    And I should not see the "Group 03" groups option

  @dataset_author_17 @noworkflow
  Scenario: Editors should only see the groups they are member of
    Given I am logged in as "Jaz"
    When I visit "node/add/dataset"
    Then I should see the "Group 02" groups option
    And I should not see the "Group 03" groups option

  @dataset_author_18 @noworkflow
  Scenario: Site Managers should see authoring information and publishing options
    Given I am logged in as "John"
    When I visit "node/add/dataset"
    Then I should see "Authoring information"
    And I should see "Publishing options"

  @dataset_author_19 @noworkflow
  Scenario: Content Creators not part of a group should see publishing options
    Given users:
      | name    | mail                | roles                |
      | Keith   | keith@example.com   | content creator      |
    Given I am logged in as "Keith"
    When I visit "node/add/dataset"
    Then I should not see "Authoring information"
    Then I should see "Publishing options"
    When I visit "node/add/resource"
    Then I should not see "Authoring information"
    Then I should see "Publishing options"

  @dataset_author_20 @noworkflow
  Scenario: Content Creators who are part of a group should not see authoring information
    Given I am logged in as "Katie"
    When I visit "node/add/dataset"
    Then I should not see "Authoring information"
    Then I should see "Publishing options"
    When I visit "node/add/resource"
    Then I should not see "Authoring information"
    Then I should see "Publishing options"
