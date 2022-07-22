<?php

namespace Drupal\datastore\FullText\AlterTableQuery;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\StatementInterface;

use Drupal\datastore\FullText\AlterTableQueryInterface;

/**
 * MySQL table alter query.
 */
class MySQLQuery implements AlterTableQueryInterface {

  /**
   * Build a MySQL table alter query.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param string $datastore_table
   *   Datastore table.
   * @param array $indexes
   *   Fulltext indexes to apply.
   */
  public function __construct(
      Connection $connection,
      string $datastore_table,
      array $indexes
  ) {
    $this->connection = $connection;
    $this->datastoreTable = $this->connection->escapeTable($datastore_table);
    $this->indexes = $indexes;
  }

  /**
   * {@inheritdoc}
   */
  public function applyFullTextIndexes(): void {
    $this->indexes = $this->mergeIndexFields($this->indexes, $this->datastoreTable);
    // Loop through each index.
    foreach ($this->indexes as $indexId => $columns) {
      // Build and execute SQL command to perform table alter.
      $this->buildAlterCommand($indexId, $columns, $this->datastoreTable)->execute();
    }
  }

  /**
   * Confirm the index fields exist in the given table.
   *
   * @param array $indexes
   *   Fulltext indexes to apply.
   * @param string $table
   *   MySQL table to filter against.
   *
   * @return array
   *   Confirmed list of applicable index fields.
   */
  protected function mergeIndexFields(array $indexes, string $table): array {
    $table_cols = $this->getTableColsAndComments($table);
    $column_names = array_keys($table_cols);

    foreach ($indexes as $index) {
      // Filter out un-applicable index fields.
      $filtered_index_fields[] = array_filter($index, fn ($fields) => in_array($fields['name'], $column_names, TRUE));
    }
    return $filtered_index_fields;
  }

  /**
   * Get list of MySQL table field details.
   *
   * @param string $table
   *   Table name.
   *
   * @return string[]
   *   List of column comments keyed by column names.
   */
  protected function getTableColsAndComments(string $table): array {
    return $this->connection->query("SHOW FULL COLUMNS FROM {{$table}};")->fetchAllKeyed(0, 8);
  }

  /**
   * Build alter command to add fulltext index.
   *
   * @param string $indexId
   *   Identifier for the index.
   * @param array $mergedColumns
   *   Columns to be indexed.
   * @param string $table
   *   Mysql table name.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   Prepared MySQL table alter command statement.
   */
  protected function buildAlterCommand(string $indexId, array $mergedColumns, string $table): StatementInterface {
    // Create the alter table command.
    return $this->connection->prepareStatement("ALTER TABLE {{$table}} ADD FULLTEXT " . $indexId . "(" . implode(', ', $mergedColumns) . ");");
  }

}
