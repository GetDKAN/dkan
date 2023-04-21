<?php

namespace Drupal\datastore;

use Drupal\common\Storage\JobStoreFactory;
use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Service\Factory\ImportFactoryInterface;
use Drupal\datastore\Service\Info\ImportInfoList;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer;

/**
 * Main services for the datastore.
 *
 * @deprecated
 */
class Service extends DatastoreService {

  public function __construct(ResourceLocalizer $resourceLocalizer, ImportFactoryInterface $importServiceFactory, QueueFactory $queue, JobStoreFactory $jobStoreFactory, ImportInfoList $importInfoList, DictionaryEnforcer $dictionaryEnforcer) {
    parent::__construct($resourceLocalizer, $importServiceFactory, $queue, $jobStoreFactory, $importInfoList, $dictionaryEnforcer);
    @trigger_error(__NAMESPACE__ . '\Service is deprecated. Use \Drupal\datastore\DatastoreService instead.', E_USER_DEPRECATED);
  }

}
