<?php

namespace Drupal\Tests\datastore\Unit\Controller;

use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Storage\Data;

/**
 * Mock metastore controller for certain datastore tests.
 */
class MockStorage extends Data {

  public function retrieveContains(string $string, bool $caseSensitive): array {
    return [];
  }

  public function retrieveByHash($hash, $schemaId) {
    return [];
  }

  public function retrieve(string $uuid, bool $published = FALSE) : ?string {
    throw new MissingObjectException("Error retrieving published dataset: distribution {$uuid} not found.");
  }

}
