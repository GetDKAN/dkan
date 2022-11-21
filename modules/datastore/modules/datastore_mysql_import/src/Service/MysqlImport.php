<?php

namespace Drupal\datastore_mysql_import\Service;

use Drupal\Core\Database\Database;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Procrastinator\Result;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Expiremental MySQL LOAD DATA importer.
 */
class MysqlImport extends ImportJob {

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
  protected function runIt() {
    // Attempt to resolve resource file name from file path.
    $file_path = \Drupal::service('file_system')->realpath($this->resource->getFilePath());

    $delimiter = ",";
    if ($this->resource->getMimeType() == 'text/tab-separated-values') {
      $delimiter = "\t";
    }

    if ($file_path === FALSE) {
      return $this->setResultError(sprintf('Unable to resolve file name "%s" for resource with identifier "%s".', $this->resource->getFilePath(), $this->resource->getId()));
    }

    // Read the columns and EOL character sequence from the CSV file.
    try {
      [$columns, $column_lines] = $this->getColsFromFile($file_path, $delimiter);
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
    $this->dataStorage->setSchema(['fields' => $spec]);

    // @todo Find a better way to ensure creation of datastore tables.
    $this->dataStorage->innodbStrictMode(FALSE);
    $this->dataStorage->count();
    $this->dataStorage->innodbStrictMode(TRUE);
    // Construct and execute a SQL import statement using the information
    // gathered from the CSV file being imported.
    $this->getDatabaseConnectionCapableOfDataLoad()->query(
      $this->getSqlStatement($file_path, $this->dataStorage->getTableName(), array_keys($spec), $eol, $header_line_count, $delimiter));

    Database::setActiveConnection();

    $this->getResult()->setStatus(Result::DONE);

    return $this->getResult();
  }

  /**
   * Attempt to read the columns and detect the EOL chars of the given CSV file.
   *
   * @param string $file_path
   *   File path.
   *
   * @param string $delimiter
   *   File delimiter.
   *
   * @return array
   *   An array containing only two elements; the CSV columns and the column
   *   lines.
   *
   * @throws Symfony\Component\HttpFoundation\File\Exception\FileException
   *   On failure to open the file;
   *   on failure to read the first line from the file.
   */
  protected function getColsFromFile(string $file_path, string $delimiter): array {

    // Ensure the "auto_detect_line_endings" ini setting is enabled before
    // openning the file to ensure Mac style EOL characters are detected.
    $old_ini = ini_set('auto_detect_line_endings', '1');
    // Open the CSV file.
    $f = fopen($file_path, 'r');
    // Revert ini setting once the file has been opened.
    if ($old_ini !== FALSE) {
      ini_set('auto_detect_line_endings', $old_ini);
    }
    // Ensure the file could be successfully opened.
    if (!isset($f) || $f === FALSE) {
      throw new FileException(sprintf('Failed to open resource file "%s".', $file_path));
    }

    // Attempt to retrieve the columns from the resource file.
    $columns = fgetcsv($f, 0 , $delimiter);
    // Attempt to read the column lines from the resource file.
    $end_pointer = ftell($f);
    rewind($f);
    $column_lines = fread($f, $end_pointer);

    // Close the resource file, since it is no longer needed.
    fclose($f);
    // Ensure the columns of the resource file were successfully read.
    if (!isset($columns) || $columns === FALSE) {
      throw new FileException(sprintf('Failed to read columns from resource file "%s".', $file_path));
    }

    return [$columns, $column_lines];
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
  protected function getDatabaseConnectionCapableOfDataLoad() {
    $options = \Drupal::database()->getConnectionOptions();
    $options['pdo'][\PDO::MYSQL_ATTR_LOCAL_INFILE] = 1;
    Database::addConnectionInfo('extra', 'default', $options);
    Database::setActiveConnection('extra');

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
      $name = ImportJob::sanitizeHeader($column ?? '');

      // Truncate the generated table column name, if necessary, to fit the max
      // column length.
      $name = ImportJob::truncateHeader($name);

      // Generate unique numeric suffix for the header if a header already
      // exists with the same name.
      for ($i = 2; isset($spec[$name]); $i++) {
        $suffix = '_' . $i;
        $name = substr($name, 0, ImportJob::MAX_COLUMN_LENGTH - strlen($suffix)) . $suffix;
      }

      $spec[$name] = [
        'type' => "text",
        'description' => ImportJob::sanitizeDescription($column ?? ''),
      ];
    }

    return $spec;
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
   * @param int $header_line_count
   *   Number of lines occupied by the csv header row.
   * @param $delimiter
   *  File delimiter.
   *
   * @return string
   *   Generated SQL file import statement.
   */
  protected function getSqlStatement(string $file_path, string $tablename, array $headers, string $eol, int $header_line_count, string $delimiter): string {
    return implode(' ', [
      'LOAD DATA LOCAL INFILE \'' . $file_path . '\'',
      'INTO TABLE ' . $tablename,
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
