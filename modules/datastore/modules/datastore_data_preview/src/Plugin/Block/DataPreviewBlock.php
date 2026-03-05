<?php

namespace Drupal\datastore_data_preview\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datastore_data_preview\DataSource\ApiDataSource;
use Drupal\datastore_data_preview\DataSource\DatabaseDataSource;
use Drupal\datastore_data_preview\Service\DataPreviewBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a data preview table block.
 *
 * @Block(
 *   id = "datastore_data_preview",
 *   admin_label = @Translation("Data Preview Table"),
 *   category = @Translation("DKAN"),
 * )
 */
class DataPreviewBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructor.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected DataPreviewBuilder $previewBuilder,
    protected DatabaseDataSource $databaseDataSource,
    protected ApiDataSource $apiDataSource,
    protected LoggerInterface $logger,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dkan.data_preview.builder'),
      $container->get('dkan.data_preview.datasource.database'),
      $container->get('dkan.data_preview.datasource.api'),
      $container->get('logger.factory')->get('datastore_data_preview'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'resource_id' => '',
      'data_source' => 'database',
      'columns' => '',
      'default_page_size' => 25,
      'default_sort' => '',
      'default_sort_direction' => 'asc',
      'api_base_url' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['resource_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Resource ID'),
      '#description' => $this->t('For Database source: resource identifier in "hash__version" format. For API source: distribution UUID.'),
      '#required' => TRUE,
      '#default_value' => $config['resource_id'],
    ];

    $form['data_source'] = [
      '#type' => 'select',
      '#title' => $this->t('Data source'),
      '#options' => [
        'database' => $this->t('Database (direct)'),
        'api' => $this->t('API (HTTP)'),
      ],
      '#default_value' => $config['data_source'],
    ];

    $form['api_base_url'] = [
      '#type' => 'url',
      '#title' => $this->t('API base URL'),
      '#description' => $this->t('Base URL of an external DKAN site (e.g. https://data.example.com). Leave empty to use the current site. The external site must allow anonymous API access.'),
      '#default_value' => $config['api_base_url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[data_source]"]' => ['value' => 'api'],
        ],
      ],
    ];

    $form['columns'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Columns'),
      '#description' => $this->t('Comma-separated column machine names to display. Leave empty for all columns.'),
      '#default_value' => $config['columns'],
    ];

    $form['default_page_size'] = [
      '#type' => 'select',
      '#title' => $this->t('Default page size'),
      '#options' => [
        10 => '10',
        25 => '25',
        50 => '50',
        100 => '100',
      ],
      '#default_value' => $config['default_page_size'],
    ];

    $form['default_sort'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default sort column'),
      '#description' => $this->t('Column machine name to sort by initially. Leave empty for default order.'),
      '#default_value' => $config['default_sort'],
    ];

    $form['default_sort_direction'] = [
      '#type' => 'select',
      '#title' => $this->t('Default sort direction'),
      '#options' => [
        'asc' => $this->t('Ascending'),
        'desc' => $this->t('Descending'),
      ],
      '#default_value' => $config['default_sort_direction'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $configKeys = [
      'resource_id',
      'data_source',
      'api_base_url',
      'columns',
      'default_page_size',
      'default_sort',
      'default_sort_direction',
    ];
    foreach ($configKeys as $key) {
      $this->configuration[$key] = $form_state->getValue($key);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    if (empty($config['resource_id'])) {
      return ['#markup' => $this->t('No resource ID configured.')];
    }

    $dataSource = $config['data_source'] === 'api'
      ? $this->apiDataSource
      : $this->databaseDataSource;

    // Set external base URL for API data source if configured.
    $useExternalUrl = $config['data_source'] === 'api' && !empty($config['api_base_url']);
    if ($useExternalUrl) {
      $this->apiDataSource->setBaseUrl($config['api_base_url']);
    }

    $columns = [];
    if (!empty($config['columns'])) {
      $columns = array_map('trim', explode(',', $config['columns']));
      $columns = array_filter($columns);
    }

    $options = [
      'columns' => $columns,
      'default_page_size' => (int) $config['default_page_size'],
      'default_sort' => $config['default_sort'] ?: NULL,
      'default_sort_direction' => $config['default_sort_direction'],
    ];

    try {
      return $this->previewBuilder->build($dataSource, $config['resource_id'], $options);
    }
    catch (\Exception $e) {
      $this->logger->error('Block preview error for @id: @message', [
        '@id' => $config['resource_id'],
        '@message' => $e->getMessage(),
      ]);
      return ['#markup' => $this->t('Unable to load data preview.')];
    }
    finally {
      if ($useExternalUrl) {
        $this->apiDataSource->setBaseUrl(NULL);
      }
    }
  }

}
