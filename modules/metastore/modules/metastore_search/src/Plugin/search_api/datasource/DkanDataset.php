<?php

namespace Drupal\metastore_search\Plugin\search_api\datasource;

/**
 * Represents a datasource which exposes DKAN dataset data.
 *
 * @SearchApiDatasource(
 *   id = "dkan_dataset",
 *   label = "DKAN Dataset",
 * )
 */
final class DkanDataset extends MetadataDatasourcePluginBase {

  /**
   * {@inheritDoc}
   */
  protected static function getDataType(): string {
      return 'dataset';
  }

}
