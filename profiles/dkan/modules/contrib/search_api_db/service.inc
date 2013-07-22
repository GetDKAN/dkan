<?php

/**
 * Search service class using the database for storing index information.
 */
class SearchApiDbService extends SearchApiAbstractService {

  protected $previous_db;
  protected $query_options = array();
  protected $ignored = array();
  protected $warnings = array();

  /**
   * Overrides SearchApiAbstractService::configurationForm().
   */
  public function configurationForm(array $form, array &$form_state) {
    // Discern between creation and editing of a server, since we don't allow
    // the database to be changed later on.
    if (empty($this->options)) {
      global $databases;
      foreach ($databases as $key => $targets) {
        foreach ($targets as $target => $info) {
          $options[$key]["$key:$target"] = "$key > $target";
        }
      }
      if (count($options) > 1 || count(reset($options)) > 1) {
        $form['database'] = array(
          '#type' => 'select',
          '#title' => t('Database'),
          '#description' => t('Select the database key and target to use for storing indexing information in. ' .
              'Cannot be changed after creation.'),
          '#options' => $options,
          '#default_value' => 'default:default',
          '#required' => TRUE,
        );
      }
      else {
        $form['database'] = array(
          '#type' => 'value',
          '#value' => "$key:$target",
        );
      }
    }
    else {
      $form = array(
        'database' => array(
          '#type' => 'value',
          '#title' => t('Database'), // Slight hack for the "View server" page.
          '#value' => $this->options['database'],
        ),
        'database_text' => array(
          '#type' => 'item',
          '#title' => t('Database'),
          '#markup' => check_plain(str_replace(':', ' > ', $this->options['database'])),
        ),
      );
    }

    // Set default settings.
    $options = $this->options + array(
      'min_chars' => 1,
      'autocomplete' => array(),
    );
    $options['autocomplete'] += array(
      'suggest_suffix' => TRUE,
      'suggest_words' => TRUE,
    );

    $form['min_chars'] = array(
      '#type' => 'select',
      '#title' => t('Minimum word length'),
      '#description' => t('The minimum number of characters a word must consist of to be indexed.'),
      '#options' => drupal_map_assoc(array(1, 2, 3, 4, 5, 6)),
      '#default_value' => $options['min_chars'],
    );

    if (module_exists('search_api_autocomplete')) {
      $form['autocomplete'] = array(
        '#type' => 'fieldset',
        '#title' => t('Autocomplete settings'),
        '#description' => t('These settings allow you to configure how suggestions are computed when autocompletion is used. If you are seeing many inappropriate suggestions you might want to deactivate the corresponding suggestion type. You can also deactivate one method to speed up the generation of suggestions.'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      );
      $form['autocomplete']['suggest_suffix'] = array(
        '#type' => 'checkbox',
        '#title' => t('Suggest word endings'),
        '#description' => t('Suggest endings for the currently entered word.'),
        '#default_value' => $options['autocomplete']['suggest_suffix'],
      );
      $form['autocomplete']['suggest_words'] = array(
        '#type' => 'checkbox',
        '#title' => t('Suggest additional words'),
        '#description' => t('Suggest additional words the user might want to search for.'),
        '#default_value' => $options['autocomplete']['suggest_words'],
      );
    }

    return $form;
  }

  /**
   * Overrides SearchApiAbstractService::supportsFeature().
   */
  public function supportsFeature($feature) {
    $supported = drupal_map_assoc(array(
      'search_api_autocomplete',
      'search_api_facets',
    ));
    return isset($supported[$feature]);
  }

  /**
   * Overrides SearchApiAbstractService::postUpdate().
   */
  public function postUpdate() {
    return !empty($this->server->original) && $this->server->options != $this->server->original->options;
  }

  /**
   * Overrides SearchApiAbstractService::preDelete().
   */
  public function preDelete() {
    // Only react on real deletes, not on reverts.
    if ($this->server->hasStatus(ENTITY_IN_CODE)) {
      return;
    }
    if (empty($this->options['indexes'])) {
      return;
    }
    foreach ($this->options['indexes'] as $index) {
      foreach ($index as $field) {
        db_drop_table($field['table']);
      }
    }
  }

  /**
   * Overrides SearchApiAbstractService::addIndex().
   */
  public function addIndex(SearchApiIndex $index) {
    // If there are no fields, we can take a shortcut.
    if (!isset($index->options['fields'])) {
      if (!isset($this->options['indexes'][$index->machine_name])) {
        $this->options['indexes'][$index->machine_name] = array();
        $this->server->save();
      }
      elseif ($this->options['indexes'][$index->machine_name]) {
        $this->removeIndex($index);
        $this->options['indexes'][$index->machine_name] = array();
        $this->server->save();
      }
      return;
    }
    $this->options += array('indexes' => array());
    $this->options['indexes'] += array($index->machine_name => array());

    // If dealing with features or stale data or whatever, we might already have
    // settings stored for this index. If we have, we should take care to only
    // change what is needed, so we don't save the server (potentially setting
    // it to "Overridden") unnecessarily.
    // The easiest way to do this is by just pretending the index was already
    // present, but its fields were updated.
    $this->fieldsUpdated($index);
  }

  /**
   * Helper method for finding free table names for fields.
   *
   * MySQL 5.0 imposes a 64 characters length limit for table names, PostgreSQL
   * 8.3 only allows 63 bytes. Therefore, always return a name at most 63
   * bytes long.
   */
  protected function findFreeTable($prefix, $name) {
    // A DB prefix might further reduce the maximum length of the table name.
    $maxbytes = 62;
    list($key, $target) = explode(':', $this->options['database'], 2);
    if ($db_prefix = Database::getConnection($target, $key)->tablePrefix()) {
      // Use strlen instead of drupal_strlen since we want to measure bytes
      // instead of characters.
      $maxbytes -= strlen($db_prefix);
    }

    $base = $table = mb_strcut($prefix . drupal_strtolower(preg_replace('/[^a-z0-9]/i', '_', $name)), 0, $maxbytes);
    $i = 0;
    while (db_table_exists($table)) {
      $suffix = '_' . ++$i;
      $table = mb_strcut($base, 0, $maxbytes - strlen($suffix)) . $suffix;
    }
    return $table;
  }

  /**
   * Helper method for creating the table for a field.
   */
  protected function createFieldTable(SearchApiIndex $index, $field, $name) {
    $table = array(
      'name' => $name,
      'module' => 'search_api_db',
      'fields' => array(
        'item_id' => array(
          'description' => 'The primary identifier of the item.',
          'not null' => TRUE,
        ),
      ),
    );
    // The type of the item_id field depends on the ID field's type.
    $id_field = $index->datasource()->getIdFieldInfo();
    $table['fields']['item_id'] += $this->sqlType($id_field['type'] == 'text' ? 'string' : $id_field['type']);
    if (isset($table['fields']['item_id']['length'])) {
      // A length of 255 is overkill for IDs. 50 should be more than enough.
      $table['fields']['item_id']['length'] = 50;
    }

    $type = search_api_extract_inner_type($field['type']);
    if ($type == 'text') {
      $table['fields']['word'] = array(
        'description' => 'The text of the indexed token.',
        'type' => 'varchar',
        'length' => 50,
        'not null' => TRUE,
      );
      $table['fields']['score'] = array(
        'description' => 'The score associated with this token.',
        'type' => 'float',
        'not null' => TRUE,
      );
      $table['primary key'] = array('item_id', 'word');
      $table['indexes']['word'] = array(array('word', 10));
    }
    else {
      $table['fields']['value'] = $this->sqlType($type);
      $table['fields']['value'] += array(
        'description' => "The field's value for this item.",
        'not null' => TRUE,
      );
      if ($type != $field['type']) {
        // This is a list type.
        $table['primary key'] = array('item_id', 'value');
      }
      else {
        $table['primary key'] = array('item_id');
      }
      $table['indexes']['value'] = $table['fields']['value'] == 'varchar' ? array(array('value', 10)) : array('value');
    }

    $set = $this->setDb();
    db_create_table($name, $table);

    //Some DBMSs will need a character encoding and collation set.
    global $databases;
    list($key, $target) = explode(':', $this->options['database'], 2);
    $db_driver = $databases[$key][$target]['driver'];

    switch ($db_driver) {
      case 'mysql':
        db_query("ALTER TABLE {{$name}} CONVERT TO CHARACTER SET 'utf8' COLLATE 'utf8_bin'");
        break;
      // @todo Add fixes for other DBMSs.
      case 'oracle':
      case 'pgsql':
      case 'sqlite':
      case 'sqlsrv':
        break;
    }

    if ($set) {
      $this->resetDb();
    }
  }

  protected function sqlType($type) {
    $type = search_api_extract_inner_type($type);
    switch ($type) {
      case 'string':
      case 'uri':
        return array('type' => 'varchar', 'length' => 255);
      case 'integer':
      case 'duration':
      case 'date': // 'datetime' sucks. This way, we just convert every input into a timestamp.
        return array('type' => 'int');
      case 'decimal':
        return array('type' => 'float');
      case 'boolean':
        return array('type' => 'int', 'size' => 'tiny');

      default:
        throw new SearchApiException(t('Unknown field type @type. Database search module might be out of sync with Search API.', array('@type' => $type)));
    }
  }

  /**
   * Overrides SearchApiAbstractService::fieldsUpdated().
   *
   * Internally, this is also used by addIndex().
   */
  public function fieldsUpdated(SearchApiIndex $index) {
    $fields = &$this->options['indexes'][$index->machine_name];
    $new_fields = $index->getFields();

    $reindex = FALSE;
    $cleared = FALSE;
    $change = FALSE;

    $set = $this->setDb();

    foreach ($fields as $name => $field) {
      if (!isset($new_fields[$name])) {
        db_drop_table($field['table']);
        unset($fields[$name]);
        $change = TRUE;
        continue;
      }
      $old_type = $field['type'];
      $new_type = $new_fields[$name]['type'];
      $fields[$name]['type'] = $new_type;
      $fields[$name]['boost'] = $new_fields[$name]['boost'];
      $old_inner_type = search_api_extract_inner_type($old_type);
      $new_inner_type = search_api_extract_inner_type($new_type);
      if ($old_type != $new_type) {
        $change = TRUE;
        if ($old_inner_type == 'text' || $new_inner_type == 'text'
            || search_api_list_nesting_level($old_type) != search_api_list_nesting_level($new_type)) {
          // A change in fulltext or list status necessitates completely
          // clearing the index.
          $reindex = TRUE;
          if (!$cleared) {
            $cleared = TRUE;
            $this->deleteItems('all', $index);
          }
          db_drop_table($field['table']);
          $this->createFieldTable($index, $new_fields[$name], $field['table']);
        }
        elseif ($this->sqlType($old_inner_type) != $this->sqlType($new_inner_type)) {
          // There is a change in SQL type. We don't have to clear the index, since types can be converted.
          db_change_field($field['table'], 'value', 'value', $this->sqlType($new_type) + array('description' => "The field's value for this item."));
          $reindex = TRUE;
        }
        elseif ($old_inner_type == 'date' || $new_inner_type == 'date') {
          // Even though the SQL type stays the same, we have to reindex since conversion rules change.
          $reindex = TRUE;
        }
      }
      elseif ($field['boost'] != $new_fields[$name]['boost']) {
        $change = TRUE;
        if (!$reindex && $new_inner_type == 'text') {
          $multiplier = $new_fields[$name]['boost'] / $field['boost'];
          db_update($field['table'], $this->query_options)
            ->expression('score', 'score * :mult', array(':mult' => $multiplier))
            ->execute();
        }
      }
      // Make sure the set table now exists. (Especially important when we
      // actually add the index for the first time.)
      if (!db_table_exists($field['table'])) {
        $this->createFieldTable($index, $new_fields[$name], $field['table']);
      }
      unset($new_fields[$name]);
    }

    $prefix = 'search_api_db_' . $index->machine_name. '_';
    // These are new fields that were previously not indexed.
    foreach ($new_fields as $name => $field) {
      $reindex = TRUE;
      $table = $this->findFreeTable($prefix, $name);
      $this->createFieldTable($index, $field, $table);
      $fields[$name]['table'] = $table;
      $fields[$name]['type']  = $field['type'];
      $fields[$name]['boost'] = $field['boost'];
      $change = TRUE;
    }

    if ($set) {
      $this->resetDb();
    }

    if ($change) {
      $this->server->save();
    }
    return $reindex;
  }

  /**
   * Overrides SearchApiAbstractService::removeIndex().
   */
  public function removeIndex($index) {
    $id = is_object($index) ? $index->machine_name : $index;
    if (!isset($this->options['indexes'][$id])) {
      return;
    }
    // Don't delete the index data of read-only indexes!
    if (!is_object($index) || empty($index->read_only)) {
      $set = $this->setDb();
      foreach ($this->options['indexes'][$id] as $field) {
        db_drop_table($field['table']);
      }
      if ($set) {
        $this->resetDb();
      }
    }
    unset($this->options['indexes'][$id]);
    $this->server->save();
  }

  /**
   * Implements SearchApiServiceInterface::indexItems().
   */
  public function indexItems(SearchApiIndex $index, array $items) {
    if (empty($this->options['indexes'][$index->machine_name])) {
      throw new SearchApiException(t('No field settings for index with id @id.', array('@id' => $index->machine_name)));
    }
    $indexed = array();
    $set = $this->setDb();
    foreach ($items as $id => $item) {
      try {
        if ($this->indexItem($index, $id, $item)) {
          $indexed[] = $id;
        }
      }
      catch (Exception $e) {
        // We just log the error, hoping we can index the other items.
        watchdog('search_api_db', check_plain($e->getMessage()), NULL, WATCHDOG_WARNING);
      }
    }
    if ($set) {
      $this->resetDb();
    }
    return $indexed;
  }

  protected function indexItem(SearchApiIndex $index, $id, array $item) {
    $fields = $this->options['indexes'][$index->machine_name];
    $fields_updated = FALSE;
    $txn = db_transaction('search_api_indexing', $this->query_options);
    try {
      foreach ($item as $name => $field) {
        // Sometimes index changes are not triggering the update hooks
        // correctly. Therefore, to avoid DB errors, we re-check the tables
        // here before indexing.
        if (empty($fields[$name]['table']) && !$fields_updated) {
          unset($this->options['indexes'][$index->machine_name][$name]);
          $this->fieldsUpdated($index);
          $fields_updated = TRUE;
          $fields = $this->options['indexes'][$index->machine_name];
        }
        if (empty($fields[$name]['table'])) {
          watchdog('search_api_db', "Unknown field !field: please check (and re-save) the index's fields settings.",
              array('!field' => $name), WATCHDOG_WARNING);
          continue;
        }
        $table = $fields[$name]['table'];
        $boost = $fields[$name]['boost'];
        db_delete($table, $this->query_options)
            ->condition('item_id', $id)
            ->execute();
        // Don't index null values
        if($field['value'] === NULL) {
          continue;
        }
        $type = $field['type'];
        $value = $this->convert($field['value'], $type, $field['original_type'], $index);

        if (search_api_is_text_type($type, array('text', 'tokens'))) {
          $words = array();
          foreach ($value as $token) {
            // Taken from core search to reflect less importance of words later
            // in the text.
            // Focus is a decaying value in terms of the amount of unique words
            // up to this point. From 100 words and more, it decays, to e.g. 0.5
            // at 500 words and 0.3 at 1000 words.
            $focus = min(1, .01 + 3.5 / (2 + count($words) * .015));

            $value = &$token['value'];
            if (is_numeric($value)) {
              $value = ltrim($value, '-0');
            }
            elseif (drupal_strlen($value) < $this->options['min_chars']) {
              continue;
            }
            $value = drupal_strtolower($value);
            $token['score'] *= $focus;
            if (!isset($words[$value])) {
              $words[$value] = $token;
            }
            else {
              $words[$value]['score'] += $token['score'];
            }
          }
          if ($words) {
            $query = db_insert($table, $this->query_options)
              ->fields(array('item_id', 'word', 'score'));
            foreach ($words as $word) {
              $query->values(array(
                'item_id' => $id,
                'word'    => $word['value'],
                'score'   => $word['score'] * $boost,
              ));
            }
            $query->execute();
          }
        }
        elseif (search_api_is_list_type($type)) {
          $values = array();
          if (is_array($value)) {
            foreach ($value as $v) {
              if ($v !== NULL) {
                $values[$v] = TRUE;
              }
            }
            $values = array_keys($values);
          }
          else {
            $values[] = $value;
          }
          if ($values) {
            $insert = db_insert($table, $this->query_options)
              ->fields(array('item_id', 'value'));
            foreach ($values as $v) {
              $insert->values(array(
                'item_id' => $id,
                'value'   => $v,
              ));
            }
            $insert->execute();
          }
        }
        elseif (isset($value)) {
          db_insert($table, $this->query_options)
            ->fields(array(
              'item_id' => $id,
              'value'   => $value,
            ))
            ->execute();
        }
      }
    }
    catch (Exception $e) {
      $txn->rollback();
      throw $e;
    }
    return TRUE;
  }

  protected function convert($value, $type, $original_type, SearchApiIndex $index) {
    if (search_api_is_list_type($type)) {
      $type = substr($type, 5, -1);
      $original_type = search_api_extract_inner_type($original_type);
      $ret = array();
      if (is_array($value)) {
        foreach ($value as $v) {
          $v = $this->convert($v, $type, $original_type, $index);

          // Don't add NULL values to the return array. Also, adding an empty
          // array is, of course, a waste of time.
          if (isset($v) && $v !== array()) {
            $ret = array_merge($ret, is_array($v) ? $v : array($v));
          }
        }
      }
      return $ret;
    }
    if (!isset($value)) {
      // For text fields, we have to return an array even if the value is NULL.
      return search_api_is_text_type($type, array('text', 'tokens')) ? array() : NULL;
    }
    switch ($type) {
      case 'text':
        $ret = array();
        foreach (preg_split('/[^\p{L}\p{N}]+/u', $value, -1, PREG_SPLIT_NO_EMPTY) as $v) {
          if ($v) {
            $ret[] = array(
              'value' => $v,
              'score' => 1.0,
            );
          }
        }
        $value = $ret;
        // FALL-THROUGH!
      case 'tokens':
        while (TRUE) {
          foreach ($value as $i => $v) {
            // Check for over-long tokens.
            $score = $v['score'];
            $v = $v['value'];
            if (strlen($v) > 50) {
              $words = preg_split('/[^\p{L}\p{N}]+/u', $v, -1, PREG_SPLIT_NO_EMPTY);
              if (count($words) > 1 && max(array_map('strlen', $words)) <= 50) {
                // Overlong token is due to bad tokenizing.
                // Check for "Tokenizer" preprocessor on index.
                if (empty($index->options['processors']['search_api_tokenizer']['status'])) {
                  watchdog('search_api_db', 'An overlong word (more than 50 characters) was encountered while indexing, due to bad tokenizing. ' .
                      'It is recommended to enable the "Tokenizer" preprocessor for indexes using database servers. ' .
                      'Otherwise, the service class has to use its own, fixed tokenizing.', array(), WATCHDOG_WARNING);
                }
                else {
                  watchdog('search_api_db', 'An overlong word (more than 50 characters) was encountered while indexing, due to bad tokenizing. ' .
                      'Please check your settings for the "Tokenizer" preprocessor to ensure that data is tokenized correctly.',
                      array(), WATCHDOG_WARNING);
                }
              }

              $tokens = array();
              foreach ($words as $word) {
                if (strlen($word) > 50) {
                  watchdog('search_api_db', 'An overlong word (more than 50 characters) was encountered while indexing: %word.<br />' .
                      'Database search servers currently cannot index such words correctly – the word was therefore trimmed to the allowed length.',
                      array('%word' => $word), WATCHDOG_WARNING);
                  $word = mb_strcut($word, 0, 50);
                }
                $tokens[] = array(
                  'value' => $word,
                  'score' => $score,
                );
              }
              array_splice($value, $i, 1, $tokens);
              continue 2;
            }
          }
          break;
        }
        return $value;

      case 'string':
      case 'uri':
        // For non-dates, PHP can handle this well enough
        if ($original_type == 'date') {
          return date('%c', $value);
        }
        if (strlen($value) > 255) {
          $value = mb_strcut($value, 0, 255);
          watchdog('search_api_db', 'An overlong value (more than 255 characters) was encountered while indexing: %value.<br />' .
              'Database search servers currently cannot index such values correctly – the value was therefore trimmed to the allowed length.',
              array('%value' => $value), WATCHDOG_WARNING);
        }
        return $value;

      case 'integer':
      case 'duration':
      case 'decimal':
        return 0 + $value;

      case 'boolean':
        return $value ? 1 : 0;

      case 'date':
        if (is_numeric($value) || !$value) {
          return 0 + $value;
        }
        return strtotime($value);

      default:
        throw new SearchApiException(t('Unknown field type @type. Database search module might be out of sync with Search API.', array('@type' => $type)));
    }
  }

  /**
   * Implements SearchApiServiceInterface::deleteItems().
   */
  public function deleteItems($ids = 'all', SearchApiIndex $index = NULL) {
    if (!$index) {
      if (empty($this->options['indexes'])) {
        return;
      }
      $set = $this->setDb();
      foreach ($this->options['indexes'] as $index) {
        foreach ($index as $fields) {
          foreach ($fields as $field) {
            db_truncate($field['table'], $this->query_options)->execute();
          }
        }
      }
      if ($set) {
        $this->resetDb();
      }
      return;
    }

    if (empty($this->options['indexes'][$index->machine_name])) {
      return;
    }
    $set = $this->setDb();
    foreach ($this->options['indexes'][$index->machine_name] as $field) {
      if (is_array($ids)) {
        db_delete($field['table'], $this->query_options)
          ->condition('item_id', $ids, 'IN')
          ->execute();
      }
      else {
        db_truncate($field['table'], $this->query_options)->execute();
      }
    }
    if ($set) {
      $this->resetDb();
    }
  }

  /**
   * Implements SearchApiServiceInterface::search().
   */
  public function search(SearchApiQueryInterface $query) {
    $time_method_called = microtime(TRUE);
    $this->ignored = $this->warnings = array();
    $index = $query->getIndex();
    if (empty($this->options['indexes'][$index->machine_name])) {
      throw new SearchApiException(t('Unknown index @id.', array('@id' => $index->machine_name)));
    }
    $set = $this->setDb();
    $fields = $this->options['indexes'][$index->machine_name];

    $db_query = $this->createDbQuery($query, $fields);

    $time_processing_done = microtime(TRUE);
    $results = array();
    $count_query = $db_query->countQuery();
    $results['result count'] = $count_query->execute()->fetchField();

    if ($results['result count']) {
      if ($query->getOption('search_api_facets')) {
        $results['search_api_facets'] = $this->getFacets($query, clone $db_query);
      }

      $query_options = $query->getOptions();
      if (isset($query_options['offset']) || isset($query_options['limit'])) {
        $offset = isset($query_options['offset']) ? $query_options['offset'] : 0;
        $limit = isset($query_options['limit']) ? $query_options['limit'] : 1000000;
        $db_query->range($offset, $limit);
      }

      $sort = $query->getSort();
      if ($sort) {
        foreach ($sort as $field_name => $order) {
          if ($order != 'ASC' && $order != 'DESC') {
            $msg = t('Unknown sort order @order. Assuming "ASC".', array('@order' => $order));
            $this->warnings[$msg] = $msg;
            $order = 'ASC';
          }
          if ($field_name == 'search_api_relevance') {
            $db_query->orderBy('score', $order);
            continue;
          }
          if ($field_name == 'search_api_id') {
            $db_query->orderBy('item_id', $order);
            continue;
          }
          if (!isset($fields[$field_name])) {
            throw new SearchApiException(t('Trying to sort on unknown field @field.', array('@field' => $field_name)));
          }
          $field = $fields[$field_name];
          if (search_api_is_list_type($field['type'])) {
            throw new SearchApiException(t('Cannot sort on field @field of a list type.', array('@field' => $field_name)));
          }
          if (search_api_is_text_type($field['type'])) {
            throw new SearchApiException(t('Cannot sort on fulltext field @field.', array('@field' => $field_name)));
          }
          $alias = $this->getTableAlias($field, $db_query);
          $db_query->orderBy($alias . '.value', $order);
        }
      }
      else {
        $db_query->orderBy('score', 'DESC');
      }

      $result = $db_query->execute();
      $time_queries_done = microtime(TRUE);

      foreach ($result as $row) {
        $results['results'][$row->item_id] = array(
          'id' => $row->item_id,
          'score' => $row->score,
        );
      }
    }
    else {
      $time_queries_done = microtime(TRUE);
      $results['results'] = array();
    }

    $results['warnings'] = array_keys($this->warnings);
    $results['ignored'] = array_keys($this->ignored);

    if ($set) {
      $this->resetDb();
    }

    $time_end = microtime(TRUE);
    $results['performance'] = array(
      'complete' => $time_end - $time_method_called,
      'preprocessing' => $time_processing_done - $time_method_called,
      'execution' => $time_queries_done - $time_processing_done,
      'postprocessing' => $time_end - $time_queries_done,
    );

    return $results;
  }

  /**
   * Helper method for creating a database query for a search.
   *
   * @param SearchApiQueryInterface $query
   *   The search query for which to create the database query.
   *
   * @param array $fields
   *   The internal field information to use.
   *
   * @return SelectQuery
   *   A database query object which will return the appropriate results (except
   *   for the range setting) for the given search query.
   */
  protected function createDbQuery(SearchApiQueryInterface $query, array $fields) {
    $set = $this->setDb();

    $keys = &$query->getKeys();
    $keys_set = (boolean) $keys;
    $keys = $this->prepareKeys($keys);
    if ($keys && !(is_array($keys) && count($keys) == 1)) {
      $fulltext_fields = $query->getFields();
      if ($fulltext_fields) {
        $_fulltext_fields = $fulltext_fields;
        $fulltext_fields = array();
        foreach ($_fulltext_fields as $name) {
          if (!isset($fields[$name])) {
            throw new SearchApiException(t('Unknown field @field specified as search target.', array('@field' => $name)));
          }
          if (!search_api_is_text_type($fields[$name]['type'])) {
            $types = search_api_field_types();
            $type = $types[$fields[$name]['type']];
            throw new SearchApiException(t('Cannot perform fulltext search on field @field of type @type.', array('@field' => $name, '@type' => $type)));
          }
          $fulltext_fields[$name] = $fields[$name];
        }

        $db_query = $this->createKeysQuery($keys, $fulltext_fields, $fields);
        if (is_array($keys) && !empty($keys['#negation'])) {
          $db_query->addExpression(':score', 'score', array(':score' => 1));
          $db_query->distinct();
        }
      }
      else {
        $msg = t('Search keys are given but no fulltext fields are defined.');
        watchdog('search_api_db', $msg, NULL, WATCHDOG_WARNING);
        $this->warnings[$msg] = 1;
      }
    }
    elseif ($keys_set) {
      $msg = t('No valid search keys were present in the query.');
      $this->warnings[$msg] = 1;
    }

    if (!isset($db_query)) {
      $db_query = db_select($fields['search_api_language']['table'], 't', $this->query_options);
      $db_query->addField('t', 'item_id', 'item_id');
      $db_query->addExpression(':score', 'score', array(':score' => 1));
      $db_query->distinct();
    }

    $filter = $query->getFilter();
    if ($filter->getFilters()) {
      $condition = $this->createFilterCondition($filter, $fields, $db_query);
      if ($condition) {
        $db_query->condition($condition);
      }
    }

    $db_query->addTag('search_api_db_search');
    $db_query->addMetaData('search_api_query', $query);
    $db_query->addMetaData('search_api_db_fields', $fields);

    if ($set) {
      $this->resetDb();
    }

    return $db_query;
  }

  /**
   * Helper method for removing unnecessary nested expressions from keys.
   */
  protected function prepareKeys($keys) {
    if (is_scalar($keys)) {
      $keys = $this->splitKeys($keys);
      return is_array($keys) ? $this->eliminateDuplicates($keys) : $keys;
    }
    elseif (!$keys) {
      return NULL;
    }
    $keys = $this->eliminateDuplicates($this->splitKeys($keys));
    $conj = $keys['#conjunction'];
    $neg = !empty($keys['#negation']);
    foreach ($keys as $i => &$nested) {
      if (is_array($nested)) {
        $nested = $this->prepareKeys($nested);
        if (is_array($nested) && $neg == !empty($nested['#negation'])) {
          if ($nested['#conjunction'] == $conj) {
            unset($nested['#conjunction'], $nested['#negation']);
            foreach ($nested as $renested) {
              $keys[] = $renested;
            }
            unset($keys[$i]);
          }
        }
      }
    }
    $keys = array_filter($keys);
    if (($count = count($keys)) <= 2) {
      if ($count < 2 || isset($keys['#negation'])) {
        $keys = NULL;
      }
      else {
        unset($keys['#conjunction']);
        $keys = array_shift($keys);
      }
    }
    return $keys;
  }

  /**
   * Helper method for splitting keys.
   */
  protected function splitKeys($keys) {
    if (is_scalar($keys)) {
      $proc = drupal_strtolower(trim($keys));
      if (is_numeric($proc)) {
        return ltrim($proc, '-0');
      }
      elseif (drupal_strlen($proc) < $this->options['min_chars']) {
        $this->ignored[$keys] = 1;
        return NULL;
      }
      $words = preg_split('/[^\p{L}\p{N}]+/u', $proc, -1, PREG_SPLIT_NO_EMPTY);
      if (count($words) > 1) {
        $proc = $this->splitKeys($words);
        $proc['#conjunction'] = 'AND';
      }
      return $proc;
    }
    foreach ($keys as $i => $key) {
      if (element_child($i)) {
        $keys[$i] = $this->splitKeys($key);
      }
    }
    return array_filter($keys);
  }

  /**
   * Helper method for eliminating duplicates from the search keys.
   */
  protected function eliminateDuplicates($keys, &$words = array()) {
    foreach ($keys as $i => $word) {
      if (!element_child($i)) {
        continue;
      }
      if (is_scalar($word)) {
        if (isset($words[$word])) {
          unset($keys[$i]);
        }
        else {
          $words[$word] = TRUE;
        }
      }
      else {
        $keys[$i] = $this->eliminateDuplicates($word, $words);
      }
    }
    return $keys;
  }

  /**
   * Helper method for creating a SELECT query for given search keys.
   *
   * @return SelectQueryInterface
   *   A SELECT query returning item_id and score (or only item_id, if
   *   $keys['#negation'] is set).
   */
  protected function createKeysQuery($keys, array $fields, array $all_fields) {
    if (!is_array($keys)) {
      $keys = array(
        '#conjunction' => 'AND',
        $keys,
      );
    }

    $or = db_or();
    $neg = !empty($keys['#negation']);
    $conj = $keys['#conjunction'];
    $words = array();
    $nested = array();
    $negated = array();
    $db_query = NULL;
    $mul_words = FALSE;
    $not_nested = FALSE;

    foreach ($keys as $i => $key) {
      if (!element_child($i)) {
        continue;
      }
      if (is_scalar($key)) {
        $words[] = $key;
      }
      elseif (empty($key['#negation'])) {
        if ($neg) {
          // If this query is negated, we also only need item_ids from
          // subqueries.
          $key['#negation'] = TRUE;
        }
        $nested[] = $key;
      }
      else {
        $negated[] = $key;
      }
    }
    $subs = count($words) + count($nested);
    $not_nested = ($subs <= 1 && count($fields) == 1) || ($neg && $conj == 'OR' && !$negated);

    if ($words) {
      if (count($words) > 1) {
        $mul_words = TRUE;
        foreach ($words as $word) {
          $or->condition('word', $word);
        }
      }
      else {
        $word = array_shift($words);
      }
      foreach ($fields as $name => $field) {
        $table = $field['table'];
        $query = db_select($table, 't', $this->query_options);
        if ($neg) {
          $query->fields('t', array('item_id'));
        }
        elseif ($not_nested) {
          $query->fields('t', array('item_id', 'score'));
        }
        else {
          $query->fields('t');
        }
        if ($mul_words) {
          $query->condition($or);
        }
        else {
          $query->condition('word', $word);
        }
        if (!isset($db_query)) {
          $db_query = $query;
        }
        elseif ($not_nested) {
          $db_query->union($query, 'UNION');
        }
        else {
          $db_query->union($query, 'UNION ALL');
        }
      }
    }

    if ($nested) {
      $word = '';
      foreach ($nested as $k) {
        $query = $this->createKeysQuery($k, $fields, $all_fields);
        if (!$neg) {
          $word .= ' ';
          $var = ':word' . strlen($word);
          $query->addExpression($var, 'word', array($var => $word));
        }
        if (!isset($db_query)) {
          $db_query = $query;
        }
        elseif ($not_nested) {
          $db_query->union($query, 'UNION');
        }
        else {
          $db_query->union($query, 'UNION ALL');
        }
      }
    }

    if (isset($db_query) && !$not_nested) {
      $db_query = db_select($db_query, 't', $this->query_options);
      $db_query->addField('t', 'item_id', 'item_id');
      if (!$neg) {
        $db_query->addExpression('SUM(t.score)', 'score');
        $db_query->groupBy('t.item_id');
      }
      if ($conj == 'AND' && $subs > 1) {
        $var = ':subs' . ((int) $subs);
        if (!$db_query->getGroupBy()) {
          $db_query->groupBy('t.item_id');
        }
        if ($mul_words) {
          $db_query->having('COUNT(DISTINCT t.word) >= ' . $var, array($var => $subs));
        }
        else {
          $db_query->having('COUNT(DISTINCT t.word) >= ' . $var, array($var => $subs));
        }
      }
    }

    if ($negated) {
      if (!isset($db_query) || $conj == 'OR') {
        if (isset($all_fields['search_api_language'])) {
          // We use this table because all items should be contained exactly once.
          $table = $all_fields['search_api_language']['table'];
        }
        else {
          $distinct = TRUE;
          foreach ($all_fields as $field) {
            $table = $field['table'];
            if (!search_api_is_list_type($field['type']) && !search_api_is_text_type($field['type'])) {
              unset($distinct);
              break;
            }
          }
        }
        if (isset($db_query)) {
          // We are in a rather bizarre case where the keys are something like "a OR (NOT b)".
          $old_query = $db_query;
        }
        $db_query = db_select($table, 't', $this->query_options);
        $db_query->addField('t', 'item_id', 'item_id');
        if (!$neg) {
          $db_query->addExpression(':score', 'score', array(':score' => 1));
          $distinct = TRUE;
        }
        if (isset($distinct)) {
          $db_query->distinct();
        }
      }

      if ($conj == 'AND') {
        foreach ($negated as $k) {
          $db_query->condition('t.item_id', $this->createKeysQuery($k, $fields, $all_fields), 'NOT IN');
        }
      }
      else {
        $or = db_or();
        foreach ($negated as $k) {
          $or->condition('t.item_id', $this->createKeysQuery($k, $fields, $all_fields), 'NOT IN');
        }
        if (isset($old_query)) {
          $or->condition('t.item_id', $old_query, 'NOT IN');
        }
        $db_query->condition($or);
      }
    }

    return $db_query;
  }

  /**
   * Helper method for finding any needed table for a filter query.
   */
  protected function findTable(array $filters, array $fields) {
    foreach ($filters as $filter) {
      if (is_array($filter)) {
        return $fields[$filter[0]]['table'];
      }
    }
    foreach ($filters as $filter) {
      if (is_object($filter)) {
        $ret = $this->findTable($filter->getFilters(), $fields);
        if ($ret) {
          return $ret;
        }
      }
    }
  }

  /**
   * Helper method for creating a condition for filtering search results.
   *
   * @return QueryConditionInterface
   */
  protected function createFilterCondition(SearchApiQueryFilterInterface $filter, array $fields, SelectQueryInterface $db_query) {
    $cond = db_condition($filter->getConjunction());
    $empty = TRUE;
    // Store whether a JOIN alrady occurred for a field, so we don't JOIN
    // repeatedly for OR filters.
    $first_join = array();
    // Store the table aliases for the fields in this condition group.
    $tables = array();
    foreach ($filter->getFilters() as $f) {
      if (is_object($f)) {
        $c = $this->createFilterCondition($f, $fields, $db_query);
        if ($c) {
          $empty = FALSE;
          $cond->condition($c);
        }
      }
      else {
        $empty = FALSE;
        if (!isset($fields[$f[0]])) {
          throw new SearchApiException(t('Unknown field in filter clause: @field.', array('@field' => $f[0])));
        }
        $field = $fields[$f[0]];
        if ($f[1] === NULL) {
          $query = db_select($field['table'], 't')
            ->fields('t', array('item_id'));
          $cond->condition('t.item_id', $query, $f[2] == '<>' || $f[2] == '!=' ? 'IN' : 'NOT IN');
          continue;
        }
        if (search_api_is_text_type($field['type'])) {
          $keys = $this->prepareKeys($f[1]);
          $query = $this->createKeysQuery($keys, array($field), $fields);
          // We don't need the score, so we remove it. The score might either be
          // an expression or a field.
          $query_expressions = &$query->getExpressions();
          if ($query_expressions) {
            $query_expressions = array();
          }
          else {
            $query_fields = &$query->getFields();
            unset($query_fields['score']);
            unset($query_fields);
          }
          unset($query_expressions);
          $cond->condition('t.item_id', $query, $f[2] == '<>' || $f[2] == '!=' ? 'NOT IN' : 'IN');
        }
        else {
          $new_join = search_api_is_list_type($field['type'])
              && ($filter->getConjunction() == 'AND'
                  || empty($first_join[$f[0]]));
          if ($new_join || empty($tables[$f[0]])) {
            $tables[$f[0]] = $this->getTableAlias($field, $db_query, $new_join);
            $first_join[$f[0]] = TRUE;
          }
          $cond->condition($tables[$f[0]] . '.value', $f[1], $f[2]);
        }
      }
    }
    return $empty ? NULL : $cond;
  }

  /**
   * Helper method for adding a field's table to a database query.
   *
   * @param array $field
   *   The field information array. The "table" key should contain the table
   *   name to which a join should be made.
   * @param SelectQueryInterface $db_query
   *   The database query used.
   * @param bool $newjoin
   *   (optional) If TRUE, a join is done even if the table was already joined
   *   to in the query.
   * @param string $join
   *   (optional) The join method to use. Must be a method of the $db_query.
   *   Normally, "join", "innerJoin", "leftJoin" and "rightJoin" are supported.
   *
   * @return string
   *   The alias for the field's table.
   */
  protected function getTableAlias(array $field, SelectQueryInterface $db_query, $newjoin = FALSE, $join = 'leftJoin') {
    if(!$newjoin) {
      foreach ($db_query->getTables() as $alias => $info) {
        $table = $info['table'];
        if (is_scalar($table) && $table == $field['table']) {
          return $alias;
        }
      }
    }
    return $db_query->$join($field['table'], 't', 't.item_id = %alias.item_id');
  }

  /**
   * Helper method for getting the facet values for a query.
   *
   * @param SearchApiQueryInterface $query
   *   The search query for which facets should be computed.
   * @param SelectQueryInterface $db_query
   *   A database select query which returns all results of that search query.
   *
   * @return array
   *   An array of facets, as specified by the search_api_facets feature.
   */
  protected function getFacets(SearchApiQueryInterface $query, SelectQueryInterface $db_query) {
    $table = $this->getTemporaryResultsTable($db_query);
    if (!$table) {
      return array();
    }

    $fields = $this->options['indexes'][$query->getIndex()->machine_name];
    $ret = array();
    foreach ($query->getOption('search_api_facets') as $key => $facet) {
      if (empty($fields[$facet['field']])) {
        $this->warnings[] = t('Unknown facet field @field.', array('@field' => $facet['field']));
        continue;
      }
      $field = $fields[$facet['field']];
      $missing_count = 0;

      $select = db_select($table, 't');
      $alias = $this->getTableAlias($field, $select, TRUE, $facet['missing'] ? 'leftJoin' : 'innerJoin');
      $select->addField($alias, search_api_is_text_type($field['type']) ? 'word' : 'value', 'value');
      $select->addExpression('COUNT(DISTINCT t.item_id)', 'num');
      $select->groupBy('value');
      $select->orderBy('num', 'DESC');

      $limit = $facet['limit'];
      if ((int) $limit > 0) {
        $select->range(0, $limit);
      }
      if ($facet['min_count'] > 1) {
        $select->having('num >= :count', array(':count' => $facet['min_count']));
      }

      $terms = array();
      foreach ($select->execute() as $row) {
        $terms[] = array(
          'count' => $row->num,
          'filter' => isset($row->value) ? '"' . $row->value . '"' : '!',
        );
      }
      $ret[$key] = $terms;
    }
    return $ret;
  }

  /**
   * Helper method for creating a temporary table from a SelectQuery.
   *
   * Will return the name of a table containing the item IDs of all results, or
   * FALSE on failure.
   *
   * @param SelectQueryInterface $db_query
   *   The select query whose results should be stored in the temporary table.
   *
   * @return string|false
   *   The name of the temporary table, or FALSE on failure.
   */
  protected function getTemporaryResultsTable(SelectQueryInterface $db_query) {
    // We only need the id field, not the score.
    $fields = &$db_query->getFields();
    unset($fields['score']);
    if (count($fields) != 1 || !isset($fields['item_id'])) {
      watchdog('search_api_db', 'Error while adding facets: only "item_id" field should be used, used are: @fields.',
          array('@fields' => implode(', ', array_keys($fields))), WATCHDOG_WARNING);
      return FALSE;
    }
    $expressions = &$db_query->getExpressions();
    $expressions = array();
    $db_query->distinct();
    if (!$db_query->preExecute()) {
      return FALSE;
    }
    $args = $db_query->getArguments();
    return db_query_temporary((string) $db_query, $args, $this->query_options);
  }

  /**
   * Implements SearchApiAutocompleteInterface::getAutocompleteSuggestions().
   */
  public function getAutocompleteSuggestions(SearchApiQueryInterface $query, SearchApiAutocompleteSearch $search, $incomplete_key, $user_input) {
    $settings = isset($this->options['autocomplete']) ? $this->options['autocomplete'] : array();
    $settings += array(
      'suggest_suffix' => TRUE,
      'suggest_words' => TRUE,
    );
    // If none of these options is checked, the user apparently chose a very
    // roundabout way of telling us he doesn't want autocompletion.
    if (!array_filter($settings)) {
      return array();
    }

    $index = $query->getIndex();
    if (empty($this->options['indexes'][$index->machine_name])) {
      throw new SearchApiException(t('Unknown index @id.', array('@id' => $index->machine_name)));
    }
    $set = $this->setDb();
    $fields = $this->options['indexes'][$index->machine_name];

    $suggestions = array();
    $passes = array();

    // Decide which methods we want to use.
    if ($incomplete_key && $settings['suggest_suffix']) {
      $passes[] = 1;
      $incomplete_like = db_like($incomplete_key) . '%';
    }
    if ($settings['suggest_words']
        && (!$incomplete_key || strlen($incomplete_key) >= $this->options['min_chars'])) {
      $passes[] = 2;
    }

    // We want about half of the suggestions from each enabled method.
    $limit = $query->getOption('limit', 10);
    $limit /= count($passes);

    // Also collect all keywords already contained in the query so we don't
    // suggest them.
    $keys = drupal_map_assoc(preg_split('/[^\p{L}\p{N}]+/u', $user_input, -1, PREG_SPLIT_NO_EMPTY));
    if ($incomplete_key) {
      $keys[$incomplete_key] = $incomplete_key;
    }

    foreach ($passes as $pass) {
      if ($pass == 2 && $incomplete_key) {
        $query->keys($user_input);
      }
      $db_query = $this->createDbQuery($query, $fields);

      // We need a list of all current results to match the suggestions against.
      // However, since MySQL doesn't allow using a temporary table multiple
      // times in one query, we regrettably have to do it this way.
      if (count($query->getFields()) > 1) {
        $all_results = $db_query->execute()->fetchCol();
        // Compute the total number of results so we can later sort out matches
        // that occur too often.
        $total = count($all_results);
      }
      else {
        $table = $this->getTemporaryResultsTable($db_query);
        if (!$table) {
          return;
        }
        $all_results = db_select($table, 't', $this->query_options)
          ->fields('t', array('item_id'));
        $total = db_query("SELECT COUNT(item_id) FROM {{$table}}")->fetchField();
      }
      $max_occurrences = max(1, floor($total * variable_get('search_api_db_autocomplete_max_occurrences', 0.9)));

      if (!$total) {
        if ($pass == 1) {
          return;
        }
        continue;
      }

      foreach ($query->getFields() as $field) {
        if (!isset($fields[$field]) || !search_api_is_text_type($fields[$field]['type'])) {
          continue;
        }
        $field_query = db_select($fields[$field]['table'], 't', $this->query_options)
          ->fields('t', array('word', 'item_id'))
          ->condition('item_id', $all_results, 'IN');
        if ($pass == 1) {
          $field_query->condition('word', $incomplete_like, 'LIKE')
            ->condition('word', $keys, 'NOT IN');
        }
        if (!isset($word_query)) {
          $word_query = $field_query;
        }
        else {
          $word_query->union($field_query);
        }
      }
      $db_query = db_select($word_query, 't', $this->query_options);
      $db_query->addExpression('COUNT(DISTINCT item_id)', 'results');
      $db_query->fields('t', array('word'))
        ->groupBy('word')
        ->having('results <= :max', array(':max' => $max_occurrences))
        ->orderBy('results', 'DESC')
        ->range(0, ceil($limit));
      $incomp_len = strlen($incomplete_key);
      foreach ($db_query->execute() as $row) {
        $suffix = ($pass == 1) ? substr($row->word, $incomp_len) : ' ' . $row->word;
        $suggestions[] = array(
          'suggestion_suffix' => $suffix,
          'results' => $row->results,
        );
      }
    }

    if ($set) {
      $this->resetDb();
    }

    return $suggestions;
  }

  /**
   * Helper method for setting the database to the one selected by the user.
   */
  protected function setDb() {
    if (!isset($this->previous_db)) {
      list($key, $target) = explode(':', $this->options['database'], 2);
      $this->previous_db = db_set_active($key);
      if (!isset($this->query_options)) {
        $this->query_options = array('target' => $target);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Helper method for resetting the original database.
   */
  protected function resetDb() {
    if (isset($this->previous_db)) {
      db_set_active($this->previous_db);
      $this->previous_db = NULL;
      return TRUE;
    }
    return FALSE;
  }

}
