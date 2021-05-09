<?php

namespace Drupal\metastore\LifeCycle;

use Drupal\metastore\MetastoreItemInterface;

/**
 * [Description LifeCycleInerface]
 */
interface LifeCycleInterface {
  /**
   * @return [type]
   */
  public function load(MetastoreItemInterface $metastoreItem);

  /**
   * @return [type]
   */
  public function presave(MetastoreItemInterface $metastoreItem);

  /**
   * @return [type]
   */
  public function predelete(MetastoreItemInterface $metastoreItem);

}
