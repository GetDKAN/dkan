<?php

declare(strict_types=1);

namespace Drupal\json_form_widget\Plugin\JsonFormOptionSource;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\json_form_widget\OptionSource\JsonFormOptionSourcePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Simple source plugin to get taxonomy terms as options.
 *
 * Use this as an example for building custom plugins to provide options for
 * list elements.
 *
 * @JsonFormOptionSource(
 *   id = "taxonomy",
 *   label = @Translation("Drupal Taxonomy"),
 *   description = @Translation("Get JSON options from a Drupal taxonomy.")
 * )
 */
class TaxonomySource extends JsonFormOptionSourcePluginBase implements ContainerFactoryPluginInterface {

  /**
   * The metastore service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructs a new TaxonomySource instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManager $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
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
  public function getOptions(array $config): array {
    $this->validateConfig($config);
    $vocabulary = $config['vocabulary'];
    /** @var \Drupal\taxonomy\TermStorageInterface $termStorage */
    $termStorage = $this->getEntityTypeManager()->getStorage('taxonomy_term');
    $terms = $termStorage->loadTree($vocabulary);
    $options = [];
    foreach ($terms as $term) {
      $options[$term->name] = $term->name;
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetType(array $config): string {
    $this->validateConfig($config);
    return 'taxonomy_term';
  }

  /**
   * Returns the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManager
   *   The entity type manager.
   */
  public function getEntityTypeManager(): EntityTypeManager {
    return $this->entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfig(array $config): bool {
    if (empty($config['vocabulary']) || !is_string($config['vocabulary'])) {
      throw new \InvalidArgumentException('Vocabulary must be specified in the configuration.');
    }
    return TRUE;
  }

}
