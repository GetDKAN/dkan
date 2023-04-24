<?php

namespace Drupal\metastore;

use Drupal\metastore\Storage\DataFactory;

/**
 * The metastore service.
 *
 * @deprecated
 * @see \Drupal\metastore\MetastoreService
 */
class Service extends MetastoreService {

  /**
   * Constructor.
   */
  public function __construct(SchemaRetriever $schemaRetriever, DataFactory $factory, ValidMetadataFactory $validMetadataFactory) {
    parent::__construct($schemaRetriever, $factory, $validMetadataFactory);
    @trigger_error(__NAMESPACE__ . '\Service is deprecated. Use \Drupal\metastore\MetastoreService instead.', E_USER_DEPRECATED);
  }

}
