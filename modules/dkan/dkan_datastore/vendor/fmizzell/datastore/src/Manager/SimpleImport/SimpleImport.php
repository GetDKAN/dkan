<?php

namespace Dkan\Datastore\Manager\SimpleImport;

use Dkan\Datastore\Manager\Manager;
use Dkan\Datastore\Manager\IManager;
use Dkan\Datastore\CsvParser;
use Dkan\Datastore\Resource;
use Dkan\Datastore\Storage\Database\Query\Insert;

/**
 * Class SimpleImport.
 */
class SimpleImport extends Manager {

  private $query;

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

    $this->query = new Insert($this->getTableName());
    $header = $this->getTableHeaders();
    $this->query->fields($header);

    $counter = 0;

    $parser = $this->getParser();

    $h = fopen($this->getResource()->getFilePath(), 'r');

    $finished = TRUE;
    $interrupt = $this->getInterrupt();
    while ($chunk = fread($h, 32)) {
      if ($interrupt) {
        $finished = FALSE;
        break;
      }
      if (time() < $end) {
        $parser->feed($chunk);
        $counter = $this->getAndStore($parser, $header, $counter, $start);

        if ($counter === FALSE) {
          return IManager::DATA_IMPORT_ERROR;
        }
      }
      else {
        $finished = FALSE;
        break;
      }
      if ($counter % 1000 == 0) {
        $interrupt = $this->getInterrupt();
      }
    }

    fclose($h);

    // Flush the parser.
    $parser->finish();
    $this->getAndStore($parser, $header, $counter, $start);

    try {
      $this->database->insert($this->query);
    }
    catch (\Exception $e) {
      $this->setError($e->getMessage());
      return IManager::DATA_IMPORT_ERROR;
    }

    if ($finished) {
      return IManager::DATA_IMPORT_DONE;
    }
    else {
      return IManager::DATA_IMPORT_PAUSED;
    }
  }

  /**
   * Get Interrupt.
   */
  private function getInterrupt() {
    return 0;
  }

  /**
   * Private method.
   */
  private function getAndStore(CsvParser $parser, $header, $counter, $start) {
    while ($record = $parser->getRecord()) {
      if ($counter >= $start) {
        $values = $record;

        if ($this->valuesAreValid($values, $header)) {
          $this->query->values($values);
        }
        else {
          $this->setError("Invalid line {$counter} in {$this->getResource()->getFilePath()}");
          return FALSE;
        }

        if ($counter % 1000 == 0) {
          try {
            $this->database->insert($this->query);
          }
          catch (\Exception $e) {
            $this->setError($e->getMessage());
            return FALSE;
          }
          unset($this->query);
          $this->query = new Insert($this->getTableName());
          $this->query->fields($header);
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
