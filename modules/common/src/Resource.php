<?php

namespace Drupal\common;

use Procrastinator\HydratableTrait;
use Procrastinator\JsonSerializeTrait;

/**
 * Resource.
 *
 * A resource models a means to get data. URLs to data/files, API endpoints,
 * paths to files stored locally in the server, are all possible
 * forms of resources.
 *
 * This class not only models a resource, but it helps model other properties
 * necessary for other systems like the Drupal::metastore::ResourceMapper and
 * Drupal::datastore::Service::ResourceLocalizer to
 * enhance DKAN's capabilities around resources.
 *
 * These properties are __version__ and __perspective__.
 *
 * For more details refer to the methods governing these behaviors:
 * 1. Resource::createNewVersion()
 * 2. Resource::createNewPerspective()
 *
 * @todo Rename filePath to uri or url.
 * @todo Refactor as service.
 */
class Resource implements \JsonSerializable {
  use HydratableTrait, JsonSerializeTrait;

  const DEFAULT_SOURCE_PERSPECTIVE = 'source';

  /**
   * The file path or URL for the resource.
   *
   * @var string
   */
  private $filePath;

  /**
   * MD5 hash of the filepath, used as the main identifier.
   *
   * @var string
   */
  private $identifier;

  /**
   * Content type of the resource.
   *
   * @var string
   */
  private $mimeType;

  /**
   * Specifies the perspective for the resource's file path.
   *
   * Can be one of "local_file", "local_url", or "source".
   *
   * @var string
   */
  private $perspective;

  /**
   * The resource "version" -- a timestamp.
   *
   * @var int
   */
  private $version;
  private $checksum;

  /**
   * Constructor.
   */
  public function __construct($file_path, $mimeType, $perspective = self::DEFAULT_SOURCE_PERSPECTIVE) {
    // @todo generate UUID instead.
    $this->identifier = md5($file_path);
    $this->filePath = $file_path;
    $this->mimeType = $mimeType;
    $this->perspective = $perspective;
    // @todo Create a timestamp property and generate uuid for version.
    $this->version = time();
    $this->checksum = NULL;
  }

  /**
   * Create a new version.
   *
   * Versions are, simply, a unique "string" used to represent changes in a
   * resource. For example, when new data is added to a file/resource a new
   * version of the resource should be created.
   *
   * This class does not have any functionality that keeps track of changes in
   * resources, it simply models the behavior to allow other parts of the
   * system to create new versions of resources when they deem it necessary.
   */
  public function createNewVersion() {
    $newVersion = time();
    if ($newVersion == $this->version) {
      $newVersion++;
    }

    return $this->createCommon('version', $newVersion);
  }

  /**
   * Create a new perspective.
   *
   * Perspectives are useful to represent clusters of connected resources.
   *
   * For example, a CSV file might also have an API endpoint that makes the
   * data available. In this circumstance we could create the API endpoint
   * resource as a new __perspective__ of the file resource to make the system
   * aware of the new resource, the API endpoint, and maintain the relatioship
   * between the 2 resources.
   */
  public function createNewPerspective($perspective, $uri) {
    $new = $this->createCommon('perspective', $perspective);
    $new->changeFilePath($uri);

    return $new;
  }

  /**
   * Change file path.
   */
  public function changeFilePath($newPath) {
    $this->filePath = $newPath;
  }

  /**
   * Change MIME type.
   */
  public function changeMimeType($newMimeType) {
    $this->mimeType = $newMimeType;
  }

  /**
   * Private.
   */
  private function createCommon($property, $value) {
    $current = $this->{$property};
    $new = $value;
    $this->{$property} = $new;
    $newResource = clone $this;
    $this->{$property} = $current;
    return $newResource;
  }

  /**
   * Getter.
   */
  public function getIdentifier() {
    return $this->identifier;
  }

  /**
   * Getter.
   */
  public function getFilePath() {
    return $this->filePath;
  }

  /**
   * Getter.
   */
  public function getFolder() {
    return dirname($this->filePath);
  }

  /**
   * Getter.
   */
  public function getMimeType() {
    return $this->mimeType;
  }

  /**
   * Getter.
   */
  public function getVersion() {
    return $this->version;
  }

  /**
   * Getter.
   */
  public function getPerspective() {
    return $this->perspective;
  }

  /**
   * Getter.
   */
  public function getUniqueIdentifier() {
    return self::buildUniqueIdentifier($this->identifier, $this->version, $this->perspective);
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function jsonSerialize() {
    return $this->serialize();
  }

  /**
   * Build full resource identifier.
   *
   * @param string $identifier
   *   MD5 hash of resource file path.
   * @param string $version
   *   Resource creation timestamp.
   * @param string $perspective
   *   Resource perspective.
   *
   * @return string
   *   Full resource identifier.
   */
  public static function buildUniqueIdentifier(string $identifier, string $version, string $perspective): string {
    return $identifier . '__' . $version . '__' . $perspective;
  }

  /**
   * Parse unique identifier.
   *
   * @param string $uid
   *   A string with the form <identifier>__<version>__perspective.
   *
   * @return array
   *   An array keyed with identifier, version and perspective.
   *
   * @throws \Exception
   *   When string does not contain the 3 pieces of a unique identifier.
   */
  public static function parseUniqueIdentifier(string $uid): array {
    $pieces = explode("__", $uid);
    if (count($pieces) != 3) {
      throw new \Exception("Badly constructed unique identifier {$uid}");
    }
    return [
      'identifier' => $pieces[0],
      'version' => $pieces[1],
      'perspective' => $pieces[2],
    ];
  }

  /**
   * Get Identifier and Version.
   *
   * @param string $string
   *   The string given could be a unique identifier, or a partial identifier
   *   (no perspective), or a distribution uuid.
   */
  public static function getIdentifierAndVersion($string) {
    // Complete unique identifier.
    try {
      $parts = self::parseUniqueIdentifier($string);
      return [$parts['identifier'], $parts['version']];
    }
    catch (\Exception $e) {
    }

    // Partial identifier.
    if (substr_count($string, '__') > 0) {
      $parts = explode("__", $string);
      if (count($parts) == 2) {
        return $parts;
      }
    }

    $distribution = self::getDistribution($string);

    // Are we dealing with a distribution id?
    if (isset($distribution->data->{"%Ref:downloadURL"})) {
      $resource = $distribution->data->{"%Ref:downloadURL"}[0]->data;
      return [$resource->identifier, $resource->version];
    }

    throw new \Exception("Could not find identifier and version for {$string}");
  }

  /**
   * Get a distribution object from the metastore.
   *
   * @param mixed $identifier
   *   A distribution UUID.
   *
   * @return object
   *   JSON-decoded object.
   */
  private static function getDistribution($identifier) {
    $factory = \Drupal::service('dkan.metastore.storage');
    $storage = $factory->getInstance('distribution');

    $distroJson = $storage->retrieve($identifier);
    return json_decode($distroJson);
  }

  /**
   * Generates MD5 checksum for a file.
   */
  public function generateChecksum() {
    $this->checksum = md5_file($this->filePath);
  }

}
