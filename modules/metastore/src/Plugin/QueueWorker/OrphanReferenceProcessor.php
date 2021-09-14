<?php

declare(strict_types = 1);

namespace Drupal\metastore\Plugin\QueueWorker;

use Drupal\common\LoggerTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\common\EventDispatcherTrait;
use Drupal\metastore\ReferenceLookupInterface;

/**
 * Verifies if a dataset property reference is orphaned, then deletes it.
 *
 * @QueueWorker(
 *   id = "orphan_reference_processor",
 *   title = @Translation("Task Worker: Check for orphaned property reference"),
 *   cron = {"time" = 15}
 * )
 *
 * @codeCoverageIgnore
 */
class OrphanReferenceProcessor extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  use LoggerTrait;
  use EventDispatcherTrait;

  const EVENT_ORPHANING_DISTRIBUTION = 'metastore_orphaning_distribution';

  /**
   * The node storage service.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\node\NodeStorageInterface $nodeStorage
   *   Node storage service.
   * @param \Drupal\metastore\ReferenceLookupInterface $referenceLookup
   *   The referencer lookup service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    NodeStorageInterface $nodeStorage,
    ReferenceLookupInterface $referenceLookup) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->referenceLookup = $referenceLookup;
    $this->nodeStorage = $nodeStorage;
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $me = new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('dkan.common.node_storage'),
          $container->get('dkan.metastore.reference_lookup')
      );
    $me->setLoggerFactory($container->get('logger.factory'));
    return $me;
  }

  /**
   * {@inheritdoc}
   *
   * @todo make the SchemaID for this dynamic
   */
  public function processItem($data) {
    $metadataProperty = $data[0];
    $identifier = $data[1];
    $referencers = $this->referenceLookup->getReferencers('dataset', $identifier, $metadataProperty);

    if (!empty($referencers)) {
      return;
    }

    // Value reference uuid not found in any dataset, therefore safe to delete.
    $this->unpublishReference($metadataProperty, $identifier);
  }

  /**
   * Unpublish a reference.
   *
   * @param string $property_id
   *   The property id.
   * @param string $uuid
   *   The uuid.
   */
  protected function unpublishReference(string $property_id, string $uuid) {
    $references = $this->nodeStorage->loadByProperties(
      [
        'uuid' => $uuid,
        'field_data_type' => $property_id,
      ]
    );
    // The reference might be deleted manually beforehand.
    if (FALSE !== ($reference = reset($references))) {
      // When orphaning distribution nodes, trigger database clean up.
      if ($property_id === 'distribution') {
        $this->dispatchEvent(self::EVENT_ORPHANING_DISTRIBUTION, $uuid);
      }
      $reference->set('moderation_state', 'orphaned');
      $reference->save();
    }
  }

}
