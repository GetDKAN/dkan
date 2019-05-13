<?php

namespace Drupal\dkan_data;

use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 *
 */
class ConfigurationOverrider implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritDoc}.
   */
  public function loadOverrides($names) {
    if (in_array("core.entity_form_display.node.data.default", $names)) {

      $schema = $this->getSchema();
      return [
        "core.entity_form_display.node.data.default" =>
        [
          'content' =>
          [
            'field_json_metadata' =>
            [
              'settings' =>
              [
                'json_form' => $schema,
              ],
            ],
          ],
        ],
      ];
    }
    return [];
  }

  /**
   * Get Schema.
   *
   * @return string|null
   *   Schema.
   */
  protected function getSchema() {
    /** @var \Drupal\dkan_schema\SchemaRetriever $schemaRetriever */
    $schemaRetriever = \Drupal::service('dkan_schema.schema_retriever');
    return $schemaRetriever->retrieve("dataset");
  }

  /**
   * {@inheritDoc}.
   */
  public function getCacheSuffix() {
  }

  /**
   * {@inheritDoc}.
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
  }

  /**
   * {@inheritDoc}.
   */
  public function getCacheableMetadata($name) {
  }

}
