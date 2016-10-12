@api
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
      | My Content          | /user                        |
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
      | Badmin  | admin@example.com   | site manager         |
      | Gabriel | gabriel@example.com | editor               |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | content creator      |
      | Martin  | martin@example.com  | authenticated user   |
      | Celeste | celeste@example.com | authenticated user   |
    Given groups:
      | title    | author | published |
      | Group 01 | Admin  | Yes       |
      | Group 02 | Admin  | Yes       |
      | Group 03 | Admin  | No        |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
      | Jaz     | Group 01 | member               | Pending           |
      | Celeste | Group 02 | member               | Active            |
      | Katie   | Group 02 | member               | Active            |
    And "Tags" terms:
      | name   |
      | Health |
      | Gov    |
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
      | title       | publisher | format | author | published | dataset    | description |
      | Resource 01 | Group 01  | csv    | Katie  | Yes       | Dataset 01 |             |
      | Resource 02 | Group 01  | html   | Katie  | Yes       | Dataset 01 |             |
      | Resource 03 | Group 01  | html   | Katie  | Yes       | Dataset 02 |             |
      | Resource 04 |           | csv    | Katie  | Yes       |            |             |
      | Resource 05 |           | csv    | Katie  | Yes       | Dataset 08 |             |
      | Resource 06 | Group 02  | csv    | Katie  | Yes       | Dataset 09 |             |

  @noworkflow @javascript
  Scenario: Create dataset as content creator
    Given I am logged in as "Katie"
    And I am on "Datasets Search" page
    Then I hover over the admin menu item "Content"
    Then I hover over the admin menu item "Add content"
    Then I click "Dataset"
    And I fill in the following:
      | Title           | Test Dataset      |
      | Description     | Test description  |
    And I fill in the chosen field "edit_og_group_ref_und_0_default_chosen" with "Group 01"
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"

  @api @noworkflow
  Scenario: Save using "Additional Info"
    Given I am logged in as a user with the "content creator" role
    And I am on "/node/add/dataset"
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

  @noworkflow @javascript
  Scenario: Seeing the License
    Given I am logged in as "Katie"
    And I am on "Dataset 03" page
    When I click "Edit"
    Given I select "Creative Commons Attribution" from "edit-field-license-und-select" chosen.js select box
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

  @noworkflow @javascript
  Scenario: Add a dataset to group that I am a member of
    Given I am logged in as "Katie"
    And I am on "Dataset 03" page
    When I click "Edit"
    And I fill in the chosen field "edit_og_group_ref_und_0_default_chosen" with "Group 01"
    And I press "Finish"
    Then I should see "Dataset Dataset 03 has been updated"
    When I am on "Group 01" page
    Then I should see "Dataset 03" in the "content" region

  # https://github.com/Behat/Behat/issues/834
  @dummy
  Scenario: Dummy test
    Given I am on "/"

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
    And I click "Back to dataset"
    Then I should see "There is no dataset associated with this resource"

  @noworkflow @javascript
  Scenario: Add a resource with no group to a dataset with group
    Given I am logged in as "Katie"
    And I am on "Dataset 07" page
    When I click "Edit"
    And I fill in the resources field "edit-field-resources-und-0-target-id" with "Resource 04"
    And I press "Finish"
    Then I should see "Dataset 07 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I click "Resource 04"
    And I click "Edit"
    Then I should see "Group 01" in the "resource groups" region

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
    When I click "Resource 04"
    And I click "Edit"
    Then I should see "Group 01" in the "resource groups" region
    When I am on "Dataset 07" page
    And I click "Edit"
    And I empty the field "edit-field-resources-und-0-target-id"
    And I press "Finish"
    Then I should see "Dataset 07 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I am on "Resource 04" page
    And I click "Edit"
    Then I should not see "Group 01" in the "resource groups" region

  @noworkflow @javascript
  Scenario: Add group to a dataset with resources
    Given I am logged in as "Katie"
    And I am on "Dataset 08" page
    When I click "Edit"
    And I fill in the chosen field "edit_og_group_ref_und_0_default_chosen" with "Group 02"
    And I press "Finish"
    Then I should see "Dataset 08 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I am on "Resource 05" page
    And I click "Edit"
    Then I should see "Group 02" in the "resource groups" region

  @noworkflow @javascript
  Scenario: Remove group from dataset with resources
    Given I am logged in as "Katie"
    And I am on "Dataset 09" page
    When I click "Edit"
    And I empty the resources field "edit_og_group_ref_und_0_default_chosen"
    And I press "Finish"
    Then I should see "Dataset 09 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    When I am on "Resource 06" page
    And I click "Edit"
    Then I should not see "Group 02" in the "resource groups" region

  @noworkflow @javascript
  Scenario: Add group and resource to a dataset on the same edition
    Given I am logged in as "Katie"
    And I am on "Dataset 08" page
    When I click "Edit"
    And I fill in the chosen field "edit_og_group_ref_und_0_default_chosen" with "Group 02"
    And I fill in the resources field "edit-field-resources-und-0-target-id" with "Resource 04"
    And I press "Finish"
    Then I should see "Dataset 08 has been updated"
    And I should see "Groups were updated on 1 resource(s)"
    And I should see "Resource 04" in the "dataset resource list" region
    When I click "Resource 04"
    And I click "Edit"
    Then I should see "Group 02" in the "resource groups" region
