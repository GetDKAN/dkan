<?php

namespace Drupal\dkan_datastore\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Dkan\Datastore\Manager\IManager;
use Dkan\Datastore\Resource;
use Dkan\Datastore\Manager\Info;
use Dkan\Datastore\Manager\InfoProvider;
use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\LockableBinStorage;
use Drupal\dkan_datastore\Manager\DatastoreManagerBuilderHelper as Helper;

/**
 * DatastoreManagerBuilder.
 *
 * This is a single use builder class to make.
 */
class DatastoreManagerBuilder {

  protected $container;

  /**
   *
   * @var \Dkan\Datastore\Resource
   */
  protected $resource;

  /**
   * Helper.
   *
   * @var \Drupal\dkan_datastore\Manager\DatastoreManagerBuilderHelper
   */
  protected $helper;

  /**
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
    $this->helper    = $this->container
      ->get('dkan_datastore.manager.datastore_manager_builder_helper');
  }

  /**
   * @todo make it so Info is overridable. seems like a good place to specify a different import handler.
   * @return \Dkan\Datastore\Manager\Info
   */
  protected function getInfo(): Info {
    return $this->helper->newInfo(SimpleImport::class, "simple_import", "SimpleImport");
  }

  /**
   *
   * @return \Dkan\Datastore\Manager\InfoProvider
   */
  protected function getInfoProvider(): InfoProvider {
    $infoProvider = $this->helper->newInfoProvider();
    $infoProvider->addInfo($this->getInfo());
    return $infoProvider;
  }

  /**
   * Set Resource from file path and id.
   *
   * @param mixed $id
   *   identifier for file.
   * @param string $filePath
   *
   * @return static
   */
  public function setResourceFromFilePath($id, $filePath) {
    $this->setResource(
      $this->helper
        ->newResourceFromFilePath($id, $filePath)
    );
    return $this;
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

  /**
   *
   * @return \Dkan\Datastore\Resource
   */
  protected function getResource(): ?Resource {
    return $this->resource;
  }

  /**
   *
   * @return \Dkan\Datastore\LockableBinStorage
   */
  protected function getLockableStorage(): LockableBinStorage {
    $name = 'dkan_datastore';
    return $this->helper->newLockableStorage(
        $name,
        $this->helper->newLocker($name),
        $this->container->get('dkan_datastore.storage.variable')
    );
  }

  /**
   * @return \Drupal\dkan_datastore\Storage\Database
   */
  protected function getDatabase() {
    return $this->container
      ->get('dkan_datastore.database');
  }

  /**
   * Build datastore manager using uuid to load some information.
   *
   * @param string $uuid
   *
   * @todo seems li
   *
   * @return \Dkan\Datastore\Manager\IManager
   */
  public function buildFromUuid($uuid): IManager {

    $this->setResource(
      $this->helper
        ->newResourceFromEntity($uuid)
    );

    return $this->build();
  }

  /**
   * Build datastore manager with set params, otherwise defaults.
   *
   * @param string $uuid
   *
   * @return \Dkan\Datastore\Manager\IManager
   */
  public function build(): IManager {

    $resource = $this->getResource();

    if (!($resource instanceof Resource)) {
      throw new \Exception('Resource is invalid or uninitialized.');
    }

    $provider    = $this->getInfoProvider();
    $bin_storage = $this->getLockableStorage();
    $database    = $this->getDatabase();

    $factory = $this->helper
      ->newDatastoreFactory($resource, $provider, $bin_storage, $database);

    return $factory->get();
  }

}
