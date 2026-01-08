<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\datastore\Service\ResourceLocalizer;
use Procrastinator\Result;
use Psr\Log\LoggerInterface;
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
   */
  protected ResourceLocalizer $resourceLocalizer;

  /**
   * Logger service.
   */
  protected LoggerInterface $logger;

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
   * @param \Psr\Log\LoggerInterface $loggerChannel
   *   A logger channel factory instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ResourceLocalizer $resourceLocalizer,
    LoggerInterface $loggerChannel
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->resourceLocalizer = $resourceLocalizer;
    $this->logger = $loggerChannel;
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
      $container->get('dkan.datastore.logger_channel')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\Core\Cron::processQueues()
   */
  public function processItem($data) {
    $identifier = $data['identifier'] ?? NULL;
    $version = $data['version'] ?? NULL;

    // LocalizeTask() must return Result::DONE if the resource is already
    // localized.
    $result = $this->resourceLocalizer->localizeTask($identifier, $version, FALSE);
    $status = $result->getStatus();

    // Handy message string.
    $message = 'Localization of resource ' . $identifier . ': ' . $result->getError();

    // Error status means do not re-queue. The user should fix something and
    // manually set up the queue again.
    if ($status === Result::ERROR) {
      $this->logger->error($message);
      return;
    }

    // For all other cases, we want to log a message.
    $this->logger->notice($message);

    // If the status is not done, but it's not an error, then re-queue the item
    // by throwing an exception.
    if ($status !== Result::DONE) {
      throw new \Exception($message);
    }
  }

}
