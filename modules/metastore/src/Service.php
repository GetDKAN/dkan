<?php

namespace Drupal\metastore;

use Drupal\metastore\Storage\DataFactory;
use RootedData\RootedJsonData;

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

  /**
   * {@inheritDoc}
   */
  public static function removeReferences(RootedJsonData $object, $prefix = "%"): RootedJsonData {
    @trigger_error(__NAMESPACE__ . '\Service is deprecated. Use \Drupal\metastore\MetastoreService instead.', E_USER_DEPRECATED);
    return parent::removeReferences($object, $prefix);
  }

  /**
   * {@inheritDoc}
   */
  public static function metadataHash($data) {
    @trigger_error(__NAMESPACE__ . '\Service is deprecated. Use \Drupal\metastore\MetastoreService instead.', E_USER_DEPRECATED);
    return parent::metadataHash($data);
  }

}
