<?php

namespace Drupal\dkan_metastore\Plugin\Validation\Constraint;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dkan_metastore\ValidMetadataFactory;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use RootedData\Exception\ValidationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class.
 */
class ProperJsonValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Module configuration.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $metastoreConfig;

  /**
   * Service dkan.metastore.valid_metadata.
   *
   * @var \Drupal\dkan_metastore\ValidMetadataFactory
   */
  protected $validMetadataFactory;

  /**
   * ProperJsonValidator constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\dkan_metastore\ValidMetadataFactory $valid_metadata_factory
   *   Service dkan.metastore.valid_metadata.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ValidMetadataFactory $valid_metadata_factory) {
    $this->metastoreConfig = $config_factory->get('metastore.settings');
    $this->validMetadataFactory = $valid_metadata_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('dkan.metastore.valid_metadata'),
    );
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint): void {
    // Bypass validation if the setting to disable it is enabled.
    if (!$this->metastoreConfig->get('disable_json_validation')) {
      $this->validateItems($items);
    }
  }

  /**
   * Validates items and adds violations if needed.
   *
   * @param object|mixed $items
   *   Items to validate.
   */
  protected function validateItems($items): void {
    $schema_id = $this->getSchemaIdFromEntity($items);
    // Loop through items, validating each and collecting errors.
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
      $result = $e->getResult();
      if ($result->hasError()) {
        $errors = $this->getValidationErrorsMessages($result->error());
      }
    }
    catch (\InvalidArgumentException $e) {
      $errors[] = $e->getMessage();
    }
    return $errors;
  }

  /**
   * Flatten the validation error tree into one message per leaf.
   *
   * @param \Opis\JsonSchema\Errors\ValidationError $error
   *   Root validation error to flatten.
   *
   * @return array
   *   Per-leaf message strings ready to pass to addViolation().
   */
  private function getValidationErrorsMessages(ValidationError $error): array {
    $formatter = new ErrorFormatter();
    $messages = [];
    foreach ($formatter->formatKeyed($error) as $errs) {
      foreach ($errs as $msg) {
        $messages[] = $msg;
      }
    }
    return $messages;
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
