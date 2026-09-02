<?php

namespace Drupal\dkan_datastore_preview\Element;

use Drupal\Core\Render\Attribute\RenderElement;
use Drupal\Core\Render\Element\RenderElementBase;
use Drupal\dkan_datastore_preview\DataSource\DataSourceInterface;

/**
 * Provides a render element for data preview tables.
 *
 * Usage:
 * @code
 * $build['preview'] = [
 *   '#type' => 'dkan_datastore_preview',
 *   '#resource_id' => $resource_id,
 *   '#pager_element' => 0,
 *   '#query_prefix' => 'dp0_',
 * ];
 * @endcode
 *
 * When several previews are rendered on one page, each must get a unique
 * #pager_element and #query_prefix so sorting and paging one table does not
 * affect the others.
 */
#[RenderElement('dkan_datastore_preview')]
class DataPreview extends RenderElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#resource_id' => '',
      '#data_source_instance' => NULL,
      '#columns' => [],
      '#default_page_size' => 25,
      '#default_sort' => NULL,
      '#default_sort_direction' => 'asc',
      '#conditions' => [],
      '#pager_element' => 0,
      '#query_prefix' => '',
      '#caption' => NULL,
      '#pre_render' => [
        [$class, 'preRender'],
      ],
    ];
  }

  /**
   * Pre-render callback: builds the data preview table.
   */
  public static function preRender(array $element): array {
    if (empty($element['#resource_id'])) {
      return $element;
    }

    $dataSource = $element['#data_source_instance'] ?? NULL;
    if (!$dataSource instanceof DataSourceInterface) {
      $dataSource = \Drupal::service('dkan.datastore_preview.data_source.database');
    }

    // Accept columns as a CSV string or an array.
    $columns = $element['#columns'];
    if (is_string($columns) && $columns !== '') {
      $columns = array_filter(array_map('trim', explode(',', $columns)));
    }
    elseif (!is_array($columns)) {
      $columns = [];
    }

    $options = [
      'columns' => $columns,
      'default_page_size' => (int) $element['#default_page_size'],
      'default_sort' => $element['#default_sort'] ?: NULL,
      'default_sort_direction' => $element['#default_sort_direction'],
      'conditions' => $element['#conditions'],
      'pager_element' => (int) $element['#pager_element'],
      'query_prefix' => (string) $element['#query_prefix'],
      'caption' => $element['#caption'] ?? NULL,
    ];

    try {
      /** @var \Drupal\dkan_datastore_preview\Service\DataPreviewBuilder $builder */
      $builder = \Drupal::service('dkan.datastore_preview.builder');
      $buildResult = $builder->build($dataSource, $element['#resource_id'], $options);

      if ($buildResult === NULL) {
        $element['message'] = static::statusMessage($element['#resource_id']);
        return $element;
      }
      $element = $buildResult + $element;
    }
    catch (\Throwable $e) {
      \Drupal::logger('dkan_datastore_preview')->warning('Data preview error for resource @id: @message', [
        '@id' => $element['#resource_id'],
        '@message' => $e->getMessage(),
      ]);
      $element['message'] = static::statusMessage($element['#resource_id']);
    }

    return $element;
  }

  /**
   * Build the import status message for an unavailable preview.
   */
  protected static function statusMessage(string $resource_id): array {
    /** @var \Drupal\dkan_datastore_preview\Service\ImportStatusMessage $statusMessage */
    $statusMessage = \Drupal::service('dkan.datastore_preview.import_status_message');
    return $statusMessage->build($resource_id);
  }

}
