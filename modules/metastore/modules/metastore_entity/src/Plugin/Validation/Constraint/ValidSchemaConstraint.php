<?php

namespace Drupal\metastore_entity\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Schema validator for metastore schema entities.
 *
 * @Constraint(
 *   id = "PriceBelowMax",
 *   label = @Translation("Donation amount is below the maximum", context="Validation")
 * )
 */
class ValidSchemaConstraint extends Constraint {
  /**
   * A message.
   *
   * @var string
   */
  public $invalidSchema = 'The schema is not valid. %message';

}
