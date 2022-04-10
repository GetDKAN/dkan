<?php

namespace PDLT\Grammar;

use PDLT\GrammarInterface;

/**
 * Frictionless date format grammar.
 *
 * Since Frictionless is also the format used for representing directive tokens
 * internally, the tokens' values are the same as the literals they're wrapping.
 *
 * Date Format Mappings:
 *
 * | **Description**                            | **Frictionless** |
 * |--------------------------------------------|------------------|
 * | Abbreviated weekday name (Sun to Sat)      | %a               |
 * | Abbreviated month name (Jan to Dec)        | %b               |
 * | Numeric month name (0 to 12)               | %m               |
 * | Numeric day of the month (01 to 31)        | %d               |
 * | Numeric day of the month (0 to 31)         | %-d              |
 * | Microseconds (000000 to 999999)            | %f               |
 * | Hour (00 to 23)                            | %H               |
 * | Hour (00 to 12)                            | %I               |
 * | Minutes (00 to 59)                         | %M or %-M        |
 * | Day of the year (001 to 366)               | %j or %-j        |
 * | Hour (0 to 23)                             | %-H              |
 * | Hour (1 to 12)                             | %-I              |
 * | Month name in full (January to December)   | %B               |
 * | Month name as a numeric value (01 to 12)   | %-m              |
 * | AM or PM                                   | %p               |
 * | Seconds (00 to 59)                         | %S or %-S        |
 * | Week where Sunday is first day (00 to 53)  | %U               |
 * | Week where Monday is first day (00 to 53)  | %W               |
 * | Weekday name in full (Sunday to Saturday)  | %A               |
 * | Numeric day of week where Sun=0 and Sat=6  | %w               |
 * | Year as a numeric, 4-digit value           | %Y               |
 * | Year as a numeric, 2-digit value           | %y               |
 * | A literal '%' character                    | %%               |
 * | Locale date/time (e.g. 09/08/13 07:06:05)  | %c               |
 * | Locale’s date (e.g. 09/08/13)              | %x               |
 * | Locale’s time (e.g. 07:06:05)              | %X               |
 * | UTC offset ±HHMM[SS[.ffffff]] (e.g. +0000) | %z               |
 * | Time zone name (e.g. UTC)                  | %Z               |
 */
class Frictionless extends \ArrayObject implements GrammarInterface {

  /**
   * {@inheritdoc}
   */
  protected $storage = [
    '%' => [
      // Abbreviated weekday name (Sun to Sat).
      'a' => '%a',
      // Weekday name in full (Sunday to Saturday).
      'A' => '%A',
      // Abbreviated month name (Jan to Dec).
      'b' => '%b',
      // Locale date/time (e.g. 09/08/13 07:06:05).
      'c' => '%c',
      // Month name in full (January to December).
      'B' => '%B',
      // Day of the month as a numeric value (01 to 31).
      'd' => '%d',
      // Microseconds (000000 to 999999).
      'f' => '%f',
      // Hour (00 to 23).
      'H' => '%H',
      // Hour (00 to 12).
      'I' => '%I',
      // Numeric month name (0 to 12).
      'm' => '%m',
      // Minutes (00 to 59).
      'M' => '%M',
      // AM or PM.
      'p' => '%p',
      // Seconds (00 to 59).
      'S' => '%S',
      // Week where Sunday is the first day of the week (00 to 53).
      'U' => '%U',
      // Week where Monday is the first day of the week (00 to 53).
      'W' => '%W',
      // Locale’s date (e.g. 09/08/13).
      'x' => '%x',
      // Locale’s time (e.g. 07:06:05).
      'X' => '%X',
      // Year as a numeric, 2-digit value.
      'y' => '%y',
      // Year as a numeric, 4-digit value.
      'Y' => '%Y',
      // UTC offset ±HHMM[SS[.ffffff]] (e.g. +0000).
      'z' => '%z',
      // Time zone name (e.g. UTC).
      'Z' => '%Z',
      // A literal '%' character.
      '%' => '%%',
      '-' => [
        // Day of the month as a numeric value (0 to 31).
        'd' => '%-d',
        // Day of the year (001 to 366).
        'j' => '%-j',
        // Hour (0 to 23).
        'H' => '%-H',
        // Hour (1 to 12).
        'I' => '%-I',
        // Month name as a numeric value (01 to 12).
        'm' => '%-m',
        // Minutes (00 to 59).
        'M' => '%-M',
        // Seconds (00 to 59).
        'S' => '%-S',
      ],
    ],
  ];

  /**
   * Creates a Frictionless date format grammar.
   */
  public function __construct() {
    parent::__construct($this->storage);
  }

}
