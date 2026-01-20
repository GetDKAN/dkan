<?php

namespace Drupal\metastore\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManager;
use PHPUnit\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrphanNodeProcessor implements ContainerInjectionInterface {

  const SECONDS_PER_DAY = 60 * 60 * 24;

  /**
   * The datastore.settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * DKAN logger channel service.
   */
  private LoggerInterface $logger;

  /**
   * The datetime component.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  public function __construct(
    ConfigFactoryInterface $configFactory,
    EntityTypeManager $entityTypeManager,
    LoggerInterface $loggerChannel,
    TimeInterface $time
  ) {
    $this->config = $configFactory->get('metastore.settings');
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $loggerChannel;
    $this->time = $time;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('dkan.common.logger_channel'),
      $container->get('datetime.time')
    );
  }

  /**
   * Deletes orphaned nodes based on config settings.
   *
   * @return array|bool
   *   Returns array containing any deleted node ids if it runs or false if
   *   orphan deletion is disabled.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteOutdatedOrphans() {
    // Check metastore setting.
    if (!$this->config->get('orphan.delete')) {
      return FALSE;
    }

    $retain_days = $this->config->get('orphan.retain_for') ?? 0;
    $retain_seconds = self::SECONDS_PER_DAY * $retain_days;

    $node_storage = $this->entityTypeManager->getStorage('node');
    $nids = $node_storage->getQuery()
      ->accessCheck(FALSE)
      ->addTag('dkan_orphan_filter')
      ->condition('type', 'data')
      ->condition('changed', $this->time->getCurrentTime() - $retain_seconds, '<')
      ->execute();
    foreach ($nids as $nid) {
      $node_storage->load($nid)->delete();
    }
    return $nids;
  }

}
