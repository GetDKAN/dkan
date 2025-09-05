<?php

declare(strict_types=1);

namespace Drupal\metastore\Plugin\JsonFormOptionSource;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\json_form_widget\OptionSource\JsonFormOptionSourcePluginBase;
use Drupal\metastore\MetastoreService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the json_form_option_source.
 *
 * @JsonFormOptionSource(
 *   id = "metastoreSchema",
 *   label = @Translation("DKAN Metastore Option Source"),
 *   description = @Translation("Provides options to JSON Forms based on a DKAN schema.")
 * )
 */
class MetastoreSchema extends JsonFormOptionSourcePluginBase implements ContainerFactoryPluginInterface {

  /**
   * The metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  protected $metastore;

  /**
   * Constructs a new MetastoreSchema instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\metastore\MetastoreService $metastore
   *   The metastore service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MetastoreService $metastore,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->metastore = $metastore;
  }

  /**
   * Return the metastore service.
   */
  public function getMetastore(): MetastoreService {
    return $this->metastore;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dkan.metastore.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions(array $config): array {
    $this->validateConfig($config);
    $options = [];
    $metastore_items = $this->getMetastore()->getAll($config['schema']);
    foreach ($metastore_items as $item) {
      $item = json_decode((string) $item);
      $title = $this->metastoreOptionTitle($item, $config['titleProperty'] ?? NULL);
      $value = $this->metastoreOptionValue($item, $config);
      $options[$value] = $title;
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetType(array $config): string {
    return 'node';
  }

  /**
   * Determine the title for the select option.
   *
   * @param object|string $item
   *   Single item from Metastore::getAll()
   * @param string|null $titleProperty
   *   Title property defined in UI schema.
   *
   * @return string
   *   String to be used in title.
   */
  protected function metastoreOptionTitle($item, $titleProperty): string {
    if ($titleProperty) {
      return is_object($item) ? $item->data->$titleProperty : $item;
    }
    return $item->data;
  }

  /**
   * Determine the value for the select option.
   *
   * @param object|string $item
   *   Single item from Metastore::getAll()
   * @param array $config
   *   Configuration array containing the schema and other options.
   *
   * @return string
   *   String to be used as option value.
   */
  protected function metastoreOptionValue($item, $config): string {
    if (($config['returnValue'] ?? NULL) == 'url') {
      return 'dkan://metastore/schemas/' . $config['schema'] . '/items/' . $item->identifier;
    }
    if ($config['titleProperty'] ?? FALSE) {
      return is_object($item) ? $item->data->{$config['titleProperty']} : $item;
    }
    return $item->data;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfig(array $config): bool {
    // Validate config properties using match expressions.
    match (TRUE) {
      empty($config['schema']) =>
        throw new \InvalidArgumentException('The "schema" config property is required.'),
      isset($config['titleProperty']) && !is_string($config['titleProperty']) =>
        throw new \InvalidArgumentException("The \"titleProperty\" config property must be a string."),
      isset($config['returnValue']) && !is_string($config['returnValue']) =>
        throw new \InvalidArgumentException("The \"returnValue\" config property must be a string."),
      default => NULL,
    };
    return TRUE;
  }

}
