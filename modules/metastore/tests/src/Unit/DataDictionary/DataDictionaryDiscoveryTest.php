<?php

namespace Drupal\Tests\metastore\Unit\DataDictionary;

use DomainException;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery as Discovery;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\Reference\MetastoreUrlGenerator;
use Drupal\metastore\Reference\ReferenceLookup;
use MockChain\Chain;
use MockChain\Options;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;

class DataDictionaryDiscoveryTest extends TestCase {

  // If mode is set to "none", we should get NULL no matter what.
  public function testModeNone() {
    $discovery = new Discovery(
      $this->getConfigFactoryMock(Discovery::MODE_NONE, 'abc-123'),
      $this->getMetastoreService(),
      $this->getLookup(),
      $this->getUrlGenerator()
    );
    $id = $discovery->dictionaryIdFromResource('resource1', 1);
    $this->assertEquals("Disabled", $id);
  }

  // If mode is sitewide, and we have a sitewide dictionary ID set, it should be
  // returned, no matter what resource we pass to the method.
  public function testSitewideId() {
    $discovery = new Discovery(
      $this->getConfigFactoryMock(Discovery::MODE_SITEWIDE, 'abc-123'),
      $this->getMetastoreService(),
      $this->getLookup(),
      $this->getUrlGenerator()
    );
    $id = $discovery->dictionaryIdFromResource('resource1', 1);
    $this->assertEquals('abc-123', $id);
    $idVersion = $discovery->dictionaryIdFromResource('resource1', '2352643');
    $this->assertEquals('abc-123', $idVersion);
  }

  // If mode is sitewide but sitewide ID unset, we should get an exception.
  public function testSitewideIdUnset() {
    // Need to use 0 because MockChain\Options doesn't support NULL returns.
    $discovery = new Discovery(
      $this->getConfigFactoryMock(Discovery::MODE_SITEWIDE, 0),
      $this->getMetastoreService(),
      $this->getLookup(),
      $this->getUrlGenerator()
    );

    $this->expectException(\OutOfBoundsException::class);
    $discovery->dictionaryIdFromResource('resource1', 1);
  }

  // Test the reference type, four different flows:
  public function testGetReferenceDictId() {
    $discovery = new Discovery(
      $this->getConfigFactoryMock(Discovery::MODE_REFERENCE, 'abc-123'),
      $this->getMetastoreService(),
      $this->getLookup(),
      $this->getUrlGenerator()
    );
    $id = $discovery->dictionaryIdFromResource('resource1', 1);
    $this->assertEquals('111', $id);

    $id = $discovery->dictionaryIdFromResource('resource3', 3);
    $this->assertNull($id);

    $id = $discovery->dictionaryIdFromResource('resource4', 4);
    $this->assertNull($id);

    $this->expectExceptionMessage("Distribution lookup: Can not map resource ID");
    $id = $discovery->dictionaryIdFromResource('resource2', 2);
  }

  // Test if bad mode in settings
  public function testDictBadMode() {
    $discovery = new Discovery(
      $this->getConfigFactoryMock('foo', 'abc-123'),
      $this->getMetastoreService(),
      $this->getLookup(),
      $this->getUrlGenerator()
    );

    $this->expectException(OutOfRangeException::class);
    $discovery->dictionaryIdFromResource('resource1', 1);
  }
  private function getLookup() {
    $options = (new Options())
      ->add('resource1__1', ['111'])
      ->add('resource3__3', ['333'])
      ->add('resource4__4', ['444'])
      ->add('resource2__2', [])
      ->index(1);
    return (new Chain($this))
      ->add(ReferenceLookup::class, 'getReferencers', $options)
      ->getMock();
  }

  // Build mock config service, based on arguments for mode and sitewide ID.
  private function getConfigFactoryMock($mode, $sitewideId) {
    $options = (new Options())
      ->add('data_dictionary_mode', $mode)
      ->add('data_dictionary_sitewide', $sitewideId)
      ->index(0);

    return (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', $options)
      ->getMock();
  }

  private function getMetastoreService() {
    $json1 = json_encode((object) [
      'data' => (object) [
        'describedBy' => "https://example.com/api/1/metastore/schemas/data-dictionary/items/111",
        'describedByType' => 'application/vnd.tableschema+json',
      ],
    ]);
    $json4 = json_encode((object) [
      'data' => (object) [
        'describedBy' => "dkan://metastore/schemas/dataset/items/444",
        'describedByType' => 'application/vnd.tableschema+json',
      ],
    ]);
    $sequence = (new options())
      ->add('111', new RootedJsonData($json1, "{}"))
      ->add('333', new RootedJsonData())
      ->add('444', new RootedJsonData($json4, "{}"))
      ->index(1);
    return (new Chain($this))
      ->add(MetastoreService::class, 'get', $sequence)
      ->getMock();
  }

  private function getUrlGenerator() {
    $extract = (new Options())
      ->add('dkan://metastore/schemas/data-dictionary/items/111', '111')
      ->add('dkan://metastore/schemas/dataset/items/444', new DomainException())
      ->index(0);
    $uriFromUrl = (new Options())
      ->add('https://example.com/api/1/metastore/schemas/data-dictionary/items/111', 'dkan://metastore/schemas/data-dictionary/items/111')
      ->add('dkan://metastore/schemas/dataset/items/444', 'dkan://metastore/schemas/dataset/items/444');

    return (new Chain($this))
      ->add(MetastoreUrlGenerator::class, 'uriFromUrl', $uriFromUrl)
      ->add(MetastoreUrlGenerator::class, 'extractItemId', $extract)
      ->getMock();
  }

}
