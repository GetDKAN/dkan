<?php

declare(strict_types = 1);

namespace Drupal\harvest;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a harvest run entity type.
 */
interface HarvestRunInterface extends ContentEntityInterface {

  /**
   * Assemble the data into a result array, as from Harvester::harvest().
   *
   * This exists for BC with \Harvest\ResultInterpreter.
   *
   * @return array
   *   Result array as would be returned from \Harvest\Harvester::harvest()
   *
   * @internal
   *
   * @see \Drupal\harvest\Harvester::harvest()
   * @see \Drupal\harvest\ResultInterpreter
   *
   * @todo Refactor other areas of the harvest system so they know how to deal
   *   with the entity rather than the results array.
   */
  public function toResult(): array;

}
