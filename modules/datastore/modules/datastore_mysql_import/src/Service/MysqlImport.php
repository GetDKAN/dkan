<?php

namespace Drupal\datastore_mysql_import\Service;

use Drupal\common\Storage\ImportedItemInterface;
use Drupal\Core\Database\Database;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Procrastinator\Result;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * MySQL LOAD DATA importer.
 *
 * @todo Figure out how to inject the file_system service into this class.
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
   * Constructor method.
   *
   * Identical to parent, but requires an ImportedDatabaseTableInterface
   * storage object.
   *
   * @param string $identifier
   *   Job identifier.
   * @param mixed $storage
   *   Storage class.
   * @param array|null $config
   *   Configuration options.
   */
  protected function __construct(string $identifier, $storage, array $config = NULL) {
    if (!($config['storage'] instanceof ImportedItemInterface)) {
      throw new \Exception('Storage must be an instance of ' . ImportedItemInterface::class);
    }
    parent::__construct($identifier, $storage, $config);
  }

  /**
   * Perform the import job.
   *
   * @return mixed
   *   The data to be placed in the Result object. This class does not use the
   *   result data, so it returns void.
   *
   * @throws \Exception
   *   Any exception thrown will be turned into an error in the Result object
   *   in the run() method.
   */
  protected function runIt() {
    // If the storage table already exists, we already performed an import and
    // can stop here.
    if ($this->dataStorage->hasBeenImported()) {
      $this->getResult()->setStatus(Result::DONE);
      return NULL;
    }

    // Attempt to resolve resource file name from file path.
    if (($file_path = \Drupal::service('file_system')->realpath($this->resource->getFilePath())) === FALSE) {
      return $this->setResultError(sprintf('Unable to resolve file name "%s" for resource with identifier "%s".', $this->resource->getFilePath(), $this->resource->getId()));
    }

    // Read the columns and EOL character sequence from the CSV file.
    $delimiter = $this->resource->getMimeType() == 'text/tab-separated-values' ? "\t" : ',';
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

    // Count() will attempt to create the database table by side-effect of
    // calling setTable().
    $this->dataStorage->count();

    // Construct and execute a SQL import statement using the information
    // gathered from the CSV file being imported.
    $this->getDatabaseConnectionCapableOfDataLoad()->query(
      $this->getSqlStatement($file_path, $this->dataStorage->getTableName(), array_keys($spec), $eol, $header_line_count, $delimiter));

    Database::setActiveConnection();

    $this->getResult()->setStatus(Result::DONE);
    return NULL;
  }

  /**
   * Attempt to read the columns and detect the EOL chars of the given CSV file.
   *
   * @param string $file_path
   *   File path.
   * @param string $delimiter
   *   File delimiter.
   *
   * @return array
   *   An array containing only two elements; the CSV columns and the column
   *   lines.
   *
   * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
   *   On failure to open the file;
   *   on failure to read the first line from the file.
   */
  protected function getColsFromFile(string $file_path, string $delimiter): array {

    // Open the CSV file.
    $f = fopen($file_path, 'r');

    // Ensure the file could be successfully opened.
    if (!isset($f) || $f === FALSE) {
      throw new FileException(sprintf('Failed to open resource file "%s".', $file_path));
    }

    // Attempt to retrieve the columns from the resource file.
    $columns = fgetcsv($f, 0, $delimiter);
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
        'type' => 'text',
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
      'INTO TABLE {' . $table_name . '}',
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
