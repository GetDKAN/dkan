<?php

namespace Dkan\Datastore\Manager\FastImport;

use Dkan\Datastore\Manager\Manager;
use Dkan\Datastore\Resource;

/**
 * Class FastImport.
 */
class FastImport extends Manager {

  /**
   * {@inheritdoc}
   */
  protected function initialization(Resource $resource) {}

  /**
   * {@inheritdoc}
   */
  protected function storeRecords($time_limit = 0) {
    $properties = $this->getConfigurableProperties();

    $file_path = $this->getResource()->getFilePath();

    $headers = $this->getTableHeaders();
    $fields = implode(", ", $headers);

    $delim = $properties['delimiter'];

    // @todo Add support for no headers.
    $has_headers = '';

    $quote_delimiters = $properties["quote"];

    $lines_terminated_by = "\n";

    $fields_escaped_by = $properties["escape"];

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
