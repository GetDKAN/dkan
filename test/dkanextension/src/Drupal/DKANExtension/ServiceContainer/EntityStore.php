<?php
namespace Drupal\DKANExtension\ServiceContainer;

use Behat\Behat\Context\Context;

/**
 * Defines application features from the specific context.
 */
class EntityStore implements StoreInterface {
  // Store entities as EntityMetadataWrappers for easy property inspection.
  protected $entities = array();
  protected $names = array();

  function store($entity_type, $bundle, $entity_id, $entity, $name = false) {
    $entities = &$this->entities;
    $names = &$this->names;
    if (!isset($entities[$entity_type])) {
      $entities[$entity_type] = array();
    }
    if (!isset($entities[$entity_type][$bundle])) {
      $entities[$entity_type][$bundle] = array();
    }
    $entities[$entity_type][$bundle][$entity_id] = $entity;

    if ($name !== false) {
      if (!isset($names[$name])) {
        // This should point to the same objects if they get updated.
        $names[$name] =  &$entities[$entity_type][$bundle][$entity_id];
      }
      else {
        throw new \Exception("Named Entity with name '$name' already exists.");
      }
    }
  }

  function retrieve($entity_type = false, $bundle = false, $entity_id = false) {
    $entities = &$this->entities;
    if ($entity_type !== FALSE && !isset($entities[$entity_type])) {
      return FALSE;
    }
    if ($bundle !== FALSE && !isset($entities[$entity_type][$bundle])) {
      return FALSE;
    }
    if ($entity_id !== FALSE && !isset($entities[$entity_type][$bundle][$entity_id])) {
      return FALSE;
    }
    if ($entity_type === FALSE) {
      return $entities;
    }
    if ($bundle === FALSE) {
      return $entities[$entity_type];
    }
    if ($entity_id === FALSE) {
      return $entities[$entity_type][$bundle];
    }
    return $entities[$entity_type][$bundle][$entity_id];
  }

  function delete($entity_type = false, $bundle = false, $entity_id = false) {
    $entities = &$this->entities;
    if ($entity_type !== FALSE && !isset($entities[$entity_type])) {
      return FALSE;
    }
    if ($bundle !== FALSE && !isset($entities[$entity_type][$bundle])) {
      return FALSE;
    }
    if ($entity_id !== FALSE && !isset($entities[$entity_type][$bundle][$entity_id])) {
      return FALSE;
    }
    if ($entity_type === FALSE) {
      unset($entities);
      return true;
    }
    if ($bundle === FALSE) {
      unset($entities[$entity_type]);
      return true;
    }
    if ($entity_id === FALSE) {
      unset($entities[$entity_type][$bundle]);
      return true;
    }
    unset($entities[$entity_type][$bundle][$entity_id]);
    return true;
  }

  function retrieve_by_name($name) {
    if (isset($this->names[$name])) {
      return $this->names[$name];
    }
    return false;
  }

  function names_flush() {
    $this->names = array();
  }

  function flush() {
    $this->entities = array();
    $this->names_flush();
  }
}
