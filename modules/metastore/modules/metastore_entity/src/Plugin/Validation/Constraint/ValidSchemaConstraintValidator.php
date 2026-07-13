<?php

namespace Drupal\my_module\Plugin\Validation\Constraint;

use Opis\JsonSchema\Schema;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ValidSchema constraint.
 *
 * @package Drupal\metastore_entity\Plugin\Validation\Constraint
 */
class ValidSchemaConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      try {
        Schema::fromJsonString($item->value);
      }
      catch (\Exception $e) {
        $this->context->addViolation($constraint->invalidSchema, ['%value' => $e->getMessage()]);
      }
    }
  }

}
