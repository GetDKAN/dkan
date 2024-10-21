<?php

namespace Drupal\datastore\Commands;

use Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer;
use Drupal\datastore\Service\ResourceProcessor\ResourceDoesNotHaveDictionary;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\metastore\ResourceMapper;
use Drush\Commands\DrushCommands;
use Drush\Psysh\DrushCommand;

class DictionaryCommands extends DrushCommands {

  /**
   * Resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected ResourceMapper $resourceMapper;

  /**
   * Dictionary enforcer service.
   *
   * @var \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer
   */
  protected DictionaryEnforcer $dictionaryEnforcer;

  /**
   * Data dictionary discovery service.
   *
   * @var \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface
   */
  protected DataDictionaryDiscoveryInterface $dataDictionaryDiscovery;

  /**
   * Constructor.
   */
  public function __construct(
    ResourceMapper $resourceMapper,
    DictionaryEnforcer $dictionaryEnforcer,
    DataDictionaryDiscoveryInterface $dataDictionaryDiscovery,
  ) {
    parent::__construct();
    $this->resourceMapper = $resourceMapper;
    $this->dictionaryEnforcer = $dictionaryEnforcer;
    $this->dataDictionaryDiscovery = $dataDictionaryDiscovery;
  }

  /**
   * Apply the configured data dictionary to a datastore resource.
   *
   * @param string $resource_identifier
   *   Datastore resource identifier, e.g., "b210fb966b5f68be0421b928631e5d51".
   *
   * @command dkan:datastore:apply-dictionary
   */
  public function applyDictionary(string $resource_identifier) {
    if ($this->dataDictionaryDiscovery->getDataDictionaryMode() === DataDictionaryDiscoveryInterface::MODE_NONE) {
      $this->logger()->notice('This site is not configured to use data dictionaries.');
      return DrushCommand::SUCCESS;
    }
    try {
      $this->dictionaryEnforcer->process(
        $this->resourceMapper->get($resource_identifier)
      );
    }
    catch (ResourceDoesNotHaveDictionary $exception) {
      // If there's no associated dictionary that's not really a problem.
      $this->logger()->notice($exception->getMessage());
      return DrushCommand::SUCCESS;
    }
    $this->logger()->notice('Applied dictionary for ' . $resource_identifier);
    return DrushCommand::SUCCESS;
  }

}
