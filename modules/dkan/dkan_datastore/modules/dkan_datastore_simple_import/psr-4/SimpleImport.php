<?php

namespace Dkan\Datastore\Manager;

use Dkan\Datastore\Resource;

class SimpleImport extends Manager {

  public function initialization(Resource $resource) {}

  public function storeRecords() {
    $number_of_items_imported = $this->numberOfRecordsImported();
    $index = ($number_of_items_imported > 0) ? $number_of_items_imported - 1 : 1;

    $query = db_insert($this->getTableName());
    $header = $this->getTableHeaders();
    $query->fields($header);

    $counter = 0;

    $parser = $this->getParser();

    $h = fopen($this->getResource()->getFilePath(), 'r');

    while ($chunk = fread($h, 32)) {
      $parser->feed($chunk);

      while ($record = $parser->getRecord()) {
        if ($counter >= $index) {

          $values = $record;

          if ($this->valuesAreValid($values, $header)) {
            $query->values($values);
          } else {
            $this->setError("Invalid line {$counter} in {$this->getResource()->getFilePath()}");
            return FALSE;
            break;
          }

          if ($counter % 1000 == 0) {
            try {
              $query->execute();
            }
            catch(\Exception $e) {
              $this->setError($e->getMessage());
              return FALSE;
            }
            $query = db_insert($this->getTableName());
            $query->fields($header);
          }
        }

        $counter++;
      }
    }

    fclose($h);

    // Flush the parser
    $parser->finish();
    while ($record = $parser->getRecord()) {
      $values = $record;

      if ($this->valuesAreValid($values, $header)) {
        $query->values($values);
      } else {
        $this->setError("Invalid line {$counter} in {$this->getResource()->getFilePath()}");
        return FALSE;
      }
      $counter++;
    }

    try {
      $query->execute();
    } catch(\Exception $e) {
      $this->setError($e->getMessage());
      return FALSE;
    }

    return TRUE;
  }

  private function valuesAreValid($values, $header) {
    $number_of_fields = count($header);
    $number_of_values = count($values);
    if ($number_of_fields == $number_of_values) {
      return TRUE;
    }
    return FALSE;
  }


  /*public function deleteForm(&$form_state){
    $table_name = $this->getTableName();

    if (db_table_exists($table_name) && $this->numberOfitemsImported() > 0) {
      $form = [];

      $form['actions'] = array('#type' => 'actions');
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Delete'),
      );
    }
    else {
      $form['status'] = [
        '#type' => 'item',
        '#title' => t('@source_name: Status', array('@source_name' => "Simple Importer")),
        '#markup' => "<br> Can't delete the items in the datastore <br> The datastore has not been created or it has 0 items.",
      ];
    }

    return $form;
  }

  public function deleteFormSubmit(&$form_state) {
    db_truncate($this->getTableName())->execute();
  }

  public function dropForm(&$form_state) {
    $table_name = $this->getTableName();

    if (db_table_exists($table_name)) {
      $form = [];

      $form['actions'] = array('#type' => 'actions');
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Drop'),
      );
    }
    else {
      $form['status'] = [
        '#type' => 'item',
        '#title' => t('@source_name: Status', array('@source_name' => "Simple Importer")),
        '#markup' => "<br> Can't drop the datastore <br> The datastore has not been created.",
      ];
    }

    return $form;
  }*/

}
