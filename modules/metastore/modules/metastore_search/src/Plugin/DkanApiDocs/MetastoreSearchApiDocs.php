<?php

namespace Drupal\metastore_search\Plugin\DkanApiDocs;

use Drupal\common\Plugin\DkanApiDocsBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\metastore_search\Search;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Docs plugin.
 *
 * @DkanApiDocs(
 *  id = "metastore_search_api_docs",
 *  description = "Search docs"
 * )
 *
 * @codeCoverageIgnore
 */
class MetastoreSearchApiDocs extends DkanApiDocsBase {

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The module handler service.
   * @param \Drupal\metastore_search\Search $metastoreSearch
   *   The metastore search service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    ModuleHandlerInterface $moduleHandler,
    TranslationInterface $stringTranslation,
    Search $metastoreSearch
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $moduleHandler, $stringTranslation);
    $this->metastoreSearch = $metastoreSearch;
  }

  /**
   * Container injection.
   *
   * @param \Drupal\common\Plugin\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   *
   * @return static
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('module_handler'),
      $container->get('string_translation'),
      $container->get('dkan.metastore_search.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function spec() {
    $spec = $this->getDoc('metastore_search');

    $propList = $this->t('Available properties: %list', [
      '%list' => implode(", ", $this->getIndexedFields()),
    ]);
    $spec['paths']['/api/1/search']['get']['parameters'][3]['description'] .= " $propList";

    $facetTypes = $this->getFacetTypes();
    foreach ($facetTypes as $type => $example) {
      $spec['paths']['/api/1/search']['get']['parameters'][] = [
        'name' => $type,
        'in' => 'query',
        'description' => $this->t("Filter results using %facet facet.", ['%facet' => $type]),
        'schema' => ['type' => 'string'],
        'example' => $example,
        'style' => 'form',
      ];
    }
    return $spec;
  }

  /**
   * Get names of availale facets.
   *
   * @return array
   *   Array of facet names.
   */
  private function getFacetTypes(): array {
    $facets = $this->metastoreSearch->facets();
    $types = [];
    foreach ($facets as $facet) {
      $types[$facet->type] = $facet->name;
    }
    return $types;
  }

  /**
   * Get a list of indexed fields for sort enum.
   *
   * @return array
   *   All fields indexed in search API.
   *
   * @todo Add this function directly to search service so no Search API specific.
   */
  private function getIndexedFields(): array {
    $index = $this->metastoreSearch->getSearchIndex();
    $fields = array_keys($index->getFields());
    $fields[] = 'search_api_relevance';
    return $fields;
  }

}
