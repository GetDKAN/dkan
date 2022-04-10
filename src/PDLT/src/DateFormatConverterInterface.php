<?php

namespace PDLT;

/**
 * Converts date formats using the supplied parser and compiler services.
 */
interface ConverterInterface {

  /**
   * Convert the supplied date format string.
   *
   * @param string $input_format
   *   Input date format.
   *
   * @return string
   *   Output date format.
   */
  public function convert(string $input_format): string;

}
