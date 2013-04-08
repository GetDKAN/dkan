<?php
/**
 * @file
 * og_example.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function og_example_ctools_plugin_api() {
  list($module, $api) = func_get_args();
  if ($module == "page_manager" && $api == "pages_default") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_default_message_type().
 */
function og_example_default_message_type() {
  $items = array();
  $items['og_new_content'] = entity_import('message_type', '{
    "name" : "og_new_content",
    "description" : "OG new content",
    "argument_keys" : [],
    "argument" : [],
    "category" : "message_type_email",
    "data" : { "purge" : { "override" : 0, "enabled" : 0, "quota" : "", "days" : "" } },
    "language" : "",
    "arguments" : null,
    "message_text" : { "und" : [
        {
          "value" : "Hello [message:user:name],\\r\\n\\r\\nThere is a new post called \\u003Ca href=\\u0022[message:field-node-reference:url]\\u0022\\u003E[message:field-node-reference:title]\\u003C\\/a\\u003E that belongs to one of the groups you are subscribed to.\\r\\n\\r\\nCheers,\\r\\n[site:name]",
          "format" : "full_html",
          "safe_value" : "\\u003Cp\\u003EHello [message:user:name],\\u003C\\/p\\u003E\\n\\u003Cp\\u003EThere is a new post called \\u003Ca href=\\u0022[message:field-node-reference:url]\\u0022\\u003E[message:field-node-reference:title]\\u003C\\/a\\u003E that belongs to one of the groups you are subscribed to.\\u003C\\/p\\u003E\\n\\u003Cp\\u003ECheers,\\u003Cbr \\/\\u003E\\n[site:name]\\u003C\\/p\\u003E\\n"
        }
      ]
    },
    "message_text_subject" : { "und" : [
        {
          "value" : "New post in group -- [message:field-node-reference:title]",
          "format" : null,
          "safe_value" : "New post in group -- [message:field-node-reference:title]"
        }
      ]
    },
    "rdf_mapping" : []
  }');
  return $items;
}

/**
 * Implements hook_node_info().
 */
function og_example_node_info() {
  $items = array(
    'group' => array(
      'name' => t('Group'),
      'base' => 'node_content',
      'description' => t('The group that will have members and content associated with.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'post' => array(
      'name' => t('Post'),
      'base' => 'node_content',
      'description' => t('Content that will belong to a single or multiple groups.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  return $items;
}
