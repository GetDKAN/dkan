<?php

namespace Drupal\datastore\DataDictionary\AlterTableQuery;

use Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryBuilderBase;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;

/**
 * MySQL alter table query builder.
 */
class MySQLQueryBuilder extends AlterTableQueryBuilderBase implements AlterTableQueryBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function getQuery(): AlterTableQueryInterface {
    return new MySQLQuery(
      $this->databaseConnectionFactory->getConnection(),
      $this->dateFormatConverter,
      $this->table,
      $this->fields,
      $this->indexes,
    );
  }

}
