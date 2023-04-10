<?php

namespace Drupal\datastore;

use Drupal\metastore\ResourceMapper;
use Drupal\Core\Database\Connection;
use Drupal\datastore\Service\PostImport;

/**
 * PostImportResult class to create PostImportResult objects.
 *
 * Contains the results of the PostImportResourceProcessor.
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
   * The PostImportResult.
   *
   * @var array
   */
  protected array $postImportResult;

  /**
   * PostImportResource constructor.
   */
  public function __construct(
    $postImportResult,
    ResourceMapper $resourceMapper,
    PostImport $postImport
    ) {
    $this->resourceIdentifier = $postImportResult['resource_identifier'];
    $this->resourceVersion = $postImportResult['resourceVersion'];
    $this->postImportStatus = $postImportResult['postImportStatus'];
    $this->postImportMessage = $postImportResult['postImportMessage'];
    $this->resourceMapper = $resourceMapper;
    $this->postImport = $postImport;
  }

  /**
   * Calls PostImport service to execute database insert transaction.
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
