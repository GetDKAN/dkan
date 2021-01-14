<?php

namespace Drupal\common;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\datastore\Service as Datastore;
use Drupal\metastore\ResourceMapper;
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
   * Resource mapper.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected $resourceMapper;

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
   * Set the resource mapper.
   *
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   Resource mapper service.
   */
  public function setResourceMapper(ResourceMapper $resourceMapper) {
    $this->resourceMapper = $resourceMapper;
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

    if (!$this->metastore) {
      $info['notice'] = 'The DKAN Metastore module is not enabled, reducing the available information.';
      return $info;
    }

    $latestRevision = $this->storage->getNodeLatestRevision($uuid);
    if (!$latestRevision) {
      $info['notice'] = 'Not found.';
      return $info;
    }

    if (!$this->datastore) {
      $info['notice'] = 'The DKAN Datastore module is not enabled, reducing the available information.';
      return $info;
    }

    return $info;
  }

}
