<?php

namespace Drupal\datastore;

interface SchemaTranslatorInterface {

  /**
   * Translate the supplied frictionless schema into a Drupal database schema.
   *
   * @param array $frictionless_schema
   *   Frictionless Schema array.
   *
   * @return array
   *   Drupal database Schema.
   */
  public function translate(array $frictionless_schema);

}
