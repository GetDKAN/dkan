<?php

namespace Dkan\Datastore\Manager\SimpleImport;

use Dkan\Datastore\Manager\CharsetEncoding;
use Dkan\Datastore\Manager\Manager;
use Dkan\Datastore\Manager\ManagerInterface;
use Dkan\Datastore\Parser\Csv;
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

    $encoder = new CharsetEncoding($this);

    $finished = TRUE;
    $interrupt = $this->getInterrupt();
    while ($chunk = fread($h, 32)) {
      if ($interrupt) {
        $finished = FALSE;
        break;
      }
      if (time() < $end) {
        try {
          $chunk = $encoder->fixEncoding($chunk);
        }
        catch (\Exception $exception) {
          drupal_set_message($exception->getMessage(), 'warning');
        }
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
      if ($counter % 1000 == 0) {
        $interrupt = $this->getInterrupt();
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

    if ($interrupt) {
      variable_set('dkan_datastore_interrupt', 0);
    }

    if ($finished) {
      return ManagerInterface::DATA_IMPORT_DONE;
    }
    else {
      return ManagerInterface::DATA_IMPORT_PAUSED;
    }
  }

  /**
   * Get Interrupt.
   */
  private function getInterrupt() {
    $query = db_select("variable", 'v');
    $query->fields('v', ['value']);
    $query->condition('name', "dkan_datastore_interrupt");
    $results = $query->execute();
    foreach ($results as $result) {
      $value = unserialize($result->value);
      return $value;
    }
    return 0;
  }

  /**
   * Private method.
   */
  private function getAndStore(Csv $parser, \InsertQuery $query, $header, $counter, $start) {
    while ($record = $parser->getRecord()) {
      if ($counter >= $start) {
        $values = $record;

        if ($this->valuesAreValid($values, $header)) {
          $query->values($values);
        }
        else {
          $header_count = count($header);
          $values_count = count($values);
          $json_header = json_encode($header);
          $json_values = json_encode($values);

          $message = "";
          if ($header_count != $values_count) {
            $message = "The number of values ($values_count) does not match the number of columns ($header_count). ";
          }
          $this->setError($message . "Invalid line {$counter} in {$this->getResource()->getFilePath()}; header({$header_count}): {$json_header} values({$values_count}): {$json_values}");
          return FALSE;
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
