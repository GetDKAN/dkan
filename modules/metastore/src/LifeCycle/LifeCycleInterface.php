<?php

namespace Drupal\metastore\LifeCycle;

use Drupal\metastore\NodeWrapper\Data;

/**
 * [Description LifeCycleInerface]
 */
interface LifeCycleInterface {
  /**
   * @return [type]
   */
  public function load(Data $metastoreItem);

  /**
   * @return [type]
   */
  public function presave(Data $metastoreItem);

  /**
   * @return [type]
   */
  public function predelete(Data $metastoreItem);

}
