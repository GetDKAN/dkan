<?php

namespace Drupal\datastore\DataDictionary;

/**
 * Converts frictionless date formats to local database date formats.
 *
 * +------------------------------------------------------------------------------------------------------------+
 * | Date Format Mappings                                                                                       |
 * |------------------------------------------------------------------------------------------------------------|
 * | Description                                                                      | Frictionless | Local DB |
 * |------------------------------------------------------------------------------------------------------------|
 * | Abbreviated weekday name (Sun to Sat)                                            | %a           | ?        |
 * | Abbreviated month name (Jan to Dec)                                              | %b           | ?        |
 * | Numeric month name (0 to 12)                                                     | %m           | ?        |
 * | Day of the month as a numeric value (01 to 31)                                   | %d           | ?        |
 * | Day of the month as a numeric value (0 to 31)                                    | %-d          | ?        |
 * | Microseconds (000000 to 999999)                                                  | %f           | ?        |
 * | Hour (00 to 23)                                                                  | %H           | ?        |
 * | Hour (00 to 12)                                                                  | %I           | ?        |
 * | Minutes (00 to 59)                                                               | %M or %-M    | ?        |
 * | Day of the year (001 to 366)                                                     | %j or %-j    | ?        |
 * | Hour (0 to 23)                                                                   | %-H          | ?        |
 * | Hour (1 to 12)                                                                   | %-I          | ?        |
 * | Month name in full (January to December)                                         | %B           | ?        |
 * | Month name as a numeric value (01 to 12)                                         | %-m          | ?        |
 * | AM or PM                                                                         | %p           | ?        |
 * | Seconds (00 to 59)                                                               | %S or %-S    | ?        |
 * | Week where Sunday is the first day of the week (00 to 53)                        | %U           | ?        |
 * | Week where Monday is the first day of the week (00 to 53)                        | %W           | ?        |
 * | Weekday name in full (Sunday to Saturday)                                        | %A           | ?        |
 * | Day of the week where Sunday=0 and Saturday=6                                    | %w           | ?        |
 * | Year as a numeric, 4-digit value                                                 | %Y           | ?        |
 * | Year as a numeric, 2-digit value                                                 | %y           | ?        |
 * | A literal '%' character                                                          | %%           | ?        |
 * | Locale’s appropriate date and time representation (e.g. Sun Sep 8 07:06:05 2013) | %c           | ?        |
 * | Locale’s appropriate date representation (e.g. 09/08/13)                         | %x           | ?        |
 * | Locale’s appropriate time representation (e.g. 07:06:05)                         | %X           | ?        |
 * | UTC offset in the form ±HHMM[SS[.ffffff]] (e.g. +0000)                           | %z           | ?        |
 * | Time zone name (empty string if the object is naive) (e.g. UTC)                  | %Z           | ?        |
 * +------------------------------------------------------------------------------------------------------------+
 */
abstract class FrictionlessDateFormatConverterBase implements FrictionlessDateFormatConverterInterface {

  /**
   * Get Abstract Syntax Tree for the date format converter.
   *
   * @return array
   *   Date conversion AST.
   */
  abstract protected function getDateConversionAST(): array;

  /**
   * Convert frictionless date format string to its local DB equivalent.
   *
   * @param string $frictionless_date_fromat
   *   Frictionless date format.
   *
   * @return string
   *   Local DB date format.
   */
  public function convert(string $frictionless_date_format): string {
    // Initialize our AST reference and local DB output format string.
    $ast = $this->getDateConversionAST();
    $ast_ptr = &$ast;
    $db_date_format = '';

    // Traverse the AST
    foreach (str_split($frictionless_date_format) as $char) {
      // If the current character doesn't exist in this level of the AST, we
      // have no path to tokenizing it; therefore, we want to go back to the top
      // level of the AST and write the current character to the output format.
      if (!array_key_exists($char, $ast_ptr)) {
        $db_date_format .= $char;
        $ast_ptr = &$ast;
      }
      // Otherwise, we have a path forward...
      else {
        // If the current character is not an end node in the AST, continue down
        // the AST path.
        if (is_array($ast_ptr[$char])) {
          $ast_ptr = &$ast_ptr[$char];
        }
        // If the current character is an end node, write the content of the end
        // node to the output format, and go back to the top level of the AST.
        else {
          $db_date_format .= $ast_ptr[$char];
          $ast_ptr = &$ast;
        }
      }
    }

    return $db_date_format;
  }
}
