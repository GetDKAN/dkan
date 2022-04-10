<?php

namespace PDLT\CompilationMap;

use PDLT\CompilationMapInterface;

/**
 * MySQL compilation map.
 *
 * MySQL only supports a subset of Frictionless' date format directives. If an
 * unsupported directive is encountered, an UnsupportedDirectiveException will
 * be thrown by the compiler.
 *
 * Date Format Mappings:
 *
 * | **Description**                            | **Frictionless** | **MySQL** |
 * |--------------------------------------------|------------------|-----------|
 * | Abbreviated weekday name (Sun to Sat)      | %a               | %a        |
 * | Abbreviated month name (Jan to Dec)        | %b               | %b        |
 * | Numeric month name (0 to 12)               | %m               | %c        |
 * | Numeric day of the month (01 to 31)        | %d               | %d        |
 * | Numeric day of the month (0 to 31)         | %-d              | %e        |
 * | Microseconds (000000 to 999999)            | %f               | %f        |
 * | Hour (00 to 23)                            | %H               | %H        |
 * | Hour (00 to 12)                            | %I               | %I        |
 * | Minutes (00 to 59)                         | %M or %-M        | %i        |
 * | Day of the year (001 to 366)               | %j or %-j        | %j        |
 * | Hour (0 to 23)                             | %-H              | %k        |
 * | Hour (1 to 12)                             | %-I              | %l        |
 * | Month name in full (January to December)   | %B               | %M        |
 * | Month name as a numeric value (01 to 12)   | %-m              | %m        |
 * | AM or PM                                   | %p               | %p        |
 * | Seconds (00 to 59)                         | %S or %-S        | %S or %s  |
 * | Week where Sunday is first day (00 to 53)  | %U               | %U        |
 * | Week where Monday is first day (00 to 53)  | %W               | %u        |
 * | Weekday name in full (Sunday to Saturday)  | %A               | %W        |
 * | Numeric day of week where Sun=0 and Sat=6  | %w               | %w        |
 * | Year as a numeric, 4-digit value           | %Y               | %Y        |
 * | Year as a numeric, 2-digit value           | %y               | %y        |
 * | A literal '%' character                    | %%               | %%        |
 *
 * Not Supported:
 *
 * | **Exclusive to Frictionless**              | **Frictionless** | **MySQL** |
 * |--------------------------------------------|------------------|-----------|
 * | Locale date/time (e.g. 09/08/13 07:06:05)  | %c               |           |
 * | Locale’s date (e.g. 09/08/13)              | %x               |           |
 * | Locale’s time (e.g. 07:06:05)              | %X               |           |
 * | UTC offset ±HHMM[SS[.ffffff]] (e.g. +0000) | %z               |           |
 * | Time zone name (e.g. UTC)                  | %Z               |           |
 *
 * | **Exclusive to MySQL**                     | **Frictionless** | **MySQL** |
 * |--------------------------------------------|------------------|-----------|
 * | Numeric day of month with suffix (3rd)     |                  | %D        |
 * | Time in 12 hour format (hh:mm:ss AM/PM)    |                  | %r        |
 * | Time in 24 hour format (hh:mm:ss)          |                  | %T        |
 * | Week where Sunday is first day (01 to 53)  |                  | %V        |
 * | Week where Monday is first day (01 to 53)  |                  | %v        |
 * | Year for week where Sunday is first day    |                  | %X        |
 * | Year for week where Monday is first day    |                  | %x        |
 */
class MySQL extends \ArrayObject implements CompilationMapInterface {

  /**
   * {@inheritdoc}
   */
  protected $storage = [
    // Abbreviated weekday name (Sun to Sat).
    '%a' => '%a',
    // Weekday name in full (Sunday to Saturday).
    '%A' => '%W',
    // Abbreviated month name (Jan to Dec).
    '%b' => '%b',
    // Month name in full (January to December).
    '%B' => '%M',
    // Day of the month as a numeric value (01 to 31).
    '%d' => '%d',
    // Microseconds (000000 to 999999).
    '%f' => '%f',
    // Hour (00 to 23).
    '%H' => '%H',
    // Hour (00 to 12).
    '%I' => '%I',
    // Numeric month name (0 to 12).
    '%m' => '%c',
    // Minutes (00 to 59).
    '%M' => '%i',
    // AM or PM.
    '%p' => '%p',
    // Seconds (00 to 59).
    '%S' => '%s',
    // Week where Sunday is the first day of the week (00 to 53).
    '%U' => '%U',
    // Week where Monday is the first day of the week (00 to 53).
    '%W' => '%u',
    // Year as a numeric, 2-digit value.
    '%y' => '%y',
    // Year as a numeric, 4-digit value.
    '%Y' => '%Y',
    // A literal '%' character.
    '%%' => '%%',
    // Day of the month as a numeric value (0 to 31).
    '%-d' => '%e',
    // Day of the year (001 to 366).
    '%-j' => '%j',
    // Hour (0 to 23).
    '%-H' => '%k',
    // Hour (1 to 12).
    '%-I' => '%l',
    // Month name as a numeric value (01 to 12).
    '%-m' => '%m',
    // Minutes (00 to 59).
    '%-M' => '%i',
    // Seconds (00 to 59).
    '%-S' => '%s',
  ];

  /**
   * Creates a MySQL compilation map.
   */
  public function __construct() {
    parent::__construct($this->storage);
  }

}
