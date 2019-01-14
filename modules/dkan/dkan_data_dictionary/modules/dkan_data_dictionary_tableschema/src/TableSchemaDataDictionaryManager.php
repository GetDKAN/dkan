<?php

namespace Dkan\DataDictionary;

use frictionlessdata\tableschema\Schema;
use frictionlessdata\tableschema\Table;
use frictionlessdata\tableschema\Exceptions\DataSourceException;
use frictionlessdata\tableschema\Exceptions\SchemaValidationError;
use frictionlessdata\tableschema\Exceptions\FieldValidationException;

class TableSchemaDataDictionaryManager extends DataDictionaryManagerBase {

  protected $table;

  /**
   * {@inheritdoc}
   */
  public function __construct(DataDictionaryBase $dataDictionary, Resource $resource) {
    parent::__construct($dataDictionary, $resource);

    $this->table = new Table($this->data, $this->schema);
    // Make sure table is valid.
    $this->table->valid();

  }

  /**
   * {@inheritdoc}
   */
  public function preValidate() {
    parent::preValidate();
    // Setup the validation report object.
    // Limit only to csv files with a single table.
    $this->validationReport->addTable(
      basename($this->resource->getFilePath()),
      $this->resource->getFilePath(),
      $this->getSchema(),
      NULL,
      $this->table->headers()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validateChunk($chunk_size = 20) {
    $rows = array();
    $errors = array();

    while ($this->table->valid()) {
      try {
        $rows[] = $this->table->current();
      }
      catch (SchemaValidationError $exception) {
        // TODO support fixeld exception as well.
        $errors[] = array(
          'row-number' => $this->table->key(),
          // TODO fix this?
          'code' =>  -1,
          'message' => $exception->getMessage(),
        );
        $this->validationReport->logTableError(
          file_uri_target($this->resource->getFilePath()),
          $this->table->key(),
          // TODO fix this?
          -1,
          $exception->getMessage()
        );
      }
      catch (FieldValidationException $exception) {
        foreach($exception->validationErrors as $error) {
          $errors[] = array(
            'row-number' => $this->table->key(),
            'code' =>  $error->code,
            'message' => "{$error->extraDetails['field']}: {$error->extraDetails['error']}",
          );

          $this->validationReport->logTableError(
            basename($this->resource->getFilePath()),
            $this->table->key(),
            $error->code,
            "{$error->extraDetails['field']}: {$error->extraDetails['error']}"
          );
        }
      }

      $this->table->next();

      if (count($rows) == $chunk_size) {
        break;
      }
    }

    return array($rows, $errors);
  }
}
