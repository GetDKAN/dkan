<?php

namespace Drupal\dkan_datastore_preview\Hook;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\dkan_common\DataResource;
use Drupal\dkan_common\DatasetInfo;
use Drupal\dkan_metastore\NodeWrapper\NodeDataFactory;
use Drupal\node\NodeInterface;

/**
 * Hook implementations for dkan_datastore_preview.
 *
 * On Drupal 10 the procedural stubs in dkan_datastore_preview.module
 * delegate here; on Drupal 11.1+ the #[Hook] attributes register directly.
 */
class DataPreviewHooks {

  use StringTranslationTrait;

  /**
   * Extra field name on the data node display.
   */
  const EXTRA_FIELD = 'data_preview';

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\dkan_common\DatasetInfo $datasetInfo
   *   The DKAN dataset info service.
   * @param \Drupal\dkan_metastore\NodeWrapper\NodeDataFactory $itemFactory
   *   The metastore item factory.
   */
  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly DatasetInfo $datasetInfo,
    protected readonly NodeDataFactory $itemFactory,
  ) {}

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(): array {
    return [
      'dkan_datastore_preview' => [
        'variables' => [
          'table' => NULL,
          'pager' => NULL,
          'page_size_form' => NULL,
          'result_summary' => NULL,
        ],
        'template' => 'dkan-datastore-preview',
      ],
    ];
  }

  /**
   * Implements hook_entity_extra_field_info().
   */
  #[Hook('entity_extra_field_info')]
  public function entityExtraFieldInfo(): array {
    $extra = [];
    if ($this->entityTypeManager->getStorage('node_type')->load('data')) {
      $extra['node']['data']['display'][self::EXTRA_FIELD] = [
        'label' => $this->t('Data Preview'),
        'description' => $this->t('Paginated preview tables for tabular distributions.'),
        'weight' => 100,
        'visible' => TRUE,
      ];
    }
    return $extra;
  }

  /**
   * Implements hook_ENTITY_TYPE_view() for node entities.
   *
   * Adds a data preview table for each importable distribution on dataset
   * nodes, rendered by dkan_metastore's node--data template via the
   * data_preview extra field.
   */
  #[Hook('node_view')]
  public function nodeView(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, string $view_mode): void {
    if (!$entity instanceof NodeInterface || $entity->bundle() !== 'data') {
      return;
    }
    if (($entity->get('field_data_type')->value ?? '') !== 'dataset') {
      return;
    }
    if (!$display->getComponent(self::EXTRA_FIELD)) {
      return;
    }

    // Datastore tables cannot exist for an unsaved node being previewed.
    if (!empty($entity->in_preview) && $entity->isNew()) {
      $build[self::EXTRA_FIELD] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Data preview is available after the dataset is saved and data is imported.'),
      ];
      return;
    }

    $info = $this->datasetInfo->gather($entity->uuid());
    $revision = $info['published_revision'] ?? $info['latest_revision'] ?? NULL;
    if (!$revision || empty($revision['distributions'])) {
      return;
    }

    $previews = [];
    $index = 0;
    foreach ($revision['distributions'] as $dist) {
      if (!is_array($dist) || empty($dist['resource_id']) || empty($dist['resource_version'])) {
        continue;
      }
      if (!in_array($dist['mime_type'] ?? '', DataResource::IMPORTABLE_FILE_TYPES)) {
        continue;
      }

      $label = '';
      if (!empty($dist['source_path'])) {
        $label = basename(parse_url($dist['source_path'], PHP_URL_PATH) ?: $dist['source_path']);
      }

      $previews[] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['dkan-datastore-preview-distribution']],
        '#cache' => [
          'tags' => Cache::mergeTags(
            $entity->getCacheTags(),
            $this->distributionCacheTags($dist['distribution_uuid'] ?? '')
          ),
        ],
        'table' => [
          '#type' => 'dkan_datastore_preview',
          '#resource_id' => $dist['resource_id'] . '__' . $dist['resource_version'],
          '#pager_element' => $index,
          '#query_prefix' => 'dp' . $index . '_',
          '#caption' => $label ? $this->t('Preview: @label', ['@label' => $label]) : NULL,
        ],
      ];
      $index++;
    }

    if ($previews) {
      $build[self::EXTRA_FIELD] = $previews;
    }
  }

  /**
   * Get the cache tags for a distribution metastore item.
   *
   * The datastore invalidates the distribution node's cache tags after an
   * import completes (via PostImport), so attaching them flips a "not yet
   * available" message to the data table without a manual cache rebuild.
   *
   * @param string $distribution_uuid
   *   The distribution's UUID.
   *
   * @return string[]
   *   Cache tags, or an empty array when the item cannot be loaded.
   */
  protected function distributionCacheTags(string $distribution_uuid): array {
    if ($distribution_uuid === '') {
      return [];
    }
    try {
      return $this->itemFactory->getInstance($distribution_uuid)->getCacheTags();
    }
    catch (\Throwable) {
      return [];
    }
  }

}
