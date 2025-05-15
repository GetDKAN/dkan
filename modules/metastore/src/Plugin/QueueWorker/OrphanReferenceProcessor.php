<?php

declare(strict_types = 1);

namespace Drupal\metastore\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\common\Events\Event;
use Drupal\metastore\ReferenceLookupInterface;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

  const EVENT_ORPHANING_DISTRIBUTION = 'metastore_orphaning_distribution';

  /**
   * The node storage service.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * Reference lookup service.
   */
  private ReferenceLookupInterface $referenceLookup;

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected EventDispatcherInterface $eventDispatcher;

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
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    NodeStorageInterface $nodeStorage,
    ReferenceLookupInterface $referenceLookup,
    EventDispatcherInterface $eventDispatcher
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->referenceLookup = $referenceLookup;
    $this->nodeStorage = $nodeStorage;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('dkan.common.node_storage'),
          $container->get('dkan.metastore.reference_lookup'),
          $container->get('event_dispatcher')
      );
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
        $event = new Event($uuid);
        $this->eventDispatcher->dispatch($event, self::EVENT_ORPHANING_DISTRIBUTION);
      }
      $reference->set('moderation_state', 'orphaned');
      $reference->save();
    }
  }

}
