<?php

namespace Drupal\data_dictionary_widget\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Various operations for the Data Dictionary Widget.
 */
class DataDictionary extends ControllerBase {

public static function getDataDictionaries(){
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


public static function setAjaxElements(array $dictionaryFields){
  foreach ($dictionaryFields['data']['#rows'] as $row => $data) {
    $edit_button = $dictionaryFields['edit_buttons'][$row];
    $edit_fields = $dictionaryFields['edit_fields'][$row];
    $data_types = $dictionaryFields['data_types'][$row];
     //Setting the ajax fields if they exsist.
    if ($edit_button) {
      $dictionaryFields['data']['#rows'][$row] =  array_merge($data, $edit_button) ;
      unset($dictionaryFields['edit_buttons'][$row]);
    }else if ($edit_fields) {
      unset($dictionaryFields['data']['#rows'][$row]);
      $dictionaryFields['data']['#rows'][$row]['field_collection'] = $edit_fields;
      //Remove the buttons so they don't show up twice.
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
   *
   * @return string
   */
  public static function generateFormatDescription($dataType) {
    $description = "<p>Supported formats depend on the specified field type:</p>";
    
    if ($dataType === 'string') {
      $description .= "
        <ul>
          <li><b>default</b>: Any valid string.
          <li><b>email</b>: A valid email address.
          <li><b>uri</b>: A valid URI.
          <li><b>binary</b>: A base64 encoded string representing binary data.
          <li><b>uuid</b>: A string that is a UUID.
        </ul>";
    }

    if ($dataType === 'date') {
      $description .= "
        <ul>
          <li><b>default</b>: An ISO8601 format string of YYYY-MM-DD.
          <li><b>any</b>: Any parsable representation of a date. The implementing library can attept to parse the datetime via a range of strategies.
          <li><b>other</b>: The value can be parsed according to {PATTERN}, which MUST follow the date formatting syntax of C / Python strftime.
        </ul>";
    }

    if ($dataType === 'integer') {
      $description .= "
        <ul>
          <li><b>default</b>: Any valid string.
        </ul>";
    }

    if ($dataType === 'number') {
      $description .= "
        <ul>
          <li><b>default</b>: Any valid string.
        </ul>";
    }

    return $description;
  }

}