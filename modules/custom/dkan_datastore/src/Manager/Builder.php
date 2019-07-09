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

  /**
   *
   * @var \Dkan\Datastore\Resource
   */
  protected $resource;

  /**
   * Helper.
   *
   * @var \Drupal\dkan_datastore\Manager\Helper
   */
  protected $helper;

  /**
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   */
  public function __construct(Helper $helper) {
    $this->helper = $helper;
  }

  /**
   * Set resource.
   *
   * @param \Dkan\Datastore\Resource $resource
   *
   * @return static
   */
  public function setResource(Resource $resource) {
    $this->resource = $resource;
    return $this;
  }

  public function setResourceFromUUid(string $uuid) {
    $this->resource = $this->helper->getResourceFromEntity($uuid);
    return $this;
  }

  /**
   * Build datastore manager with set params, otherwise defaults.
   *
   * @param string $uuid
   *
   * @return \Dkan\Datastore\Manager\IManager
   */
  public function build(): Manager {

    $resource = $this->resource;

    if (!($resource instanceof Resource)) {
      throw new \Exception('Resource is invalid or uninitialized.');
    }

    return new Manager($resource, $this->helper->getDatabaseForResource($resource), Csv::getParser());
  }
}
