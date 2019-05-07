<?php

namespace Drupal\dkan_data\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is proper JSON.
 *
 * @Constraint(
 *   id = "ProperJson",
 *   label = @Translation("Proper JSON", context = "Validation"),
 *   type = "string"
 * )
 */
class ProperJson extends Constraint {

  /**
   * The message that will be shown if the value is not an integer.
   */
  public $notProper = '%value is not proper JSON';

}
