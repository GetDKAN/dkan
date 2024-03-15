<?php

declare(strict_types = 1);

namespace Drupal\harvest;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a harvest run entity type.
 */
interface HarvestRunInterface extends ContentEntityInterface, \JsonSerializable {

  /**
   * Get the run status array.
   *
   * @return object
   */
  public function getRun(): mixed;

}
