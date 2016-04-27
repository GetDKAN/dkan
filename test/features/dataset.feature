@api
Feature: Datasets

  Background:
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | site manager         |
      | Badmin  | admin@example.com   | site manager         |
      | Gabriel | gabriel@example.com | editor               |
      | Jaz     | jaz@example.com     | editor               |
      | Katie   | katie@example.com   | content creator      |
      | Martin  | martin@example.com  | editor               |
      | Celeste | celeste@example.com | editor               |
    Given groups:
      | title    | author  | published |
      | Group 01 | Badmin  | Yes       |
      | Group 02 | Badmin  | Yes       |
      | Group 03 | Badmin  | No        |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
      | Katie   | Group 01 | member               | Active            |
      | Jaz     | Group 01 | member               | Pending           |
      | Admin   | Group 02 | administrator member | Active            |
      | Celeste | Group 02 | member               | Active            |
    And "Tags" terms:
      | name     |
      | price    |
      | election |
    And datasets:
      | title      | publisher | author  | published | tags     | description |
      | Dataset 01 | Group 01  | Gabriel | Yes       | price    | Test 01     |
      | Dataset 02 | Group 01  | Gabriel | Yes       | election | Test 02     |
      | Dataset 03 | Group 01  | Katie   | Yes       | price    | Test 03     |
      | Dataset 04 | Group 02  | Celeste | No        | election | Test 04     |
    And "format" terms:
      | name |
      | csv  |
      | html |
    And resources:
      | title       | publisher | format | author | published | dataset    | description |
      | Resource 01 | Group 01  | csv    | Katie  | Yes       | Dataset 01 | Test R1     |
      | Resource 02 | Group 01  | html   | Katie  | Yes       | Dataset 01 | Test R2     |
      | Resource 03 | Group 01  | html   | Katie  | Yes       | Dataset 02 | Test R3     |

  @api @noworkflow
  Scenario: Sharing the Dataset on Facebook
    Given I am on "/dataset/dataset-01"
    When I click "Facebook"
    Then I should see "Facebook"

  @noworkflow
  Scenario: Sharing the Dataset on Twitter
    Given I am on "/dataset/dataset-01"
    When I click "Twitter"
    Then I should see "Share a link with your followers"

  @noworkflow @fixme
    #TODO: We need to fix this as we can't rely on dkan default content. In the next lines we can see an approach
    #      in which we tried to update a dataset with the new information about license in order to see the license
    #      at the end, the problem is this is not working, the step "I fill in the chosen field" fails because it
    #      can't see the option we are trying to set. So we should fix that function on the context or maybe fix
    #      the "Given datasets" function in order to have license in the mapping of fields and add this as part of
    #      the background.
    #Scenario: Seeing the License
    #Given I am logged in as a user with the "administrator" role
    #And I am on "/dataset/dataset-01"
    #When I click "Edit"
    #And I fill in the chosen field "edit_field_license_und_select_chosen" with "Creative Commons Attribution"
    #And I press "edit-submit"
    #When I am on "/dataset/dataset-01"
    #And I click "Creative Commons Attribution"
    #Then I should see "The Creative Commons Attribution license allows re-distribution and re-use of a licensed work"
  Scenario: Seeing the License
    Given I am on "/dataset/wisconsin-polling-places"
    When I click "Creative Commons Attribution"
    Then I should see "The Creative Commons Attribution license allows re-distribution and re-use of a licensed work"

  @javascript @noworkflow
  Scenario: Viewing the Dataset
    Given I am on "/dataset/dataset-01"
    Then I should see "Test 01"
    And I should see "Go to resource"
    And I should see "Dataset Info"
    And I should see "Modified Date"
    And I should see "Identifier"
    When I click "Resource 01"
    Then I wait for "Test R1"

  @api @javascript @noworkflow @fixme
    #TODO: There is an issue with file structures in containers, where files located in one container's folder
    #       are not visible to other containers
    #       This makes it difficult to upload files to the browser, as the browser container won't contain the file.
    #       This is working on CircleCI as it is only a single container thus no file strucutre issues.
    #       A solution where files can be shared across containers should be added
  Scenario: Create a dataset as a content creator
    Given I am logged in as a user with the "content creator" role
    Given I am on "/node/add/dataset"
    Then I should see "Create Dataset"
    When I fill in "title" with "Test Dataset"
    And I fill in "body[und][0][value]" with "Test description"
    And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I fill in "title" with "Test Resource Link File"
    And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "http://demo.getdkan.com/sites/default/files/district_centerpoints_0.csv"
    And I press "edit-another"
    Then I wait for "Test Resource Link File has been created"
    And I should see "Add content"
    When I fill in "title" with "Test Resource Upload"
    And I click "Upload a file"
    And I attach the file "Polling_Places_Madison.csv" to "field_upload[und][0][resup]" using file resup
    And I wait for the file upload to finish
    And I check "field_upload[und][0][view][grid]"
    And I press "edit-submit"
    And I wait for "Test Resource Upload has been created"
    When I click "Test Dataset"
    Then I should see "Test Resource"
    When I click "Test Resource Link File"
    And I wait for "Farah"
    When I am on "dataset/test-dataset"
    Then I should see "Edit"
    And I should see "Add Resource"
    When I am on "/dataset/dataset-01"
    Then I should not see "Edit"
    And I should not see "Add Resource"

  @api @javascript @noworkflow
  Scenario: Data previews when only local enabled
    Given cartodb previews are disabled for csv resources
    And I am on "/dataset/dataset-01"
    Then I should see "Preview"
    And I should not see "Open with"

  @api @javascript @noworkflow @fixme
  #TODO: This test is relying in default dkan content so we need to fix it, in the next lines there is
  #      an approach but it doesn't work because for some reason the preview is not working for external
  #      files.
  #Scenario: Open data previews in external services
  #  Given cartodb previews are enabled for csv resources
  #  And I am logged in as a user with the "site manager" role
  #  And I am on "/dataset/dataset-01"
  #  When I click "Resource 01"
  #  Then I should see "Edit"
  #  When I click "Edit"
  #  And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "http://demo.getdkan.com/sites/default/files/district_centerpoints_0.csv"
  #  And I press "edit-submit"
  #  When I am on "/dataset/dataset-01"
  #  Then I should see "Open With"
  #  When I press "Open With"
  #  Then I should see the local preview link
  #  And I should see "CartoDB"
  Scenario: Open data previews in external services
    Given cartodb previews are enabled for csv resources
    And I am logged in as a user with the "site manager" role
    And I am on "/dataset/wisconsin-polling-places"
    Then I should see "Open With"
    When I press "Open With"
    Then I should see the local preview link
    And I should see "CartoDB"

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

  # https://github.com/Behat/Behat/issues/834
  @dummy
  Scenario: Dummy test
    Given I am on "/"
