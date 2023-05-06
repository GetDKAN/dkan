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
   * Get the mimeType.
   */
  public function getMimeType(): string {
    return $this->mimeType;
  }

  public function getEol() {

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
   * Attempt to read the columns and detect the EOL chars of the given CSV file.
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
    $file_path = \Drupal::service('file_system')->realpath($this->getFilePath());
    $delimiter = $this->getDelimiter();

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
