# time:0m22s
@api @javascript
Feature: Theme

  Background:
    Given pages:
      | name          | url                                     |
      | Appearance    | /admin/appearance                       |
      | Settings      | /admin/appearance/settings/nuboot_radix |
    Given users:
      | name    | mail                | roles                |
      | John    | john@example.com    | administrator         |
  
  @noworkflow 
  Scenario: Add custom logo
    Given I am logged in as "John"
    And I am on "Settings" page
    Then I should see "Logo image settings"
    And I uncheck "Use the default logo"
    And I attach the drupal file "dkan/dkan_logo.png" to "files[logo_upload]"
    And I wait for the file upload to finish
    When I press "Save configuration"
    Then I wait for "3" seconds
    Then I should see "The configuration options have been saved"

  @noworkflow
  Scenario: Add custom hero image
    Given I am logged in as "John"
    And I am on "Settings" page
    Then I should see "Hero Unit"
    And I attach the drupal file "dkan/dkan_hero.jpg" to "files[hero_file]"
    And I wait for the file upload to finish
    When I press "Save configuration"
    Then I wait for "3" seconds
    Then I should see "The configuration options have been saved"
