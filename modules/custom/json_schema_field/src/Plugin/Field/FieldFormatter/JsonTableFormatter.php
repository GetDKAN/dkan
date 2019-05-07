<?php

namespace Drupal\json_schema_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_example_simple_text' formatter.
 *
 * @FieldFormatter(
 *   id = "json_table",
 *   module = "json_schema_field",
 *   label = @Translation("Metadata table"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class JsonTableFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $metadata = (array) json_decode($item->value);

      $elements[$delta] = [
        '#type' => 'table',
      ];
      foreach ($metadata as $key => $value) {
        $elements[$delta]['#rows'][] = ['key' => $key, 'value' => (string) $value];
      }
    }
    return $elements;
  }

}
