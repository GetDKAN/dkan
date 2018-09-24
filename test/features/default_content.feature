# time:0m16s
@customizable @api @disablecaptcha
Feature: Default Content

  Scenario: Viewing the site title
    Given I am on the homepage
    Then I should see "Welcome to DKAN"

  Scenario: Viewing main menu
    Given I am on the homepage
    Then I should see "Datasets"
    And I should see "Groups"
    And I should see "About"
    And I should see "Topics"
    And I should see "Stories"
    And I should see "Dashboards"

  Scenario: Viewing sections
    Given I am on the homepage
    Then I should see "Latest Data Stories" in the "content" region
    And I should see "Groups" in the "content" region
    And I should see "Dashboards" in the "content" region

  Scenario: All default content should be loaded
    Given I enable the module "dkan_default_content"
    Then all default content with type "node" and bundle "group" listed in "group" fixture should "be loaded"
    And all default content with type "node" and bundle "resource" listed in "resource" fixture should "be loaded"
    And all default content with type "node" and bundle "dataset" listed in "package" fixture should "be loaded"
    And all default content with type "node" and bundle "dkan_data_story" listed in "dkan_data_story" fixture should "be loaded"
    And all default content with type "node" and bundle "data_dashboard" listed in "data_dashboard" fixture should "be loaded"
    And all default content with type "node" and bundle "page" listed in "page" fixture should "be loaded"
    And all default content with type "visualization" and bundle "ve_chart" listed in "visualization_entity" fixture should "be loaded"

  Scenario: All default content should be removed if the module is disabled
    Given I disable the module "dkan_default_content"
    Then all default content with type "node" and bundle "group" listed in "group" fixture should "not be loaded"
    And all default content with type "node" and bundle "resource" listed in "resource" fixture should "not be loaded"
    And all default content with type "node" and bundle "dataset" listed in "package" fixture should "not be loaded"
    And all default content with type "node" and bundle "dkan_data_story" listed in "dkan_data_story" fixture should "not be loaded"
    And all default content with type "node" and bundle "data_dashboard" listed in "data_dashboard" fixture should "not be loaded"
    And all default content with type "visualization" and bundle "ve_chart" listed in "visualization_entity" fixture should "not be loaded"
    # Pages should not get removed when the default content module is disabled.
    And all default content with type "node" and bundle "page" listed in "page" fixture should "be loaded"

  Scenario: Enable the default content module back
    Given I am logged in as a user with the "administrator" role
    Then I enable the module "dkan_default_content"
