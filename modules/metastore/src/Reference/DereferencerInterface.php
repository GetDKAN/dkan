<?php

namespace Drupal\metastore\Reference;

/**
 * Metastore dereferencer.
 */
interface DereferencerInterface {

  /**
   * Replaces value references in a dataset with with their actual values.
   *
   * @param object $metadata
   *   The json metadata object.
   *
   * @return mixed
   *   Modified json metadata object.
   */
  public function dereference(object $metadata);

}
