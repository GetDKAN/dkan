<?php

namespace Dkan\DataDictionary;

// @TODO fix autoloaoding for libraries.
// include_once 'sites/all/libraries/tableschema-php/vendor/autoload.php';
use frictionlessdata\tableschema\Schema;
use frictionlessdata\tableschema\Table;
use frictionlessdata\tableschema\Exceptions\DataSourceException;

/**
 *
 */
class TableSchemaValidationManager extends ValidationManagerBase {
  private $errors = array();

  /**
   * Get Validator Label.
   */
  public static function validateSchema($schema) {
    // TODO maybe add specific exception classes?
    return array_map(
      function ($validationError) {
        return $validationError->extraDetails;
      },
    Schema::validate($schema));
  }

  /**
   * @throws Exception
   */
  public function postInit() {
    parent::postInit();

    $this->table = new Table($this->data, $this->schema);

    // Reset state.
    $this->errors = array();
  }

  /**
   * Get Validator Label.
   */
  public function validate($step = 20) {
    while (TRUE) {
      list($output, $errors) = $this->validateChunk($step);
      if (count($output) < $step) {
        break;
      }
    }
  }

  /**
   * Get Validator Label.
   */
  public function validateChunk($chunk_size = 20) {
    $rows = array();
    $errors = array();

    while ($this->table->valid()) {
      try {
        $rows[] = $this->table->current();
      }
      catch (DataSourceException $exception) {
        $errors[] = array(
          "code" => "",
          "row-number" => $this->table->key(),
          "message" => $exception->getMessage(),
        );
      }

      $this->table->next();
      if (count($rows) == $chunk_size) {
        break;
      }
    }

    // Save the errors.
    $this->errors = array_merge($this->errors, $errors);
    return array($rows, $errors);
  }

  /**
   * {@inheritdoc}
   */
  public static function render($schema) {
  }

}
