<?php

declare(strict_types = 1);

namespace Drupal\dkan_api\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Verifies if a theme is orphaned, then deletes it.
 *
 * @QueueWorker(
 *   id = "orphan_theme_processor",
 *   title = @Translation("Task Worker: Verify then delete orphaned theme"),
 *   cron = {"time" = 15}
 * )
 */
class OrphanThemeProcessor extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($uuid) {
    $datasets = $this->entityTypeManager->getStorage('node')
      ->loadByProperties([
        'field_data_type' => 'dataset',
      ]);

    foreach ($datasets as $dataset) {
      $data = json_decode($dataset->field_json_metadata->value);
      $themes = $data->theme ?? [];
      if (in_array($uuid, $themes)) {
        // Uuid found in use, abort.
        return;
      }
    }

    // Theme uuid not found in any dataset, safe to delete.
    $themes = $this->entityTypeManager->getStorage('node')
      ->loadByProperties([
        'field_data_type' => 'theme',
        'uuid' => $uuid,
      ]);
    if (FALSE !== ($theme = reset($themes))) {
      $theme->delete();
    }
  }

}
