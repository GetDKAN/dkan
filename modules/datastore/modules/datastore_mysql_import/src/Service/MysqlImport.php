<?php

namespace Drupal\datastore_mysql_import\Service;

use Dkan\Datastore\Importer;
use Drupal\Core\Database\Database;
use Procrastinator\Result;

/**
 * Expiremental MySQL LOAD DATA importer.
 *
 * @codeCoverageIgnore
 */
class MysqlImport extends Importer {

  /**
   * Override.
   *
   * {@inheritdoc}
   */
  protected function runIt() {
    $fileSystem = \Drupal::service('file_system');
    $filename = $fileSystem->realpath($this->resource->getFilePath());

    $f = fopen($filename, 'r');
    $line = fgets($f);
    fclose($f);
    $header = str_getcsv($line);
    $header = $this->cleanHeaders($header);

    $this->setStorageSchema($header);

    // Instance of Drupal\datastore\Storage\DatabaseTable.
    $storage = $this->dataStorage;
    $storage->count();

    // Attempt to detect the line ending for this resource file using the first
    // line from the file.
    $eol = $this->getEol($line);
    // On failure, stop the import job and log an error.
    if (!isset($eol)) {
      return $this->setResultError(sprintf('Failed to detect EOL character for resource file "%s" from header line "%s".', $filename, $line));
    }

    // Construct an SQL import statement using the information gathered from the
    // CSV file being imported.
    $sqlStatement = $this->getSqlStatement($filename, $storage->getTableName(), $header, $eol);

    $db = $this->getDatabaseConnectionCapableOfDataLoad();
    $db->query($sqlStatement);

    Database::setActiveConnection();

    $this->getResult()->setStatus(Result::DONE);

    return $this->getResult();
  }

  /**
   * Attempt to detect the EOL character for the given line.
   *
   * @param string $line
   *   Line being analyzed.
   *
   * @return string|null
   *   The EOL character for the given line, or NULL on failure.
   */
  protected function getEol(string $line): ?string {
    $eol = NULL;

    if (preg_match('/\r\n$/', $line)) {
      $eol = '\r\n';
    }
    elseif (preg_match('/\r$/', $line)) {
      $eol = '\r';
    }
    elseif (preg_match('/\n$/', $line)) {
      $eol = '\n';
    }

    return $eol;
  }

  /**
   * Private.
   */
  private function getDatabaseConnectionCapableOfDataLoad() {
    $options = \Drupal::database()->getConnectionOptions();
    $options['pdo'][\PDO::MYSQL_ATTR_LOCAL_INFILE] = 1;
    Database::addConnectionInfo('extra', 'default', $options);
    Database::setActiveConnection('extra');

    return Database::getConnection();
  }

  /**
   * Private.
   */
  private function cleanHeaders($headers) {
    $row = [];
    foreach ($headers as $field) {
      $new = preg_replace("/[^A-Za-z0-9_ ]/", '', $field);
      $new = trim($new);
      $new = strtolower($new);
      $new = str_replace(" ", "_", $new);

      $mysqlMaxColLength = 64;
      if (strlen($new) > $mysqlMaxColLength) {
        $strings = str_split($new, $mysqlMaxColLength - 5);
        $token = substr(md5($field), 0, 4);
        $new = $strings[0] . "_{$token}";
      }

      $row[] = $new;
    }
    return $row;
  }

  /**
   * Construct a SQL file import statement using the given file information.
   *
   * @param string $filename
   *   Name of the CSV file being imported.
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
  private function getSqlStatement(string $filename, string $tablename, array $headers, string $eol): string {
    return implode(' ', [
      'LOAD DATA LOCAL INFILE \'' . $filename . '\'',
      'INTO TABLE ' . $tablename,
      'FIELDS TERMINATED BY \',\'',
      'ENCLOSED BY \'\"\'',
      'LINES TERMINATED BY \'' . $eol . '\'',
      'IGNORE 1 ROWS',
      '(' . implode(',', $headers) . ')',
      'SET record_number = NULL;',
    ]);
  }

}
