<?php

namespace Dkan\DataDictionary;

use frictionlessdata\tableschema\Schema;

/**
 * Class Info.
 *
 * Validator metadata.
 */
class TableSchemaDataDictionary extends DataDictionaryBase {

  /**
   * {@inheritdoc}
   */
  public static function validateSchema($schema) {
    return array_map(
      function ($validationError) {
        return $validationError->extraDetails;
      },
      Schema::validate($schema)
    );
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

      // Check the set of values for each field description against all table
      // headers.
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
              $column = $field->$header_key() ? t('True') : t('False');
              break;

            default:
              $column = (string) $field->$header_key();
              break;
          }

          $row[] = array('data' => $column, 'class' => array('dkan-datadict-item', 'dkan-datadict-' . $header_key));

        }

        $rows[] = $row;
      }

      return array(
        '#theme' => 'table',
        '#header' => $headers,
        '#rows' => $rows,
        '#attributes' => array('class' => array('dkan-datadict', 'json-schema')),
      );

    }
    else {
      // If the supplied value isn't valid JSON or it is valid JSON but
      // isn't a schema containing fields - simply output the raw text.
      return array('#markup' => $descriptor);
    }
  }

}
