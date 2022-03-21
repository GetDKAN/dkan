<?php

namespace Drupal\datastore\DataDictionary;

/**
 * Converts frictionless date formats to local database date formats.
 */
interface FrictionlessDateFormatConverterInterface {

  /**
   * Convert the supplied frictionless date format string.
   *
   * @param string $frictionless_date_format
   *   Frictionless date format.
   *
   * @return string
   *   Local DB date format.
   */
  public function convert(string $frictionless_date_format): string;

}
