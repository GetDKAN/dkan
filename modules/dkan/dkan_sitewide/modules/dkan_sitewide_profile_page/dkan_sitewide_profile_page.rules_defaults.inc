<?php
/**
 * @file
 * dkan_sitewide_profile_page.rules_defaults.inc
 */

/**
 * Implements hook_default_rules_configuration().
 */
function dkan_sitewide_profile_page_default_rules_configuration() {
  $items = array();
  $items['rules_user_dashboard_page_after_login'] = entity_import('rules_config', '{ "rules_user_dashboard_page_after_login" : {
      "LABEL" : "User dashboard page after login",
      "PLUGIN" : "reaction rule",
      "TAGS" : [ "after", "dashboard", "login", "user" ],
      "REQUIRES" : [ "rules" ],
      "ON" : [ "user_login" ],
      "DO" : [ { "redirect" : { "url" : "user" } } ]
    }
  }');
  return $items;
}
