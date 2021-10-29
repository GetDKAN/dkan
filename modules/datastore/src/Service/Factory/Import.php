<?php

namespace Drupal\datastore\Service\Factory;

use Drupal\datastore\SchemaTranslatorInterface;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Service\Import as Instance;
use Drupal\common\Storage\JobStoreFactory;

/**
 * Create an importer object for a given resource.
 *
 * @codeCoverageIgnore
 */
class Import implements ImportFactoryInterface {
  private $jobStoreFactory;
  private $databaseTableFactory;
  private $schemaTranslator;

  private $services = [];

  /**
   * Constructor.
   */
  public function __construct(JobStoreFactory $jobStoreFactory, DatabaseTableFactory $databaseTableFactory, SchemaTranslatorInterface $schemaTranslator) {
    $this->jobStoreFactory = $jobStoreFactory;
    $this->databaseTableFactory = $databaseTableFactory;
    $this->schemaTranslator = $schemaTranslator;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getInstance(string $identifier, array $config = []) {

    if (!isset($config['resource'])) {
      throw new \Exception("config['resource'] is required");
    }

    $resource = $config['resource'];

    if (!isset($this->services[$identifier])) {
      $this->services[$identifier] = new Instance($resource, $this->jobStoreFactory, $this->databaseTableFactory, $this->schemaTranslator);
    }

    return $this->services[$identifier];
  }

}
