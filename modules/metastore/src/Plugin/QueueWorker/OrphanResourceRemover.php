<?php

namespace Drupal\metastore\Plugin\QueueWorker;

use Drupal\common\LoggerTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\metastore\ResourceMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deletes orphaned resources belonging to deleted distributions.
 *
 * @QueueWorker(
 *   id = "orphan_resource_remover",
 *   title = @Translation("Delete orphaned resources"),
 *   cron = {"time" = 15}
 * )
 *
 * @see \Drupal\metastore\LifeCycle\LifeCycle::distributionPredelete()
 *
 * @codeCoverageIgnore
 */
class OrphanResourceRemover extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  use LoggerTrait;

  /**
   * Resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected $resourceMapper;

  /**
   * OrphanResourceRemover constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   Resource mapper service.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, ResourceMapper $resourceMapper) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->resourceMapper = $resourceMapper;
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('dkan.metastore.resource_mapper')
    );
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function processItem($data) {
    list($id, $perspective, $version) = $data;

    // Use the metastore resourceMapper to remove the source entry.
    $resource = $this->resourceMapper->get($id, $perspective, $version);
    if ($resource) {
      $this->resourceMapper->remove($resource);
    }

  }

}
