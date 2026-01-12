<?php

namespace Drupal\dkan_harvest;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a harvest plan entity type.
 */
interface HarvestPlanInterface extends ContentEntityInterface, \JsonSerializable {

  /**
   * Get the harvest plan as an object, ready to be JSON-encoded.
   *
   * See components.schemas.harvestPlan within
   * modules/dkan_harvest/docs/openapi_spec.json for the schema of a plan.
   *
   * @return object
   *   The harvest plan as an object.
   */
  public function getPlan(): object;

}
