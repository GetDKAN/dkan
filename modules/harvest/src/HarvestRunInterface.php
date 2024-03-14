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
   * @see \Harvest\Harvester::harvest()
   * @see \Harvest\ResultInterpreter
   */
  public function toResult(): array;

}
