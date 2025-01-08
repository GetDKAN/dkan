<?php

namespace Drupal\datastore;

/**
 * Basic datastore resource class.
 *
 * @deprecated in dkan:8.x-2.20 and is removed from dkan:8.x-2.21. Use
 *   \Drupal\common\DataResource instead.
 * @see https://github.com/GetDKAN/dkan/pull/4372
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
   * @deprecated in dkan:8.x-2.20 and is removed from dkan:8.x-2.21. Use
   *   \Drupal\common\DataResource::getUniqueIdentifier() instead.
   * @see https://github.com/GetDKAN/dkan/pull/4372
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * Get the file path.
   *
   * @return string
   *   The file path.
   *
   * @deprecated in dkan:8.x-2.20 and is removed from dkan:8.x-2.21. Use
   *   \Drupal\common\DataResource::getUniqueIdentifier() instead.
   * @see https://github.com/GetDKAN/dkan/pull/4372
   */
  public function getFilePath(): string {
    return $this->filePath;
  }

  /**
   * Get the mimeType.
   *
   * @return string
   *   The mimeType.
   *
   * @deprecated in dkan:8.x-2.20 and is removed from dkan:8.x-2.21. Use
   *   \Drupal\common\DataResource::getMimeType() instead.
   * @see https://github.com/GetDKAN/dkan/pull/4372
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
