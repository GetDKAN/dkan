<?php

declare(strict_types = 1);

namespace Drupal\common;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\datastore\Service as Datastore;
use Drupal\datastore\Service\Info\ImportInfo;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\DataFactory;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extract helpful information from a dataset identifier.
 *
 * @package Drupal\common
 */
class DatasetInfo implements ContainerInjectionInterface {

  /**
   * Metastore storage.
   *
   * @var \Drupal\metastore\Storage\Data
   */
  protected $storage;

  /**
   * Datastore.
   *
   * @var \Drupal\datastore\Service
   */
  protected $datastore;

  /**
   * Resource mapper.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected $resourceMapper;

  /**
   * Import info service.
   *
   * @var \Drupal\datastore\Service\Info\ImportInfo
   */
  protected $importInfo;

  /**
   * Set storage.
   *
   * @param \Drupal\metastore\Storage\DataFactory $dataFactory
   *   Metastore's data factory.
   */
  public function setStorage(DataFactory $dataFactory) {
    $this->storage = $dataFactory->getInstance('dataset');
  }

  /**
   * Set datastore.
   *
   * @param \Drupal\datastore\Service $datastore
   *   Datastore service.
   */
  public function setDatastore(Datastore $datastore) {
    $this->datastore = $datastore;
  }

  /**
   * Set the resource mapper.
   *
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   Resource mapper service.
   */
  public function setResourceMapper(ResourceMapper $resourceMapper) {
    $this->resourceMapper = $resourceMapper;
  }

  /**
   * Set the import info service.
   *
   * @param \Drupal\datastore\Service\Info\ImportInfo $importInfo
   *   Import info service.
   */
  public function setImportInfo(ImportInfo $importInfo) {
    $this->importInfo = $importInfo;
  }

  /**
   * Instantiates a new instance of this class.
   *
   * While the relevant services are each called conditionally, leaving none
   * needed here, this function must still be implemented.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this instance should use.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
    );
  }

  /**
   * Gather info about a dataset from its identifier.
   *
   * @param string $uuid
   *   Dataset identifier.
   *
   * @return array
   *   Dataset information array.
   */
  public function gather(string $uuid) : array {
    if (!$this->storage) {
      $info['notice'] = 'The DKAN Metastore module is not enabled.';
      return $info;
    }

    $latest = $this->storage->getEntityLatestRevision($uuid);
    if (!$latest) {
      $info['notice'] = 'Not found';
      return $info;
    }
    $info['latest_revision'] = $this->getRevisionInfo($latest);

    $latestRevisionIsDraft = 'draft' === $latest->get('moderation_state')->getString();
    $published = $this->storage->getEntityPublishedRevision($uuid);
    if ($latestRevisionIsDraft && $published && 'published' === $published->get('moderation_state')->getString()) {
      $info['published_revision'] = $this->getRevisionInfo($published);
    }

    return $info;
  }

  /**
   * Get various information from a dataset node's specific revision.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Dataset node.
   *
   * @return array
   *   Dataset node revision info.
   */
  protected function getRevisionInfo(Node $node) : array {

    $metadata = json_decode($node->get('field_json_metadata')->getString());

    return [
      'uuid' => $node->uuid(),
      'node_id' => $node->id(),
      'revision_id' => $node->getRevisionId(),
      'moderation_state' => $node->get('moderation_state')->getString(),
      'title' => $metadata->title ?? 'Not found',
      'modified_date_metadata' => $metadata->modified ?? 'Not found',
      'modified_date_dkan' => $metadata->{'%modified'} ?? 'Not found',
      'distributions' => $this->getDistributionsInfo($metadata),
    ];
  }

  /**
   * Get distributions info.
   *
   * @param object $metadata
   *   Dataset metadata object.
   *
   * @return array
   *   Distributions.
   */
  protected function getDistributionsInfo(\stdClass $metadata) : array {
    $distributions = [];

    if (!isset($metadata->{'%Ref:distribution'})) {
      return ['Not found'];
    }

    foreach ($metadata->{'%Ref:distribution'} as $distribution) {
      $distributions[] = $this->getResourcesInfo($distribution);
    }

    return $distributions;
  }

  /**
   * Get resources information.
   *
   * @param object $distribution
   *   A distribution object extracted from dataset metadata.
   *
   * @return array
   *   Resources information.
   */
  protected function getResourcesInfo(\stdClass $distribution) : array {

    if (!isset($distribution->data->{'%Ref:downloadURL'})) {
      return ['No resource found'];
    }

    // A distribution's first resource, regardless of perspective or index,
    // should provide the information needed.
    $resource = array_shift($distribution->data->{'%Ref:downloadURL'});
    $identifier = $resource->data->identifier;
    $version = $resource->data->version;

    $info = $this->importInfo->getItem($identifier, $version);
    $fileMapper = $this->resourceMapper->get($identifier, 'local_file', $version);

    try {
      $storage = $this->datastore->getStorage($identifier, $version);
    }
    catch (\Exception $e) {
    }

    return [
      'distribution_uuid' => $distribution->identifier,
      'resource_id' => $identifier,
      'resource_version' => $version,
      'fetcher_status' => $info->fileFetcherStatus,
      'fetcher_percent_done' => $info->fileFetcherPercentDone ?? 0,
      'file_path' => isset($fileMapper) ? $fileMapper->getFilePath() : 'not found',
      'importer_status' => $info->importerStatus,
      'importer_percent_done' => $info->importerPercentDone ?? 0,
      'table_name' => isset($storage) ? $storage->getTableName() : 'not found',
    ];
  }

}
