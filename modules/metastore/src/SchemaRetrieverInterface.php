<?php

namespace Drupal\metastore;

use Contracts\RetrieverInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * [Description SchemaRetrieverInterface]
 */
interface SchemaRetrieverInterface extends RetrieverInterface, ContainerInjectionInterface {

  /**
   * @return array
   */
  public function getAllIds();

}
