<?php

namespace Drupal\metastore\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrphanProcessor implements ContainerInjectionInterface {

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
   * The state object.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private $state;

  /**
   * The datetime component.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  public function __construct(
    ConfigFactoryInterface $configFactory,
    EntityTypeManager $entityTypeManager,
    StateInterface $state,
    TimeInterface $time
  ) {
    $this->config = $configFactory->get('metastore.settings');
    $this->entityTypeManager = $entityTypeManager;
    $this->state = $state;
    $this->time = $time;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('state'),
      $container->get('datetime.time')
    );
  }

  /**
   * Deletes orphaned nodes based on config settings.
   *
   * @return void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @see metastore_cron()
   */
  public function runCron() {
    if (!$this->cronShouldRun()) {
      return;
    }

    $retain_days = $this->config->get('orphan.retain_for') ?? 0;
    $retain_seconds = self::SECONDS_PER_DAY * $retain_days;

    $node_storage = $this->entityTypeManager->getStorage('node');
    $nids = $node_storage->getQuery()
      ->accessCheck(FALSE)
      ->addTag('orphan_filter')
      ->condition('type', 'data')
      ->condition('changed', $this->time->getCurrentTime() - $retain_seconds, '<')
      ->condition('status', 0)
      ->execute();

    foreach ($nids as $nid) {
      $node = $node_storage->load($nid);
      $node->delete();
    }
  }

  /**
   * Check that orphan deletion is enabled and it's been at least 24 hours
   * since last run.
   *
   * @return bool
   *  True if cron job should run. Otherwise, false
   */
  private function cronShouldRun() : bool {

    // Check metastore setting.
    if (!$this->config->get('orphan.delete')) {
      return FALSE;
    }

    // Check time since process last ran.
    $last_run = $this->state->get('orphan_delete.last_run', 0);
    $request_time = $this->time->getRequestTime();

    if ($request_time - $last_run < self::SECONDS_PER_DAY) {
      return FALSE;
    }

    $this->state->set('orphan_delete.last_run', $request_time);

    return TRUE;
  }

}
