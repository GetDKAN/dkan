<?php

namespace Drupal\datastore;

use Drupal\metastore\ResourceMapper;
use Drupal\Core\Database\Connection;

/**
 * PostImportResult class to insert,retrieve,remove post import jobs.
 *
 * Contains the results of the PostImport.
 */
class PostImportResult {

  /**
   * Resource identifier.
   *
   * @var string
   */
  private $resourceIdentifier;

  /**
   * Resource version.
   *
   * @var string
   */
  private $resourceVersion;

  /**
   * Post import status.
   *
   * @var string
   */
  private $postImportStatus;

  /**
   * Post import message.
   *
   * @var string
   */
  private $postImportMessage;

  /**
   * The database connection.
   */
  protected Connection $connection;

  /**
   * The metastore resource mapper service.
   */
  protected ResourceMapper $resourceMapper;

  /**
   * The PostImportResult.
   */
  protected array $postImportResult;

  /**
   * PostImportResult constructor.
   */
  public function __construct(
    $postImportResult,
    Connection $connection,
    ResourceMapper $resourceMapper,
    ) {
    $this->resourceIdentifier = $postImportResult['resource_id'];
    $this->resourceVersion = $postImportResult['resource_version'] ?? NULL;
    $this->postImportStatus = $postImportResult['postImportStatus'] ?? NULL;
    $this->postImportMessage = $postImportResult['postImportMessage'] ?? NULL;
    $this->connection = $connection;
    $this->resourceMapper = $resourceMapper;
  }

  /**
   * Store row.
   */
  public function storeJobStatus(): bool {
    try {
      $this->connection->insert('dkan_post_import_job_status')
        ->fields([
          'resource_identifier' => $this->getResourceIdentifier(),
          'resource_version' => $this->getResourceVersion(),
          'post_import_status' => $this->getPostImportStatus(),
          'post_import_error' => $this->getPostImportMessage(),
        ])
        ->execute();

      return TRUE;
    }
    catch (\Exception) {
      return FALSE;
    }
  }

  /**
   * Retrieve row.
   */
  public function retrieveJobStatus() {
    try {
      return $this->connection->select('dkan_post_import_job_status')
        ->condition('resource_identifier', $this->getResourceIdentifier(), '=')
        ->condition('resource_version', $this->getResourceVersion(), '=')
        ->fields('dkan_post_import_job_status', [
          'resource_version',
          'post_import_status',
          'post_import_error',
        ])
        ->execute()
        ->fetchAssoc();
    }
    catch (\Exception) {
      return FALSE;
    }
  }

  /**
   * Remove row.
   */
  public function removeJobStatus(): bool {
    try {
      $latest_resource = $this->resourceMapper->get($this->getResourceIdentifier());
      $latest_version = $latest_resource->getVersion();
      $this->connection->delete('dkan_post_import_job_status')
        ->condition('resource_identifier', $this->getResourceIdentifier(), '=')
        ->condition('resource_version', $latest_version, '=')
        ->execute();

      return TRUE;
    }
    catch (\Exception) {
      return FALSE;
    }
  }

  /**
   * Getter.
   */
  public function getResourceIdentifier() {
    return $this->resourceIdentifier;
  }

  /**
   * Getter.
   */
  public function getResourceVersion() {
    return $this->resourceVersion;
  }

  /**
   * Getter.
   */
  public function getPostImportStatus() {
    return $this->postImportStatus;
  }

  /**
   * Getter.
   */
  public function getPostImportMessage() {
    return $this->postImportMessage;
  }

}
