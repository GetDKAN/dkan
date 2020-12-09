<?php

namespace Drupal\metastore\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class.
 */
class ProperJsonValidator extends ConstraintValidator {

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      $info = $this->isProper($item->value);
      if (!$info['valid']) {
        $this->addViolations($info['errors']);
      }
    }
  }

  /**
   * Is proper JSON?
   *
   * @param string $value
   *   Value.
   */
  protected function isProper($value) {
    // @codeCoverageIgnoreStart
    /** @var SaeFactory $saeFactory */
    $saeFactory = \Drupal::service("metastore.sae_factory");

    /** @var Sae $engine */
    $engine = $saeFactory->getInstance('dataset');

    return $engine->validate($value);
    // @codeCoverageIgnoreEnd
  }

  /**
   * Add Violations.
   */
  private function addViolations($errors) {
    foreach ($errors as $error) {
      $this->context->addViolation($error['message']);
    }
  }

}
