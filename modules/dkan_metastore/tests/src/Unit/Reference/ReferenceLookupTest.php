<?php

namespace Drupal\Tests\dkan_metastore\Unit\Reference;

use Contracts\FactoryInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\dkan_metastore\Factory\MetastoreItemFactoryInterface;
use Drupal\dkan_metastore\Reference\ReferenceLookup;
use Drupal\dkan_metastore\Storage\MetastoreStorageInterface;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 * Tests ReferenceLookup class.
 *
 * @coversDefaultClass \Drupal\dkan_metastore\Reference\ReferenceLookup
 *
 * @group dkan_metastore
 * @group unit
 */
class ReferenceLookupTest extends TestCase {

  /**
   * @covers ::getReferencers
   * @covers ::valueContainsStartsWith
   */
  public function testGetReferencers() {
    // Call to retrieveContains() returns item IDs for all metadata shapes.
    $contains_items = [
      'item-id-1',
      'item-id-2',
      'item-id-3',
      'item-id-4',
      'item-id-5',
      'item-id-6',
    ];

    $metastoreStorageFactory = (new Chain($this))
      ->add(FactoryInterface::class, 'getInstance', MetastoreStorageInterface::class)
      ->add(MetastoreStorageInterface::class, 'retrieveContains', $contains_items)
      ->getMock();

    $referenceLookup = $this->getMockBuilder(ReferenceLookup::class)
      ->setConstructorArgs([
        $metastoreStorageFactory,
        $this->createMock(MetastoreItemFactoryInterface::class),
        $this->createMock(CacheTagsInvalidatorInterface::class),
        $this->createMock(ModuleHandlerInterface::class),
      ])
      ->onlyMethods(['decodeJsonMetadata'])
      ->getMock();

    $referenceLookup
      ->expects($this->exactly(count($contains_items)))
      ->method('decodeJsonMetadata')
      ->willReturnOnConsecutiveCalls(
        ['ref-array', [(object) ['foo' => 'bar'], (object) ['identifier' => 'abc-array']]],
        ['ref-object', (object) ['identifier' => (object) ['value' => 'abc-object']]],
        ['ref-string', (object) ['identifier' => 'abc-object']],
        ['array-not-match', ['identifier' => ['other', 'still-other']]],
        ['object-not-match', (object) ['identifier' => (object) ['value' => 'other']]],
        ['unknown-shape', 'not-array-or-object']
      );

    $referencers = $referenceLookup->getReferencers('test-identifier', 'abc', 'identifier');

    $this->assertIsArray($referencers);
    // The first two returns matched the reference.
    $this->assertSame(['ref-array', 'ref-object', 'ref-string'], array_values($referencers));
  }

}
