Feature: Project Open Data
  In order to know that the site is Project Open Data complient
  As a website user
  I should see valid data.json

  Scenario: Viewing valid data.json
    Given I am an anonymous user
    And I am on "/data.json"
    Then I should see valid data.json
