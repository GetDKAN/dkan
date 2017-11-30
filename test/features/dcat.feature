# time:0m2.5s
Feature: DCAT

  @api
  Scenario: Check access to dcat endpoint
    Given I am on the homepage
    Then I should see a valid catalog xml
