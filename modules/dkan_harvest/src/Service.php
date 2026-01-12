<?php

namespace Drupal\dkan_harvest;

use Contracts\FactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\dkan_metastore\MetastoreService;

/**
 * Main DKAN Harvester service.
 *
 * @deprecated
 * @see \Drupal\dkan_harvest\HarvestService
 */
class Service extends HarvestService {

  /**
   * Constructor.
   */
  public function __construct(FactoryInterface $storeFactory, MetastoreService $metastore, EntityTypeManager $entityTypeManager) {
    parent::__construct($storeFactory, $metastore, $entityTypeManager);
    @trigger_error(__NAMESPACE__ . '\Service is deprecated. Use \Drupal\dkan_harvest\HarvestService instead.', E_USER_DEPRECATED);
  }

}
