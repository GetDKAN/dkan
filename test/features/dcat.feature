Feature: DCAT

  @api @noworkflow
  Scenario: Check access to dcat endpoint
    Given I am on "catalog.xml"
    Then I should see a valid catalog xml


