<?php

namespace Drupal\datastore_mysql_import\DataDictionary\AlterTableQuery;

use Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQueryBuilder;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;

/**
 * MySQL alter table query builder decorator.
 *
 * @see \Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQueryBuilder
 */
class StrictModeOffMySQLQueryBuilder extends MySQLQueryBuilder {

  /**
   * {@inheritDoc}
   */
  public function getQuery(): AlterTableQueryInterface {
    $settings = $this->configFactory->get('datastore_mysql_import.settings');
    // If strict_mode_disabled is not set or is FALSE, back out.
    if (!($settings->get('strict_mode_disabled') ?? FALSE)) {
      return parent::getQuery();
    }

    $query = new StrictModeOffMySQLQuery(
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
