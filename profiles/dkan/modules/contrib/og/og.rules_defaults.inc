<?php
/**
 * @file
 * og.rules_defaults.inc
 */

/**
 * Implementation of hook_default_rules_configuration().
 */
function og_default_rules_configuration() {
  $items = array();
  $items['rules_og_member_active'] = entity_import('rules_config', '{ "rules_og_member_active" : {
    "LABEL" : "OG member subscribe (Active)",
    "PLUGIN" : "reaction rule",
    "ACTIVE" : false,
    "REQUIRES" : [ "rules", "og" ],
    "ON" : [ "og_user_insert" ],
    "IF" : [
      { "data_is" : { "data" : [ "og-membership:state" ], "value" : "1" } },
      { "entity_is_of_type" : { "entity" : [ "og-membership:group" ], "type" : "node" } }
    ],
    "DO" : [
      { "variable_add" : {
          "USING" : { "type" : "node", "value" : [ "og-membership:group" ] },
          "PROVIDE" : { "variable_added" : { "group" : "Added variable" } }
        }
      },
      { "mail" : {
          "to" : [ "account:mail" ],
          "subject" : "Your membership request was approved for \'[group:title]\'\r\n",
          "message" : "[account:name],\r\n\r\nYou are now a member in the group \'[group:title]\' located at [group:url]"
        }
      },
      { "mail" : {
          "to" : [ "group:author:mail" ],
          "subject" : "[account:name] joined [group:title]",
          "message" : "[group:author:name],\r\n\r\n[account:name] joined [group:title] in [group:url]"
        }
      }
    ]
  }
}');
  $items['rules_og_member_pending'] = entity_import('rules_config', '{ "rules_og_member_pending" : {
    "LABEL" : "OG member subscribe (Pending)",
    "PLUGIN" : "reaction rule",
    "ACTIVE" : false,
    "REQUIRES" : [ "rules", "og" ],
    "ON" : [ "og_user_insert" ],
    "IF" : [
      { "data_is" : { "data" : [ "og-membership:state" ], "value" : "2" } },
      { "entity_is_of_type" : { "entity" : [ "og-membership:group" ], "type" : "node" } }
    ],
    "DO" : [
      { "variable_add" : {
          "USING" : { "type" : "node", "value" : [ "og-membership:group" ] },
          "PROVIDE" : { "variable_added" : { "group" : "Added variable" } }
        }
      },
      { "mail" : {
          "to" : [ "account:mail" ],
          "subject" : "Your membership request for \'[group:title]\'",
          "message" : "[account:name],\r\n\r\nYour membership to group \'[group:title]\' located at [group:url] is pending approval."
        }
      },
      { "mail" : {
          "to" : [ "group:author:mail" ],
          "subject" : "[account:name] membership request [group:title]",
          "message" : "[group:author:name],\r\n\r\n[account:name] requests membership for group \'[group:title]\' in [group:url].\r\n\r\n[og_membership:og-membership-request]"
        }
      }
    ]
  }
}');

  $items['rules_og_new_content'] = entity_import('rules_config', '{ "rules_og_new_content" : {
    "LABEL" : "OG new content notification",
    "PLUGIN" : "reaction rule",
    "ACTIVE" : false,
    "REQUIRES" : [ "og", "rules" ],
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
            { "mail" : {
                "to" : [ "list-item:mail" ],
                "subject" : "New post in group",
                "message" : "Hello [list-item:name],\r\n\r\nThere is a new post called [node:title] that belongs to one of the groups you are subscribed to.\r\n\r\n[site:name]\r\n",
                "from" : [ "" ]
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
