# time:3m30.05s
@harvest_rollback @disablecaptcha
Feature: Dkan Harvest
  Background:
    Given pages:
      | name       | url                        |
      | Source one | /harvest_source/source-one |
      | Source two | /harvest_source/source-two |

  @api @javascript
  Scenario: As a site manager I should be able to add a harvest source.
    Given users:
      | name              | mail                     | status | roles             |
      | Site manager      | admin@fakeemail.com      | 1      | site manager      |

    And I am logged in as "Site manager"
    And I am on "node/add/harvest-source"
    Then I should see the text "Create Harvest Source"
    And I fill in "Title" with "Source 1"
    And I wait for "2" seconds
    And I fill in "Source URI" with "http://s3.amazonaws.com/dkan-default-content-files/files/data_harvest.json"
    And I select "Project Open Data v1.1 JSON" from "Type"
    And I press "Save"
    And I wait for "2" seconds
    Then I should see the success message "Harvest Source Source 1 has been created."

  @api @javascript
  Scenario: Harvest source machine name should not have forward slash character.
    Given users:
      | name              | mail                     | status | roles             |
      | Site manager      | admin@fakeemail.com      | 1      | site manager      |

    And I am logged in as "Site manager"
    And I am on "node/add/harvest-source"
    Then I should see the text "Create Harvest Source"
    And I fill in "Title" with "Harvest test 01/17"
    And I wait for "2" seconds
    Then I should see "harvest_test_01_17"

  @api
  Scenario Outline: As a user I should not be able to add a harvest source.
    Given pages:
      | name                  | url                      |
      | Create Harvest Source | /node/add/harvest-source |

    And I am logged in as a "<role>"
    And I should be denied access to the "Create Harvest Source" page

    Examples:
      | role                    |
      | authenticated user      |

  @api @harvest
  Scenario: As a site manager I should see only the published harvest sources listed on the harvest dashboard.
    Given users:
      | name            | mail                   | roles           |
      | Site manager    | admin@fakeemail.com    | site manager    |
    And pages:
      | name               | url                            |
      | Harvest Dashboard  | /admin/dkan/harvest/dashboard  |
    And I am logged in as "Site manager"
    And I am on the "Harvest Dashboard" page
    Then I should see the text "Source one"
    And I should not see the text "Source two"

  @api @javascript @harvest
  Scenario: Delete all associated content when a Source is deleted
    Given users:
      | name              | mail                     | status | roles             |
      | Site manager      | admin@fakeemail.com      | 1      | site manager      |
    And The "source_one" source is harvested
    And I am logged in as "Site manager"
    When I am on "admin/content"
    Then I should see "Gold Prices in London 1950-2008 (Monthly) Harvest"
    Given I am on the "Source one" page
    And I click "Edit"
    And I press "Delete"
    Then I should see "Are you sure you want to delete Source one?"
    And I should see "Source one: 10 Datasets, 18 Resources"
    And I should see "No groups generated during harvest will be affected by this action. They must be manually managed"
    When I select the radio button "Delete content." with the id "edit-dataset-op-0"
    And I press "Delete Sources"
    And I wait for the batch job to finish
    Then I should see "Harvest Source Source one has been deleted."
    And I wait for "3" seconds
    And the content "Gold Prices in London 1950-2008 (Monthly) Harvest" should be "deleted"
    And the content "Table of Gold Prices Harvest" should be "deleted"

  @api @javascript @harvest
  Scenario: Unpublish and mark as orphan all associated content when a Source is deleted
    Given users:
      | name              | mail                     | status | roles             |
      | Site manager      | admin@fakeemail.com      | 1      | site manager     |
    And The "source_one" source is harvested
    And I am logged in as "Site manager"
    When I am on the "Source one" page
    And I click "Edit"
    And I press "Delete"
    Then I should see "Are you sure you want to delete Source one?"
    When I select the radio button "Unpublish content." with the id "edit-dataset-op-1"
    And I press "Delete Sources"
    And I wait for the batch job to finish
    Then I should see "Harvest Source Source one has been deleted."
    And the content "Gold Prices in London 1950-2008 (Monthly) Harvest" should be "unpublished"
    And the content "Gold Prices in London 1950-2008 (Monthly) Harvest" should be "orphaned"

  @api @javascript @harvest
  Scenario: Keep published but mark as orphan all associated content when a Source is deleted
    Given users:
      | name              | mail                     | status | roles             |
      | Site Manager      | admin@fakeemail.com      | 1      | site manager      |
    And The "source_one" source is harvested
    And I am logged in as "Site Manager"
    When I am on the "Source one" page
    And I click "Edit"
    And I press "Delete"
    Then I should see "Are you sure you want to delete Source one?"
    When I select the radio button "Leave content published." with the id "edit-dataset-op-2"
    And I press "Delete Sources"
    And I wait for the batch job to finish
    Then I should see "Harvest Source Source one has been deleted."
    And the content "Gold Prices in London 1950-2008 (Monthly) Harvest" should be "published"
    And the content "Gold Prices in London 1950-2008 (Monthly) Harvest" should be "orphaned"

  @api @harvest
  Scenario: As a user I should have access to see harvest information into dataset node.
    Given users:
      | name            | mail                   | roles           |
      | Site Manager    | admin@fakeemail.com    | site manager    |
    And I am logged in as a "Site Manager"
    And I am on the "Source one" page
    Given The "source_one" source is harvested
    When I am on the "Source one" page
    Then I should see the link "Florida Bike Lanes Harvest"
    And I click "Florida Bike Lanes Harvest"
    And I should see the text "Harvested from Source one"
    And I should see the text "Last Harvest Performed"
    And I should see the text "Harvest Source URI"
    And I should see the text "Harvest Source Title"
    And I should see "2016-06-22" in the "Release Date" row
    And I should see "2016-08-02" in the "Modified Date" row

  @api @harvest
  Scenario: As a user I should have access to see harvest preview information.
    Given users:
      | name            | mail                   | roles           |
      | Site Manager    | admin@fakeemail.com    | site manager    |
    And I am logged in as a "Site Manager"
    And I am on the "Source one" page
    Given The "source_one" source is harvested
    When I am on the "Source one" page
    Then I should see the link "Preview"
    And I click "Preview"
    And I should see the text "Harvest now"
    And I should see the text "Florida Bike Lanes Harvest"

  @api @harvest
  Scenario: As a user I should be able to refresh the preview on the Harvest Source.
    Given users:
      | name            | mail                   | roles           |
      | Site Manager    | admin@fakeemail.com    | site manager    |
    And I am logged in as a "Site Manager"
    And I am on the "Source one" page
    Given The "source_one" source is harvested
    When I am on the "Source one" page
    Then I should see the link "Preview"
    And I click "Preview"
    And I should see the text "Harvest now"
    When I press "Refresh"
    Then The page status should be 'ok'
    And I should see the text "Preview"


  @api @harvest
  Scenario Outline: As a user I should have access to the Event log tab on the Harvest Source.
    Given users:
      | name            | mail                   | roles           |
      | Site Manager    | admin@fakeemail.com    | site manager    |
    And I am logged in as a "<role>"
    And I am on the "Source one" page
    Given The "source_one" source is harvested
    Then I should see the link "Events"
    When I click "Event"
    Then The page status should be 'ok'
    And I should see a table with a class name "harvest-event-log"
    And the table with the class name "harvest-event-log" should have 1 row
    And I should see the text "OK"

    Examples:
      | role              |
      | site manager      |

  @api @harvest
  Scenario Outline: As a user I should see a list of imported datasets on the Harvest Source page.
    Given users:
      | name            | mail                   | roles           |
      | Site manager    | admin@fakeemail.com    | site manager    |
    And The "source_one" source is harvested
    And I am logged in as a "<role>"
    And I am on the "Source one" page
    When I click "Manage Datasets"
    Then the table with the class name "views-table" should have 10 rows

    Examples:
      | role              |
      | site manager      |

  @api @harvest
  Scenario Outline: As user I should see a list of imported datasets in the harvest administration dashboard
    Given users:
      | name            | mail                   | roles           |
      | Site manager    | admin@fakeemail.com    | site manager    |
    And pages:
      | name                       | url                                     |
      | Harvest Dashboard Datasets | /admin/dkan/harvest/dashboard/datasets  |
    And The "source_one" source is harvested
    And I am logged in as a "<role>"
    And I am on the "Harvest Dashboard Datasets" page
    And I should see a table with a class name "views-table"
    And the table with the class name "views-table" should have 10 rows

    Examples:
      | role              |
      | site manager      |

  @api @javascript @harvest
  Scenario Outline: As user I want to filter harvested datasets by orphan status in the harvest administration dashboard
    Given users:
      | name            | mail                   | roles           |
      | Site manager    | admin@fakeemail.com    | site manager    |
    And pages:
      | name                       | url                                     |
      | Harvest Dashboard Datasets | /admin/dkan/harvest/dashboard/datasets  |
    And The "source_one" source is harvested
    And I am logged in as a "<role>"
    And I am on the "Harvest Dashboard Datasets" page
    And I select "Orphans only" from "Orphan Status"
    And I press "Apply"
    Then I wait for "No harvested datasets were found"

    Examples:
      | role              |
      | site manager      |

  @api @javascript @harvest
  Scenario Outline: As user I want to filter harvested datasets by post date in the harvest administration dashboard
    Given users:
      | name            | mail                   | roles           |
      | Site manager    | admin@fakeemail.com    | site manager    |
    And pages:
      | name                       | url                                     |
      | Harvest Dashboard Datasets | /admin/dkan/harvest/dashboard/datasets  |
    And The "source_one" source is harvested
    And I am logged in as a "<role>"
    And I am on the "Harvest Dashboard Datasets" page
    And I fill in "edit-field-harvest-source-issued-value-min-datepicker-popup-0" with "Wednesday, June 1, 2016"
    And I fill in "edit-field-harvest-source-issued-value-max-datepicker-popup-0" with "Thursday, June 30, 2016"
    And I press "Apply"
    And I wait for "3" seconds
    Then I should see "Florida Bike Lanes Harvest"
    And I should see a table with a class name "views-table"
    Then the table with the class name "views-table" should have 1 rows

    Examples:
      | role              |
      | site manager      |

  @api @javascript @harvest
  Scenario Outline: As user I want to filter harvested datasets by updated date in the harvest administration dashboard
    Given users:
      | name            | mail                   | roles           |
      | Site manager    | admin@fakeemail.com    | site manager    |
    And pages:
      | name                       | url                                     |
      | Harvest Dashboard Datasets | /admin/dkan/harvest/dashboard/datasets  |
    And The "source_one" source is harvested
    And I am logged in as a "<role>"
    And I am on the "Harvest Dashboard Datasets" page
    And I fill in "edit-field-harvest-source-modified-value-min-datepicker-popup-0" with "Friday, January 1, 1999"
    And I fill in "edit-field-harvest-source-modified-value-max-datepicker-popup-0" with "Friday, January 1, 1999"
    And I press "Apply"
    Then I wait for "No harvested datasets were found"
    Then I fill in "edit-field-harvest-source-modified-value-min-datepicker-popup-0" with "Friday, January 1, 1999"
    And I fill in "edit-field-harvest-source-modified-value-max-datepicker-popup-0" with "Friday, December 31, 2100"
    And I press "Apply"
    Then I wait for "3" seconds
    And I should see a table with a class name "views-table"
    Then the table with the class name "views-table" should have 10 rows

    Examples:
      | role              |
      | site manager      |

  @api @javascript @harvest
  Scenario Outline: As user I want to delete harvested datasets in the harvest administration dashboard
    Given users:
      | name            | mail                   | roles           |
      | Site manager    | admin@fakeemail.com    | site manager    |
    And pages:
      | name                       | url                                     |
      | Harvest Dashboard Datasets | /admin/dkan/harvest/dashboard/datasets  |
    And The "source_one" source is harvested
    And I am logged in as a "<role>"
    And I am on the "Harvest Dashboard Datasets" page
    And I check the box "views_bulk_operations[0]"
    And I select "Delete item" from "operation"
    And I press "Execute"
    And I press "Confirm"
    Then I wait for "DKAN Harvest Dashboard"
    And I should see "Performed Delete item on 1 item"
    And I should see a table with a class name "views-table"
    Then the table with the class name "views-table" should have 9 rows

    Examples:
      | role              |
      | site manager      |

  @api
  Scenario: Site Manager role should have access to the Harvest actions on the Harvest Dashboard.
    Given pages:
      | name              | url                           |
      | Harvest Dashboard | /admin/dkan/harvest/dashboard |
    And I am logged in as a user with the "site manager" role
    And I am on the "Harvest Dashboard" page
    And I select "Cache Source(s)" from "operation"
    And I select "Harvest (Cache and Migrate) Source(s)" from "operation"
    And I select "Migrate Source(s)" from "operation"

  @api @javascript
  Scenario: Topics set in harvest should set topic field in harvested datasources.
    Given users:
      | name              | mail                     | status | roles             |
      | Site manager      | admin@fakeemail.com      | 1      | site manager      |
    And harvest sources:
      | title         | machine name  | source uri                                                                 | type               | author       | published | topics                 |
      | Topics source | topics_source | http://s3.amazonaws.com/dkan-default-content-files/files/data_harvest.json | datajson_v1_1_json | Site manager | Yes       | Health Care, Education |

    And The "topics_source" source is harvested
    And I am logged in as "Site manager"
    Given I am on the "Topics source" page
    And I click "Edit"
    Then the "Health Care" option from "Topics" should be selected
    And the "Education" option from "Topics" should be selected
