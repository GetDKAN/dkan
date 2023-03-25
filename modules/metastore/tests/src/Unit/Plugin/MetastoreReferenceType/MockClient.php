<?php

namespace Drupal\Tests\metastore\Unit\Plugin\MetastoreReferenceType;

use GuzzleHttp\Client;

/**
 * Fake Guzzle client class with explicit head method.
 */
class MockClient extends Client {

  /**
   * Guzzle 6.x doesn't allow us to mock head().
   */
  public function head($uri, $options = []) {
  }

}