<?php

namespace Drupal\Tests\Unit\harvest\ETL\Load;

use Contracts\RetrieverInterface;
use Drupal\harvest\ETL\Load\Load;
use Drupal\harvest\Harvester;
use Drupal\harvest\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\harvest\ETL\Load\Load
 * @coversDefaultClass \Drupal\harvest\ETL\Load\Load
 *
 * @group harvest
 * @group unit
 * @group dkan
 */
class LoadTest extends TestCase {

  public static function provideUnchangedHash(): array {
    return [
      'legacy' => ['918dae0183618b5a4c0da7cd0ca82a61a95c7d03c4822c2f46e4c34be43f7f65'],
      'not_legacy' => ['6474af9ec864832917578b9a0e1824315cb98b907e66824ea06e1ce193fe3c0a'],
    ];
  }

  /**
   * @covers ::itemState
   * @dataProvider provideUnchangedHash
   */
  public function testLoadUnchanged(string $hash): void {
    $identifier = 'test_id';

    // Mock up a hash storage to give us back a hash that will match,
    // either as a legacy hash or a non-legacy one, depending on the
    // data provider.
    $hash_storage = $this->getMockBuilder(RetrieverInterface::class)
      ->onlyMethods(['retrieve'])
      ->getMockForAbstractClass();
    $hash_storage->expects($this->once())
      ->method('retrieve')
      ->with($identifier)
            // Mock up our hash object.
      ->willReturn(json_encode((object) [
        'identifier' => $identifier,
        'hash' => $hash,
      ]));

    // Mock up a loader that has run() and itemState() so we can test. Pass
    // along our mocked hash storage to the constructor.
    $load = $this->getMockBuilder(Load::class)
      ->setConstructorArgs([
        $this->createStub(StorageInterface::class),
        $hash_storage,
        $this->createStub(StorageInterface::class),
      ])
      ->getMockForAbstractClass();

    // Finally assert that our data has a matching hash. You can make this
    // test fail by changing the 'something' => 'else' data, resulting in
    // a different hash.
    $this->assertEquals(
          Harvester::HARVEST_LOAD_UNCHANGED,
          $load->run((object) [
            'identifier' => $identifier,
            'something' => 'else',
          ])
      );
  }

  /**
   * @covers ::itemState
   */
  public function testItemStateException(): void {
    // The itemState() method should throw an exception if the item does
    // not have an identifier. This should make its way back through run()
    // to the caller.
    $load = $this->getMockBuilder(Load::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Item does not have an identifier');

    $load->run((object) ['no_identifier' => '']);
  }

}
