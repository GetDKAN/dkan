<?php

namespace Dkan\DataDictionary;

// @TODO fix autoloaoding for libraries.
// include_once 'sites/all/libraries/tableschema-php/vendor/autoload.php';
use frictionlessdata\tableschema\Schema;
use frictionlessdata\tableschema\Table;
use frictionlessdata\tableschema\Exceptions\DataSourceException;
use frictionlessdata\tableschema\Exceptions\SchemaValidationError;
use frictionlessdata\tableschema\Exceptions\FieldValidationException;

/**
 *
 */
class TableSchemaDataDictionaryManager extends DataDictionaryManagerBase {

  /**
   * {@inheritdoc}
   *
   * @throws Exception().
   */
  public function initialize(Resource $resource) {
    parent::initialize($resource);

    $this->table = new Table($this->data, $this->schema);
    // Make sure table is valid.
    $this->table->valid();

    // Limit only to csv files with a single table.
    $this->validationReport->addTable(
      basename($this->resource->getFilePath()),
      $this->table->headers()
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function initValidationReport(Resource $resource) {
    $source = $resource->getFilePath();
    $schema = "table-schema";
    $this->validationReport = new ValidationReport($source, $schema);
  }

  /**
   * {@inheritdoc}
   */
  public function validate($step = 20) {
    $count = 0;

    while (TRUE) {
      list($output, $errors) = $this->validateChunk($step);

      $count = $count + count($output);

      if (count($output) < $step) {
        break;
      }
    }

    // Validation done.
    $this->validationReport->updateTableRowCount(
      basename($this->resource->getFilePath()),
      $count
    );

    //TODO update time.

    // Write validation record to the supporting backend.
    $this->validationReport->write($this->resource);
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
