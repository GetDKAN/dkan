<?php

/**
 * @file
 * Provide diff functions for the node module.
 */

/**
 * Implements hook_entity_diff().
 *
 * This function compares core node properties. This is currently limited to:
 *   - title: The title of the node.
 *
 * @param object $old_node
 *   The older node revision.
 * @param object $new_node
 *   The newer node revision.
 * @param array $context
 *   An associative array containing:
 *   - entity_type: The entity type; e.g., 'node' or 'user'.
 *   - old_entity: The older entity.
 *   - new_entity: The newer entity.
 *   - view_mode: The view mode to use. Defaults to FALSE. If no view mode is
 *                given, the recommended fallback view mode is 'default'.
 *   - states: An array of view states. These could be one of:
 *     - raw: The raw value of the diff, the classic 7.x-2.x view.
 *     - rendered: The rendered HTML as determined by the view mode. Only
 *                 return markup for this state if the value is normally shown
 *                 by this view mode. The user will most likely be able to see
 *                 the raw or raw_plain state, so this is optional.
 *
 *                 The rendering state is a work in progress.
 *
 *     Conditionally, you can get these states, but setting these will override
 *     the user selectable markdown method.
 *
 *     - raw_plain: As raw, but text should be markdowned.
 *     - rendered_plain: As rendered, but text should be markdowned.
 *
 * @return array
 *   An associative array of values keyed by the entity property.
 *
 *   This is effectively an unnested Form API-like structure.
 *
 *   States are returned as follows:
 *
 *   $results['line'] = array(
 *     '#name' => t('Line'),
 *     '#states' => array(
 *       'raw' => array(
 *         '#old' => '<p class="line">This was the old line number [tag].</p>',
 *         '#new' => '<p class="line">This is the new line [tag].</p>',
 *       ),
 *       'rendered' => array(
 *         '#old' => '<p class="line">This was the old line number <span class="line-number">57</span>.</p>',
 *         '#new' => '<p class="line">This is the new line <span class="line-number">57</span>.</p>',
 *       ),
 *     ),
 *   );
 *
 *   For backwards compatability, no changes are required to support states,
 *   but it is recommended to provide a better UI for end users.
 *
 *   For example, the following example is equivalent to returning the raw
 *   state from the example above.
 *
 *   $results['line'] = array(
 *     '#name' => t('Line'),
 *     '#old' => '<p class="line">This was the old line number [tag].</p>',
 *     '#new' => '<p class="line">This is the new line [tag].</p>',
 *   );
 */
function node_entity_diff($old_node, $new_node, $context) {
  $result = array();
  if ($context['entity_type'] == 'node') {
    $type = node_type_get_type($new_node);
    $result['title'] = array(
      '#name' => $type->title_label,
      '#states' => array(),
      '#weight' => -5,
      '#settings' => array(
        // Global setting - 'diff_show_header_' . $entity_type
        'show_header' => variable_get('diff_show_header_node', 1),
      ),
    );
    foreach ($context['states'] as $state) {
      switch ($state) {
        case 'rendered':
          $result['title']['#states'][$state] = array(
            '#old' => l($old_node->title, 'node/' . $old_node->title),
            '#new' => l($new_node->title, 'node/' . $new_node->title),
          );
          break;

        // We specify a default so that the title is allows compared.
        case 'raw':
        default:
          $result['title']['#states'][$state] = array(
            '#old' => array($old_node->title),
            '#new' => array($new_node->title),
          );
          break;
      }
    }
  }
  return $result;
}
