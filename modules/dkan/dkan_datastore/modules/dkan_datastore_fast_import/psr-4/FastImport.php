<?php

namespace Dkan\Datastore\Manager;

use Dkan\Datastore\Resource;

class FastImport extends Manager {



  function initialization(Resource $resource) {
  }

  function storeRecords() {
    $properties = $this->getConfigurableProperties();

    $file_path = $this->getResource()->getFilePath();

    $headers = $this->getTableHeaders();
    $fields = implode(", ", $headers);

    $delim = $properties['delimiter'];

    // @todo Add support for no headers.
    $has_headers = ''; //($config['no_headers']) ? '' : 'IGNORE 1 LINES';

    $quote_delimiters = $properties["quote"];

    $lines_terminated_by = "\n";

    $fields_escaped_by = $properties["escape"];

    $set_null_values = '';
    // @todo Add support for empty as null.
    /*
    $empty_as_null = FALSE; //variable_get('dkan_datastore_fast_import_load_empty_cells_as_null', 0);

    $params = array();

    // If importing empty values as null, create a local var for each column.
    // See https://stackoverflow.com/questions/2675323/mysql-load-null-values-from-csv-data
    if ($empty_as_null) {
      $vars = dkan_datastore_fast_import_get_fields_as_vars($headers);
      $fields = implode(',', $vars);
      $headers_to_vars = array_combine($headers, $vars);
      foreach ($headers_to_vars as $header => $var) {
        $set_null_values = $set_null_values . ", $header = nullif($var,'')";
      }
    }*/

    $load_data_statement = 'LOAD DATA';

    $sql = "$load_data_statement INFILE :file_path IGNORE
      INTO TABLE {$this->getTableName()}
      FIELDS TERMINATED BY :delim
      ENCLOSED BY :quote_delimiters";
    $params[':file_path'] = $file_path;
    $params[':delim'] = $delim;
    $params[':quote_delimiters'] = $quote_delimiters;

    if ($fields_escaped_by) {
      $sql = $sql . "  ESCAPED BY :fields_escaped_by";
      $params[':fields_escaped_by'] = $fields_escaped_by;
    }
    $sql = $sql . " LINES TERMINATED BY '$lines_terminated_by' $has_headers ($fields);";

    try {
      db_query($sql, $params);
    }
    catch (\Exception $e) {
      $this->setError($e->getMessage());
      return FALSE;
    }

    return TRUE;
  }
}
