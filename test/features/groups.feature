Feature: Groups 
  In order to know the groups are working 
  As a website user
  I need to be able to view the group pages

  @api
  Scenario: Join a group and edit group content as an Authenticated User
    Given I am logged in as a user with the "authenticated user" role
    When I visit "dataset/wisconsin-polling-places"
    Then I should not see "edit" in the "toolbar" region
    When I click "Madison Polling Places"
    Then I should not see "edit" in the "toolbar" region
    Given I am a "member" of the group "Geospatial Data Explorer Examples"
    When I visit "dataset/wisconsin-polling-places"
    Then I should see "Wisconsin Polling Places"
    Then I should see "Edit" in the "toolbar" region

   @api @javascript
   Scenario: Request to join a group as an Auth User
    Given I am logged in as a user with the "authenticated user" role
    When I visit "group/geospatial-data-explorer-examples"
      And I click "Request group membership"
      Then I should see "Are you sure you want to join the group Geospatial Data Explorer Examples?"
    
  @api @javascript
  Scenario: View Groups 
    Given I am on "/group/geospatial-data-explorer-examples"
      Then I should see "Wisconsin Polling Places"
      And I should see "Afghanistan Election Districts"
    When I click "country-afghanistan (1)"
      Then I should see "Afghanistan Election Districts"
      And I should not see "Wisconsin Polling Places"
    When I click "country-afghanistan"
      Then I should see "Afghanistan Election Districts"
      And I should see "Wisconsin Polling Places"

  @api @javascript
  Scenario: Manage a group as an Editor 
    Given I am logged in as a user with the "editor" role
      And I am on "/group/data-explorer-examples"
    Given users:
      | name     | mail            | status |
      | teo      | teo@rocks.com   | 1      |
      | federica | fed@rocks.com   | 1      |
    When I click "Group" in the "toolbar" region
    Then I should see "Add group members."
    When I click "Add people"
      And I wait for "3" seconds
    Then I should see "ADD A GROUP MEMBER TO DATA EXPLORER EXAMPLES"
    When I fill in "name" with "teo"
      And I wait for "3" seconds
      And I press "edit-submit"
      And I wait for "3" seconds
    Then I should see "teo has been added to the group Data Explorer Examples."
    When I am on "/group/data-explorer-examples"
    Then I should see "Members"
    When I click "Members"
    Then I should see "teo"
    When I click "Group" in the "toolbar" region
      And I wait for "1" seconds
      And I click "People"
    Then I should see "teo"
    When I check "edit-views-bulk-operations-1"
      And I select "action::og_membership_delete_action" from "edit-operation"
      And I press "edit-submit--2"
    Then I should see "Are you sure you want to perform Remove from group on the selected items?"
    When I press "edit-submit"
      And I wait for "1" seconds
    Then I should see "Performed Remove from group"
    Given I am logged in as a user with the "authenticated user" role
      And I am on "/node/add/group"
    Then I should see "Create Group"
    When I fill in "title" with "Test Group"
      And I press "Save"
    Then I should see "Test Group has been created"
    Given I am on "/node/add/dataset"
      Then I should see "Create Dataset"
    When I fill in "title" with "Test Dataset"
      And I fill in "body[und][0][value]" with "Test description"
      And I click the chosen field "License Not Specified" and enter "Creative Commons Attribution"
      And I fill in the chosen field "Choose some options" with "Test Group"
      And I press "Next: Add data"
    Then I should see "Test Dataset has been created"
    When I fill in "title" with "Test Resource Link File"
      And I fill in "edit-field-link-remote-file-und-0-filefield-remotefile-url" with "http://demo.getdkan.com/sites/default/files/district_centerpoints_0.csv"
      And I press "edit-another"
    Then I should see "Test Resource Link File has been created"
      And I should see "Add resource"
    When I fill in "title" with "Test Resource Upload"
      And I press "edit-submit"
    Then I should see "Test Resource Upload has been created"
