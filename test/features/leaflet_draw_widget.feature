# time:0m11.71s
@api @javascript @disablecaptcha
Feature: Leaflet Map Widget

  Scenario: Adds "New Table Widget" block to home page using panels ipe editor

    Given "Tags" terms:
      | name    |
      | New Tag |
    Given users:
      | name    | mail                | roles  |
      | Gabriel | gabriel@example.com | editor |
    Given groups:
      | title    | author | published |
      | Group 01 | Admin  | Yes       |
    And group memberships:
      | user    | group    | role on group        | membership status |
      | Gabriel | Group 01 | administrator member | Active            |
    And datasets:
      | title                  | author | published | tags    | description | publisher |
      | This is a test dataset | admin  | Yes       | New Tag | Test        | Group 01  |
    And I am logged in as "Gabriel"
    And I visit the "This is a test dataset" page
    And I click "Edit"
    And I hide the admin menu
    Then I should see "Spatial / Geographical Coverage Area"
    And I should see the link "Map" in the "dataset spatial" region
    And I should see the link "GeoJSON" in the "dataset spatial" region
    And I should see the link "Points" in the "dataset spatial" region
    # Default Map element
    Then the "div" element with id set to "leaflet-widget_field-spatial" in the "dataset spatial" region should be visible
    And the "textarea" element with id set to "leaflet-widget_field-spatial-geojson-textarea" in the "dataset spatial" region should not be visible
    And the "input" element with id set to "leaflet-widget_field-spatial-points-input" in the "dataset spatial" region should not be visible
    # GeoJSON tab
    When I click "GeoJSON" in the "dataset spatial" region
    Then I should see "Enter GeoJSON" in the "dataset spatial" region
    And the "textarea" element with id set to "leaflet-widget_field-spatial-geojson-textarea" in the "dataset spatial" region should be visible
    And the "div" element with id set to "leaflet-widget_field-spatial" in the "dataset spatial" region should not be visible
    # Points tab
    When I click "Points" in the "dataset spatial" region
    Then the "div" element with id set to "leaflet-widget_field-spatial" in the "dataset spatial" region should be visible
    And the "input" element with id set to "leaflet-widget_field-spatial-points-input" in the "dataset spatial" region should be visible
    And I should see the link "Add Points" in the "dataset spatial" region
