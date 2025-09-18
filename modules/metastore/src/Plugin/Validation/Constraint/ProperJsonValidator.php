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
      $errors = $e->getResult()->getErrors();
    }
    catch (InvalidArgumentException $e) {
      $errors[] = $e->getMessage();
    }
    return $errors;
  }

  /**
   * Add Violations with field context.
   */
  private function addViolations($errors) {
    foreach ($errors as $error) {
      // Extract field information from error pointer
      $field_name = $this->extractFieldFromPointer($error);
      $message = is_array($error) ? $error['message'] : $this->presenter->present($error)[0]->message();
      
      // Add violation with field context
      $violation = $this->context->buildViolation($message);
      
      // Store field information in the violation for later use
      if ($field_name) {
        $violation->setParameter('json_field_pointer', json_encode($field_name));
      }
      
      $violation->addViolation();
    }
  }

  /**
   * Extract field name from JSON Schema error pointer.
   */
  private function extractFieldFromPointer($error) {
    if (!is_object($error)) {
      return null;
    }
    
    // Handle required field errors - field name is in keywordArgs['missing']
    if (method_exists($error, 'keywordArgs')) {
      $keywordArgs = $error->keywordArgs();
      if (isset($keywordArgs['missing'])) {
        return [$keywordArgs['missing']];
      }
    }
    
    // Handle other validation errors - field name is in dataPointer
    if (method_exists($error, 'dataPointer')) {
      $pointer = $error->dataPointer();
      if (is_array($pointer) && !empty($pointer)) {
        // If dataPointer contains just one index, return it as an array
        if (count($pointer) === 1) {
          return $pointer;
        }
        
        // For array fields with multiple indices, we need to add the field name both before and after numeric indices
        $processed_pointer = [];
        
        foreach ($pointer as $index => $part) {
          // If this is the first part and it's a field name, add it twice
          if ($index === 0 && !is_numeric($part)) {
            $processed_pointer[] = $part; // First occurrence
            $processed_pointer[] = $part; // Second occurrence for array structure
          }
          // If this is a numeric index, add it and then add the field name after
          elseif (is_numeric($part)) {
            $processed_pointer[] = $part;
            // Find the field name (first non-numeric part)
            $field_name = null;
            foreach ($pointer as $p) {
              if (!is_numeric($p)) {
                $field_name = $p;
                break;
              }
            }
            if ($field_name) {
              $processed_pointer[] = $field_name;
            }
          }
          // For other parts (like 'privateEmail'), just add them
          else {
            $processed_pointer[] = $part;
          }
        }
        
        return $processed_pointer;
      }
    }
    
    return null;
  }

}
