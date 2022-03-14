<?php

namespace Drupal\datastore\Service;

use Drupal\datastore\DataDictionary\FrictionlessDateFormatConverterBase;

/**
 * Converts frictionless date formats to MySQL according the following table.
 *
 * +------------------------------------------------------------------------------------------------------------+
 * | Date Format Mappings                                                                                       |
 * |------------------------------------------------------------------------------------------------------------|
 * | Description                                                                      | Frictionless | MySQL    |
 * |------------------------------------------------------------------------------------------------------------|
 * | Abbreviated weekday name (Sun to Sat)                                            | %a           | %a       |
 * | Abbreviated month name (Jan to Dec)                                              | %b           | %b       |
 * | Numeric month name (0 to 12)                                                     | %m           | %c       |
 * | Day of the month as a numeric value (01 to 31)                                   | %d           | %d       |
 * | Day of the month as a numeric value (0 to 31)                                    | %-d          | %e       |
 * | Microseconds (000000 to 999999)                                                  | %f           | %f       |
 * | Hour (00 to 23)                                                                  | %H           | %H       |
 * | Hour (00 to 12)                                                                  | %I           | %I       |
 * | Minutes (00 to 59)                                                               | %M or %-M    | %i       |
 * | Day of the year (001 to 366)                                                     | %j or %-j    | %j       |
 * | Hour (0 to 23)                                                                   | %-H          | %k       |
 * | Hour (1 to 12)                                                                   | %-I          | %l       |
 * | Month name in full (January to December)                                         | %B           | %M       |
 * | Month name as a numeric value (01 to 12)                                         | %-m          | %m       |
 * | AM or PM                                                                         | %p           | %p       |
 * | Seconds (00 to 59)                                                               | %S or %-S    | %S or %s |
 * | Week where Sunday is the first day of the week (00 to 53)                        | %U           | %U       |
 * | Week where Monday is the first day of the week (00 to 53)                        | %W           | %u       |
 * | Weekday name in full (Sunday to Saturday)                                        | %A           | %W       |
 * | Day of the week where Sunday=0 and Saturday=6                                    | %w           | %w       |
 * | Year as a numeric, 4-digit value                                                 | %Y           | %Y       |
 * | Year as a numeric, 2-digit value                                                 | %y           | %y       |
 * | A literal '%' character                                                          | %%           | %%       |
 * |------------------------------------------------------------------------------------------------------------|
 * | Exclusive to MySQL (not supported)                                                                         |
 * |------------------------------------------------------------------------------------------------------------|
 * | Day of the month as a numeric value, followed by suffix (1st, 2nd, 3rd, ...)     |              | %D       |
 * | Time in 12 hour AM or PM format (hh:mm:ss AM/PM)                                 |              | %r       |
 * | Time in 24 hour format (hh:mm:ss)                                                |              | %T       |
 * | Week where Sunday is the first day of the week (01 to 53). Used with %X          |              | %V       |
 * | Week where Monday is the first day of the week (01 to 53). Used with %X          |              | %v       |
 * | Year for the week where Sunday is the first day of the week. Used with %V        |              | %X       |
 * | Year for the week where Monday is the first day of the week. Used with %V        |              | %x       |
 * |------------------------------------------------------------------------------------------------------------|
 * | Exclusive to Frictionless (not supported)                                                                  |
 * |------------------------------------------------------------------------------------------------------------|
 * | Locale’s appropriate date and time representation (e.g. Sun Sep 8 07:06:05 2013) | %c           |          |
 * | Locale’s appropriate date representation (e.g. 09/08/13)                         | %x           |          |
 * | Locale’s appropriate time representation (e.g. 07:06:05)                         | %X           |          |
 * | UTC offset in the form ±HHMM[SS[.ffffff]] (e.g. +0000)                           | %z           |          |
 * | Time zone name (empty string if the object is naive) (e.g. UTC)                  | %Z           |          |
 * +------------------------------------------------------------------------------------------------------------+
 */
class MySQLConverter extends FrictionlessDateFormatConverterBase {

  /**
   * Get Abstract Syntax Tree for the date format converter.
   *
   * @return array
   *   Date conversion AST.
   */
  protected function getDateConversionAST(): array {
    return [
      '%' => [
        'a' => '%a', // Abbreviated weekday name (Sun to Sat)
        'A' => '%W', // Weekday name in full (Sunday to Saturday)
        'b' => '%b', // Abbreviated month name (Jan to Dec)
        'B' => '%M', // Month name in full (January to December)
        'd' => '%d', // Day of the month as a numeric value (01 to 31)
        'f' => '%f', // Microseconds (000000 to 999999)
        'H' => '%H', // Hour (00 to 23)
        'I' => '%I', // Hour (00 to 12)
        'm' => '%c', // Numeric month name (0 to 12)
        'M' => '%i', // Minutes (00 to 59)
        'p' => '%p', // AM or PM
        'S' => '%s', // Seconds (00 to 59)
        'U' => '%U', // Week where Sunday is the first day of the week (00 to 53)
        'W' => '%u', // Week where Monday is the first day of the week (00 to 53)
        'y' => '%y', // Year as a numeric, 2-digit value
        'Y' => '%Y', // Year as a numeric, 4-digit value
        '%' => '%%', // A literal '%' character
        '-' => [
          'd' => '%e', // Day of the month as a numeric value (0 to 31)
          'j' => '%j', // Day of the year (001 to 366)
          'H' => '%k', // Hour (0 to 23)
          'I' => '%l', // Hour (1 to 12)
          'm' => '%m', // Month name as a numeric value (01 to 12)
          'M' => '%i', // Minutes (00 to 59)
          'S' => '%s', // Seconds (00 to 59)
        ],
      ],
    ];
  }

}
