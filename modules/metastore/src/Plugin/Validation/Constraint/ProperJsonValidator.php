<?php

namespace Drupal\metastore\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\metastore\ValidMetadataFactory;
use InvalidArgumentException;
use OpisErrorPresenter\Implementation\MessageFormatterFactory;
use OpisErrorPresenter\Implementation\PresentedValidationErrorFactory;
use OpisErrorPresenter\Implementation\ValidationErrorPresenter;
use RootedData\Exception\ValidationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class.
 */
class ProperJsonValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Service dkan.metastore.valid_metadata.
   *
   * @var \Drupal\metastore\ValidMetadataFactory
   */
  protected $validMetadataFactory;

  /**
   * ValidationErrorPresenter.
   *
   * @var \OpisErrorPresenter\Implementation\ValidationErrorPresenter
   */
  protected $presenter;

  /**
   * ProperJsonValidator constructor.
   *
   * @param \Drupal\metastore\ValidMetadataFactory $valid_metadata_factory
   *   Service dkan.metastore.valid_metadata.
   */
  public function __construct(ValidMetadataFactory $valid_metadata_factory) {
    $this->validMetadataFactory = $valid_metadata_factory;
    $this->presenter = new ValidationErrorPresenter(
      new PresentedValidationErrorFactory(
        new MessageFormatterFactory()
      )
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.valid_metadata')
    );
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $schema = 'dataset';
    if (is_object($items) && $entity = $items->getParent()->getEntity()) {
      if ($type = $entity->get('field_data_type')->value) {
        $schema = $type;
      }
    }

    foreach ($items as $item) {
      $errors = [];
      try {
        $this->validMetadataFactory->get($schema, $item->value);
      }
      catch (ValidationException $e) {
        $errors = $this->getValidationErrorsMessages($e->getResult()->getErrors());
      }
      catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
      }
      if (!empty($errors)) {
        $this->addViolations($errors);
      }
    }
  }

  /**
   * Presents errors.
   *
   * @param array $errors
   *   Validation errors array.
   *
   * @return array
   *   Presented errors array.
   */
  private function getValidationErrorsMessages(array $errors): array {
    $presented = $this->presenter->present(...$errors);
    return array_map(
      function ($presented_error) {
        return $presented_error->message();
      },
      $presented
    );
  }

  /**
   * Add Violations.
   */
  private function addViolations($errors) {
    foreach ($errors as $error) {
      $this->context->addViolation($error);
    }
  }

}
