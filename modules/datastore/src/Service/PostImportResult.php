<?php

namespace Drupal\datastore\Service;

use Drupal\Core\Database\Connection;
use Drupal\metastore\ResourceMapper;
use Drupal\datastore\PostImportResource;

class PostImportResult {

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
   * The post import resource.
   *
   * @var \Drupal\datastore\PostImportResource
   */
  protected PostImportResource $postImportResource;

  /**
   * Constructs a new PostImport object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\metastore\ResourceMapper $resource_mapper
   *   The metastore resource mapper service.
   * @param \Drupal\datastore\PostImportResource $post_import_resource
   *   The post import resource.
   */
  public function __construct(
    Connection $connection,
    ResourceMapper $resource_mapper,
    PostImportResource $post_import_resource
    ) {
    $this->connection = $connection;
    $this->resourceMapper = $resource_mapper;
    $this->postImportResource = $post_import_resource;
  }

  /**
   * Stores post import result in the database.
   *
   * @param string $identifier
   *   The resource identifier.
   * @param string $version
   *   The resource version.
   * @param string $post_import_status
   *   The overall status.
   * @param string $post_import_percent_done
   *   The percent done, currently either 0 for errors or 100 for no errors.
   * @param string $post_import_error
   *   The error message if any.
   */
  public function storeJobStatus($postImportResource) {
    $this->connection->insert('dkan_post_import_job_status')
      ->fields([
        'resource_identifier' => $postImportResource->resourceIdentifier,
        'resource_version' => $postImportResource->resourceVersion,
        'post_import_status' => $postImportResource->postImportStatus,
        'post_import_error' => $postImportResource->postImportMessage,
      ])
      ->execute();
  }

  /**
   * Retrieve post import result in the database.
   *
   * @param string $identifier
   *   The resource identifier of the distribution to retrieve post import result.
   */
  public function retrieveJobStatus($identifier, $version) {
    return $this->connection->select('dkan_post_import_job_status')
      ->condition('resource_identifier', $identifier, '=')
      ->condition('resource_version', $version, '=')
      ->fields('dkan_post_import_job_status', [
        'resource_version',
        'post_import_status',
        'post_import_error',
      ])
      ->execute()
      ->fetchAssoc();
  }

  /**
   * Remove post import job status row.
   *
   * @param string $identifier
   *   The resource identifier of the distribution to retrieve post import result.
   */
  public function removeJobStatus($identifier) {
    $latest_resource = $this->resourceMapper->get($identifier);
    $latest_version = $latest_resource->getVersion();
    $this->connection->delete('dkan_post_import_job_status')
      ->condition('resource_identifier', $identifier, '=')
      ->condition('resource_version', $latest_version, '=')
      ->execute();
  }

}
