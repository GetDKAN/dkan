<?php

namespace Drupal\datastore\DataDictionary\AlterTableQuery;

use Drupal\datastore\DataDictionary\AlterTableQueryFactoryBase;
use Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface;

/**
 *
 */
class MySQLQueryFactory extends AlterTableQueryFactoryBase implements AlterTableQueryFactoryInterface {

  /**
   * Get the class to use in the factory.
   *
   * @return string
   *   Class implementing the AlterTableQueryInterface.
   */
  protected function getQueryClass(): string {
    return MySQLQuery::class;
  }

  /**
   * Set the wait_timeout for the default database connection.
   *
   * @param int $timeout
   *   Wait timeout in seconds.
   */
  public function setConnectionTimeout(int $timeout): void {
    $command = 'SET SESSION wait_timeout = ' . $timeout;
    $this->connection->query($command);
  }

}
