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
      | Badmin  | admin@example.com   | site manager         |
      | Gabriel | gabriel@example.com | editor               |
      | Jaz     | jaz@example.com     | editor               |
      | Daniel  | daniel@example.com  | editor               |
      | Katie   | katie@example.com   | content creator      |
      | Keith   | keith@example.com   | content creator      |
      | Martin  | martin@example.com  | authenticated user   |
      | Celeste | celeste@example.com | authenticated user   |
    Given groups:
      | title    | author | published |
      | Group 01 | Admin  | Yes       |
      | Group 02 | Admin  | Yes       |
      | Group 03 | Admin  | No        |
      | Group 04 | Admin  | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
      | Jaz     | Group 01 | member               | Pending           |
      | Celeste | Group 02 | member               | Active            |
      | Katie   | Group 02 | member               | Active            |
      | Daniel  | Group 02 | member               | Active            |
    And "Tags" terms:
      | name   |
      | price1 |
      | election1 |
    And datasets:
      | title      | publisher | author  | published        | tags     | description |
      | Dataset 01 | Group 01  | Gabriel | Yes              | price1    |             |
      | Dataset 02 | Group 01  | Gabriel | Yes              | election1 |             |
      | Dataset 03 |           | Katie   | Yes              | price1    |             |
      | Dataset 04 | Group 02  | Celeste | No               | election1 |             |
      | Dataset 05 | Group 01  | Katie   | No               | election1 |             |
      | Dataset 06 |           | Katie   | Yes              | election1 |             |
      | Dataset 07 | Group 01  | Katie   | Yes              | election1 |             |
      | Dataset 08 |           | Katie   | Yes              | election1 |             |
      | Dataset 09 | Group 02  | Katie   | Yes              | election1 |             |
    And resources:
      | title       | publisher | author | published | dataset    | description |
      | Resource 01 | Group 01  | Katie  | Yes       | Dataset 01 |             |
      | Resource 02 | Group 01  | Katie  | Yes       | Dataset 01 |             |
      | Resource 03 | Group 01  | Katie  | Yes       | Dataset 02 |             |
      | Resource 04 |           | Katie  | Yes       |            |             |
      | Resource 05 |           | Katie  | Yes       | Dataset 08 |             |
      | Resource 06 | Group 02  | Katie  | Yes       | Dataset 09 |             |

  @noworkflow @dataset_author_1
  Scenario: Create dataset as content creator
    Given I am logged in as "Katie"
    And I am on "Add Dataset" page
    Then I should not see "Authoring information"
    And I fill in the following:
      | Title           | Test Dataset      |
      | Description     | Test description  |
    And I select "Group 01" from "og_group_ref[und][]"
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"

  @noworkflow @dataset_author_2
  Scenario: Save using Additional Info
    Given I am logged in as a user with the "content creator" role
    And I am on "Add Dataset" page
    When I fill in "title" with "Test Dataset"
    And I fill in "body[und][0][value]" with "Test description"
    And I press "Next: Add data"
    And I fill in "title" with "Test Resource Link File"
    And I press "Next: Additional Info"
    And I press "Save"
    Then I should see "Test Dataset"
    And I should see "Test description"

  @noworkflow
  Scenario: Edit own dataset as a content creator
    Given I am logged in as "Katie"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I fill in "edit-title" with "Dataset 03 edited"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 edited has been updated"
    When I am on "My Content" page
    Then I should see "Dataset 03 edited"

  @noworkflow
  Scenario: Seeing the License
    Given I am logged in as "Katie"
    And I am on "Dataset 03" page
    When I click "Edit"
    Given I select "Creative Commons Attribution" from "edit-field-license-und-select"
    And I press "edit-submit"
    And I click "Log out"
    When I am on "Dataset 03" page
    And I click "Creative Commons Attribution"
    Then I should see "The Creative Commons Attribution license allows re-distribution and re-use of a licensed work"

  @fixme @noworkflow
    # TODO: Needs definition. How can a data contributor unpublish content?
  Scenario: Unpublish own dataset as a content creator
    Given I am on the homepage

  @noworkflow
  Scenario: Delete own dataset as content creator
    Given I am logged in as "Katie"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I press "Delete"
    And I press "Delete"
    Then I should see "Dataset 03 has been deleted"

  @noworkflow
  Scenario: Add a dataset to group that I am a member of
    Given I am logged in as "Katie"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I select "Group 01" from "og_group_ref[und][]"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 has been updated"
    When I am on "Group 01" page
    Then I should see "Dataset 03" in the "content" region

  @noworkflow @javascript
  Scenario: Add a resource with no dataset to a dataset with no resource
    Given I am logged in as "Katie"
    And I am on "Dataset 06" page
    When I click "Edit"
    And I fill in the resources field "edit-field-resources-und-0-target-id" with "Resource 04"
    And I press "Finish"
    Then I should see "Dataset 06 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    And I should see "Resource 04" in the "dataset resource list" region
    When I click "Resource 04"
    Then I should see "Resource 04" in the "resource title" region

  # NOTE: Datasets and resources associated through the 'Background' steps cannot be used here
  #       because the URL of the resources change based on the datasets where they are added
  #       so going back to a resource page after the dataset association is modified throws an error.
  @noworkflow @javascript
  Scenario: Remove a resource with only one dataset from the dataset
    Given I am logged in as "Katie"
    And I am on "Dataset 06" page
    When I click "Edit"
    And I fill in the resources field "edit-field-resources-und-0-target-id" with "Resource 04"
    And I press "Finish"
    Then I should see "Dataset 06 has been updated"
    And I should see "Resource 04" in the "dataset resource list" region
    When I click "Edit"
    And I empty the field "edit-field-resources-und-0-target-id"
    And I press "Finish"
    Then I should see "Dataset 06 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    And I should not see "Resource 04" in the "resource title" region
    When I am on "Resource 04" page
    Then I should not see the link "Back to dataset"

  @noworkflow @javascript
  Scenario: Add a resource with no group to a dataset with group
    Given I am logged in as "Katie"
    And I am on "Dataset 07" page
    When I click "Edit"
    And I fill in the resources field "edit-field-resources-und-0-target-id" with "Resource 04"
    And I press "Finish"
    Then I should see "Dataset 07 has been updated"
    And I should see "Groups were updated on 1 resource(s)"

  # NOTE: Datasets and resources associated through the 'Background' steps cannot be used here
  #       because the URL of the resources change based on the datasets where they are added
  #       so going back to a resource page after the dataset association is modified throws an error.
  @noworkflow @javascript
  Scenario: Remove a resource from a dataset with group
    Given I am logged in as "Katie"
    And I am on "Dataset 07" page
    When I click "Edit"
    And I fill in the resources field "edit-field-resources-und-0-target-id" with "Resource 04"
    And I press "Finish"
    Then I should see "Dataset 07 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I am on "Dataset 07" page
    And I click "Edit"
    And I empty the field "edit-field-resources-und-0-target-id"
    And I press "Finish"
    Then I should see "Dataset 07 has been updated"
    And I should see "Groups were updated on 1 resource(s)"

  @noworkflow
  Scenario: Add group to a dataset with resources
    Given I am logged in as "Katie"
    And I am on "Dataset 08" page
    When I click "Edit"
    And I select "Group 02" from "og_group_ref[und][]"
    And I press "Finish"
    Then I should see "Dataset 08 has been updated"
    And I should see "Groups were updated on 1 resource(s)"

  @noworkflow
  Scenario: Remove group from dataset with resources
    Given I am logged in as "Katie"
    And I am on "Dataset 09" page
    When I click "Edit"
    And I select "" from "og_group_ref[und][]"
    And I press "Finish"
    Then I should see "Dataset 09 has been updated"
    And I should see "Groups were updated on 1 resource(s)"

  @noworkflow @javascript
  Scenario: Add group and resource to a dataset on the same edition
    Given I am logged in as "Katie"
    And I am on "Dataset 08" page
    When I click "Edit"
    And I fill in the chosen field "edit_og_group_ref_und_chosen" with "Group 02"
    And I fill in the resources field "edit-field-resources-und-0-target-id" with "Resource 04"
    And I press "Finish"
    Then I should see "Dataset 08 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    And I should see "Resource 04" in the "dataset resource list" region

  @noworkflow
  Scenario: Site Managers should see groups they are not member of
    Given I am logged in as "John"
    When I visit "node/add/dataset"
    Then I should see the "Group 01" groups option
    And I should see the "Group 02" groups option

  @noworkflow
  Scenario: Content Creators should only see the groups they are member of
    Given I am logged in as "Katie"
    When I visit "node/add/dataset"
    Then I should see the "Group 02" groups option
    And I should not see the "Group 04" groups option

  @noworkflow
  Scenario: Editors should only see the groups they are member of
    Given I am logged in as "Daniel"
    When I visit "node/add/dataset"
    Then I should see the "Group 02" groups option
    And I should not see the "Group 04" groups option

  @noworkflow
  Scenario: Site Managers should see authoring information and publishing options
    Given I am logged in as "John"
    When I visit "node/add/dataset"
    Then I should see "Authoring information"
    And I should see "Publishing options"

  @noworkflow
  Scenario: Content Creators not part of a group should see publishing options
    Given I am logged in as "Keith"
    When I visit "node/add/dataset"
    Then I should not see "Authoring information"
    Then I should see "Publishing options"
    When I visit "node/add/resource"
    Then I should not see "Authoring information"
    Then I should see "Publishing options"

  @noworkflow
  Scenario: Content Creators who are part of a group should not see authoring information
    Given I am logged in as "Katie"
    When I visit "node/add/dataset"
    Then I should not see "Authoring information"
    Then I should see "Publishing options"
    When I visit "node/add/resource"
    Then I should not see "Authoring information"
    Then I should see "Publishing options"
