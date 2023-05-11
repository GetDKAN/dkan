<?php

namespace Drupal\datastore_mysql_import\Service;

use Drupal\Core\Database\Database;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableExistsException;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableInterface;
use Procrastinator\Result;

/**
 * MySQL LOAD DATA importer.
 */
class MySqlImportJob extends ImportJob {

  /**
   * {@inheritDoc}
   */
  protected function __construct(string $identifier, $storage, array $config = NULL) {
    $this->dataStorage = $config['storage'];
    if (!($this->dataStorage instanceof MySqlDatabaseTableInterface)) {
      throw new \Exception('Storage must be an instance of ' . MySqlDatabaseTableInterface::class);
    }
    parent::__construct($identifier, $storage, $config);
  }

  /**
   * {@inheritdoc}
   */
  protected function runIt(): Result {
    // Attempt to resolve resource file name from file path.
    if (!($file_path = $this->resource->realPath())) {
      return $this->setResultError(sprintf('Unable to resolve file name "%s" for resource with identifier "%s".', $this->resource->getFilePath(), $this->resource->getId()));
    }

    // Read the columns and EOL character sequence from the CSV file.
    try {
      [$columns, $column_lines] = $this->resource->getColsFromFile();
      $eol = $this->resource->getEol() ?? "\n";
    }
    catch (\Throwable $e) {
      return $this->setResultError($e->getMessage());
    }
    // Count the number of EOL characters in the header row to determine how
    // many lines the headers are occupying.
    $header_line_count = substr_count(trim($column_lines), $eol) + 1;
    // Generate sanitized table headers from column names.
    // Use headers to set the storage schema.
    $spec = $this->generateTableSpec($columns);

    $storage = $this->getStorage();

    $storage->setSchema(['fields' => $spec]);
    try {
      // The count() method has a side effect of creating the table, via
      // setTable().
      $storage->count();
    }
    catch (MySqlDatabaseTableExistsException $e) {
      // Error out if the table already existed.
      return $this->setResultError($e->getMessage());
    }
    // Construct and execute a SQL import statement using the information
    // gathered from the CSV file being imported.
    $this->getDatabaseConnectionCapableOfDataLoad()->query(
      $this->getSqlStatement($file_path, $storage->getTableName(), array_keys($spec), $eol, $header_line_count, $this->resource->getDelimiter())
    );

    // Set the active connection back to default.
    Database::setActiveConnection('default');
    $this->getResult()->setStatus(Result::DONE);
    return $this->getResult();
  }

  /**
   * Set up an alternate DB connection which can read a CSV file.
   */
  protected function getDatabaseConnectionCapableOfDataLoad($key = 'extra') {
    $options = \Drupal::database()->getConnectionOptions();
    $options['pdo'][\PDO::MYSQL_ATTR_LOCAL_INFILE] = 1;
    Database::addConnectionInfo($key, 'default', $options);
    Database::setActiveConnection($key);

    return Database::getConnection();
  }

  /**
   * Properly escape and format the supplied list of column names.
   *
   * @param string|null[] $columns
   *   List of column names.
   *
   * @return array
   *   List of sanitized table headers.
   */
  public function generateTableSpec(array $columns): array {
    $spec = [];

    foreach ($columns as $column) {
      // Sanitize the supplied table header to generate a unique column name;
      // null-coalesce potentially NULL column names to empty strings.
      $name = static::sanitizeHeader($column ?? '');

      // Truncate the generated table column name, if necessary, to fit the max
      // column length.
      $name = static::truncateHeader($name);

      // Generate unique numeric suffix for the header if a header already
      // exists with the same name.
      for ($i = 2; isset($spec[$name]); $i++) {
        $suffix = '_' . $i;
        $name = substr($name, 0, static::MAX_COLUMN_LENGTH - strlen($suffix)) . $suffix;
      }

      $spec[$name] = [
        'type' => 'text',
        'description' => static::sanitizeDescription($column ?? ''),
      ];
    }

    return $spec;
  }

  /**
   * Construct a SQL file import statement using the given file information.
   *
   * @param string $file_path
   *   File path to the CSV file being imported.
   * @param string $table_name
   *   Name of the datastore table the file is being imported into.
   * @param string[] $headers
   *   List of CSV headers.
   * @param string $eol
   *   End Of Line character for file importation.
   * @param int $header_line_count
   *   Number of lines occupied by the csv header row.
   * @param string $delimiter
   *   File delimiter.
   *
   * @return string
   *   Generated SQL file import statement.
   */
  protected function getSqlStatement(string $file_path, string $table_name, array $headers, string $eol, int $header_line_count, string $delimiter): string {
    return implode(' ', [
      'LOAD DATA LOCAL INFILE \'' . $file_path . '\'',
      'INTO TABLE ' . $table_name,
      'FIELDS TERMINATED BY \'' . $delimiter . '\'',
      'OPTIONALLY ENCLOSED BY \'"\'',
      'ESCAPED BY \'\'',
      'LINES TERMINATED BY \'' . $eol . '\'',
      'IGNORE ' . $header_line_count . ' LINES',
      '(' . implode(',', $headers) . ')',
      'SET record_number = NULL;',
    ]);
  }

}
