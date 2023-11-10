<?php

namespace Drupal\metastore\Reference;

use Contracts\FactoryInterface;
use Drupal\common\DataResource;
use Drupal\common\LoggerTrait;
use Drupal\common\UrlHostTokenResolver;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\metastore\Exception\AlreadyRegistered;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\ResourceMapper;

use GuzzleHttp\Client as GuzzleClient;

/**
 * Metastore referencer service.
 */
class Referencer {
  use HelperTrait;
  use LoggerTrait;

  /**
   * Default Mime Type to use when mime type detection fails.
   *
   * @var string
   */
  protected const DEFAULT_MIME_TYPE = 'text/plain';

  /**
   * Storage factory interface service.
   *
   * @var \Contracts\FactoryInterface
   */
  private $storageFactory;

  /**
   * Metastore URL Generator service.
   *
   * @var \Drupal\metastore\Reference\MetastoreUrlGenerator
   */
  public MetastoreUrlGenerator $metastoreUrlGenerator;

  /**
   * Constructor.
   */
  public function __construct(
    ConfigFactoryInterface $configService,
    FactoryInterface $storageFactory,
    MetastoreUrlGenerator $metastoreUrlGenerator
  ) {
    $this->setConfigService($configService);
    $this->storageFactory = $storageFactory;
    $this->setLoggerFactory(\Drupal::service('logger.factory'));
    $this->metastoreUrlGenerator = $metastoreUrlGenerator;
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
  public function reference($data, $raw) {
    if (!is_object($data)) {
      throw new \Exception("data must be an object.");
    }
    // Cycle through the dataset properties we seek to reference.
    foreach ($this->getPropertyList() as $property_id) {
      if (isset($data->{$property_id})) {
        $data->{$property_id} = $this->referenceProperty($property_id, $data->{$property_id}, $raw->{$property_id});
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
  private function referenceProperty(string $property_id, $data, $raw) {
    if (is_array($data)) {
      return $this->referenceMultiple($property_id, $data, $raw);
    }
    else {
      // Case for $data being an object or a string.
      return $this->referenceSingle($property_id, $data, $raw);
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
  private function referenceMultiple(string $property_id, array $values, $raw) : array {
    $result = [];
    foreach ($values as $key => $value) {
      $data = $this->referenceSingle($property_id, $value, $raw[$key]);
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
   * @return string|null
   *   The Uuid reference, or NULL on failure.
   */
  private function referenceSingle(string $property_id, $value, $raw) {

    if ($property_id == 'distribution') {
      $value = $this->distributionHandling($value);
    }

    $uuid = $this->checkExistingReference($property_id, $value, $raw);

    if (!$uuid) {
      $uuid = $this->createPropertyReference($property_id, $value);
    }
    else {
      $data = new \stdClass();
      $data->identifier = $uuid;
      $data->data = $value;
      $json = json_encode($data);

      // Create node to store this reference.
      $storage = $this->storageFactory->getInstance($property_id);
      $storage->store($json, $data->identifier);
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
   * Attempt to register this distribution's resource with the resource mapper.
   *
   * If this distribution has a resource, register it with the resource mapper
   * and replace the download URL with a resource ID.
   *
   * @param object $distribution
   *   A dataset distribution object.
   *
   * @return object
   *   The supplied distribution with an updated resource download URL.
   */
  public function distributionHandling($distribution): object {
    // Ensure the supplied distribution has a valid resource before attempting
    // to register it with the resource mapper.
    if (isset($distribution->downloadURL)) {
      // Register this distribution's resource with the resource mapper and
      // replace the download URL with a unique ID registered in the resource
      // mapper.
      $distribution->downloadURL = $this->registerWithResourceMapper(
        UrlHostTokenResolver::hostify($distribution->downloadURL), $this->getMimeType($distribution));
    }

    // If there is a describedBy value, convert to dkan:// URL if appropriate.
    if ($distribution->describedBy ?? FALSE) {
      $distribution->describedBy = $this->normalizeDictionaryValue($distribution->describedBy);
    }

    return $distribution;
  }

  /**
   * Normalize an incoming URL to a reference ID.
   *
   * @param string $value
   *   Value for describedBy field, usually a URL.
   *
   * @return string
   *   Metastore Item ID.
   */
  protected function normalizeDictionaryValue(string $value): string {
    $incoming_scheme = StreamWrapperManager::getScheme($value);
    try {
      $uri = ($incoming_scheme) ? $this->metastoreUrlGenerator->uriFromUrl($value) : $value;
    }
    // If the URL cannot be converted to a DKAN URI, pass it through.
    catch (\DomainException $e) {
      return $value;
    }
    // If it was converted to DKAN URI, validate it as a data dictionary.
    if (!$this->metastoreUrlGenerator->validateUri($uri, 'data-dictionary')) {
      throw new \DomainException("The value $value, is not a valid data-dictionary URI.");
    }
    return $uri;
  }

  /**
   * Register the supplied resource details with the resource mapper.
   *
   * @param string $downloadUrl
   *   The download URL for the resource being registered.
   * @param string $mimeType
   *   The mime type for the resource being registered.
   *
   * @return string
   *   A unique ID for the resource generated using the supplied details.
   */
  private function registerWithResourceMapper(string $downloadUrl, string $mimeType): string {
    try {
      // Create a new resource using the supplied resource details.
      $resource = new DataResource($downloadUrl, $mimeType);

      // Attempt to register the url with the resource file mapper.
      if ($this->getFileMapper()->register($resource)) {
        // Upon successful registration, replace the download URL with a unique
        // ID generated by the resource mapper.
        $downloadUrl = $resource->getUniqueIdentifier();
      }
    }
    catch (AlreadyRegistered $e) {
      $info = json_decode($e->getMessage());

      // If resource mapper registration failed due to this resource already
      // being registered, generate a new version of the resource and update the
      // download URL with the new version ID.
      if (isset($info[0]->identifier)) {
        /** @var \Drupal\common\DataResource $stored */
        $stored = $this->getFileMapper()->get($info[0]->identifier, DataResource::DEFAULT_SOURCE_PERSPECTIVE);
        $downloadUrl = $this->handleExistingResource($info, $stored, $mimeType);
      }
    }

    return $downloadUrl;
  }

  /**
   * Get download URL for existing resource.
   *
   * @param array $info
   *   Info.
   * @param \Drupal\common\DataResource $stored
   *   Stored data resource object.
   * @param string $mimeType
   *   MIME type.
   *
   * @return string
   *   The download URL.
   */
  private function handleExistingResource(array $info, DataResource $stored, string $mimeType): string {
    if ($info[0]->perspective == DataResource::DEFAULT_SOURCE_PERSPECTIVE &&
      (ResourceMapper::newRevision() == 1 || $stored->getMimeType() != $mimeType)) {
      $new = $stored->createNewVersion();
      // Update the MIME type, since this may be updated by the user.
      $new->changeMimeType($mimeType);

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
   * Determine the mime type of the supplied local file.
   *
   * @param string $downloadUrl
   *   Local resource file path.
   *
   * @return string|null
   *   The detected mime type or NULL on failure.
   */
  private function getLocalMimeType(string $downloadUrl): ?string {
    $mime_type = NULL;

    // Retrieve and decode the file name from the supplied download URL's path.
    $filename = \Drupal::service('file_system')->basename($downloadUrl);
    $filename = urldecode($filename);

    // Attempt to load the file by file name.
    /** @var \Drupal\file\FileInterface[] $files */
    $files = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties(['filename' => $filename]);
    $file = reset($files);

    // If a valid file was found for the given file name, extract the file's
    // mime type...
    if ($file !== FALSE) {
      $mime_type = $file->getMimeType();
    }
    // Otherwise, log an error notifying the user that a file was not found.
    else {
      $this->log('value_referencer', 'Unable to determine mime type of file with name "@name", because no file was found with that name.', [
        '@name' => $filename,
      ]);
    }

    return $mime_type;
  }

  /**
   * Determine the mime type of the supplied remote file.
   *
   * @param string $downloadUrl
   *   Remote resource file URL.
   *
   * @return string|null
   *   The detected mime type, or NULL on failure.
   */
  private function getRemoteMimeType(string $downloadUrl): ?string {
    $mime_type = NULL;

    // Perform HTTP Head request against the supplied URL in order to determine
    // the content type of the remote resource.
    $client = new GuzzleClient();
    $response = $client->head($downloadUrl);
    // Extract the full value of the content type header.
    $content_type = $response->getHeader('Content-Type');
    // Attempt to extract the mime type from the content type header.
    if (isset($content_type[0])) {
      $mime_type = $content_type[0];
    }

    return $mime_type;
  }

  /**
   * Determine the mime type of the supplied distribution's resource.
   *
   * @param object $distribution
   *   A dataset distribution object.
   *
   * @return string
   *   The detected mime type, or DEFAULT_MIME_TYPE on failure.
   *
   * @todo Update the UI to set mediaType when a format is selected.
   */
  private function getMimeType($distribution): string {
    $mimeType = "text/plain";

    // If we have a mediaType set, use that.
    if (isset($distribution->mediaType)) {
      $mimeType = $distribution->mediaType;
    }
    // Fall back if we have an importable format set.
    elseif (isset($distribution->format) && $distribution->format == 'csv') {
      $mimeType = 'text/csv';
    }
    elseif (isset($distribution->format) && $distribution->format == 'tsv') {
      $mimeType = 'text/tab-separated-values';
    }
    // Otherwise, determine the proper mime type using the distribution's
    // download URL.
    elseif (isset($distribution->downloadURL)) {
      // Determine whether the supplied distribution has a local or remote
      // resource.
      $is_local = $distribution->downloadURL !== UrlHostTokenResolver::hostify($distribution->downloadURL);
      $mimeType = $is_local ?
        $this->getLocalMimeType($distribution->downloadURL) :
        $this->getRemoteMimeType($distribution->downloadURL);
    }

    return $mimeType ?? self::DEFAULT_MIME_TYPE;
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
  private function checkExistingReference(string $property_id, $data, $raw) {
    $storage = $this->storageFactory->getInstance($property_id);
    if (!empty($raw) && is_string($raw)) {
      $nodes = $storage->getEntityStorage()->loadByProperties([
        'field_data_type' => $property_id,
        'uuid' => $raw,
      ]);
    }
    if (empty($nodes)) {
      $nodes = $storage->getEntityStorage()->loadByProperties([
        'field_data_type' => $property_id,
        'title' => MetastoreService::metadataHash($data),
      ]);
    }

    if ($node = reset($nodes)) {
      // @todo if referencing node in draft state, don't publish referenced node
      // If an existing referenced node is found but unpublished, publish it.
      if ($node->get('moderation_state')->value !== "published") {
        $node->set('moderation_state', 'published');
        $node->save();
      }
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
    $json = json_encode($data);

    // Create node to store this reference.
    $storage = $this->storageFactory->getInstance($property_id);
    $entity_uuid = $storage->store($json, $data->identifier);
    return $entity_uuid;
  }

}
