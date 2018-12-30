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

  protected $validationReport;

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
   * Get Validator Label.
   *
   * @throws Exception
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
   *
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

  /**
   * {@inheritdoc}
   *
   * @param mixed $descriptor
   *   Schema source, can anything that file_get_content() reads.
   *
   * @param mixed $display_type
   *   Type of user facing display.
   *
   * @throws
   */
  public static function schemaFormatterView($discriptor, $display_type) {
    $schema = new Schema($discriptor);

    if ($display_type == 'text_schema_table') {
      $headers = array();
      $rows = array();

      // Parse the schema array and build the table.
      $headers_fields = array(
        'name' => 'Name',
        'title' => 'Title',
        'type' => 'Type',
        'description' => 'Description',
        'required' => 'Required',
      );

      // Build our collection of unique table headers.
      $fields = $schema->fields();

      // Format headers.
      foreach ($headers_fields as $key => $value) {
        $headers[$key] = array('data' => t(ucfirst($value)), 'class' => array('json-schema-item', 'json-schema-' . $key));
      }

      // Foreach ($schema->fields() as $field) {
      // $item = (array) $field;
      // $new_keys = array_keys($item);
      // $headers = array_merge($headers, array_diff($new_keys, $headers));
      // }.
      // Check the set of values for each field description against all table headers.
      foreach ($fields as $field) {
        $row = array();

        foreach ($headers as $header_key => $header_value) {
          $column = '';

          // Compare all properties for the current field definition ($item)
          // against each table header ($headers).
          // Default behavior:
          // If the $item contains a value for the $header return it.
          // If the $item does not contain a value for the $header return ''.
          // Special cases can be defined using the switch statement below.
          switch ($header_key) {
            case 'constraints':
              if (!empty($field->constraints())) {
                $constraints = $field->constraints();

                $column = implode(', ', array_map(
                  function ($v, $k) {
                    return sprintf("%s = %s", $k, $v);
                  },
                  $constraints,
                  array_keys($constraints)
                ));
              }
              else {
                $column = '';
              }
              break;

            case 'required':
              $column = $field->$header_key() ? 'true' : 'false';
              break;

            default:
              $column = (string) $field->$header_key();
              break;
          }

          $row[] = array('data' => $column, 'class' => array('json-schema-item', 'json-schema-' . $header_key));

        }

        $rows[] = $row;

      }

      return array(
        '#theme' => 'table',
        '#header' => $headers,
        '#rows' => $rows,
        '#attributes' => array('class' => array('json-schema')),
      );

    }
    else {
      // If the supplied value isn't valid JSON or it is valid JSON but
      // isn't a schema containing fields - simply output the raw text.
      return array('#markup' => $item['value']);
    }
  }

  /**
   *
   */
  public function getValidationReport() {
    return $this->validationReport;
  }
}
