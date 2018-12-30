<?php

namespace Dkan\DataDictionary;

/**
 * Class Info.
 *
 * Validator metadata.
 */
class CsvDataDictionary extends DataDictionaryBase {

  /**
   * {@inheritdoc}
   */
  public static function validateSchema($schema) {
    // @TODO
    return array();
  }

  /**
   * {@inheritdoc}
   *
   * @param mixed $descriptor
   *   Schema source, can anything that file_get_content() reads.
   *
   * @param mixed $display_type
   *   Type of user facing display.
   *
   * @throws
   */
  public static function schemaFormatterView($discriptor, $display_type) {
    // @TODO
    return array('#markup' => "TODO");
  }

}
