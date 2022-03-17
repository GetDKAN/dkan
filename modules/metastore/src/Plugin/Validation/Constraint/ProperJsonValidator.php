<?php

namespace Drupal\metastore\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\metastore\ValidMetadataFactory;
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
    $schema_id = $this->getSchemaIdFromEntity($items);
    foreach ($items as $item) {
      $errors = $this->doValidate($schema_id, $item);
      if (!empty($errors)) {
        $this->addViolations($errors);
      }
    }
  }

  /**
   * Gets schema id from the entity field_data_type field.
   *
   * @param object|mixed $items
   *   Entity.
   *
   * @return string
   *   Schema id.
   */
  private function getSchemaIdFromEntity($items): string {
    $schema = 'dataset';
    if (is_object($items) && $type = $items->getParent()->getEntity()->get('field_data_type')->value) {
      $schema = $type;
    }
    return $schema;
  }

  /**
   * A wrapper to call the validation service and collect errors.
   *
   * @param string $schema_id
   *   Schema id.
   * @param object $item
   *   JSON metadata value.
   *
   * @return array
   *   Errors array.
   *
   * @throws \RootedData\Exception\ValidationException
   * @throws \JsonPath\InvalidJsonException
   */
  private function doValidate(string $schema_id, $item): array {
    $errors = [];
    try {
      $this->validMetadataFactory->get($item->value, $schema_id);
    }
    catch (ValidationException $e) {
      $errors = $this->getValidationErrorsMessages($e->getResult()->getErrors());
    }
    catch (InvalidArgumentException $e) {
      $errors[] = $e->getMessage();
    }
    return $errors;
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
