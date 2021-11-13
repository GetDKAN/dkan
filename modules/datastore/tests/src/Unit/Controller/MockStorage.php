<?php

namespace Drupal\Tests\datastore\Unit\Controller;

use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Storage\Data;

class MockStorage extends Data {

  public function retrieveContains(string $string, bool $caseSensitive): array {
    return [];
  }

  public function retrieveByHash($hash, $schemaId) {
    return [];
  }

  public function retrievePublished(string $uuid) : ?string {
    throw new MissingObjectException("Error retrieving published dataset: {$this->schemaId} {$uuid} not found.");
  }

  public function retrieve(string $uuid) : ?string {
    throw new MissingObjectException("Error retrieving published dataset: {$this->schemaId} {$uuid} not found.");
  }

}