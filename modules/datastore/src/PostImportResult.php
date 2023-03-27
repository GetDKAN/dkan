<?php

namespace Drupal\datastore;

use Drupal\metastore\ResourceMapper;
use Drupal\Core\Database\Connection;
use Drupal\datastore\Service\PostImport;

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
   * The PostImport service.
   *
   * @var \Drupal\datastore\Service\PostImport
   */
  protected PostImport $postImport;

  /**
   * PostImportResource constructor.
   */
  public function __construct(
    $resource_identifier,
    $resource_version,
    $post_import_status,
    $post_import_message,
    ResourceMapper $resourceMapper,
    PostImport $postImport
    ) {
    $this->resourceIdentifier = $resource_identifier;
    $this->resourceVersion = $resource_version;
    $this->postImportStatus = $post_import_status;
    $this->postImportMessage = $post_import_message;
    $this->resourceMapper = $resourceMapper;
    $this->postImport = $postImport;
  }

  /**
   * Calls PostImport service to execute database insert transaction.
   *
   * @param string $resourceIdentifier
   *   The resource identifier of the distribution.
   * @param string $resourceVersion
   *   The resource version of the distribution.
   * @param string $postImportStatus
   *   The status of the post_import_result_process.
   * @param string $postImportMessage
   *   The message of the post_import_result_process.
   */
  public function storeResult() {
    return $this->postImport->storeJobStatus($this->resourceIdentifier, $this->resourceVersion, $this->postImportStatus, $this->postImportMessage);
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