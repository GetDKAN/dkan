<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\datastore\Service\PostImport;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Apply specified data-dictionary to datastore belonging to specified dataset.
 *
 * @QueueWorker(
 *   id = "post_import",
 *   title = @Translation("Pass along new resources to resource processors"),
 *   cron = {
 *     "time" = 180,
 *     "lease_time" = 10800
 *   }
 * )
 */
class PostImportResourceProcessor extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The PostImport service.
   */
  protected PostImport $postImport;

  /**
   * The datastore.settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $configFactory;

  /**
   * Constructor for PostImportResourceProcessor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\datastore\Service\PostImport $post_import
   *   The PostImport service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    PostImport $post_import,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->postImport = $post_import;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dkan.datastore.service.post_import'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $result = $this->postImport->processResource($data);
    $result->storeJobStatus();
  }

}
