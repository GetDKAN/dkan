<?php

namespace Drupal\datastore_mysql_import\Service;

use Drupal\Core\Database\Database;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableExistsException;
use Procrastinator\Result;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * MySQL LOAD DATA importer.
 */
class MySqlImportJob extends ImportJob {

  /**
   * End Of Line character sequence escape to literal map.
   *
   * @var string[]
   */
  protected const EOL_TABLE = [
    '\r\n' => "\r\n",
    '\r' => "\r",
    '\n' => "\n",
  ];

  /**
   * Override.
   *
   * {@inheritdoc}
   */
  protected function runIt(): Result {
    // Attempt to resolve resource file name from file path.
    if (!($file_path = \Drupal::service('file_system')->realpath($this->resource->getFilePath()))) {
      return $this->setResultError(sprintf('Unable to resolve file name "%s" for resource with identifier "%s".', $this->resource->getFilePath(), $this->resource->getId()));
    }

    // Read the columns and EOL character sequence from the CSV file.
    try {
      [$columns, $column_lines] = $this->resource->getColsFromFile();
    }
    catch (FileException $e) {
      return $this->setResultError($e->getMessage());
    }
    // Attempt to detect the EOL character sequence for this file; default to
    // '\n' on failure.
    $eol = $this->getEol($column_lines) ?? '\n';
    // Count the number of EOL characters in the header row to determine how
    // many lines the headers are occupying.
    $header_line_count = substr_count(trim($column_lines), self::EOL_TABLE[$eol]) + 1;
    // Generate sanitized table headers from column names.
    // Use headers to set the storage schema.
    $spec = $this->generateTableSpec($columns);

    $this->getStorage()->setSchema(['fields' => $spec]);
    try {
      // The count() method has a side effect of creating the table, via
      // setTable().
      $this->getStorage()->count();
    }
    catch (MySqlDatabaseTableExistsException $e) {
      // Error out if the table already existed.
      $this->setResultError($e->getMessage());
      return $this->getResult();
    }
    // Construct and execute a SQL import statement using the information
    // gathered from the CSV file being imported.
    $this->getDatabaseConnectionCapableOfDataLoad()->query(
      $this->getSqlStatement(
        $file_path,
        $this->getStorage()->getTableName(),
        array_keys($spec),
        $eol,
        $header_line_count,
        $this->resource->getDelimiter()
      )
    );

    Database::setActiveConnection('default');
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
