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
  public function getId() {
    return $this->id;
  }

  /**
   * Get the file path.
   */
  public function getFilePath() {
    return $this->filePath;
  }

  /**
   * Get the mimeType.
   */
  public function getMimeType() {
    return $this->mimeType;
  }

  /**
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    return (object) [
      'filePath' => $this->filePath,
      'id' => $this->id,
      'mimeType' => $this->mimeType,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function hydrate($json) {
    $data = json_decode($json);
    $reflector = new \ReflectionClass(self::class);
    $object = $reflector->newInstanceWithoutConstructor();

    $reflector = new \ReflectionClass($object);

    $p = $reflector->getProperty('filePath');
    $p->setAccessible(TRUE);
    $p->setValue($object, $data->filePath);

    $p = $reflector->getProperty('id');
    $p->setAccessible(TRUE);
    $p->setValue($object, $data->id);

    $p = $reflector->getProperty('mimeType');
    $p->setAccessible(TRUE);
    $p->setValue($object, $data->mimeType);

    return $object;
  }

}
