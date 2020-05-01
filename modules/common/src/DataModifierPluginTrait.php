<?php

namespace Drupal\common;

/**
 * Provides common functionality for alls data modifier plugins.
 */
trait DataModifierPluginTrait {

  /**
   * Data modifier plugin manager service.
   *
   * @var \Drupal\common\Plugin\DataModifierManager
   */
  private $pluginManager;

  /**
   * Instances of discovered data modifier plugins.
   *
   * @var array
   */
  private $plugins = [];

  /**
   * Discover data modifier plugins.
   *
   * @return array
   *   A list of discovered data modifier plugins.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function discover() {
    $plugins = [];
    foreach ($this->pluginManager->getDefinitions() as $definition) {
      $plugins[] = $this->pluginManager->createInstance($definition['id']);
    }
    return $plugins;
  }

}
