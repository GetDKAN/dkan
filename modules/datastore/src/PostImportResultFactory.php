<?php

namespace Drupal\datastore;

use Drupal\Core\Database\Connection;
use Drupal\metastore\ResourceMapper;
use Drupal\common\DataResource;

/**
 * Factory class to create PostImportResult objects.
 */
class PostImportResultFactory {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * The metastore resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected ResourceMapper $resourceMapper;

  /**
   * Constructs a PostImportResultFactory instance.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   The resource mapper service.
   */
  public function __construct(Connection $connection, ResourceMapper $resourceMapper) {
    $this->connection = $connection;
    $this->resourceMapper = $resourceMapper;
  }

  /**
   * Creates a PostImportResult instance.
   *
   * Passes status, message and the resource when initialized.
   *
   * @param string $status
   *   Status of the post import process.
   * @param string $message
   *   Messages retrieved during the post import process.
   * @param \Drupal\common\DataResource $resource
   *   The DKAN resource being imported.
   *
   * @return \Drupal\datastore\PostImportResult
   *   The PostImportResult object.
   */
  public function initializeFromResource($status, $message, DataResource $resource): PostImportResult {
    return new PostImportResult(
      $status,
      $message,
      $this->getCurrentTime(),
      $resource,
      $this->connection,
    );
  }

  /**
   * Creates a PostImportResult instance.
   *
   * Passes the distribution when initialized.
   *
   * @param array $distribution
   *   The distribution.
   *
   * @return \Drupal\datastore\PostImportResult
   *   The PostImportResult object.
   */
  public function initializeFromDistribution(array $distribution): PostImportResult {
    // Retrieve the data resource object.
    $resource = $this->resourceMapper->get($distribution['resource_id']);
    return new PostImportResult(
      NULL,
      NULL,
      NULL,
      $resource,
      $this->connection,
    );
  }

  /**
   * Return current Unix timestamp.
   */
  protected function getCurrentTime(): int {
    return time();
  }

}
