<?php

namespace Drupal\datastore;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Basic datastore resource class.
 *
 * A datastore resource is assumed to be a CSV file.
 */
class DatastoreResource implements \JsonSerializable {

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
   * EOL token, stored so we don't need to recompute.
   *
   * @var string
   */
  private $eolToken;

  /**
   * Resource identifier.
   *
   * @var string
   */
  private $id;

  /**
   * Path to resource file.
   *
   * @var string
   */
  private $filePath;

  /**
   * File media type.
   *
   * @var string
   */
  private $mimeType;

  /**
   * CSV columns.
   *
   * @var string[]
   *   Array of strings representing the column names.
   */
  private $columns;

  /**
   * CSV column lines.
   *
   * @var string
   *   All the column names in a string.
   */
  private $columnLines;

  /**
   * Resource constructor.
   */
  public function __construct($id, $file_path, $mime_type) {
    $this->id = $id;
    $this->filePath = $file_path;
    $this->mimeType = $mime_type;
  }

  /**
   * Get the resource ID.
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * Get the file path.
   */
  public function getFilePath(): string {
    return $this->filePath;
  }

  /**
   * Real path to the resource.
   *
   * @return string
   */
  public function realPath(): string {
    return \Drupal::service('file_system')->realpath($this->getFilePath());
  }

  /**
   * Get the mimeType.
   *
   * @return string
   */
  public function getMimeType(): string {
    return $this->mimeType;
  }

  /**
   * Get the EOL token.
   *
   * @return string|null
   *   The EOL character for the given line, or NULL on failure.
   */
  public function getEolToken(): ?string {
    if (empty($this->eolToken)) {
      $line = $this->getColsFromFile()[1];
      $this->eolToken = NULL;

      if (preg_match('/\r\n$/', $line)) {
        $this->eolToken = '\r\n';
      }
      elseif (preg_match('/\r$/', $line)) {
        $this->eolToken = '\r';
      }
      elseif (preg_match('/\n$/', $line)) {
        $this->eolToken = '\n';
      }
    }

    return $this->eolToken;
  }

  /**
   * Get EOL literal.
   *
   * @return string
   *   EOL string literal.
   */
  public function getEol() {
    return self::EOL_TABLE[$this->getEolToken() ?? '\n'];
  }

  /**
   * Get the value delimiter for CSV.
   *
   * @return string
   *   Delimiter as determined by MIME type. Defaults to comma.
   */
  public function getDelimiter(): string {
    return ($this->getMimeType() == 'text/tab-separated-values') ? "\t" : ',';
  }

  /**
   * Attempt to read the columns of the resource CSV file.
   *
   * @return array
   *   An array containing only two elements; the CSV columns and the column
   *   lines.
   *
   * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
   *   On failure to open the file;
   *   on failure to read the first line from the file.
   */
  public function getColsFromFile(): array {
    if (!empty($this->columns) && !empty($this->columnLines)) {
      return [$this->columns, $this->columnLines];
    }

    $file_path = $this->realPath();

    // Open the CSV file.
    try {
      if (empty($f = fopen($file_path, 'r'))) {
        throw new \Exception();
      }
    }
    catch (\Throwable $e) {
      // The fopen() function can also throw errors, so we catch \Throwable.
      throw new FileException(sprintf('Failed to open resource file "%s".', $file_path));
    }

    // Attempt to retrieve the columns from the resource file.
    $this->columns = fgetcsv($f, 0, $this->getDelimiter());
    // Attempt to read the column lines from the resource file.
    $end_pointer = ftell($f);
    rewind($f);
    $this->columnLines = fread($f, $end_pointer);

    // Close the resource file, since it is no longer needed.
    fclose($f);
    // Ensure the columns of the resource file were successfully read.
    if (!isset($this->columns) || $this->columns === FALSE) {
      throw new FileException(sprintf('Failed to read columns from resource file "%s".', $file_path));
    }

    return [$this->columns, $this->columnLines];
  }

  /**
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return (object) [
      'filePath' => $this->getFilePath(),
      'id' => $this->getId(),
      'mimeType' => $this->getMimeType(),
    ];
  }

}
