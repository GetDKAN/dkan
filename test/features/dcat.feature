Feature: DCAT

  @api @noworkflow
  Scenario: Check access to dcat endpoint
    Given I am on "catalog.xml"
    Then the response status code should be 200


