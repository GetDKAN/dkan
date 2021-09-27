<?php

namespace Drupal\datastore_mysql_import\Service;

use Drupal\Core\Database\Database;

use Dkan\Datastore\Importer;
use Procrastinator\Result;

/**
 * Expiremental MySQL LOAD DATA importer.
 *
 * @codeCoverageIgnore
 */
class MysqlImport extends Importer {

  /**
   * {@inheritdoc}
   */
  protected function runIt() {
    $eol = $this->resource->getEol();
    // On failure, stop the import job and log an error.
    if (!isset($eol)) {
      return $this->setResultError(sprintf('Failed to detect EOL character for resource file "%s" from header line "%s".', $this->resource->getFilePath(), $this->resource->getFirstLineFromFile()));
    }

    // Call `count` on database table in order to ensure a database table has
    // been created for the datastore.
    // @todo Find a better way to ensure creation of datastore tables.
    $this->dataStorage->count();
    // Construct and execute a SQL import statement using the information
    // gathered from the CSV file being imported.
    self::getDatabaseConnectionCapableOfDataLoad()->query(
      self::buildSqlStatement($this->resource->getFilePath(), $this->dataStorage->getTableName(), $this->resource->getColumns(), $eol));

    Database::setActiveConnection();

    $this->getResult()->setStatus(Result::DONE);

    return $this->getResult();
  }

  /**
   * Private.
   */
  protected static function getDatabaseConnectionCapableOfDataLoad() {
    $options = \Drupal::database()->getConnectionOptions();
    $options['pdo'][\PDO::MYSQL_ATTR_LOCAL_INFILE] = 1;
    Database::addConnectionInfo('extra', 'default', $options);
    Database::setActiveConnection('extra');

    return Database::getConnection();
  }

  /**
   * Construct a SQL file import statement using the given file information.
   *
   * @param string $file_path
   *   File path to the CSV file being imported.
   * @param string $tablename
   *   Name of the datastore table the file is being imported into.
   * @param string[] $headers
   *   List of CSV headers.
   * @param string $eol
   *   End Of Line character for file importation.
   *
   * @return string
   *   Generated SQL file import statement.
   */
  protected static function buildSqlStatement(string $file_path, string $tablename, array $headers, string $eol): string {
    return implode(' ', [
      'LOAD DATA LOCAL INFILE \'' . $file_path . '\'',
      'INTO TABLE ' . $tablename,
      'FIELDS TERMINATED BY \',\'',
      'OPTIONALLY ENCLOSED BY \'"\'',
      'ESCAPED BY \'\'',
      'LINES TERMINATED BY \'' . $eol . '\'',
      'IGNORE 1 ROWS',
      '(' . implode(',', $headers) . ')',
      'SET record_number = NULL;',
    ]);
  }

}
