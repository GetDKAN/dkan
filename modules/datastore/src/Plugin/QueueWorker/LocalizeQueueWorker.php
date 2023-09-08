<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\Reference\ReferenceLookup;
use Drupal\metastore\ResourceMapper;
use Procrastinator\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes making a local copy of a source resource.
 *
 * @QueueWorker(
 *   id = "localize_import",
 *   title = @Translation("Make a local copy of a source resource."),
 *   cron = {
 *     "time" = 180,
 *     "lease_time" = 10800
 *   }
 * )
 */
class LocalizeQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Resource localizer service.
   *
   * @var \Drupal\datastore\Service\ResourceLocalizer
   */
  protected ResourceLocalizer $resourceLocalizer;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * Resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected ResourceMapper $resourceMapper;

  /**
   * Reference lookup service.
   *
   * @var \Drupal\metastore\Reference\ReferenceLookup
   */
  protected ReferenceLookup $referenceLookup;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *   A DKAN datastore service instance.
   * @param \Drupal\datastore\Service\ResourceLocalizer $resourceLocalizer
   *   Resource localizer service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   A logger channel factory instance.
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   Resource mapper service.
   * @param \Drupal\metastore\Reference\ReferenceLookup $referenceLookup
   *   Reference lookup service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ResourceLocalizer $resourceLocalizer,
    LoggerChannelFactoryInterface $loggerFactory,
    ResourceMapper $resourceMapper,
    ReferenceLookup $referenceLookup
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->resourceLocalizer = $resourceLocalizer;
    $this->logger = $loggerFactory->get('datastore');
    $this->resourceMapper = $resourceMapper;
    $this->referenceLookup = $referenceLookup;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dkan.datastore.service.resource_localizer'),
      $container->get('logger.factory'),
      $container->get('dkan.metastore.resource_mapper'),
      $container->get('dkan.metastore.reference_lookup')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @see ResourceLocalizer::localizeTask()
   * @see \Drupal\Core\Cron::processQueues()
   */
  public function processItem($data) {
    $identifier = $data['identifier'] ?? NULL;
    $version = $data['data'] ?? NULL;

    // LocalizeTask() must return Result::DONE if the resource is already
    // localized.
    $result = $this->resourceLocalizer->localizeTask($identifier, $version, FALSE);

    // @todo Make result handling more sophisticated.
    if ($result->getStatus() !== Result::DONE) {
      $message = 'Localization of resource ' . $identifier . ': ' . $result->getError();
      $this->logger->notice($message);
      // Throwing an exception re-queues the item.
      throw new \Exception($message);
    }
  }

}
