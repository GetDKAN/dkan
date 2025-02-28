<?php

namespace Drupal\datastore;

use Drupal\Core\Database\Connection;
use Drupal\common\DataResource;

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
   * Current Unix timestamp.
   *
   * @var string
   */
  private $currentTime;

  /**
   * The database connection.
   */
  protected Connection $connection;

  /**
   * The Data Resource.
   */
  protected DataResource $resource;

  /**
   * PostImportResult constructor.
   */
  public function __construct(
    ?string $status,
    ?string $message,
    ?int $timestamp,
    DataResource $resource,
    Connection $connection,
    ) {
    $this->resourceIdentifier = $resource->getIdentifier();
    $this->resourceVersion = $resource->getVersion() ?? NULL;
    $this->postImportStatus = $status ?? '';
    $this->postImportMessage = $message ?? '';
    $this->currentTime = $timestamp ?? NULL;
    $this->connection = $connection;
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
          'timestamp' => $this->currentTime,
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
        ->orderBy('timestamp', 'DESC')
        ->range(0, 1)
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
      $this->connection->delete('dkan_post_import_job_status')
        ->condition('resource_identifier', $this->getResourceIdentifier(), '=')
        ->condition('resource_version', $this->getResourceVersion(), '=')
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
