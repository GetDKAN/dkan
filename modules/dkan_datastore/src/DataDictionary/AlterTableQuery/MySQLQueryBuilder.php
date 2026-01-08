<?php

namespace Drupal\dkan_datastore\DataDictionary\AlterTableQuery;

use Drupal\dkan_datastore\DataDictionary\AlterTableQueryBuilderInterface;
use Drupal\dkan_datastore\DataDictionary\AlterTableQueryBuilderBase;
use Drupal\dkan_datastore\DataDictionary\AlterTableQueryInterface;
use Drupal\dkan_datastore\DataDictionary\AlterTableQuery\MySQLQuery;

/**
 * MySQL alter table query builder.
 */
class MySQLQueryBuilder extends AlterTableQueryBuilderBase implements AlterTableQueryBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function getQuery(): AlterTableQueryInterface {

    $query = new MySQLQuery(
      $this->databaseConnectionFactory->getConnection(),
      $this->dateFormatConverter,
      $this->table,
      $this->fields,
      $this->indexes,
    );

    $query->setCsvHeaderMode($this->configFactory->get('dkan_metastore.settings')->get('csv_headers_mode'));
    return $query;
  }

}
