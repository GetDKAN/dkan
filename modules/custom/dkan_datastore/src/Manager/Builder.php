<?php

namespace Drupal\dkan_datastore\Manager;

use CsvParser\Parser\Csv;
use Dkan\Datastore\Manager;
use Dkan\Datastore\Resource;

/**
 * Builder.
 *
 * This is a single use builder class to make.
 */
class Builder {

  protected $resource;

  /**
   * Helper.
   *
   * @var \Drupal\dkan_datastore\Manager\Helper
   */
  protected $helper;

  /**
   * Constructs a builder.
   *
   * @param Drupal\dkan_datastore\Manager\Helper $helper
   *   Helper object.
   */
  public function __construct(Helper $helper) {
    $this->helper = $helper;
  }

  /**
   * Set resource.
   *
   * @param \Dkan\Datastore\Resource $resource
   *   Defines a ersource object to use to build the datastore importer.
   *
   * @return static
   */
  public function setResource(Resource $resource) {
    $this->resource = $resource;
    return $this;
  }

  /**
   * Set the resource object using only a node UUID.
   *
   * @param string $uuid
   *   The UUID for a resource node.
   */
  public function setResourceFromUuid(string $uuid) {
    $this->resource = $this->helper->getResourceFromEntity($uuid);
    return $this;
  }

  /**
   * Build datastore manager with set params, otherwise defaults.
   *
   * @return \Dkan\Datastore\Manager\IManager
   *   A built manager object for the datastore.
   */
  public function build(): Manager {

    $resource = $this->resource;

    if (!($resource instanceof Resource)) {
      throw new \Exception('Resource is invalid or uninitialized.');
    }

    return new Manager($resource, $this->helper->getDatabaseForResource($resource), Csv::getParser());
  }

}
