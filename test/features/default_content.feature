# time:0m16s
Feature: Homepage
  In order to know all the features provided by Dkan
  As a website user
  I need to be able to see default content

  @api
  Scenario: All default content should be loaded
    Given I enable the module "dkan_default_content"
    Then all default content with type "node" and bundle "group" listed in "group" fixture should "be loaded"
    And all default content with type "node" and bundle "resource" listed in "resource" fixture should "be loaded"
    And all default content with type "node" and bundle "dataset" listed in "package" fixture should "be loaded"
    And all default content with type "node" and bundle "dkan_data_story" listed in "dkan_data_story" fixture should "be loaded"
    And all default content with type "node" and bundle "data_dashboard" listed in "data_dashboard" fixture should "be loaded"
    And all default content with type "node" and bundle "page" listed in "page" fixture should "be loaded"
    And all default content with type "visualization" and bundle "ve_chart" listed in "visualization_entity" fixture should "be loaded"

  @api
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

  @api @disablecaptcha
  Scenario: Enable the default content module back
    Given I am logged in as a user with the "administrator" role
    Then I enable the module "dkan_default_content"