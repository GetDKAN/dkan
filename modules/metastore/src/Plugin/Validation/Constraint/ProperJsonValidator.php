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
    $schema = 'dataset';
    if (is_object($items) && $type = $items->getParent()->getEntity()->get('field_data_type')->value) {
      $schema = $type;
    }
    foreach ($items as $item) {
      $info = $this->isProper($item->value, $schema);
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
   * @param string $schema
   *   Schema ID.
   */
  protected function isProper($value, $schema = 'dataset') {
    // @codeCoverageIgnoreStart
    /** @var SaeFactory $saeFactory */
    $saeFactory = \Drupal::service("dkan.metastore.sae_factory");

    /** @var Sae $engine */
    $engine = $saeFactory->getInstance($schema);

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
