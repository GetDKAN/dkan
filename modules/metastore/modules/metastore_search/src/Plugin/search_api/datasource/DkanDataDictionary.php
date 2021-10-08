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
final class DkanDataDictionary extends MetadataDatasourcePluginBase {

  /**
   * {@inheritDoc}
   */
  protected function getDataType(): string {
      return 'data-dictionary';
  }

}
