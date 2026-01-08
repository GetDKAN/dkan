<?php

namespace Drupal\Tests\datastore\Unit\Controller;

use Drupal\dkan_metastore\Exception\MissingObjectException;
use Drupal\dkan_metastore\Storage\Data;

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
