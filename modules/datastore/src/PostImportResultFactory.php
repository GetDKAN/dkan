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
   * @param string $status
   *   Status of the post import process.
   * @param string $message
   *   Error messages retrieved during the post import process.
   * @param \Drupal\common\DataResource $resource
   *   The DKAN resource being imported.
   *
   * @return \Drupal\datastore\PostImportResult
   *   The post import result service.
   */
  public function createPostImportResult($status, $message, DataResource $resource): PostImportResult {
    return new PostImportResult([
      'resource_id' => $resource->getIdentifier(),
      'resource_version' => $resource->getVersion(),
      'postImportStatus' => $status,
      'postImportMessage' => $message,
    ],
    $this->connection,
    $this->resourceMapper);
  }

  /**
   * Creates a PostImportResult instance.
   *
   * @param array $postImportResult
   *   The post import result data.
   *
   * @return \Drupal\datastore\PostImportResult
   *   The PostImportResult object.
   */
  public function create(array $postImportResult): PostImportResult {
    return new PostImportResult(
      $postImportResult,
      $this->connection,
      $this->resourceMapper
    );
  }

}
