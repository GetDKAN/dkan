<?php

namespace Drupal\harvest\Plugin\DkanApiDocs;

use Drupal\common\Plugin\DkanApiDocsBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\harvest\HarvestService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Docs plugin.
 *
 * @DkanApiDocs(
 *  id = "harvest_api_docs",
 *  description = "Harvest docs"
 * )
 */
class HarvestApiDocs extends DkanApiDocsBase {

  /**
   * The DKAN harvest service.
   *
   * @var Drupal\harvest\Service
   */
  private $harvest;

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
   * @param \Drupal\harvest\HarvestService $harvest
   *   The module handler service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    ModuleHandlerInterface $moduleHandler,
    TranslationInterface $stringTranslation,
    HarvestService $harvest
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $moduleHandler, $stringTranslation);
    $this->harvest = $harvest;
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
      $container->get('dkan.harvest.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function spec() {
    $spec = $this->getDoc('harvest');

    $ids = $this->harvest->getAllHarvestIds();
    $spec["components"]["parameters"]["harvestPlanId"]["example"] = isset($ids[0]) ? $ids[0] : "h1";
    $spec["components"]["parameters"]["harvestPlanIdQuery"]["example"] = isset($ids[0]) ? $ids[0] : "h1";

    return $spec;
  }

}
