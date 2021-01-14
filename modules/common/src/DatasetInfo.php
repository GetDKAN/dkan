<?php

namespace Drupal\common;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\datastore\Service as Datastore;
use Drupal\metastore\Service as Metastore;
use Drupal\metastore\Storage\DataFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DatasetInfo
 *
 * @package Drupal\common
 */
class DatasetInfo implements ContainerInjectionInterface {

  protected $moduleHandler;

  /**
   * Metastore storage.
   *
   * @var \Drupal\metastore\Storage\Data
   */
  protected $storage;

  /**
   * Metastore.
   *
   * @var Metastore
   */
  protected $metastore;

  /**
   * Datastore.
   *
   * @var Datastore
   */
  protected $datastore;

  /**
   * Set storage.
   *
   * @param \Drupal\metastore\Storage\DataFactory $dataFactory
   *   Metastore's data factory.
   */
  public function setStorage(DataFactory $dataFactory) {
    $this->storage = $dataFactory->getInstance('dataset');
  }

  /**
   * Set metastore.
   *
   * @param \Drupal\metastore\Service $metastore
   *   Metastore service.
   */
  public function setMetastore(Metastore $metastore) {
    $this->metastore = $metastore;
  }

  /**
   * Set datastore.
   *
   * @param \Drupal\datastore\Service $datastore
   *   Datastore service.
   */
  public function setDatastore(Datastore $datastore) {
    $this->datastore = $datastore;
  }

  /**
   * DatasetInfo constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler service.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
    );
  }

  /**
   * Gather info about a dataset from its identifier.
   *
   * @param string $uuid
   *   Dataset identifier.
   *
   * @return array
   *   Dataset information array.
   */
  public function gather(string $uuid) {
    $info['uuid'] = $uuid;

    return $info;
  }

}
