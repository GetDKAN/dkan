<?php

/**
 * @file
 * Menu callbacks for hook_menu().
 */

/**
 * Menu callback - show latest diff for a given node.
 */
function diff_latest($node) {
  $revisions = node_revision_list($node);
  $new = array_shift($revisions);
  $old = array_shift($revisions);
  drupal_goto("node/{$node->nid}/revisions/view/{$old->vid}/{$new->vid}");
}

/**
 * Menu callback - an overview table of older revisions.
 *
 * Generate an overview table of older revisions of a node and provide
 * an input form to select two revisions for a comparison.
 */
function diff_diffs_overview($node) {
  drupal_set_title(t('Revisions for %title', array('%title' => $node->title)), PASS_THROUGH);
  return drupal_get_form('diff_node_revisions', $node);
}

/**
 * Input form to select two revisions.
 */
function diff_node_revisions($form, $form_state, $node) {
  $form['nid'] = array(
    '#type' => 'hidden',
    '#value' => $node->nid,
  );

  $revision_list = node_revision_list($node);

  if (count($revision_list) > REVISION_LIST_SIZE) {
    // If the list of revisions is longer than the number shown on one page
    // split the array.
    $page = isset($_GET['page']) ? $_GET['page'] : '0';
    $revision_chunks = array_chunk(node_revision_list($node), REVISION_LIST_SIZE);
    $revisions = $revision_chunks[$page];
    // Set up global pager variables as would 'pager_query' do.
    // These variables are then used in the theme('pager') call later.
    global $pager_page_array, $pager_total, $pager_total_items;
    $pager_total_items[0] = count($revision_list);
    $pager_total[0] = ceil(count($revision_list) / REVISION_LIST_SIZE);
    $pager_page_array[0] = max(0, min($page, ((int) $pager_total[0]) - 1));
  }
  else {
    $revisions = $revision_list;
  }

  $revert_permission = FALSE;
  if ((user_access('revert revisions') || user_access('administer nodes')) && node_access('update', $node)) {
    $revert_permission = TRUE;
  }
  $delete_permission = FALSE;
  if ((user_access('delete revisions') || user_access('administer nodes')) && node_access('delete', $node)) {
    $delete_permission = TRUE;
  }

  foreach ($revisions as $revision) {
    $operations = array();
    $revision_ids[$revision->vid] = '';

    $revision_log = ($revision->log != '') ? '<p class="revision-log">' . filter_xss($revision->log) . '</p>' : '';
    if ($revision->current_vid > 0) {
      $form['info'][$revision->vid] = array(
        '#markup' => t('!date by !username', array(
          '!date' => l(format_date($revision->timestamp, 'small'), "node/$node->nid"),
          '!username' => theme('username', array('account' => $revision)))) . $revision_log,
      );
    }
    else {
      $diff_date = l(format_date($revision->timestamp, 'small'), "node/$node->nid/revisions/$revision->vid/view");
      $form['info'][$revision->vid] = array(
        '#markup' => t('!date by !username', array(
          '!date' => $diff_date,
          '!username' => theme('username', array('account' => $revision)))
        ) . $revision_log,
      );
      if ($revert_permission) {
        $operations[] = array(
          '#markup' => l(t('Revert'), "node/$node->nid/revisions/$revision->vid/revert"),
        );
      }
      if ($delete_permission) {
        $operations[] = array(
          '#markup' => l(t('Delete'), "node/$node->nid/revisions/$revision->vid/delete"),
        );
      }
      // Set a dummy, even if the user has no permission for the other
      // operations, so that we can check if the operations array is
      // empty to know if the row denotes the current revision.
      $operations[] = array();
    }
    $form['operations'][$revision->vid] = $operations;

  }
  $new_vid = key($revision_ids);
  next($revision_ids);
  $old_vid = key($revision_ids);
  $form['diff']['old'] = array(
    '#type' => 'radios',
    '#options' => $revision_ids,
    '#default_value' => $old_vid,
  );
  $form['diff']['new'] = array(
    '#type' => 'radios',
    '#options' => $revision_ids,
    '#default_value' => $new_vid,
  );

  $form['submit'] = array('#type' => 'submit', '#value' => t('Compare'));

  if (count($revision_list) > REVISION_LIST_SIZE) {
    $form['#suffix'] = theme('pager');
  }
  $form['#attached'] = diff_build_attachments(TRUE);
  return $form;
}

/**
 * Submit code for input form to select two revisions.
 */
function diff_node_revisions_submit($form, &$form_state) {
  // The ids are ordered so the old revision is always on the left.
  $old_vid = min($form_state['values']['old'], $form_state['values']['new']);
  $new_vid = max($form_state['values']['old'], $form_state['values']['new']);
  $form_state['redirect'] = 'node/' . $form_state['values']['nid'] . '/revisions/view/' . $old_vid . '/' . $new_vid;
}

/**
 * Validation for input form to select two revisions.
 */
function diff_node_revisions_validate($form, &$form_state) {
  $old_vid = $form_state['values']['old'];
  $new_vid = $form_state['values']['new'];
  if ($old_vid == $new_vid || !$old_vid || !$new_vid) {
    form_set_error('diff', t('Select different revisions to compare.'));
  }
}

/**
 * Create a comparison for the node between versions 'old_vid' and 'new_vid'.
 *
 * @param object $node
 *   Node on which to perform comparison
 * @param integer $old_vid
 *   Version ID of the old revision.
 * @param integer $new_vid
 *   Version ID of the new revision.
 */
function diff_diffs_show($node, $old_vid, $new_vid, $state = NULL) {
  // Attaches the CSS.
  $build['#attached'] = diff_build_attachments();

  $default_state = variable_get('diff_default_state_node', 'raw');
  if (empty($state)) {
  	$state = $default_state;
  }
  $state = str_replace('-', '_', $state);
  if (!array_key_exists($state, diff_available_states())) {
    $state = $default_state;
  }

  // Same title as the 'Revisions' tab. Blocked by non-page requests.
  if (node_is_page($node)) {
    drupal_set_title(t('Revisions for %title', array('%title' => $node->title)), PASS_THROUGH);
  }
  $node_revisions = node_revision_list($node);

  $old_node = node_load($node->nid, $old_vid);
  $new_node = node_load($node->nid, $new_vid);

  // Generate table header (date, username, log message).
  $old_header = t('!date by !username', array(
    '!date' => l(format_date($old_node->revision_timestamp), "node/$node->nid/revisions/$old_node->vid/view", array('absolute' => 1)),
    '!username' => theme('username', array('account' => $node_revisions[$old_vid])),
  ));
  $new_header = t('!date by !username', array(
    '!date' => l(format_date($new_node->revision_timestamp), "node/$node->nid/revisions/$new_node->vid/view", array('absolute' => 1)),
    '!username' => theme('username', array('account' => $node_revisions[$new_vid])),
  ));

  $old_log = $old_node->log != '' ? '<p class="revision-log">' . filter_xss($old_node->log) . '</p>' : '';
  $new_log = $new_node->log != '' ? '<p class="revision-log">' . filter_xss($new_node->log) . '</p>' : '';

  // Generate previous diff/next diff links.
  $nav_suffix = ($default_state != $state) ? '/' . str_replace('_', '-', $state) : '';
  $next_vid = _diff_get_next_vid($node_revisions, $new_vid);
  if ($next_vid) {
    $next_link = l(t('Next difference >'), 'node/' . $node->nid . '/revisions/view/' . $new_vid . '/' . $next_vid . $nav_suffix, array('absolute' => 1));
  }
  else {
    $next_link = '';
  }
  $prev_vid = _diff_get_previous_vid($node_revisions, $old_vid);
  if ($prev_vid) {
    $prev_link = l(t('< Previous difference'), 'node/' . $node->nid . '/revisions/view/' . $prev_vid . '/' . $old_vid . $nav_suffix, array('absolute' => 1));
  }
  else {
    $prev_link = '';
  }

  $header = _diff_default_header($old_header, $new_header);
  $rows = array();
  if ($old_log || $new_log) {
    $rows['logs'] = array(
      array(
        'data' => $old_log,
        'colspan' => 2,
      ),
      array(
        'data' => $new_log,
        'colspan' => 2,
      ),
    );
  }
  $rows['navigation'] = array(
    array(
      'data' => $prev_link,
      'class' => array('diff-prevlink'),
      'colspan' => 2,
    ),
    array(
      'data' => $next_link,
      'class' => array('diff-nextlink'),
      'colspan' => 2,
    ),
  );

  $links = array();
  foreach (diff_available_states('node') as $alternative_state => $label) {
    if ($alternative_state == $state) {
      // @todo: Should we show both or just alternatives?
    }
    $links[$alternative_state] = array(
      'title' => $label,
      'href' => "node/{$node->nid}/revisions/view/{$old_vid}/{$new_vid}" . ($alternative_state == $default_state ? '' : '/' . str_replace('_', '-', $alternative_state)),
    );
  }
  if (count($links) > 1) {
    $state_links = theme('links', array(
      'links' => $links,
      'attributes' => array('class' => array('links', 'inline')),
    ));
    $rows['states'] = array(
      array(
        'data' => $state_links,
        'class' => 'diff-links',
        'colspan' => 4,
      ),
    );
  }
  $rows = array_merge($rows, _diff_body_rows($old_node, $new_node, $state));

  $build['diff_table'] = array(
    '#theme' => 'table__diff__standard',
    '#header' => $header,
    '#rows' => $rows,
    '#attributes' => array('class' => array('diff')),
    '#colgroups' => _diff_default_cols(),
    '#sticky' => FALSE,
  );

  // Allow users to hide or set the display mode of the preview.
  if (node_is_page($node) && $view_mode = variable_get('diff_view_mode_preview_node_' . $new_node->type, 'full')) {
    $header = '';
    if ($node->vid == $new_vid) {
      $header .= '<div class="diff-section-title">' . t('Current revision:') . '</div>';
    }
    else {
      $header .= '<div class="diff-section-title">' . t('Revision of @new_date:', array('@new_date' => format_date($new_node->revision_timestamp))) . '</div>';
    }
    $build['diff_preview']['header']['#markup'] = $header;
    // Don't include node links or comments when viewing the diff.
    $build['diff_preview']['content'] = node_view($new_node, $view_mode);
    if (isset($build['diff_preview']['content']['links'])) {
      unset($build['diff_preview']['content']['links']);
    }
    if (isset($build['diff_preview']['content']['comments'])) {
      unset($build['diff_preview']['content']['comments']);
    }
  }
  return $build;
}

/**
 * Creates an array of rows which represent the difference between nodes.
 *
 * @param object $old_node
 *   Node for comparison which will be displayed on the left side.
 * @param object $new_node
 *   Node for comparison which will be displayed on the right side.
 * @param boolean $state
 *   The state to render for the diff.
 */
function _diff_body_rows($old_node, $new_node, $state = 'raw') {
  // This is an unique index only, so no need for drupal_static().
  static $table_row_counter = 0;

  if ($theme = variable_get('diff_theme', 'default')) {
    drupal_add_css(drupal_get_path('module', 'diff') . "/css/diff.{$theme}.css");
  }
  module_load_include('inc', 'diff', 'includes/node');

  $rows = array();
  $any_visible_change = FALSE;
  $context = array(
    'entity_type' => 'node',
    'states' => array($state),
    'view_mode' => 'diff_standard',
  );

  $node_diffs = diff_compare_entities($old_node, $new_node, $context);

  // Track line numbers between multiple diffs.
  $line_stats = array(
    'counter' => array('x' => 0, 'y' => 0),
    'offset' => array('x' => 0, 'y' => 0),
  );

  // Render diffs for each.
  foreach ($node_diffs as $node_diff) {
    $show_header = !empty($node_diff['#name']);
    // These are field level settings.
    if ($show_header && isset($node_diff['#settings']['show_header'])) {
      $show_header = $show_header && $node_diff['#settings']['show_header'];
    }

    // Line counting and line header options.
    if (empty($node_diff['#settings']['line_counter'])) {
      $line_counter = FALSE;
    }
    else {
      $line_counter = $node_diff['#settings']['line_counter'];
    }
    // Every call to 'line' resets the counters.
    if ($line_counter) {
      $line_stats['counter']['x'] = 0;
      $line_stats['counter']['y'] = 0;
      if ($line_counter == 'line' && 0) {
        $line_stats['offset']['x'] = 0;
        $line_stats['offset']['y'] = 0;
      }
      $line_stats_ref = $line_stats;
    }
    else {
      $line_stats_ref = NULL;
    }

    list($old, $new) = diff_extract_state($node_diff, $state);
    if ($node_diff_rows = diff_get_rows($old, $new, $line_counter && $line_counter != 'hidden', $line_stats_ref)) {
      if ($line_counter && $line_counter != 'line') {
        $line_stats['offset']['x'] += $line_stats_ref['counter']['x'];
        $line_stats['offset']['y'] += $line_stats_ref['counter']['y'];
      }
      if ($show_header) {
        $rows['diff-header-' . $table_row_counter++] = array(
          array(
            'data' => t('Changes to %name', array('%name' => $node_diff['#name'])),
            'class' => 'diff-section-title',
            'colspan' => 4,
          ),
        );
      }
      // To avoid passing counter to the Diff engine, index rows manually here
      // to allow modules to interact with the table. i.e. no array_merge().
      foreach ($node_diff_rows as $row) {
        $rows['diff-row-' . $table_row_counter++] = $row;
      }
      $any_visible_change = TRUE;
    }
  }
  if (!$any_visible_change) {
    $rows['diff-empty-' . $table_row_counter++] = array(
      array(
        'data' => t('No visible changes'),
        'class' => 'diff-section-title',
        'colspan' => 4,
      ),
    );
    // @todo: revise this.
    // Needed to keep safari happy.
    $rows['diff-empty-' . $table_row_counter++] = array(
      array('data' => ''),
      array('data' => ''),
      array('data' => ''),
      array('data' => ''),
    );
  }

  return $rows;
}

/**
 * Generic callback to compare two entities.
 */
function diff_compare_entities($left_entity, $right_entity, $context) {
  $entity_type = $context['entity_type'];
  list(, , $bundle) = entity_extract_ids($entity_type, $right_entity);
  $context['bundle'] = $bundle;
  $context['old_entity'] = $left_entity;
  $context['new_entity'] = $right_entity;
  $context += array(
    'states' => array('raw'),
    'view_mode' => FALSE,
    'language' => LANGUAGE_NONE,
  );

  $diff = module_invoke_all('entity_diff', $left_entity, $right_entity, $context);

  // Allow other modules to interact directly with the results.
  drupal_alter('entity_diff', $diff, $context);

  // We start off assuming all form elements are in the correct order.
  $diff['#sorted'] = TRUE;

  // Field rows. Recurse through all child elements.
  $count = 0;
  foreach (element_children($diff) as $key) {
    if (!isset($diff[$key]['#states'])) {
      $diff[$key]['#states'] = array();
    }

    // Ensure that the element follows the new #states format.
    if (isset($diff[$key]['#old'])) {
      $diff[$key]['#states']['raw']['#old'] = $diff[$key]['#old'];
      unset($diff[$key]['#old']);
    }
    if (isset($diff[$key]['#new'])) {
      $diff[$key]['#states']['raw']['#new'] = $diff[$key]['#new'];
      unset($diff[$key]['#new']);
    }

    // If requested, we can convert the .
    foreach (array('raw', 'rendered') as $state) {
      if (in_array($state . '_plain', $context['states'])) {
        diff_markdown_state($diff[$key], $state);
      }
    }

    // Assign a decimal placeholder weight to preserve original array order.
    if (!isset($diff[$key]['#weight'])) {
      $diff[$key]['#weight'] = $count / 1000;
    }
    else {
      // If one child element has a weight then we will need to sort later.
      unset($diff['#sorted']);
    }
    $count++;
  }

  // One of the children has a #weight.
  if (!isset($diff['#sorted'])) {
    uasort($diff, 'element_sort');
  }

  // Process the array and get line counts per field.
  array_walk($diff, 'diff_process_state_lines');

  return $diff;
}

function diff_process_state_lines(&$diff, $key) {
  foreach ($diff['#states'] as $state => $data) {
    if (isset($data['#old'])) {
      if (is_string($data['#old'])) {
        $diff['#states'][$state]['#old'] = explode("\n", $data['#old']);
      }
      $diff['#states'][$state]['#count_old'] = count($diff['#states'][$state]['#old']);
    }
    else {
      $diff['#states'][$state]['#count_old'] = 0;
    }
    if (isset($data['#new'])) {
      if (is_string($data['#new'])) {
        $diff['#states'][$state]['#new'] = explode("\n", $data['#new']);
      }
      $diff['#states'][$state]['#count_new'] = count($diff['#states'][$state]['#new']);
    }
    else {
      $diff['#states'][$state]['#count_new'] = 0;
    }
  }
}

/**
 * Helper function to render plain states from the corresponding raw state.
 *
 * @param array $diff
 *   The Diff Engine output array.
 * @param string $state
 *   The state to markdown.
 */
function diff_markdown_state(&$diff, $state) {
  list($plain_old, $plain_new) = diff_extract_state($diff, $state . '_plain');
  list($old, $new) = diff_extract_state($diff, $state);
  $markdown = FALSE;
  if (isset($diff['#settings']) && !empty($diff['#settings']['markdown'])) {
    if (function_exists($diff['#settings']['markdown'])) {
      $markdown = $diff['#settings']['markdown'];
    }
  }

  if (!isset($plain_old) && isset($old)) {
    if (is_array($old)) {
      $diff['#states'][$state . '_plain']['#old'] = $markdown ? array_map($markdown, $old) : $old;
    }
    else {
      $diff['#states'][$state . '_plain']['#old'] = $markdown ? $markdown($old) : $old;
    }
  }
  if (!isset($plain_new) && isset($new)) {
    if (is_array($new)) {
      $diff['#states'][$state . '_plain']['#new'] = $markdown ? array_map($markdown, $new) : $new;
    }
    else {
      $diff['#states'][$state . '_plain']['#new'] = $markdown ? $markdown($new) : $new;
    }
  }
}

/**
 * Get the entry in the revisions list after $vid.
 *
 * @param array $node_revisions
 *   Array of node revision IDs in descending order.
 * @param int $vid
 *   Version ID to look for.
 *
 * @return boolean|integer
 *   Returns FALSE if $vid is the last entry.
 */
function _diff_get_next_vid($node_revisions, $vid) {
  $previous = NULL;
  foreach ($node_revisions as $revision) {
    if ($revision->vid == $vid) {
      return ($previous ? $previous->vid : FALSE);
    }
    $previous = $revision;
  }
  return FALSE;
}

/**
 * Get the entry in the revision list before $vid.
 *
 * @param array $node_revisions
 *   Array of node revision IDs in descending order.
 * @param integer $vid
 *   Version ID to look for.
 *
 * @return boolean|integer
 *   Returns FALSE if $vid is the first entry.
 */
function _diff_get_previous_vid($node_revisions, $vid) {
  $previous = NULL;
  foreach ($node_revisions as $revision) {
    if ($previous && $previous->vid == $vid) {
      return $revision->vid;
    }
    $previous = $revision;
  }
  return FALSE;
}

/**
 * Helper function to create default 'cols' array for diff table.
 */
function _diff_default_cols() {
  return array(
    array(
      array(
        'class' => 'diff-marker',
      ),
      array(
        'class' => 'diff-content',
      ),
      array(
        'class' => 'diff-marker',
      ),
      array(
        'class' => 'diff-content',
      ),
    ),
  );
}

/**
 * Helper function to create default 'header' array for diff table.
 */
function _diff_default_header($old_header = '', $new_header = '') {
  return array(
    array(
      'data' => $old_header,
      'colspan' => 2,
    ),
    array(
      'data' => $new_header,
      'colspan' => 2,
    ),
  );
}

/**
 * Show the inline diff for a given node, vid.
 *
 * If vid = 0 or no previous vid exists for the given revision returns the
 * normally rendered content of the specified revision.
 */
function diff_inline_show($node, $vid = 0, $metadata = TRUE) {
  $new_node = $vid ? node_load($node->nid, $vid, TRUE) : clone $node;
  node_build_content($new_node);
  $new = drupal_render($new_node->content);

  $old = $vid ? _diff_get_previous_vid(node_revision_list($node), $vid) : 0;
  if ($old) {
    $old_node = node_load($node->nid, $old, TRUE);
    node_build_content($old_node);
    $old = drupal_render($old_node->content);
    $output = $metadata ? theme('diff_inline_metadata', array('node' => $new_node)) : '';
    $output .= diff_get_inline($old, $new);
    return $output;
  }
  return $new;
}
