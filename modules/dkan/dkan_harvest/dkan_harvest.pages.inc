<?php

/**
 * @file
 * Page callbacks for dkan harvest pages.
 */

/**
 * Display a harvest source node's event log history.
 *
 * @param object $node
 *        The harvest source node being acted upon.
 *
 * @return array
 *         A themed table of data and links for harvest source node events.
 */
function dkan_harvest_page_event_log($node) {
  // Set page title.
  drupal_set_title(t('Events'));
  // Set the table header.
  $header = array(
    'mlid' => array('data' => t('Event ID'), 'field' => 'log.mlid'),
    'starttime' => array(
      'data' => t('Date'),
      'field' => 'starttime',
      'sort' => 'desc',
    ),
    'status' => array('data' => t('Status'), 'field' => 'failed'),
    'duration' => array('data' => t('Duration'), 'field' => 'duration'),
    'created' => array('data' => t('Created'), 'field' => 'created'),
    'updated' => array('data' => t('Updated'), 'field' => 'updated'),
    'failed' => array('data' => t('Failed'), 'field' => 'failed'),
    'orphaned' => array('data' => t('Orphaned'), 'field' => 'orphaned'),
    'unchanged' => array('data' => t('Unchanged'), 'field' => 'unchanged'),
  );

  $status_text = array(
    t('OK'),
    l(t('ERROR'), 'node/' . $node->nid . '/harvest-errors', array('attributes' => array('alt' => 'Error'))),
  );
  $status_class = array(0 => 'alert-success', 1 => t('alert-danger'));
  $harvest_source = HarvestSource::getHarvestSourceFromNode($node);
  $harvest_source_migration = dkan_harvest_get_migration($harvest_source);
  $log_table = $harvest_source_migration->getMap()->getLogTable();

  $query = db_select('migrate_log', 'log');

  $query->join($log_table, 'harvest_source_log', 'log.mlid = harvest_source_log.mlid');

  $query_result = $query
    ->fields('log')
    ->fields('harvest_source_log')
    ->condition('machine_name', $harvest_source->getMigrationMachineName())
    // Add table sort extender.
    ->extend('TableSort')
    // Add order by headers.
    ->orderByHeader($header);

  $query->addExpression('log.endtime - log.starttime', 'duration');

  $query_result = $query->execute();

  $results = $query_result->fetchAllAssoc('mlid');

  /**
   * Convert from microsecnod to seconds.
   */
  function timefloor($microtime) {
    return floor($microtime / 1000);
  }

  // Add help text.
  $build['help'] = array(
    '#attributes' => array(
      'class' => array('help-block'),
    ),
    '#type' => 'markup',
    '#markup' => t('Chronological log of harvests performed on this source. See the "Error log" page for details on any failures listed here.'),
    '#prefix' => '<span class="help-block">',
    '#suffix' => '</span>',
  );

  if (count($results)) {
    // Build the table rows.
    $rows = array();
    foreach ($results as $result) {
      $row = array(
        'data' => array(
          'mlid' => $result->mlid,
          'date' => format_date(timefloor($result->starttime)),
          'status' => array('data' => $status_text[$result->failed], 'class' => array($status_class[$result->failed])),
          'duration' => format_interval(timefloor($result->duration), 2),
          'created' => $result->created,
          'updated' => $result->updated,
          'failed' => $result->failed,
          'orphaned' => $result->orphaned,
          'unchanged' => $result->unchanged,
        ),
        'class' => array('harvest-event'),
      );

      $rows[] = $row;
    }

    // Return properly styled output.
    $build['pager_table'] = array(
      '#attached' => array(
        'css' => array(),
      ),
      '#theme' => 'table',
      '#attributes' => array(
        'class' => array('harvest-event-log'),
      ),
      '#header' => $header,
      '#rows' => $rows,
    );

    // Attach the pager theme.
    $build['pager_pager'] = array('#theme' => 'pager');
  }
  else {
    $build['no_results'] = array(
      '#markup' => '<em>No log messages available.</em>',
    );
  }

  return $build;
}

/**
 * Display a harvest source node's error log history.
 *
 * @param object $node
 *        The harvest source node being acted upon.
 *
 * @return array
 *         A themed table of data and links for
 *         harvest source node events.
 */
function dkan_harvest_page_error_log($node) {
  // Set page title.
  drupal_set_title(t('Errors'));
  // Set the table header.
  $collapsible_inner_header = array(
    'msgid' => array('data' => t('Message ID'), 'field' => 'msgid'),
    'sourceid1' => array('data' => t('Source ID'), 'field' => 'sourceid1'),
    'dataset_link' => array('data' => t('Dataset')),
    'message' => array('data' => t('Error Message'), 'field' => 'message'),
  );

  $harvest_source = HarvestSource::getHarvestSourceFromNode($node);
  $harvest_source_migration = dkan_harvest_get_migration($harvest_source);
  $harvest_source_migration_map = $harvest_source_migration->getMap();

  $query = db_select($harvest_source_migration_map->getMessageTable(), 'harvest_source_message')
    ->fields('harvest_source_message')
    ->condition('level', Migration::MESSAGE_ERROR)
    // Add table sort extender.
    ->extend('TableSort')
    // Add order by headers.
    ->orderByHeader($collapsible_inner_header);

  $query_result = $query->execute();

  $results = $query_result->fetchAllAssoc('msgid');

  // Add help text.
  $build['help'] = array(
    '#attributes' => array(
      'class' => array('help-block'),
    ),
    '#type' => 'markup',
    '#markup' => t('Log of errors reported during harvesting from this source.'),
    '#prefix' => '<span class="help-block">',
    '#suffix' => '</span>',
  );

  if (count($results)) {
    // Build the table rows.
    $collapsible_items = array();
    foreach ($results as $result) {
      if (isset($result->sourceid1)) {
        // Lookup the dataset nid. lookupDestinationID() takes an array and
        // returns an array, But really we are interested in one sincle result.
        $sourceid = $result->sourceid1;
        $destids = $harvest_source_migration_map->lookupDestinationID(array($sourceid));
        // We expect an array with one valid item. If not fallback to default
        // "N/A".
        if (is_array($destids) && count($destids) == 1 && isset($destids['destid1'])) {
          $destid = array_pop($destids);
          $result->dataset_link = l(t('Dataset link'), 'node/' . $destid);
        }
      }

      $row = array(
        'data' => array(
          'msgid' => $result->msgid,
          'sourceid1' => $result->sourceid1,
          'dataset_link' => isset($result->dataset_link) ? $result->dataset_link : "N/A",
          'message' => $result->message,
        ),
        'class' => array('harvest-error'),
      );

      $collapsible_items[$result->mlid][] = $row;
    }

    $build = array();
    $collapsible_item_offset = 0;
    // We can use the mlid key as the bases of the sorting. The bigger the mlid
    // the more recent the harvest migrate event. Preserve the key.
    $collapsible_items = array_reverse($collapsible_items, TRUE);
    foreach ($collapsible_items as $collapsible_item_mlid => $collapsible_item) {
      $collapsible_inner_table = array(
        '#attached' => array(
          'css' => array(),
        ),
        '#theme' => 'table',
        '#attributes' => array(
          'class' => array(
            'harvest-error-log-table',
            'harvest-error-log-table-' . $collapsible_item_mlid,
          ),
        ),
        '#header' => $collapsible_inner_header,
        '#rows' => $collapsible_item,
      );

      // Return properly styled output.
      $handle = t('@errors_count Error(s) encountered on @date',
        array(
          '@errors_count' => count($collapsible_item),
          '@date' => format_date(HarvestSource::getMigrationTimestampFromMlid($collapsible_item_mlid)),
        ));

      $build['collapsible_row_' . $collapsible_item_mlid] = array(
        '#theme' => 'ctools_collapsible',
        '#attributes' => array(
          'class' => array(
            'harvest-error-log-collapse',
          ),
        ),
        '#handle' => $handle,
        '#content' => drupal_render($collapsible_inner_table),
        '#collapsed' => $collapsible_item_offset > 0 ? TRUE : FALSE,
      );

      $collapsible_item_offset++;
    }

    // Attach the pager theme.
    $build['pager_pager'] = array('#theme' => 'pager');
  }
  else {
    $build['no_results'] = array(
      '#markup' => '<em>No error messages available.</em>',
    );
  }

  return $build;
}
