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
 * @see \Drupal\datastore\DatastoreService
 */
class Service extends DatastoreService {

  /**
   * Constructor.
   *
   * @param \Drupal\datastore\Service\ResourceLocalizer $resourceLocalizer
   *   Resource localizer service.
   * @param \Drupal\datastore\Service\Factory\ImportFactoryInterface $importServiceFactory
   *   Import factory service.
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   Queue factory service.
   * @param \Drupal\common\Storage\JobStoreFactory $jobStoreFactory
   *   Jobstore factory service.
   * @param \Drupal\datastore\Service\Info\ImportInfoList $importInfoList
   *   Import info list service.
   * @param \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer $dictionaryEnforcer
   *   Dictionary Enforcer object.
   */
  public function __construct(ResourceLocalizer $resourceLocalizer, ImportFactoryInterface $importServiceFactory, QueueFactory $queue, JobStoreFactory $jobStoreFactory, ImportInfoList $importInfoList, DictionaryEnforcer $dictionaryEnforcer) {
    parent::__construct($resourceLocalizer, $importServiceFactory, $queue, $jobStoreFactory, $importInfoList, $dictionaryEnforcer);
    @trigger_error(__NAMESPACE__ . '\Service is deprecated. Use \Drupal\datastore\DatastoreService instead.', E_USER_DEPRECATED);
  }

}
