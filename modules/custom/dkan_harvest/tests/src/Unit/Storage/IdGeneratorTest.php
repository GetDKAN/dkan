<?php

namespace Drupal\Tests\dkan_harvest\Unit\Storage;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Storage\IdGenerator;

/**
 * Tests Drupal\dkan_harvest\Storage\File.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Storage\File
 * @group dkan_harvest
 */
class IdGeneratorTest extends DkanTestBase {

  /**
   *
   */
  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(IdGenerator::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $json     = '{"foo":"bar"}';
    $expected = json_decode($json);

    // Assert.
    $mock->__construct($json);
    $this->assertEquals($expected, $this->readAttribute($mock, 'data'));
  }

  /**
   * Data for testGenerate.
   *
   * @return array
   */
  public function dataTestGenerate() {
    return [
        [(object) ['identifier' => 'foo'], 'foo'],
        [(object) [], NULL],
    ];
  }

  /**
   *
   * @param \stdClass $data
   * @param mixed $expected
   *
   * @dataProvider dataTestGenerate
   */
  public function testGenerate(\stdClass $data, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(IdGenerator::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $this->writeProtectedProperty($mock, 'data', $data);

    // Assert.
    $actual = $mock->generate();
    $this->assertEquals($expected, $actual);
  }

}
