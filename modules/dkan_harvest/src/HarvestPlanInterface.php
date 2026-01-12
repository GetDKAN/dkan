<?php

namespace Drupal\harvest;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a harvest plan entity type.
 */
interface HarvestPlanInterface extends ContentEntityInterface, \JsonSerializable {

  /**
   * Get the harvest plan as an object, ready to be JSON-encoded.
   *
   * See components.schemas.harvestPlan within
   * modules/harvest/docs/openapi_spec.json for the schema of a plan.
   *
   * @return object
   *   The harvest plan as an object.
   */
  public function getPlan(): object;

}
