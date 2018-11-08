<?php

namespace Dkan\DataDictionary;

/**
 *
 */
class MockValidationManager extends ValidationManagerBase {

  /**
   * {@inheritdoc}
   */
  public static function validateSchema($schema) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function schemaFormatterView($schema) {
    return array();
  }

}
