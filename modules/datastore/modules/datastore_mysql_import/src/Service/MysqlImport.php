<?php

namespace Drupal\datastore_mysql_import\Service;

use Dkan\Datastore\Importer;
use Drupal\Core\Database\Database;
use Procrastinator\Result;

/**
 * Class MysqlImport.
 *
 * @package Drupal\datastore_mysql_import\Service
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

    $this->setStorageSchema($header);

    /* @var $storage \Drupal\datastore\Storage\DatabaseTable */
    $storage = $this->dataStorage;
    $storage->count();

    $sqlStatementLines = [
      'LOAD DATA LOCAL INFILE \'' . $filename . '\'',
      'INTO TABLE ' . $storage->getTableName(),
      'FIELDS TERMINATED BY \',\'',
      'ENCLOSED BY \'\"\'',
      'LINES TERMINATED BY \'\n\'',
      'IGNORE 1 ROWS',
      '(' . implode(',', $header) . ')',
      'SET record_number = NULL;',
    ];

    $sqlStatement = implode(' ', $sqlStatementLines);

    $db = $this->getDatabaseConnectionCapableOfDataLoad();
    $db->query($sqlStatement)->execute();

    Database::setActiveConnection();

    $this->getResult()->setStatus(Result::DONE);

    return $this->getResult();
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

}
