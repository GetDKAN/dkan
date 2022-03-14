<?php

namespace Drupal\Tests\metastore\Unit\DataDictionary;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery as Discovery;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

class DataDictionaryDiscoveryTest extends TestCase {
  

  // If mode is set to "none", we should get NULL no matter what.
  public function testModeNone() {
    $configFactoryMock = $this->getConfigFactoryMock(Discovery::MODE_NONE, 'abc-123');
    $discovery = new Discovery($configFactoryMock);
    $id = $discovery->dictionaryIdFromResource('resource1');
    $this->assertNull($id);
  }

  // If mode is sitewide, and we have a sitewide dictionary ID set, it should be
  // returned, no matter what resource we pass to the method.
  public function testSitewideId() {
    $configFactoryMock = $this->getConfigFactoryMock(Discovery::MODE_SITEWIDE, 'abc-123');
    $discovery = new Discovery($configFactoryMock);
    $id = $discovery->dictionaryIdFromResource('resource1');
    $this->assertEquals('abc-123', $id);
    $idVersion = $discovery->dictionaryIdFromResource('resource1', '2352643');
    $this->assertEquals('abc-123', $idVersion);
  }

  // If mode is sitewide but sitewide ID unset, we should get an exception.
  public function testSitewideIdUnset() {
    // Need to use 0 because MockChain\Options doesn't support NULL returns.
    $configFactoryMock = $this->getConfigFactoryMock(Discovery::MODE_SITEWIDE, 0);
    $discovery = new Discovery($configFactoryMock);

    $this->expectException(\OutOfBoundsException::class);
    $discovery->dictionaryIdFromResource('resource1');
  }

  // If mode is set to "collection", at the moment we should throw an exception
  // because this is not yet supported.
  public function testModeCollection() {
    $configFactoryMock = $this->getConfigFactoryMock(Discovery::MODE_COLLECTION, 'abc-123');
    $discovery = new Discovery($configFactoryMock);

    $this->expectException(\OutOfRangeException::class);
    $discovery->dictionaryIdFromResource('resource1');
  }

  // If mode is set to "generate", at the moment we should throw an exception
  // because this is not yet supported.
  public function testModeGenerate() {
    $configFactoryMock = $this->getConfigFactoryMock(Discovery::MODE_GENERATE, 'abc-123');
    $discovery = new Discovery($configFactoryMock);

    $this->expectException(\OutOfRangeException::class);
    $discovery->dictionaryIdFromResource('resource1');
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
}