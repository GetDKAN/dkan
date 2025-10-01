<?php

declare(strict_types=1);

namespace Drupal\common;

use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\MetastoreEntityStorageInterface;
use Drupal\node\Entity\Node;

/**
 * Extract helpful information from a dataset identifier.
 *
 * Uses basic metastore information by default, other modules may add
 * additional information using the dataset_info plugin type.
 *
 * @package Drupal\common
 */
class DatasetInfo {

  /**
   * Metastore storage.
   */
  protected MetastoreEntityStorageInterface $storage;

  /**
   * Resource mapper.
   */
  protected ResourceMapper $resourceMapper;

  /**
   * DatasetInfoPluginManager.
   */
  protected DatasetInfoPluginManager $pluginManager;

  /**
   * DatasetInfo constructor.
   *
   * @param \Drupal\common\DatasetInfoPluginManager $pluginManager
   *   The DatasetInfo plugin manager.
   */
  public function __construct(DatasetInfoPluginManager $pluginManager) {
    $this->pluginManager = $pluginManager;
  }

  /**
   * Inject storage factory and set storage.
   *
   * @todo Inject this via the constructor one we have our dependencies fixed.
   *
   * @param \Drupal\metastore\Storage\DataFactory $dataFactory
   *   Metastore's data factory.
   */
  public function setStorage(DataFactory $dataFactory) {
    $this->storage = $dataFactory->getInstance('dataset');
  }

  /**
   * Inject the resource mapper.
   *
   * @todo Inject this via the constructor one we have our dependencies fixed.
   *
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   Resource mapper service.
   */
  public function setResourceMapper(ResourceMapper $resourceMapper) {
    $this->resourceMapper = $resourceMapper;
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
    // @todo Remove this check once we consolodate common and metastore.
    if (!($this->storage ?? FALSE)) {
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
    if ($latestRevisionIsDraft && isset($published)) {
      $info['published_revision'] = $this->getRevisionInfo($published);
    }

    $this->applyPlugins($info);

    return $info;
  }

  /**
   * Get the distribution UUID for a dataset.
   *
   * Return the distribution UUID for the most recent published revision
   * of a dataset.
   *
   * @param string $dataset_uuid
   *   The uuid of a dataset.
   * @param string $index
   *   The index of the resource in the dataset array. Defaults to first.
   *
   * @return string
   *   The distribution UUID
   */
  public function getDistributionUuid(string $dataset_uuid, string $index = '0'): string {
    $dataset_info = $this->gather($dataset_uuid);

    if (!isset($dataset_info['latest_revision'])) {
      return '';
    }

    // Default to latest dataset revision.
    $datasetRevision = $dataset_info['latest_revision'];

    // Use the published dataset revision instead if present.
    if (isset($dataset_info['published_revision'])) {
      $datasetRevision = $dataset_info['published_revision'];
    }
    return $datasetRevision['distributions'][$index]['distribution_uuid'] ?? '';
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

    $source = $this->resourceMapper->get($identifier, DataResource::DEFAULT_SOURCE_PERSPECTIVE, $version);

    return [
      'distribution_uuid' => $distribution->identifier,
      'resource_id' => $identifier,
      'resource_version' => $version,
      'mime_type' => isset($source) ? $source->getMimeType() : '',
      'source_path' => isset($source) ? $source->getFilePath() : '',
    ];
  }

  /**
   * Apply plugins to dataset info.
   *
   * @param array $info
   *   Dataset info array.
   */
  protected function applyPlugins(array &$info) {
    $pluginDefinitions = $this->pluginManager->getDefinitions();
    foreach ($pluginDefinitions as $definition) {
      $plugin = $this->pluginManager->createInstance($definition['id']);
      // Ensure existing values are not overwritten.
      $info = \array_replace_recursive($plugin->addDatasetInfo($info), $info);
    }

  }

}
