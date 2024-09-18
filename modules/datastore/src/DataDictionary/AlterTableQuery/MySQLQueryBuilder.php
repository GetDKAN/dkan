<?php

namespace Drupal\datastore\DataDictionary\AlterTableQuery;

use Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryBuilderBase;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;
use Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery;

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

    $query->setCsvHeaderMode($this->configFactory->get('metastore.settings')->get('csv_headers_mode'));
    return $query;
  }

}
