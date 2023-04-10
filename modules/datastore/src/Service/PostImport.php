<?php

namespace Drupal\datastore\Service;

use Drupal\Core\Database\Connection;
use Drupal\metastore\ResourceMapper;

/**
 * PostImport status storage service.
 */
class PostImport {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The metastore resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected ResourceMapper $resourceMapper;

  /**
   * Constructs a new PostImport object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\metastore\ResourceMapper $resource_mapper
   *   The metastore resource mapper service.
   */
  public function __construct(
    Connection $connection,
    ResourceMapper $resource_mapper
    ) {
    $this->connection = $connection;
    $this->resourceMapper = $resource_mapper;
  }

  /**
   * Store row.
   *
   * @param string $resourceIdentifier
   *   The resource identifier of the distribution.
   * @param string $resourceVersion
   *   The resource version of the distribution.
   * @param string $status
   *   The status of the post import job.
   * @param string $message
   *   The error message of the post import job.
   */
  public function storeJobStatus($resourceIdentifier, $resourceVersion, $status, $message): bool {
    try {
      $this->connection->insert('dkan_post_import_job_status')
        ->fields([
          'resource_identifier' => $resourceIdentifier,
          'resource_version' => $resourceVersion,
          'post_import_status' => $status,
          'post_import_error' => $message,
        ])
        ->execute();

      return TRUE;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * Retrieve row.
   *
   * @param string $resourceIdentifier
   *   The resource identifier of the distribution.
   * @param string $resourceVersion
   *   The resource version of the distribution.
   */
  public function retrieveJobStatus($resourceIdentifier, $resourceVersion) {
    try {
      return $this->connection->select('dkan_post_import_job_status')
        ->condition('resource_identifier', $resourceIdentifier, '=')
        ->condition('resource_version', $resourceVersion, '=')
        ->fields('dkan_post_import_job_status', [
          'resource_version',
          'post_import_status',
          'post_import_error',
        ])
        ->execute()
        ->fetchAssoc();
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * Remove row.
   *
   * @param string $resourceIdentifier
   *   The resource identifier of the distribution.
   */
  public function removeJobStatus($resourceIdentifier): bool {
    try {
      $latest_resource = $this->resourceMapper->get($resourceIdentifier);
      $latest_version = $latest_resource->getVersion();
      $this->connection->delete('dkan_post_import_job_status')
        ->condition('resource_identifier', $resourceIdentifier, '=')
        ->condition('resource_version', $latest_version, '=')
        ->execute();

      return TRUE;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

}
