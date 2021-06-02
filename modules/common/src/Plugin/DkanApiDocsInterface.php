<?php

namespace Drupal\common\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * An interface for all Dkan API Docs type plugins.
 */
interface DkanApiDocsInterface extends PluginInspectionInterface {

  public function spec();

}
