<?php

namespace Drupal\metastore_search\Plugin\search_api\datasource;

/**
 * Represents a datasource which exposes DKAN data-dictionary data.
 *
 * @SearchApiDatasource(
 *   id = "dkan_data_dictionary",
 *   label = "DKAN Data-Dictionary",
 * )
 */
final class DkanDatadictionary extends MetadataDatasourcePluginBase {

  /**
   * {@inheritDoc}
   */
  protected static function getDataType(): string {
      return 'data-dictionary';
  }

}
