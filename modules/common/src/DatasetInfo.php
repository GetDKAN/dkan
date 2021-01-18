<?php

declare(strict_types = 1);

namespace Drupal\common;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\datastore\Service as Datastore;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Service as Metastore;
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
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Metastore storage.
   *
   * @var \Drupal\metastore\Storage\Data
   */
  protected $storage;

  /**
   * Metastore.
   *
   * @var \Drupal\metastore\Service
   */
  protected $metastore;

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
   * Set storage.
   *
   * @param \Drupal\metastore\Storage\DataFactory $dataFactory
   *   Metastore's data factory.
   */
  public function setStorage(DataFactory $dataFactory) {
    $this->storage = $dataFactory->getInstance('dataset');
  }

  /**
   * Set metastore.
   *
   * @param \Drupal\metastore\Service $metastore
   *   Metastore service.
   */
  public function setMetastore(Metastore $metastore) {
    $this->metastore = $metastore;
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
   * DatasetInfo constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler service.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
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
  public function gather(string $uuid) {
    $info['uuid'] = $uuid;

    if (!$this->metastore) {
      $info['notice'] = 'The DKAN Metastore module is not enabled.';
      return $info;
    }

    $latest = $this->storage->getNodeLatestRevision($uuid);
    if (!$latest) {
      $info['notice'] = 'Not found.';
      return $info;
    }
    $info['node id'] = $latest->id();
    $info['latest revision'] = $this->getRevisionInfo($latest);

    $latestRevisionIsDraft = 'draft' === $latest->get('moderation_state')->getString();
    $published = $this->storage->getNodePublishedRevision($uuid);
    if ($latestRevisionIsDraft && $published && 'published' === $published->get('moderation_state')->getString()) {
      $info['published revision'] = $this->getRevisionInfo($published);
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
    $revisionInfo = [];

    $revisionInfo['revision id'] = $node->getRevisionId();
    $revisionInfo['moderation state'] = $node->get('moderation_state')->getString();
    $revisionInfo['modified date'] = $node->getChangedTime();
    $revisionInfo['distributions'] = $this->getDistributionsInfo($node);

    return $revisionInfo;
  }

  /**
   * Get distributions.
   *
   * @param \Drupal\node\Entity\Node $node
   *   A specific revision node of the uuid being queried.
   *
   * @return array
   *   Distributions.
   */
  protected function getDistributionsInfo(Node $node) {
    $distributions = [];

    $metadata = $node->get('field_json_metadata')->getString();
    foreach (json_decode($metadata)->distribution as $distribution) {
      $distributions[] = $this->getResourcesInfo($distribution);
    }

    return $distributions;
  }

  /**
   * Get resources information.
   *
   * @param \stdClass $distribution
   *   A distribution object extracted from dataset metadata.
   *
   * @return array
   *   Resources information.
   */
  protected function getResourcesInfo(\stdClass $distribution) : array {

    // A distribution's first resource, regardless of perspective or index,
    // should provide the information needed.
    $resource = array_shift($distribution->{'%Ref:downloadURL'});
    $identifier = $resource->data->identifier;
    $version = $resource->data->version;

    return [
      'identifier' => $identifier,
      'version' => $version,
      'file path' => $this->resourceMapper->get($identifier, 'local_file', $version)->getFilePath(),
      'table name' => $this->datastore->getStorage($identifier, $version)->getTableName(),
    ];
  }

}
