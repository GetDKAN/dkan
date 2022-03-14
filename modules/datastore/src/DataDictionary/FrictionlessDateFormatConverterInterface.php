<?php

namespace Drupal\datastore\DataDictionary\DateFormat;

/**
 * Converts frictionless date formats to local database date formats.
 */
interface FrictionlessConverterInterface {

  /**
   * Convert the supplied frictionless date format string.
   *
   * @param string $frictionless_date_fromat
   *   Frictionless date format.
   *
   * @return string
   *   Local DB date format.
   */
  public function convert(string $frictionless_date_format): string;

}
