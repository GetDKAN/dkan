<?php

namespace Drupal\metastore_content_type;

use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Class.
 */
class ConfigurationOverrider implements ConfigFactoryOverrideInterface {

  /**
   * Inherited.
   *
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
    /** @var \Drupal\schema\SchemaRetriever $schemaRetriever */
    $schemaRetriever = \Drupal::service('schema.schema_retriever');
    return $schemaRetriever->retrieve("dataset");
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function getCacheSuffix() {
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function getCacheableMetadata($name) {
  }

}
