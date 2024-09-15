<?php

namespace Drupal\datastore_mysql_import\DataDictionary\AlterTableQuery;

use Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQueryBuilder;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;

/**
 * MySQL alter table query builder decorator.
 *
 * @see \Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQueryBuilder
 */
class NoStrictMySQLQueryBuilder extends MySQLQueryBuilder {

  public function getQuery(): AlterTableQueryInterface {
    $query = new NoStrictMySQLQuery(
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
