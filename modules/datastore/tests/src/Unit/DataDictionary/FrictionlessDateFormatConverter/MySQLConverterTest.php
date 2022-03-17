<?php

namespace Drupal\Tests\datastore\Unit\DataDictionary\FrictionlessDateFormatConverter;

use Drupal\datastore\DataDictionary\FrictionlessDateFormatConverter\MySQLConverter;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for parent class FrictionlessDateFormatConverterBase.
 */
class MySQLConverterTest extends TestCase {

  /**
   * Test convert() in parent class.
   */
  public function testConvert() {

    $converter = new MySQLConverter();
    $result = $converter->convert('%Y-%m-%d');
    $this->assertEquals('%Y-%c-%d', $result);
  }

}
