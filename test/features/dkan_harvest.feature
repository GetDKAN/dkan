Feature: Dkan Harvest

  @api @javascript
  Scenario: As an administrator I should be able to add a harvest source.

    Given users:
    | name               | mail                     | status | roles             |
    | Administrator      | admin@fakeemail.com      | 1      | administrator     |

    And I am logged in as "Administrator"
    And I am on "node/add/harvest-source"
    Then I should see the text "Create Harvest Source"
    And I fill in "Title" with "Source 1"
    And I wait for "2" seconds
    And I fill in "Source URI" with "https://data.mo.gov/data.json"
    And I select "datajson_v1_1_json" from "Type"
    And I press "Save"
    And I wait for "2" seconds
    Then I should see the success message "Harvest Source Source 1 has been created."

  @api
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

  @api
  Scenario: As an administrator I should see only the published harvest sources listed on the harvest dashboard.

  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://demo.getdkan.com/data.json | datajson_v1_1_json | Administrator | Yes       |
    | Source two | source_two   | http://demo.getdkan.com/data.json | datajson_v1_1_json | Administrator | No        |
  And pages:
    | name               | url                            |
    | Harvest Dashboard  | /admin/dkan/harvest/dashboard  |

  And I am logged in as "Administrator"
  And I am on the "Harvest Dashboard" page
  Then I should see the text "Source one"
  And I should not see the text "Source two"

  @api
  Scenario: As a user I should have access to see harvest information into dataset node.
  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://demo.getdkan.com/data.json | datajson_v1_1_json | Administrator | Yes       |
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


  @api
  Scenario Outline: As a user I should have access to the Event log tab on the Harvest Source.
  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://demo.getdkan.com/data.json | datajson_v1_1_json | Administrator | Yes       |
  And I am logged in as a "<role>"
  And I am on the "Source one" page
  Given The "source_one" source is harvested
  Then I should see the link "Event Log"
  When I click "Event Log"
  Then The page status should be 'ok'
  And I should see a harvest event log table
  And the harvest event log table should have 1 row

  Examples:
  | role               |
  | administrator      |
  | anonymous user     |
  | authenticated user |

  @api
  Scenario Outline: As a user I should see a list of imported datasets on the Harvest Source page.
  Given users:
    | name             | mail                   | roles           |
    | Administrator    | admin@fakeemail.com    | administrator   |
  And harvest sources:
    | title      | machine name | source uri                        | type               | author        | published |
    | Source one | source_one   | http://demo.getdkan.com/data.json | datajson_v1_1_json | Administrator | Yes       |
  And The "source_one" source is harvested
  And I am logged in as a "<role>"
  And I am on the "Source one" page
  Then I should see 4 search results shown on the page in the 'harvest_source' search form

  Examples:
  | role               |
  | administrator      |
  | anonymous user     |
  | authenticated user |
