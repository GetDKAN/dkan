<?php

namespace Drupal\datastore;

/**
 * Basic datastore resource class.
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
