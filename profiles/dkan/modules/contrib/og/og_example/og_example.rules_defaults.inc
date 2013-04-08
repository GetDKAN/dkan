<?php
/**
 * @file
 * og_example.rules_defaults.inc
 */

/**
 * Implements hook_default_rules_configuration().
 */
function og_example_default_rules_configuration() {
  $items = array();
  $items['rules_og_new_content_message'] = entity_import('rules_config', '{ "rules_og_new_content_message" : {
      "LABEL" : "OG new content notification (With Message)",
      "PLUGIN" : "reaction rule",
      "REQUIRES" : [ "og", "rules", "message_notify" ],
      "ON" : [ "node_insert" ],
      "IF" : [ { "og_entity_is_group_content" : { "entity" : [ "node" ] } } ],
      "DO" : [
        { "og_get_members" : {
            "USING" : { "group_content" : [ "node" ] },
            "PROVIDE" : { "group_members" : { "group_members" : "List of group members" } }
          }
        },
        { "LOOP" : {
            "USING" : { "list" : [ "group-members" ] },
            "ITEM" : { "list_item" : "Current list item" },
            "DO" : [
              { "entity_create" : {
                  "USING" : {
                    "type" : "message",
                    "param_type" : "og_new_content",
                    "param_user" : [ "list-item" ]
                  },
                  "PROVIDE" : { "entity_created" : { "entity_created" : "Message entity" } }
                }
              },
              { "data_set" : {
                  "data" : [ "entity-created:field-node-reference" ],
                  "value" : [ "node" ]
                }
              },
              { "entity_save" : { "data" : [ "entity-created" ], "immediate" : 1 } },
              { "message_notify_process" : {
                  "message" : [ "entity-created" ],
                  "save_on_fail" : 0,
                  "save_on_success" : 0
                }
              }
            ]
          }
        }
      ]
    }
  }');
  return $items;
}


/**
 * Implements hook_default_rules_configuration_alter().
 *
 * Remove OG's default notification Rules, as this modules has a superior
 * version of it, that uses Message notify.
 */
function og_example_default_rules_configuration_alter(&$configs) {
  unset($configs['rules_og_new_content']);
}
