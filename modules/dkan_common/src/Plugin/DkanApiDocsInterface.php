<?php

namespace Drupal\common\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * An interface for all Dkan API Docs type plugins.
 */
interface DkanApiDocsInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * Get the spec.
   *
   * @return array
   *   Spec.
   */
  public function spec();

}
