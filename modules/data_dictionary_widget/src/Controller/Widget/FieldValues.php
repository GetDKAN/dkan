<?php

namespace Drupal\data_dictionary_widget\Controller\Widget;

use Drupal\Core\Controller\ControllerBase;

/**
 * Various operations for creating Data Dictionary Widget fields.
 */
class FieldValues extends ControllerBase {

  /**
   * Return updated field values after edit.
   */
  public static function updateValues($field_index, $update_values, $current_fields) {
    $format = $update_values['field_json_metadata'][0]['dictionary_fields']['data'][$field_index]['field_collection']['format'];
    $format_other = $update_values['field_json_metadata'][0]['dictionary_fields']['data'][$field_index]['field_collection']['format_other'];
    $name = $update_values['field_json_metadata'][0]['dictionary_fields']['data'][$field_index]['field_collection']['name'];

    return [
      'name' => $name,
      'title' => $update_values['field_json_metadata'][0]['dictionary_fields']['data'][$field_index]['field_collection']['title'],
      'type' => $update_values['field_json_metadata'][0]['dictionary_fields']['data'][$field_index]['field_collection']['type'],
      'format' => $format == 'other' ? $format_other : $format,
      'format_other' => $format_other,
      'description' => $update_values['field_json_metadata'][0]['dictionary_fields']['data'][$field_index]['field_collection']['description'],
    ];
  }

  /**
   * Retrun information about the string field option.
   */
  public static function returnStringInfo($type) {
    if ($type == 'options') {
      return [
        'default' => 'default',
        'email' => 'email',
        'uri' => 'uri',
        'binary' => 'binary',
        'uuid' => 'uuid',
      ];
    }
    elseif ($type == 'description') {
      return "
        <ul>
          <li><b>default</b>: Any valid string.</li>
          <li><b>email</b>: A valid email address.</li>
          <li><b>uri</b>: A valid URI.</li>
          <li><b>binary</b>: A base64 encoded string representing binary data.</li>
          <li><b>uuid</b>: A string that is a UUID.</li>
        </ul>";
    }

  }

  /**
   * Retrun information about the date field option.
   */
  public static function returnDateInfo($type) {
    if ($type == 'options') {
      return [
        'default' => 'default',
        'any' => 'any',
        'other' => 'other',
      ];
    }
    elseif ($type == 'description') {
      return "
        <ul>
          <li><b>default</b>: An ISO8601 format string of YYYY-MM-DD.</li>
          <li><b>any</b>: Any parsable representation of a date. The implementing library can attept to parse the datetime via a range of strategies.</li>
          <li><b>other</b>: If your date values follow a collective but non-ISO8601 pattern, select this option and define the incoming format using the syntax of <a href='https://strftime.org/'>C / Python strftime</a>.
            For example, if your data had dates formatted as MM/DD/YYYY, you would enter %m/%d/%Y into the Other format field.</li>
        </ul>";
    }
  }

  /**
   * Retrun information about the integer field option.
   */
  public static function returnIntegerInfo($type) {
    if ($type == 'options') {
      return [
        'default' => 'default',
      ];
    }
    elseif ($type == 'description') {
      return "
        <ul>
          <li><b>default</b>: Any valid string.</li>
        </ul>";
    }
  }

  /**
   * Retrun information about the number field option.
   */
  public static function returnNumberInfo($type) {
    if ($type == 'options') {
      return [
        'default' => 'default',
      ];
    }
    elseif ($type == 'description') {
      return "
        <ul>
          <li><b>default</b>: Any valid string.</li>
        </ul>";
    }
  }

}
