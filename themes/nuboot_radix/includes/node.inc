<?php

/**
 * @file
 * Theme and preprocess functions for nodes.
 */

/**
 * Preprocess variables for node--search-result.tpl.php.
 *
 * Search results formatting for DKAN search page. Relies on facet_icons module.
 */
function nuboot_radix_preprocess_node(&$variables) {
  if ($variables['view_mode'] == 'search_result') {
    $variables['result_icon'] = array(
      '#theme' => 'facet_icons',
      '#type' => $variables['type'],
      '#class' => array('facet-icon'),
    );
    $wrapper = entity_metadata_wrapper('node', $variables['nid']);

    $variables['dataset_list'] = '';
    $variables['group_list'] = NULL;
    $body = $wrapper->body->value();
    $variables['body'] = empty($body) ? '' : $body;
    $path = drupal_lookup_path('alias', "node/" . $wrapper->getIdentifier());
    $variables['node_url'] = $path ? $path : "node/{$variables['nid']}";
    if ($variables['type'] == 'resource' && $wrapper->field_dataset_ref->count() > 0) {
      foreach ($wrapper->field_dataset_ref as $dataset) {
        $variables['dataset_list'] .= '<li>' . $dataset->label() . '</li>';
      }
    }

    $groups = og_get_entity_groups('node', $wrapper->value());
    if (!empty($groups['node'])) {
      $groups = array_map(function ($gid) {
        $g_wrapper = entity_metadata_wrapper('node', $gid);
        return $g_wrapper->label();
      }, array_values($groups['node']));
      $group_list = implode(',', $groups);
      $variables['group_list'] = $group_list;
    }
  }
  // Blog variables.
  if ($variables['type'] == 'blog') {
    $node = node_load($variables['nid']);
    $user = user_load($node->uid);
    if ($node->uid > 0) {
      $user_link = drupal_get_path_alias('user/' . $node->uid);
    }
    $variables['blog_author'] = !empty($user->name) ? $user->name : '';
    $variables['blog_img'] = !empty($node->field_content_image['und'][0]['uri']) ? $node->field_content_image['und'][0]['uri'] : NULL;
    $variables['blog_img_path'] = file_create_url($variables['blog_img']);
    $variables['blog_author_url'] = !empty($user_link) ? $user_link : '';
    $variables['blog_url'] = drupal_get_path_alias('node/' . $node->nid);
  }

  // Grab our citation paragraphs, and place in the main node table.
  if ($variables['type'] === 'dataset' && $variables['view_mode'] === 'full') {
    nuboot_radix_metadata_table($variables);
  }

}

function nuboot_radix_metadata_table(&$variables) {
  if (!empty($variables['content']['field_metadata_extended']['#items'])) {
    foreach($variables['content']['field_metadata_extended']['#items'] as $delta => $field_metadata_extended_item) {
      // Load what we assume to be the main science metadata item, and make
      // sure it's the right bundle.
      $extended_metadata = paragraphs_item_load($field_metadata_extended_item['value']);
      // Get our list of science fields and sort according to display.
      $metadata_fields = array();
      foreach(field_info_instances('paragraphs_item', $extended_metadata->bundle) as $field) {
        $metadata_fields[$field['display']['default']['weight']] = $field;
      }
      ksort($metadata_fields);
      $rows = array();
      foreach($metadata_fields as $field_info) {
        nuboot_radix_metadata_table_add_field($rows, $field_info, $extended_metadata);
      }
      // Replace the whole extended metadata field with our table.
      $field_instance = field_info_instance('node', 'field_metadata_extended', 'dataset');
      $extended_metadata_table = array(
        '#theme' => 'table',
        '#header' => array(t('Field'), t('Value')),
        '#rows' => $rows,
        '#weight' => $field_instance['display']['default']['weight'],
        '#attributes' => array('class' => array('extended-metadata')),
      );
      $variables['content']['field_metadata_extended'][$delta]['entity']['paragraphs_item'][$extended_metadata->item_id] = $extended_metadata_table;
    }
  }
}

function nuboot_radix_metadata_table_add_field(&$rows, $field_info, $extended_metadata) {
  if (!empty($extended_metadata->$field_info['field_name'])) {
    $row_value = field_view_field('paragraphs_item', $extended_metadata, $field_info['field_name'], 'full');
    $row = array();
    $row[] = array('data' => $field_info['label'], 'class' => array('field-label'));
    $row[] = array('data' => $row_value, 'class' => array('field-content'));
    $rows[] = $row;
  }
}
