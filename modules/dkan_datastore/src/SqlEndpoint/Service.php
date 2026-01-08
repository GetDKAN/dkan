<?php

namespace Drupal\dkan_datastore\SqlEndpoint;

use Drupal\Core\Config\ConfigFactory;
use Drupal\dkan_datastore\DatastoreService;

/**
 * SQL endpoint service.
 *
 * @deprecated
 * @see \Drupal\dkan_datastore\SqlEndpoint\DatastoreSqlEndpointService
 */
class Service extends DatastoreSqlEndpointService {

  /**
   * Constructor, sets the datastoreService and configFactory properties.
   *
   * @param \Drupal\dkan_datastore\DatastoreService $datastoreService
   *   The datastore service object.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   An instance of Drupal's configFactory.
   */
  public function __construct(DatastoreService $datastoreService, ConfigFactory $configFactory) {
    parent::__construct($datastoreService, $configFactory);
    @trigger_error(__NAMESPACE__ . '\Service is deprecated. Use \Drupal\dkan_datastore\SqlEndpoint\DatastoreSqlEndpointService instead.', E_USER_DEPRECATED);
  }

}
