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

}
