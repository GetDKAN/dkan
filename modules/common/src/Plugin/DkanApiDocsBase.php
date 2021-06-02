<?php

namespace Drupal\common\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for API Docs plugins.
 *
 * @see \Drupal\plugin_type_example\Annotation\Sandwich
 * @see \Drupal\plugin_type_example\SandwichInterface
 */
abstract class DkanApiDocsBase extends PluginBase implements DkanApiDocsInterface {

  /**
   * Retrieve the @description property from the annotation and return it.
   *
   * @return string
   */
  public function description() {
    return $this->pluginDefinition['description'];
  }

}
