<?php

namespace Drupal\datastore_data_preview\Element;

use Drupal\Core\Render\Attribute\RenderElement;
use Drupal\Core\Render\Element\RenderElementBase;
use Drupal\datastore_data_preview\DataSource\DataSourceInterface;

/**
 * Provides a render element for data preview tables.
 *
 * Usage:
 * @code
 * $build['preview'] = [
 *   '#type' => 'data_preview',
 *   '#resource_id' => $resource_id,
 * ];
 * @endcode
 */
#[RenderElement('data_preview')]
class DataPreview extends RenderElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#resource_id' => '',
      '#data_source' => 'database',
      '#data_source_instance' => NULL,
      '#columns' => [],
      '#default_page_size' => 25,
      '#default_sort' => NULL,
      '#default_sort_direction' => 'asc',
      '#conditions' => [],
      '#api_base_url' => '',
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

    // Resolve the data source.
    $dataSource = $element['#data_source_instance'] ?? NULL;
    $useExternalUrl = FALSE;

    if (!$dataSource instanceof DataSourceInterface) {
      $serviceId = $element['#data_source'] === 'api'
        ? 'dkan.data_preview.datasource.api'
        : 'dkan.data_preview.datasource.database';
      $dataSource = \Drupal::service($serviceId);

      // Set external base URL for API source if provided.
      if ($element['#data_source'] === 'api' && !empty($element['#api_base_url'])) {
        $dataSource->setBaseUrl($element['#api_base_url']);
        $useExternalUrl = TRUE;
      }
    }

    // Parse columns: accept both string and array.
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
    ];

    try {
      /** @var \Drupal\datastore_data_preview\Service\DataPreviewBuilder $builder */
      $builder = \Drupal::service('dkan.data_preview.builder');
      $buildResult = $builder->build($dataSource, $element['#resource_id'], $options);

      // Merge builder output into element, preserving element properties.
      $element = $buildResult + $element;
    }
    catch (\Exception $e) {
      \Drupal::logger('datastore_data_preview')->warning('Data preview render element error for resource @id: @message', [
        '@id' => $element['#resource_id'],
        '@message' => $e->getMessage(),
      ]);
    }
    finally {
      if ($useExternalUrl) {
        $dataSource->setBaseUrl(NULL);
      }
    }

    return $element;
  }

}
