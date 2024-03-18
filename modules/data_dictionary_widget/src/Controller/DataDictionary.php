<?php

namespace Drupal\data_dictionary_widget\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Various operations for the Data Dictionary Widget.
 */
class DataDictionary extends ControllerBase {

  /**
   * Get a list of data dictionaries.
   */
  public static function getDataDictionaries() {
    $exsisting_identifiers = [];
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $node_storage
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'data')
      ->condition('field_data_type', 'data-dictionary', '=');
    $nodes_ids = $query->execute();
    $nodes = $node_storage->loadMultiple($nodes_ids);
    foreach ($nodes as $node) {
      $exsisting_identifiers[$node->id()] .= $node->uuid();
    }

    return $exsisting_identifiers;
  }

  /**
   * Setting ajax elements.
   */
  public static function setAjaxElements(array $dictionaryFields) {
    foreach ($dictionaryFields['data']['#rows'] as $row => $data) {
      $edit_button = $dictionaryFields['edit_buttons'][$row] ?? NULL;
      $edit_fields = $dictionaryFields['edit_fields'][$row] ?? NULL;
      // Setting the ajax fields if they exsist.
      if ($edit_button) {
        $dictionaryFields['data']['#rows'][$row] = array_merge($data, $edit_button);
        unset($dictionaryFields['edit_buttons'][$row]);
      }
      elseif ($edit_fields) {
        unset($dictionaryFields['data']['#rows'][$row]);
        $dictionaryFields['data']['#rows'][$row]['field_collection'] = $edit_fields;
        // Remove the buttons so they don't show up twice.
        unset($dictionaryFields['edit_fields'][$row]);
        ksort($dictionaryFields['data']['#rows']);
      }

    }
    return $dictionaryFields;
  }

  /**
   * Function to generate the description for the "Format" field.
   *
   * @param string $dataType
   *   Field data type.
   *
   * @return string
   *   Description information.
   */
  public static function generateFormatDescription($dataType) {
    $description = "<p>The format of the data in this field. Supported formats depend on the specified field type:</p>";

    if ($dataType === 'string') {
      $description .= "
        <ul>
          <li><b>default</b>: Any valid string.</li>
          <li><b>email</b>: A valid email address.</li>
          <li><b>uri</b>: A valid URI.</li>
          <li><b>binary</b>: A base64 encoded string representing binary data.</li>
          <li><b>uuid</b>: A string that is a UUID.</li>
        </ul>";
    }

    if ($dataType === 'date') {
      $description .= "
        <ul>
          <li><b>default</b>: An ISO8601 format string of YYYY-MM-DD.</li>
          <li><b>any</b>: Any parsable representation of a date. The implementing library can attept to parse the datetime via a range of strategies.</li>
          <li><b>other</b>: If your date values follow a collective but non-ISO8601 pattern, select this option and define the incoming format using the syntax of <a href='https://strftime.org/'>C / Python strftime</a>.
            For example, if your data had dates formatted as MM/DD/YYYY, you would enter %m/%d/%Y into the Other format field.</li>
        </ul>";
    }

    if ($dataType === 'integer') {
      $description .= "
        <ul>
          <li><b>default</b>: Any valid string.</li>
        </ul>";
    }

    if ($dataType === 'number') {
      $description .= "
        <ul>
          <li><b>default</b>: Any valid string.</li>
        </ul>";
    }

    return $description;
  }

  /**
   * Function to generate the options for the "Format" field.
   *
   * @param string $dataType
   *   Field data type.
   *
   * @return array
   *   List of format options.
   */
  public static function setFormatOptions($dataType = NULL) {

    switch ($dataType) {
      case 'string':
        $options = [
          'default' => 'default',
          'email' => 'email',
          'uri' => 'uri',
          'binary' => 'binary',
          'uuid' => 'uuid',
        ];
        break;

      case 'date':
        $options = [
          'default' => 'default',
          'any' => 'any',
          'other' => 'other',
        ];
        break;

      case 'integer':
        $options = [
          'default' => 'default',
        ];
        break;

      case 'number':
        $options = [
          'default' => 'default',
        ];
        break;

      default:
        $options = [
          'default' => 'default',
          'email' => 'email',
          'uri' => 'uri',
          'binary' => 'binary',
          'uuid' => 'uuid',
        ];
        break;
    }

    return $options;

  }

  /**
   * Cleaning the data up.
   */
  public static function processDataResults($data_results, $current_fields, $field_values, $op) {
    if (isset($current_fields)) {
      $data_results = $current_fields;
    }

    if (isset($field_values[0]['dictionary_fields']["field_collection"])) {
      $field_group = $field_values[0]['dictionary_fields']['field_collection']['group'];
      $field_format = $field_group["format"] == 'other' ? $field_group["format_other"] : $field_group["format"];

      $data_pre = [
        [
          "name" => $field_group["name"],
          "title" => $field_group["title"],
          "type" => $field_group["type"],
          "format" => $field_format,
          "description" => $field_group["description"],
        ],
      ];

      if (isset($data_pre) && $op === "add") {
        $data_results = isset($current_fields) ? array_merge($current_fields, $data_pre) : $data_pre;
      }
    }

    if (!isset($data_pre) && isset($data_results) && $current_fields) {
      $data_results = $current_fields;
    }

    return $data_results;
  }

  public static function editActions(){
    return [
      'format',
      'edit',
      'update',
      'abort',
      'delete',
    ];
  }

}
