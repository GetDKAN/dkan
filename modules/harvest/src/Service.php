<?php

namespace Drupal\harvest;

use Contracts\FactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\MetastoreService;

/**
 * Main DKAN Harvester service.
 *
 * @deprecated
 * @see \Drupal\harvest\HarvestService
 */
class Service extends HarvestService {

  /**
   * Constructor.
   */
  public function __construct(FactoryInterface $storeFactory, MetastoreService $metastore, EntityTypeManager $entityTypeManager) {
    parent::__construct($storeFactory, $metastore, $entityTypeManager);
    @trigger_error(__NAMESPACE__ . '\Service is deprecated. Use \Drupal\harvest\HarvestService instead.', E_USER_DEPRECATED);
  }

}
