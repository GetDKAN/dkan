<?php

namespace Dkan\Datastore\Manager\SimpleImport;

use Dkan\Datastore\Manager\Manager;
use Dkan\Datastore\Manager\ManagerInterface;
use Dkan\Datastore\CsvParser;
use Dkan\Datastore\Resource;

/**
 * Class SimpleImport.
 */
class SimpleImport extends Manager {

  /**
   * {@inheritdoc}
   */
  protected function initialization(Resource $resource) {}

  /**
   * {@inheritdoc}
   */
  protected function storeRecords($time_limit = 0) {
    $end = 9999999999999999999999999;
    if ($time_limit > 0) {
      $end = time() + $time_limit;
    }

    $number_of_items_imported = $this->numberOfRecordsImported();
    $start = ($number_of_items_imported > 0) ? $number_of_items_imported + 1 : 1;

    $query = db_insert($this->getTableName());
    $header = $this->getTableHeaders();
    $query->fields($header);

    $counter = 0;

    $parser = $this->getParser();

    $h = fopen($this->getResource()->getFilePath(), 'r');

    $finished = TRUE;
    while ($chunk = fread($h, 32)) {
      if (time() < $end) {
        $parser->feed($chunk);
        $counter = $this->getAndStore($parser, $query, $header, $counter, $start);

        if ($counter === FALSE) {
          return ManagerInterface::DATA_IMPORT_ERROR;
        }
      }
      else {
        $finished = FALSE;
        break;
      }
    }

    fclose($h);

    // Flush the parser.
    $parser->finish();
    $this->getAndStore($parser, $query, $header, $counter, $start);

    try {
      $query->execute();
    }
    catch (\Exception $e) {
      $this->setError($e->getMessage());
      return ManagerInterface::DATA_IMPORT_ERROR;
    }

    if ($finished) {
      return ManagerInterface::DATA_IMPORT_DONE;
    }
    else {
      return ManagerInterface::DATA_IMPORT_READY;
    }
  }

  /**
   * Private method.
   */
  private function getAndStore(CsvParser $parser, \InsertQuery $query, $header, $counter, $start) {
    while ($record = $parser->getRecord()) {
      if ($counter >= $start) {
        $values = $record;

        if ($this->valuesAreValid($values, $header)) {
          $query->values($values);
        }
        else {
          $this->setError("Invalid line {$counter} in {$this->getResource()->getFilePath()}");
          return FALSE;
          break;
        }

        if ($counter % 1000 == 0) {
          try {
            $query->execute();
          }
          catch (\Exception $e) {
            $this->setError($e->getMessage());
            return FALSE;
          }
          $query = db_insert($this->getTableName());
          $query->fields($header);
        }
      }

      $counter++;
    }

    return $counter;
  }

  /**
   * Private method.
   */
  private function valuesAreValid($values, $header) {
    $number_of_fields = count($header);
    $number_of_values = count($values);
    if ($number_of_fields == $number_of_values) {
      return TRUE;
    }
    return FALSE;
  }

}
