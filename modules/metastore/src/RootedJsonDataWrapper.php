<?php

namespace Drupal\metastore;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use OpisErrorPresenter\Implementation\MessageFormatterFactory;
use OpisErrorPresenter\Implementation\PresentedValidationErrorFactory;
use OpisErrorPresenter\Implementation\ValidationErrorPresenter;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service.
 */
class RootedJsonDataWrapper implements ContainerInjectionInterface {

  /**
   * Schema retriever.
   *
   * @var \Drupal\metastore\SchemaRetriever
   */
  private $schemaRetriever;

  /**
   * Inherited.
   *
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('metastore.schema_retriever'),
    );
  }

  /**
   * RootedJsonDataWrapper constructor.
   *
   * @param \Drupal\metastore\SchemaRetriever $schemaRetriever
   *   dkan.metastore.schema_retriever service.
   */
  public function __construct(SchemaRetriever $schemaRetriever) {
    $this->schemaRetriever = $schemaRetriever;
  }

  /**
   * Converts Json string into RootedJsonData object.
   *
   * @param \Drupal\metastore\string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param \Drupal\metastore\string $json_string
   *   Json string.
   *
   * @return \RootedData\RootedJsonData
   *   RootedJsonData object.
   *
   * @throws \JsonPath\InvalidJsonException
   */
  public function createRootedJsonData(string $schema_id, string $json_string): RootedJsonData {
    $schema = $this->schemaRetriever->retrieve($schema_id);
    return new RootedJsonData($json_string, $schema);
  }

  /**
   * Get validation result.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $json_data
   *   Json payload.
   *
   * @return array
   *   The validation result.
   *
   * @throws \Exception
   */
  public function getValidationInfo(string $schema_id, string $json_string) {
    $schema = $this->schemaRetriever->retrieve($schema_id);
    $result = RootedJsonData::validate($json_string, $schema);
    $presenter = new ValidationErrorPresenter(
      new PresentedValidationErrorFactory(
        new MessageFormatterFactory()
      )
    );
    $presented = $presenter->present(...$result->getErrors());
    return ['valid' => empty($presented), 'errors' => $presented];
  }

}
