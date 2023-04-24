<?php

namespace Drupal\datastore\SqlEndpoint;

use Drupal\Core\Config\ConfigFactory;
use Drupal\datastore\DatastoreService;

/**
 * SQL endpoint service.
 *
 * @deprecated
 * @see \Drupal\datastore\SqlEndpoint\DatastoreSqlEndpointService
 */
class Service extends DatastoreSqlEndpointService {

  /**
   * Constructor, sets the datastoreService and configFactory properties.
   *
   * @param \Drupal\datastore\DatastoreService $datastoreService
   *   The datastore service object.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   An instance of Drupal's configFactory.
   */
  public function __construct(DatastoreService $datastoreService, ConfigFactory $configFactory) {
    parent::__construct($datastoreService, $configFactory);
    @trigger_error(__NAMESPACE__ . '\Service is deprecated. Use \Drupal\datastore\SqlEndpoint\DatastoreSqlEndpointService instead.', E_USER_DEPRECATED);
  }

}
