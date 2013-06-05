<?php

/**
 * @file
 * Schema module enhancements to DatabaseSchema_pgsql
 */

class SchemaDatabaseSchema_pgsql extends DatabaseSchema_pgsql {
  /**
   * Retrieve generated SQL to create a new table from a Drupal schema definition.
   *
   * @param $name
   *   The name of the table to create.
   * @param $table
   *   A Schema API table definition array.
   * @return
   *   An array of SQL statements to create the table.
   */
  public function getCreateTableSql($name, $table) {
    return parent::createTableSql($name, $table);
  }

  public function schema_type_map() {
    static $map;
    if (!isset($map)) {
      $map = array_flip(array_map('strtolower', $this->getFieldTypeMap()));
      $map['character varying'] = 'varchar:normal';
      $map['integer'] = 'int:normal';
    }
    return $map;
  }

  public function inspect($connection = NULL, $table_name = NULL) {
    // Support the deprecated connection parameter.
    if (isset($connection) && $connection != $this->connection->getKey()) {
      $this->connection = Database::getConnection('default', $connection);
    }

    // Get the current database name
    $info = $this->connection->getConnectionOptions();
    $database = $info['database'];

    //
    // Retrieve information about all columns in the database from the
    // 'columns' table of 'information_schema'.  In pgsql, TABLE_CATALOG
    // is the database name and we provide $database to query() below.
    // We sort the columns by table_name and ordinal_position so the get
    // added to our array in the same order.
    //
    $sql = ('SELECT * FROM information_schema.COLUMNS ' .
            'WHERE table_catalog=:database AND table_schema=current_schema()');
    $tokens = array(':database' => $database);
    if (isset($table_name)) {
      $sql .= 'AND table_name = :table ';
      $tokens[':table'] = $table_name;
    }
    $sql .= 'ORDER BY table_name, ordinal_position';

    $res = $this->connection->query($sql, $tokens);

    //
    // Add an entry to $tables[<tablename>]['fields'] for each column.  $r
    // is a row from information_schema.columns.  $col is the Schema
    // column structure we build up from it.
    //
    foreach ($res as $r) {
      $col = array();

      $r->new_table_name = schema_unprefix_table($r->table_name);

      // We treat numeric columns slightly differently and identify them
      // because they have a 'numeric_precision_radix' property.
      $numeric = !is_null($r->numeric_precision_radix);

      // Determine the Schema type and size from the database data_type.
      list($col['type'], $col['size']) = schema_schema_type($r->data_type, $r->table_name, $r->column_name, 'pgsql');

      // Non-numeric columns (e.g. varchar) can have a 'length'.
      if (! $numeric && $r->character_maximum_length) {
        $col['length'] = $r->character_maximum_length;
      }

      // Type 'numeric' columns have precision and scale
      if ($col['type'] == 'numeric') {
        $col['precision'] = (int) $r->numeric_precision;
        $col['scale'] = (int) $r->numeric_scale;
      }

      // Any column can have NOT NULL.
      $col['not null'] = ($r->is_nullable == 'YES' ? FALSE : TRUE);

      // Any column might have a default value.  We have to set
      // $col['default'] to the correct type of data.  Remember that '',
      // 0, and '0' are all different types.
      if (! is_null($r->column_default)) {

        // pgsql's column_default can have ::typename appended,
        // nextval('<sequence_name>') if it is serial, etc.  Here, we're
        // just splitting out the actual default value.
        if (strpos($r->column_default, '::') !== FALSE) {
          list($col['default'], $def_type) = explode('::', $r->column_default);
        }
        else {
          $col['default'] = $r->column_default;
          $def_type = '';
        }

        if ($numeric) {
          // $col['default'] is currently a string.  If the column is
          // numeric, use intval() or floatval() to extract the value as a
          // numeric type.

          // The value is actually an expression, and may be stored with parentheses
          $col['default'] = preg_replace('/^\((.*)\)$/', '\1', $col['default']);

          // more pgsql-specific stuff
          if (strpos($col['default'], 'nextval(\'') !== FALSE &&
              $def_type == 'regclass)') {
            $col['type'] = 'serial';
            unset($col['default']);
          }
          elseif ($col['type'] == 'float') {
            $col['default'] = floatval($col['default']);
          }
          else {
            $col['default'] = intval($col['default']);
          }
        }
        else {
          // The column is not numeric, so $col['default'] should remain
          // a string.  However, pgsql returns $r->column_default
          // wrapped in single-quotes.  We just want the string value,
          // so strip off the quotes.
          $col['default'] = substr($col['default'], 1, -1);
        }

      }

      // Set $col['unsigned'] if the column is unsigned.  These
      // domains are currently defined in system.install (they should
      // probably eventually be moved into database.pgsql.inc).
      switch ($r->domain_name) {
        case 'int_unsigned':
        case 'smallint_unsigned':
        case 'bigint_unsigned':
          $col['unsigned'] = 1;
          break;
      }
      if (isset($r->check_clause) && $r->check_clause == '((' . $r->column_name . ' => 0))') {
        $col['unsigned'] = 1;
      }

      // Save the column definition we just derived from $r.
      $tables[$r->table_name]['fields'][$r->column_name] = $col;
      // At this point, $tables is indexed by the raw db table name - save the unprefixed
      // name for later use
      $tables[$r->table_name]['name'] = $r->new_table_name;
    }

    //
    // Make sur we caught all the unsigned columns.  I could not get
    // this to work as a left join on the previous query.
    //
    $res = $this->connection->query('SELECT ccu.*, cc.check_clause
                     FROM information_schema.constraint_column_usage ccu
                     INNER JOIN information_schema.check_constraints cc ON ccu.constraint_name=cc.constraint_name
                     WHERE table_schema=current_schema()');
    foreach ($res as $r) {
      $r->table_name = schema_unprefix_table($r->table_name);

      if ($r->check_clause == '((' . $r->column_name . ' >= 0))' ||
          $r->check_clause == '((' . $r->column_name . ' >= (0)::numeric))') {
        $tables[$r->table_name]['fields'][$r->column_name]['unsigned'] = TRUE;
      }
    }

    //
    // Retrieve information about all keys in the database from the pg
    // system catalogs.  This query is derived from phpPgAdmin's
    // getIndexes() function.  The pg_get_indexdef() function returns
    // the CREATE INDEX statement to create the index; we parse it to
    // produce our data structure.  Ick.
    //
    $res = $this->connection->query('SELECT n.nspname, c.relname AS tblname, ' .
      '   c2.relname AS indname, i.indisprimary, i.indisunique, ' .
      '   pg_get_indexdef(i.indexrelid) AS inddef ' .
      'FROM pg_class c, pg_class c2, pg_index i, pg_namespace n ' .
      'WHERE c.oid = i.indrelid AND i.indexrelid = c2.oid AND ' .
      '      c.relnamespace=n.oid AND n.nspname=current_schema() ' .
      'ORDER BY c2.relname');
    foreach ($res as $r) {
      $r->tblname = schema_unprefix_table($r->tblname);
      $r->indname = schema_unprefix_table($r->indname);

      if (preg_match('@CREATE(?: UNIQUE)? INDEX \w+ ON "?(\w+)"?(?: USING \w+)? \((.*)\)@', $r->inddef, $m)) {
        list($all, $table, $keys) = $m;

        $name = $r->indname;
        if (preg_match('@^' . $r->tblname . '_(.*)_(?:idx|key)$@', $name, $m)) {
          $name = $m[1];
        }

        preg_match_all('@((?:"?\w+"?)|(?:substr\(\(?"?(\w+)\"?.*?, 1, (\d+)\)))(?:, |$)@', $keys, $m);
        foreach ($m[1] as $idx => $colname) {
          if ($m[2][$idx]) {
            $key = array($m[2][$idx], intval($m[3][$idx]));
          }
          else {
            $key = str_replace('"', '', $colname);
          }
          if ($r->indisprimary == 't') {
            $tables[$r->tblname]['primary key'][] = $key;
          }
          elseif ($r->indisunique == 't') {
            $tables[$r->tblname]['unique keys'][$name][] = $key;
          }
          else {
            $tables[$r->tblname]['indexes'][$name][] = $key;
          }
        }
      }
      else {
        watchdog('schema', 'unrecognized pgsql index definition: @stmt',
          array('@stmt' => $r->inddef));
      }
    }

    // Now, for tables which we have unprefixed, index $tables by the unprefixed name
    foreach ($tables as $tablename => $table) {
      $newname = $tables[$tablename]['name'];
      if ($tablename != $newname) {
        $tables[$newname] = $table;
        unset($tables[$tablename]);
      }
    }

    // All done!  Visit admin/structure/schema/inspect to see the
    // pretty-printed version of what gets returned here and verify if
    // it is correct.
    return $tables;
  }

  
  
  
  /**
   * Get parent's type mapping and add missing
   * 3rd-party module defined types.
   *
   * @return
   *   An array with engine-specific type mappings
   */
  public function getFieldTypeMap() {
    static $map = null;
    
    if(!$map) {
      // get our parent class mapping first
      $map = parent::getFieldTypeMap();
      $db_type = db_driver().'_type';
      
      // Load all table schema fields. This has the effect of
      //  loading in any 3rd-party module field_schemas
      $fields = field_read_fields(array(), array('include_deleted' => TRUE, 'include_inactive' => TRUE));

      foreach($fields as $field) {
        $cols = $field['columns'];
        
        // loop thru each column and add any missing mappings
        foreach($cols as $name=>$col) {
          // Schema API says "type" is a required field - bail if it's missing
          if(!isset($col['type'])) continue;
          $type = $col['type'];
          $size = (isset($col['size']) ? $col['size'] : 'normal');
          $generic_type = $type.':'.$size;
          if(!isset($map[$generic_type])) {
            // use engine specific type if it exists
            $map[$generic_type] = isset($col[$db_type]) ? $col[$db_type] : $type;
          }
        }

      }
    }

    return $map;
  }

}
