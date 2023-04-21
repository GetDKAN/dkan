<?php

namespace Drupal\datastore\SqlEndpoint;

use Drupal\Core\Config\ConfigFactory;
use Drupal\datastore\DatastoreService;

/**
 * SQL endpoint service.
 *
 * @deprecated
 */
class Service extends DatastoreSqlEndpointService {

  public function __construct(DatastoreService $datastoreService, ConfigFactory $configFactory) {
    parent::__construct($datastoreService, $configFactory);
    @trigger_error(__NAMESPACE__ . '\Service is deprecated. Use \Drupal\datastore\SqlEndpoint\DatastoreSqlEndpointService instead.', E_USER_DEPRECATED);
  }

}
