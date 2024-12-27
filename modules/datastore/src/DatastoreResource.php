<?php

namespace Drupal\datastore;

/**
 * Basic datastore resource class.
 *
 * Always generate this object using DataResource::getDatastoreResource().
 *
 * @see \Drupal\common\DataResource::getDatastoreResource()
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
   *
   * Note: duplicates Drupal\common\DataResource::getUniqueIdentifier().
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * Get the file path.
   *
   * Note: duplicates Drupal\common\DataResource::getFilePath(TRUE).
   */
  public function getFilePath(): string {
    return $this->filePath;
  }

  /**
   * Get the mimeType.
   *
   * Note: duplicates Drupal\common\DataResource::getMimeType().
   */
  public function getMimeType(): string {
    return $this->mimeType;
  }

  /**
   * {@inheritdoc}
   */
  public function jsonSerialize(): mixed {
    return (object) [
      'filePath' => $this->getFilePath(),
      'id' => $this->getId(),
      'mimeType' => $this->getMimeType(),
    ];
  }

}
