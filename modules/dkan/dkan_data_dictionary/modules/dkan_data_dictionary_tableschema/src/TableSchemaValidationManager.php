<?php

namespace Dkan\DataDictionary;

// @TODO fix autoloaoding for libraries.
// include_once 'sites/all/libraries/tableschema-php/vendor/autoload.php';
use frictionlessdata\tableschema\Schema;
use frictionlessdata\tableschema\Table;
use frictionlessdata\tableschema\Exceptions\SchemaLoadException;
use frictionlessdata\tableschema\Exceptions\SchemaValidationFailedException;
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
   *
   * @param mixed $descriptor
   *   Schema source, can anything that file_get_content() reads.
   *
   * @param mixed $display_type
   *   Type of user facing display.
   */
  public static function schemaFormatterView($discriptor, $display_type) {

    try {
      $schema = new Schema($discriptor);
    }
    catch (\Exception $execption) {
      // Can be a empty schema to a invalide schema, display nothing.
      return array();
    }

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

      // foreach ($schema->fields() as $field) {
        // $item = (array) $field;
        // $new_keys = array_keys($item);
        // $headers = array_merge($headers, array_diff($new_keys, $headers));
      // }

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

}
