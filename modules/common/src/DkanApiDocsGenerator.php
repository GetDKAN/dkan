<?php

namespace Drupal\common;

use Drupal\common\Util\ApiDocsPathModifier;
use Drupal\Core\Site\Settings;
use Drupal\common\Plugin\DkanApiDocsPluginManager;
use Drupal\common\Plugin\OpenApiSpec;

/**
 * Generator for DKAN OpenApi docs.
 */
class DkanApiDocsGenerator {

  /**
   * Docs manager.
   *
   * @var \Drupal\common\Plugin\DkanApiDocsPluginManager
   */
  protected DkanApiDocsPluginManager $docManager;

  /**
   * Site settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected Settings $settings;

  /**
   * Constructor.
   *
   * @param \Drupal\common\Plugin\DkanApiDocsPluginManager $dkanApiDocsPluginManager
   *   The DKAN API Docs Plugin Manager service.
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal settings service.
   */
  public function __construct(DkanApiDocsPluginManager $dkanApiDocsPluginManager, Settings $settings) {
    $this->docManager = $dkanApiDocsPluginManager;
    $this->settings = $settings;
  }

  /**
   * Generate a spec from plugins.
   *
   * @param array $plugins
   *   Array of plugin ids to include. Will use all if empty.
   *
   * @return Drupal\common\Plugin\OpenApiSpec
   *   Valid openapi spec.
   */
  public function buildSpec(array $plugins = []) {
    $docPluginDefinitions = $this->docManager->getDefinitions();
    $spec = [];
    if (!empty($plugins)) {
      $this->filterPluginDefinitions($docPluginDefinitions, $plugins);
    }
    foreach ($docPluginDefinitions as $definition) {
      $pluginSpec = $this->docManager->createInstance($definition['id'])->spec();
      $spec = array_merge_recursive($spec, $pluginSpec);
    }

    // Add 'dkan_api_base' setting to prefix the API path if your routing
    // does not start with your site's root URL (e.g. "data" for API paths
    // to use "/data/api/1").
    if ($dkanApiBase = $this->settings->get('dkan_api_base')) {
      $spec = ApiDocsPathModifier::prepend($spec, $dkanApiBase);
    }

    return new OpenApiSpec(json_encode($spec));
  }

  /**
   * Filter the gathered definitions.
   *
   * @param array $definitions
   *   Output of plugin manager getDefinitions().
   * @param array $plugins
   *   Desired plugins, filter out all others except common_dkan_api_docs.
   */
  private function filterPluginDefinitions(array &$definitions, array $plugins) {
    // We always include common_dkan_api_docs.
    $plugins[] = 'common_dkan_api_docs';

    $definitions = array_filter($definitions, function ($key) use ($plugins) {
      if (in_array($key, $plugins)) {
        return TRUE;
      }
      return FALSE;
    }, ARRAY_FILTER_USE_KEY);
  }

}
