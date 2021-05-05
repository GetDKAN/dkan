<?php

namespace Drupal\metastore\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\common\LoggerTrait;
use Drupal\common\Resource;
use Drupal\common\UrlHostTokenResolver;
use Drupal\metastore\ResourceMapper;
use Drupal\node\NodeStorageInterface;

/**
 * Referencer.
 */
class Referencer {
  use HelperTrait;
  use LoggerTrait;

  private $nodeStorage;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $configService, NodeStorageInterface $nodeStorage) {
    $this->setConfigService($configService);
    $this->nodeStorage = $nodeStorage;
    $this->setLoggerFactory(\Drupal::service('logger.factory'));
  }

  /**
   * Replaces some dataset property values with references.
   *
   * @param object $data
   *   Dataset json object.
   *
   * @return object
   *   Json object modified with references to some of its properties' values.
   */
  public function reference($data) {
    if (!is_object($data)) {
      throw new \Exception("data must be an object.");
    }
    // Cycle through the dataset properties we seek to reference.
    foreach ($this->getPropertyList() as $property_id) {
      if (isset($data->{$property_id})) {
        $data->{$property_id} = $this->referenceProperty($property_id, $data->{$property_id});
      }
    }
    return $data;
  }

  /**
   * References a dataset property's value, general case.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param mixed $data
   *   Single value or array of values to be referenced.
   *
   * @return string|array
   *   Single reference, or an array of references.
   */
  private function referenceProperty(string $property_id, $data) {
    if (is_array($data)) {
      return $this->referenceMultiple($property_id, $data);
    }
    else {
      // Case for $data being an object or a string.
      return $this->referenceSingle($property_id, $data);
    }
  }

  /**
   * References a dataset property's value, array case.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param array $values
   *   The array of values to be referenced.
   *
   * @return array
   *   The array of uuid references.
   */
  private function referenceMultiple(string $property_id, array $values) : array {
    $result = [];
    foreach ($values as $value) {
      $data = $this->referenceSingle($property_id, $value);
      if (NULL !== $data) {
        $result[] = $data;
      }
    }
    return $result;
  }

  /**
   * References a dataset property's value, string or object case.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param string|object $value
   *   The value to be referenced.
   *
   * @return string
   *   The Uuid reference, or unchanged value.
   */
  private function referenceSingle(string $property_id, $value) {

    if ($property_id == 'distribution') {
      $value = $this->distributionHandling($value);
    }

    $uuid = $this->checkExistingReference($property_id, $value);
    if (!$uuid) {
      $uuid = $this->createPropertyReference($property_id, $value);
    }
    if ($uuid) {
      return $uuid;
    }
    else {
      $this->log(
        'value_referencer',
        'Neither found an existing nor could create a new reference for property_id: @property_id with value: @value',
        [
          '@property_id' => $property_id,
          '@value' => var_export($value, TRUE),
        ]
      );
      return NULL;
    }
  }

  /**
   * Private.
   */
  private function distributionHandling($value) {
    $metadata = (object) [
      'data' => $value,
    ];

    if (isset($metadata->data->downloadURL)) {
      $downloadUrl = $metadata->data->downloadURL;

      // Modify local urls to use our host/shost scheme.
      $downloadUrl = $this->hostify($downloadUrl);

      $mimeType = $this->getMimeType($metadata);

      $downloadUrl = $this->registerWithResourceMapper(
        $downloadUrl,
        $mimeType);

      $metadata->data->downloadURL = $downloadUrl;
    }
    return $metadata->data;
  }

  /**
   * Private.
   */
  private function registerWithResourceMapper($downloadUrl, $mimeType) {
    try {
      // Register the url with the filemapper.
      $resource = new Resource($downloadUrl, $mimeType);

      if ($this->getFileMapper()->register($resource)) {
        $downloadUrl = $resource->getUniqueIdentifier();
      }
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      $info = json_decode($message);

      if (is_array($info) &&
        isset($info[0]) &&
        is_object($info[0]) &&
        isset($info[0]->identifier)) {

        /** @var \Drupal\common\Resource $stored */
        $stored = $this->getFileMapper()->get($info[0]->identifier, Resource::DEFAULT_SOURCE_PERSPECTIVE);
        $downloadUrl = $this->handleExistingResource($info, $stored);
      }
    }

    return $downloadUrl;
  }

  /**
   * Private.
   */
  private function handleExistingResource($info, $stored) {
    if ($info[0]->perspective == Resource::DEFAULT_SOURCE_PERSPECTIVE && resource_mapper_new_revision() == 1) {
      $new = $stored->createNewVersion();
      $this->getFileMapper()->registerNewVersion($new);
      $downloadUrl = $new->getUniqueIdentifier();
    }
    else {
      $downloadUrl = $stored->getUniqueIdentifier();
    }
    return $downloadUrl;
  }

  /**
   * Private.
   */
  private function getFileMapper(): ResourceMapper {
    return \Drupal::service('dkan.metastore.resource_mapper');
  }

  /**
   * Private.
   */
  public static function hostify($url) {
    $host = \Drupal::request()->getHost();
    $parsedUrl = parse_url($url);
    if (isset($parsedUrl['host']) && $parsedUrl['host'] == $host) {
      $parsedUrl['host'] = UrlHostTokenResolver::TOKEN;
      $url = self::unparseUrl($parsedUrl);
    }
    return $url;
  }

  /**
   * Private.
   */
  private static function unparseUrl($parsedUrl) {
    $url = '';
    $urlParts = [
      'scheme',
      'host',
      'port',
      'user',
      'pass',
      'path',
      'query',
      'fragment',
    ];

    foreach ($urlParts as $part) {
      if (!isset($parsedUrl[$part])) {
        continue;
      }
      $url .= ($part == "port") ? ':' : '';
      $url .= ($part == "query") ? '?' : '';
      $url .= ($part == "fragment") ? '#' : '';
      $url .= $parsedUrl[$part];
      $url .= ($part == "scheme") ? '://' : '';
    }

    return $url;
  }

  /**
   * Private.
   */
  private function getMimeType($metadata) {
    $mimeType = "text/plain";
    if (isset($metadata->data->mediaType)) {
      $mimeType = $metadata->data->mediaType;
    }
    elseif (isset($metadata->data->downloadURL)) {
      $headers = get_headers($metadata->data->downloadURL, 1);
      $mimeType = isset($headers['Content-Type']) ? $headers['Content-Type'] : $mimeType;
    }
    return $mimeType;
  }

  /**
   * Checks for an existing value reference for that property id.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param string|object $data
   *   The property's value used to find an existing reference.
   *
   * @return string|null
   *   The existing reference's uuid, or NULL if not found.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function checkExistingReference(string $property_id, $data) {
    $nodes = $this->nodeStorage
      ->loadByProperties([
        'field_data_type' => $property_id,
        'title' => md5(json_encode($data)),
      ]);

    if ($node = reset($nodes)) {
      return $node->uuid();
    }
    return NULL;
  }

  /**
   * Creates a new value reference for that property id in a data node.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param string|object $value
   *   The property's value.
   *
   * @return string|null
   *   The new reference's uuid, or NULL.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function createPropertyReference(string $property_id, $value) {
    // Create json metadata for the reference.
    $data = new \stdClass();
    $data->identifier = $this->getUuidService()->generate($property_id, $value);
    $data->data = $value;

    // Create node to store this reference.
    $node = $this->nodeStorage
      ->create([
        'title' => md5(json_encode($value)),
        'type' => 'data',
        'uuid' => $data->identifier,
        'field_data_type' => $property_id,
        'field_json_metadata' => json_encode($data),
        // Unlike datasets, always publish references immediately.
        'moderation_state' => 'published',
      ]);
    $node->save();
    return $node->uuid();
  }

}
