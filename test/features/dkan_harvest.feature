Feature: Dkan Harvest

  @api @javascript @harvest_rollback
  Scenario: As an administrator I should be able to add a harvest source.

    Given users:
    | name               | mail                     | status | roles             |
    | Administrator      | admin@fakeemail.com      | 1      | administrator     |

    And I am logged in as "Administrator"
    And I am on "node/add/harvest-source"
    Then I should see the text "Create Harvest Source"
    And I fill in "Title" with "Source 1"
    And I wait for "2" seconds
    And I fill in "Source URI" with "http://s3.amazonaws.com/dkan-default-content-files/files/data.json"
    And I select "datajson_v1_1_json" from "Type"
    And I press "Save"
    And I wait for "2" seconds
    Then I should see the success message "Harvest Source Source 1 has been created."

  @api @harvest_rollback
  Scenario Outline: As a user I should not be able to add a harvest source.

    Given pages:
    | name                  | url                      |
    | Create Harvest Source | /node/add/harvest-source |

    And I am logged in as a "<role>"
    And I should be denied access to the "Create Harvest Source" page

    Examples:
    | role               |
    | anonymous user     |
    | authenticated user |

  @api @harvest_rollback
  Scenario: As an administrator I should see only the published harvest sources listed on the harvest dashboard.

  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://s3.amazonaws.com/dkan-default-content-files/files/data.json | datajson_v1_1_json | Administrator | Yes       |
    | Source two | source_two   | http://s3.amazonaws.com/dkan-default-content-files/files/data.json | datajson_v1_1_json | Administrator | No        |
  And pages:
    | name               | url                            |
    | Harvest Dashboard  | /admin/dkan/harvest/dashboard  |

  And I am logged in as "Administrator"
  And I am on the "Harvest Dashboard" page
  Then I should see the text "Source one"
  And I should not see the text "Source two"

  @api @harvest_rollback
  Scenario: As a user I should have access to see harvest information into dataset node.
  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://s3.amazonaws.com/dkan-default-content-files/files/data.json | datajson_v1_1_json | Administrator | Yes       |
  And I am logged in as a "Administrator"
  And I am on the "Source one" page
  Given The "source_one" source is harvested
  When I am on the "Source one" page
  Then I should see the link "Wisconsin Polling Places"
  And I click "Wisconsin Polling Places"
  And I should see the text "Harvested from Source one"
  And I should see the text "Harvest Object Id"
  And I should see the text "Harvest Source Id"
  And I should see the text "Harvest Source Title"


  @api @harvest_rollback
  Scenario Outline: As a user I should have access to the Event log tab on the Harvest Source.
  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://s3.amazonaws.com/dkan-default-content-files/files/data.json | datajson_v1_1_json | Administrator | Yes       |
  And I am logged in as a "<role>"
  And I am on the "Source one" page
  Given The "source_one" source is harvested
  Then I should see the link "Event Log"
  When I click "Event Log"
  Then The page status should be 'ok'
  And I should see a table with a class name "harvest-event-log"
  And the table with the class name "harvest-event-log" should have 1 row

  Examples:
  | role               |
  | administrator      |
  | anonymous user     |
  | authenticated user |

  @api @harvest_rollback
  Scenario Outline: As a user I should see a list of imported datasets on the Harvest Source page.
  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://s3.amazonaws.com/dkan-default-content-files/files/data.json | datajson_v1_1_json | Administrator | Yes       |
  And The "source_one" source is harvested
  And I am logged in as a "<role>"
  And I am on the "Source one" page
  Then I should see 10 search results shown on the page in the 'harvest_source' search form

  Examples:
  | role               |
  | administrator      |
  | anonymous user     |
  | authenticated user |


  @api @harvest_rollback
  Scenario Outline: As user I should see a list of imported datasets in the harvest administration dashboard
  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://s3.amazonaws.com/dkan-default-content-files/files/data.json | datajson_v1_1_json | Administrator | Yes       |
  And pages:
    | name                       | url                                     |
    | Harvest Dashboard Datasets | /admin/dkan/harvest/dashboard/datasets  |
  And The "source_one" source is harvested
  And I am logged in as a "<role>"
  And I am on the "Harvest Dashboard Datasets" page
  And I should see a table with a class name "views-table"
  And the table with the class name "views-table" should have 10 rows
  
  Examples:
  | role               |
  | administrator      |

  @api @javascript @harvest_rollback
  Scenario Outline: As user I want to filter harvested datasets by orphan status in the harvest administration dashboard
  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://s3.amazonaws.com/dkan-default-content-files/files/data.json | datajson_v1_1_json | Administrator | Yes       |
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
  | role               |
  | administrator      |


  @api @javascript @harvest_rollback
  Scenario Outline: As user I want to filter harvested datasets by post date in the harvest administration dashboard
  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://s3.amazonaws.com/dkan-default-content-files/files/data.json | datajson_v1_1_json | Administrator | Yes       |
  And pages:
    | name                       | url                                     |
    | Harvest Dashboard Datasets | /admin/dkan/harvest/dashboard/datasets  |
  And The "source_one" source is harvested
  And I am logged in as a "<role>"
  And I am on the "Harvest Dashboard Datasets" page
  And I fill in "edit-created-min" with "06/01/2016"
  And I fill in "edit-created-max" with "06/30/2016"
  And I press "Apply"
  Then I should see "Florida Bike Lanes"
  And I should see a table with a class name "views-table"
  Then the table with the class name "views-table" should have 1 rows
  
  Examples:
  | role               |
  | administrator      |


  @api @javascript @harvest_rollback
  Scenario Outline: As user I want to filter harvested datasets by updated date in the harvest administration dashboard
  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://s3.amazonaws.com/dkan-default-content-files/files/data.json | datajson_v1_1_json | Administrator | Yes       |
  And pages:
    | name                       | url                                     |
    | Harvest Dashboard Datasets | /admin/dkan/harvest/dashboard/datasets  |
  And The "source_one" source is harvested
  And I am logged in as a "<role>"
  And I am on the "Harvest Dashboard Datasets" page
  And I fill in "edit-changed-min" with "06/01/1999"
  And I fill in "edit-changed-max" with "06/30/1999"
  And I press "Apply"
  Then I wait for "No harvested datasets were found"
  Then I fill in "edit-changed-min" with "06/01/1990"
  And I fill in "edit-changed-max" with "06/30/2100"
  And I press "Apply"
  Then I wait for "3" seconds
  And I should see a table with a class name "views-table"
  Then the table with the class name "views-table" should have 10 rows
  
  Examples:
  | role               |
  | administrator      |


  @api @javascript @harvest_rollback
  Scenario Outline: As user I want to delete harvested datasets in the harvest administration dashboard
  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://s3.amazonaws.com/dkan-default-content-files/files/data.json | datajson_v1_1_json | Administrator | Yes       |
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
  | role               |
  | administrator      |