<?php

namespace Drupal\dkan_datastore\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Dkan\Datastore\Manager\IManager;
use Dkan\Datastore\Resource;
use Dkan\Datastore\Manager\Info;
use Dkan\Datastore\Manager\InfoProvider;
use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\LockableBinStorage;
use Dkan\Datastore\Manager\Factory as DatastoreManagerFactory;
use Dkan\Datastore\Locker;
use Drupal\dkan_datastore\Storage\Database as DatastoreDatabase;

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
   * 
   * @param ContainerInterface $container
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * @todo make it so Info is overridable. seems like a good place to specify a different import handler.
   * @return \Dkan\Datastore\Manager\Info
   */
  protected function getInfo() {
    return new Info(SimpleImport::class, "simple_import", "SimpleImport");
  }


  /**
   *
   * @return InfoProvider
   */
  protected function getInfoProvider(): InfoProvider {
    $infoProvider = new \Dkan\Datastore\Manager\InfoProvider();
    $infoProvider->addInfo($this->getInfo());
    return $infoProvider;
  }

  /**
   *
   * @param string $uuid
   * @return \Drupal\Core\Entity\EntityInterface
   */
  protected function loadEntityByUuid(string $uuid) {
    return $this->container
        ->get('entity.repository')
        ->loadEntityByUuid('node', $uuid);
  }

  /**
   * Set Resource from file path and id.
   *
   * @param mixed $id identifier for file.
   * @param string $filePath
   * @return static
   */
  public function setResourceFromFilePath($id, $filePath) {
    $this->resource = new Resource($id, $filePath);
    return $this;
  }

  /**
   * Set resource.
   * 
   * @param Resource $resource
   * @return static
   */
  public function setResource(Resource $resource) {
    $this->resource = $resource;
    return $this;
  }

  /**
   *
   * @return Resource
   */
  protected function getResource(): ?Resource {
    return $this->resource;
  }

  /**
   *
   * @return LockableBinStorage
   */
  protected function getLockableStorage(): LockableBinStorage {
    return new LockableBinStorage(
      "dkan_datastore",
      new Locker("dkan_datastore"),
      $this->container->get('dkan_datastore.storage.variable')
    );
  }

  /**
   * Gets the Manager Factory.
   *
   * @param Resource $resource
   * @param InfoProvider $provider
   * @param LockableBinStorage $bin_storage
   * @param DatastoreDatabase $database
   * @return DatastoreManagerFactory
   */
  protected function getFactory(
    Resource $resource,
    InfoProvider $provider,
    LockableBinStorage $bin_storage,
    DatastoreDatabase $database
  ) {
    return new DatastoreManagerFactory($resource, $provider, $bin_storage, $database);
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
   * @todo seems li
   * @return \Dkan\Datastore\Manager\IManager
   */
  public function buildFromUuid($uuid): IManager {
    $dataset  = $this->loadEntityByUuid($uuid);
    $metadata = json_decode($dataset->field_json_metadata->value);

    $this->setResourceFromFilePath(
      $dataset->id(),
      $metadata->distribution[0]->downloadURL
    );

    return $this->build();
  }

  public function setResourceFromEntity($uuid) {
        $dataset  = $this->loadEntityByUuid($uuid);
    $metadata = json_decode($dataset->field_json_metadata->value);

    $this->setResourceFromFilePath(
      $dataset->id(),
      $metadata->distribution[0]->downloadURL
    );
  }

  /**
   * Build datastore manager with set params, otherwise defaults.
   *
   * @param string $uuid
   *
   * @return \Dkan\Datastore\Manager\IManager
   */
  public function build(): IManager {

    $resource    = $this->getResource();

    if (!($resource instanceof Resource)) {
      throw new \Exception('Resource is invalid or uninitialized.');
    }

    $provider    = $this->getInfoProvider();
    $bin_storage = $this->getLockableStorage();
    $database    = $this->getDatabase();

    $factory = $this->getFactory($resource, $provider, $bin_storage, $database);

    return $factory->get();
  }

}
