<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\datastore\Service\ResourcePurger;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines 'resource_purger' queue worker.
 *
 * @QueueWorker(
 *   id = "resource_purger",
 *   title = @Translation("Queue to purge unneeded resources of datasets."),
 *   cron = {"time" = 10}
 * )
 */
class ResourcePurgerWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Resource purger service.
   *
   * @var \Drupal\datastore\Service\ResourcePurger
   */
  private $resourcePurger;

  /**
   * Constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ResourcePurger $resourcePurger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->resourcePurger = $resourcePurger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dkan.datastore.service.resource_purger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->resourcePurger->purgeMultiple($data['uuids'], $data['prior']);
  }

}
