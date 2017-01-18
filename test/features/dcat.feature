# time:0m5.29s
Feature: DCAT

  @api
  Scenario: Check access to dcat endpoint
    Given I am on the homepage
    Then I should see a valid catalog xml
