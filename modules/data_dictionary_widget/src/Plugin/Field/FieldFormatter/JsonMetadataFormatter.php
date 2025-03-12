<?php

namespace Drupal\data_dictionary_widget\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'json metadata' formatter.
 *
 * @FieldFormatter(
 *   id = "json_metadata",
 *   label = @Translation("JSON Metadata"),
 *   field_types = {
 *     "string_long",
 *   }
 * )
 */
class JsonMetadataFormatter extends FormatterBase {

  /**
   * @inheritDoc
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $type = $items->getEntity()->get('field_data_type')->value;
    if ($type != 'data-dictionary') {
      return [['#markup' => '']];
    }
    $nid = $items->getEntity()->id();
    $metadata = json_decode($items[0]->value, TRUE);
    $indexes = $metadata['data']['indexes'] ?? [];
    $links = [];

    foreach ($indexes as $delta => $index) {
      $url = new Url('entity.node.edit_form', ['node' => $nid], ['query' => ['index' => $delta]]);
      $links[] = Link::fromTextAndUrl($index['description'], $url)->toString();
    }

    return [['#markup' => implode(', ', $links)]];
  }

}
