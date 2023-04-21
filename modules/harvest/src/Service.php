<?php

namespace Drupal\datastore;

use Contracts\FactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\harvest\HarvestService;
use Drupal\metastore\MetastoreService as Metastore;

/**
 * Main DKAN Harvester service.
 *
 * @deprecated
 */
class Service extends HarvestService {

  /**
   * Constructor.
   */
  public function __construct(FactoryInterface $storeFactory, Metastore $metastore, EntityTypeManager $entityTypeManager) {
    parent::__construct($storeFactory, $metastore, $entityTypeManager);
    @trigger_error(__NAMESPACE__ . '\Service is deprecated. Use \Drupal\harvest\HarvestService instead.', E_USER_DEPRECATED);
  }

}
