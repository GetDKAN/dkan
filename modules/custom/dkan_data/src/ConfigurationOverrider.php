<?php

namespace Drupal\dkan_data;

use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\dkan_schema\SchemaRetriever;

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
   *
   */
  protected function getSchema() {
    // @codeCoverageIgnoreStart
    return (new SchemaRetriever())->retrieve("dataset");
    // @codeCoverageIgnoreEnd
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
