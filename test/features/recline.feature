Feature: Recline 
  In order to know the recline preview is working 
  As a website user
  I need to be able to view the recline previews 
  
  @javascript
  Scenario: Viewing map preview
    Given I am on "/dataset/wisconsin-polling-places"
      Then I should see "Polling places in the state of Wisconsin"
    Given I follow "Madison Polling Places"
      And I wait for "This is a list and map of polling places in Madison, WI."
      Then I should see "Original data"
      Then I should see "Polling_Places_Madison.csv"
    Given I press "Map"
      Then I should see "Latitude field"
      Then I wait for "3" seconds
    Given I click map icon number "88"
      And I wait for "Alicia Ashman Branch Library"

  @javascript @api
  Scenario: Viewing graph preview
    Given I am on "/dataset/gold-prices-london-1950-2008-monthly"
      Then I should see "Monthly gold prices"
    Given I click "Table of Gold Prices"
      Then I should see "748 records"
    Given I press "Graph"
      Then I should see "There's no graph here yet"

  @javascript 
  Scenario: Viewing graph preview
    Given I am on "/dataset/gold-prices-london-1950-2008-monthly"
      Then I should see "Monthly gold prices"
    Given I click "Table of Gold Prices"
      Then I should see "748 records"
    Given I press "Graph"
      And I wait for "There's no graph here yet"

  @javascript
  Scenario: Searching data
    Given I am on "/dataset/wisconsin-polling-places"
      Then I should see "Polling places in the state of Wisconsin"
    Given I click "Madison Polling Places"
      Then I should see "Polling_Places_Madison.csv"
    Given I click "»"
      Then I wait for "Our"
      Then I wait for "1" seconds
    Given I click "«"
      Then I wait for "1" seconds
      Then I wait for "East"
    Given I fill in "q" with "Glendale"
    When I press "Go"
      Then I should see "Tompkins"
