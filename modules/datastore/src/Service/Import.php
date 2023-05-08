<?php

namespace Drupal\datastore\Service;

use CsvParser\Parser\Csv;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\common\EventDispatcherTrait;
use Drupal\common\LoggerTrait;
use Drupal\common\DataResource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Procrastinator\Result;

/**
 * Datastore import service.
 *
 * @deprecated
 * @see \Drupal\datastore\Service\ImportService
 */
class Import extends ImportService {

  /**
   * Create a resource service instance.
   *
   * @param \Drupal\common\DataResource $resource
   *   DKAN Resource.
   * @param \Drupal\common\Storage\JobStoreFactory $jobStoreFactory
   *   Jobstore factory.
   * @param \Drupal\datastore\Storage\DatabaseTableFactory $databaseTableFactory
   *   Database Table factory.
   */
  public function __construct(DataResource $resource, JobStoreFactory $jobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    parent::__construct($resource, $jobStoreFactory, $databaseTableFactory);
    @trigger_error(__NAMESPACE__ . '\Import is deprecated. Use \Drupal\datastore\Service\ImportService instead.', E_USER_DEPRECATED);
  }

}
