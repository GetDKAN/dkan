Feature: Datastore 
  In order to know the datastore is working 
  As a website user
  I need to be able to add and remove items from the datastore 

  @api @javascript
  Scenario: Adding and Removing items from the datastore
    Given I am logged in as a user with the "administrator" role
      And I am on "dataset/afghanistan-election-districts"   
      And I click "District Names"
      Then I should see "Individual data records can be seen by clicking on each point."
    When I click "Manage Datastore"
      Then I should see "DKAN Datastore File: Status"
    When I press "Import"
      And I wait for "7" seconds
      Then I should see "399 imported items total."
    When I click "Data API"
      Then I should see "Example Query"
    When I click "Manage Datastore"
      Then I should see "DKAN Datastore File: Status"
    When I click "Delete items"
      Then I should see "DKAN Datastore File: Status"
    When I press "Delete"
      And I wait for "3" seconds
      Then I should see "399 items have been deleted."
    When I click "Manage Datastore"
      And I wait for "1" seconds
      And I click "Drop Datastore"
      And I press "Drop"
      Then I should see "Datastore dropped!"
      And I should see "Your file for this resource is not added to the datastore."
