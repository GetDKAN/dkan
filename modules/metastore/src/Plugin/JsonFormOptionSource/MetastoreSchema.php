<?php

declare(strict_types=1);

namespace Drupal\metastore\Plugin\JsonFormOptionSource;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\json_form_widget\OptionSource\JsonFormOptionSourcePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the json_form_option_source.
 *
 * @JsonFormOptionSource(
 *   id = "metastoreSchema",
 *   label = @Translation("Foo"),
 *   description = @Translation("Foo description.")
 * )
 */
final class MetastoreSchema extends JsonFormOptionSourcePluginBase implements ContainerFactoryPluginInterface {

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, \Drupal\metastore\MetastoreService $metastore) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->metastore = $metastore;
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
  public function getOptions(object $source, ?string $titleProperty): array {
    $options = [];
    $metastore_items = $this->metastore->getAll($source->metastoreSchema);
    foreach ($metastore_items as $item) {
      $item = json_decode((string) $item);
      $title = $this->metastoreOptionTitle($item, $titleProperty);
      $value = $this->metastoreOptionValue($item, $source, $titleProperty);
      $options[$value] = $title;
    }
    return $options;
  }

  /**
   * Determine the title for the select option.
   *
   * @param object|string $item
   *   Single item from Metastore::getAll()
   * @param string|false $titleProperty
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
   * @param object $source
   *   Source defintion from UI schema.
   * @param string|false $titleProperty
   *   Title property defined in UI schema.
   *
   * @return string
   *   String to be used as option value.
   */
  protected function metastoreOptionValue($item, object $source, $titleProperty): string {
    if (($source->returnValue ?? NULL) == 'url') {
      return 'dkan://metastore/schemas/' . $source->metastoreSchema . '/items/' . $item->identifier;
    }
    if ($titleProperty) {
      return is_object($item) ? $item->data->$titleProperty : $item;
    }
    return $item->data;
  }

}