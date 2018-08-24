<?php

namespace Drupal\interra_api;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Serialization\Json;

class Schema {

  public $config = FALSE;

  private $schemaDir = 'profiles/dkan2/schemas';
  private $interraConfigDir = 'profiles/dkan2/modules/custom/interra_api/config';

  function __construct($schema) {
    $this->schema = $schema;
    $this->config = $this->loadConfig();
  }

  public function getCurrentSchema() {
    return $this->schema;
  }

  private function loadConfig() {
    $file = $this->schemaDir . '/' . $this->schema . '/config.yml';
    return Yaml::decode(file_get_contents($file));
  }

  public function loadFullSchema() {
    $collections = $this->config['collections'];
    $references = $this->config['references'];
    $fullSchama = array();
    foreach ($collections as $collection) {
      $dereferencedSchema = Json::decode(file_get_contents($this->schemaDir . '/' . $this->schema . '/collections/' . $collection . '.json'));
      // Start HACK. TODO: Remove HACK.
      if ($collection == 'dataset') {
        $organization = array(
          'type' => 'object',
          '$ref' => 'organization.json'
        );
        $dereferencedSchema['properties']['organization'] = $organization;
      }
      // Done HACK.
      $fullSchema[$collection] = $this->dereference($references, $collection, $dereferencedSchema);
    }
    return $fullSchema;
  }

  public function loadPageSchema() {
    $file = $this->interraConfigDir . '/pageSchema.yml';
    return Yaml::decode(file_get_contents($file));
  }

  private function loadSchemaFile($collection) {
    return Json::decode(file_get_contents($this->schemaDir . '/' . $this->schema . '/collections/' . $collection . '.json'));
  }

  /**
   * Provides a deferenced version of a schema. The $references are set in
   * config. A little faster than a recursive array but could switch to that.
   * This is also only single dimensional which is probs OK.
   *
   * @param $references array
   *   Array with first level keys that are collections.
   * @param $collection string
   *   Collection to dereference.
   * @param $schema object
   *   The schema to load references for.
   *
   * @return object
   *   Derefenced schema.
   */
  private function dereference($references, $collection, $schema) {
    // No references are defined, so the schema is good to go.
    if (!isset($references[$collection])) {
      return $schema;
    }
    foreach ($references[$collection] as $reference) {
      $schema['properties'][$reference] = $this->loadSchemaFile($reference);
    }
    return $schema;
  }

}
