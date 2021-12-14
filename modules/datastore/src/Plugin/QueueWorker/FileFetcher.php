<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerBase;

use Drupal\common\LoggerTrait;
use Drupal\datastore\Service\ResourceLocalizer;

use Procrastinator\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fetches resource files.
 *
 * @QueueWorker(
 *   id = "file_fetcher",
 *   title = @Translation("Queue to fetch resource files"),
 *   cron = {
 *     "time" = 180,
 *     "lease_time" = 10800
 *   }
 * )
 */
class FileFetcher extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  use LoggerTrait;

  /**
   * The datastore import queue worker's corresponding database queue instance.
   *
   * @var \Drupal\Core\Queue\DatabaseQueue
   */
  protected $importQueue;

  /**
   * Create a file fetcher queue worker instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\datastore\Service\ResourceLocalizer $resourceLocalizer
   *   A resource localizer service instance.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   A logger channel factory instance.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   A queue factory service instance.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    $plugin_definition,
    ResourceLocalizer $resourceLocalizer,
    LoggerChannelFactoryInterface $loggerFactory,
    QueueFactory $queueFactory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->importQueue = $queueFactory->get('datastore_import');
    $this->resourceLocalizer = $resourceLocalizer;
    $this->setLoggerFactory($loggerFactory, 'datastore');
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
      $container->get('queue')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    ['identifier' => $identifier, 'version' => $version] = $data;
    $result = $this->fetchResource($identifier, $version);

    if (isset($result)) {
      if ($result->getStatus() === Result::DONE) {
        $this->scheduleImport($identifier, $version);
      }
      else {
        $this->error("Failed to fetch resource: {$result->getError()}.");
      }
    }
    else {
      $this->error("Failed to fetch resource: unable to find resource with identifier '{$identifier}' and version '{$version}'.");
    }
  }

  /**
   * Fetch the resource file belonging to the supplied identifier and version.
   *
   * @param string $identifier
   *   Resource identifier.
   * @param string $version
   *   Resource version.
   *
   * @return \Procrastinator\Result
   *   Job result.
   */
  protected function fetchResource(string $identifier, string $version): Result {
    return $this->resourceLocalizer->localize($identifier, $version);
  }

  /**
   * Schedule resource import.
   *
   * @param string $identifier
   *   Resource identifier.
   * @param string $version
   *   Resource version.
   */
  protected function scheduleImport(string $identifier, string $version): void {
    $this->importQueue->createItem(['identifier' => $identifier, 'version' => $version]);
  }

}
