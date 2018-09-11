<?php

namespace Drupal\dkan_schema;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Serialization\Json;

class Schema {

  public $config = FALSE;

  private $dir = '';

  private $schemaDir = 'schemas';

  function __construct($schema = '') {
    $this->schema = $schema ? $schema : dkan_schema_current_schema();
    $this->config = $this->loadConfig();
  }

  private function dir() {
    if ($this->dir) {
      return $this->dir;
    }
    else {
      $currentDir = __DIR__;
      $num = strlen('modules/custom/dkan_schema/src');
      $basedDir = substr($currentDir, 0, -1 * abs($num));
      $this->dir = $basedDir . $this->schemaDir . '/' . $this->schema;
      return $this->dir;
    }
  }

  public function getCurrentSchema() {
    return $this->schema;
  }

  private function loadConfig() {
    $currentDir = __DIR__;
    $num = strlen('modules/custom/dkan_schema/src');
    $basedDir = substr($currentDir, 0, -1 * abs($num));
    $file = $this->dir() . '/config.yml';
    return Yaml::decode(file_get_contents($file));
  }

  public function getActiveCollections() {
    return $this->config['collections'];
  }

  public function loadSchema($collection) {
    return $this->loadSchemaFile($collection);
  }

  public function prepareForForm($collection) {
    $schema = json_decode(file_get_contents($this->dir() . '/collections/' . $collection . '.json'));
    $references = $this->config['references'];
    // Currently we want to use strings for references. This will get fixed
    // later in the form definition itself.
    foreach ($references[$collection] as $reference => $entity) {
      $schema->properties->{$reference}->type = 'string';
      unset($schema->properties->{$reference}->items);
      unset($schema->properties->{$reference}->properties);
    }
    return $schema;
  }

  private function loadSchemaFile($collection) {
    return Json::decode(file_get_contents($this->dir() . '/collections/' . $collection . '.json'));
  }

  public function loadFullSchema() {
    $collections = $this->getActiveCollections();
    $references = $this->config['references'];
    $fullSchama = array();
    foreach ($collections as $collection) {
      $dereferencedSchema = $this->loadSchema($collection);
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
