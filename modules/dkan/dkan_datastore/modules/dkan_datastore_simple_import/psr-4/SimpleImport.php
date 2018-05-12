<?php

namespace Dkan\Datastore\Manager;

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
  protected function storeRecords() {
    $number_of_items_imported = $this->numberOfRecordsImported();
    $start = ($number_of_items_imported > 0) ? $number_of_items_imported + 1 : 1;

    $query = db_insert($this->getTableName());
    $header = $this->getTableHeaders();
    $query->fields($header);

    $counter = 0;

    $parser = $this->getParser();

    $h = fopen($this->getResource()->getFilePath(), 'r');

    while ($chunk = fread($h, 32)) {
      $parser->feed($chunk);
      $counter = $this->getAndStore($parser, $query, $header, $counter, $start);
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
      return FALSE;
    }

    return TRUE;
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
