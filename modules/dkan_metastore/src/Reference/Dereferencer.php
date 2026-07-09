<?php

namespace Drupal\dkan_metastore\Reference;

use Contracts\FactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\dkan_common\DataResource;
use Drupal\dkan_common\UrlHostTokenResolver;
use Psr\Log\LoggerInterface;
use Drupal\dkan_metastore\Exception\MissingObjectException;
use Drupal\dkan_metastore\ResourceMapper;

/**
 * Metastore dereferencer.
 */
class Dereferencer {
  use HelperTrait;

  const REF_PREFIX = '%Ref:';

  /**
   * Constructor.
   */
  public function __construct(
    ConfigFactoryInterface $configService,
    protected FactoryInterface $storageFactory,
    protected MetastoreUrlGenerator $metastoreUrlGenerator,
    protected ResourceMapper $resourceMapper,
    protected LoggerInterface $logger,
  ) {
    $this->setConfigService($configService);
  }

  /**
   * Replaces value references in a dataset with their actual values.
   *
   * @param object $data
   *   The json metadata object.
   *
   * @return mixed
   *   Modified json metadata object.
   */
  public function dereference($data) {
    $this->validate($data);

    foreach ($this->getPropertyList() as $propertyId) {
      if (isset($data->{$propertyId})) {
        $this->dereferenceProperty($propertyId, $data);
      }
    }
    // We dereference the properties within distributions next, agnostic to
    // weather or not the distribution itself is a reference.
    $this->dereferenceDistributions($data);
    return $data;
  }

  /**
   * Dereferences property and handles empty values if any.
   *
   * @param string $propertyId
   *   The dataset property id.
   * @param object $data
   *   Modified json metadata object.
   */
  private function dereferenceProperty(string $propertyId, $data) {
    $referenceProperty = self::REF_PREFIX . "{$propertyId}";
    $ref = NULL;
    $actual = NULL;
    [$ref, $actual] = $this->dereferencePropertyUuid($propertyId, $data->{$propertyId});
    if (!empty($ref) && !empty($actual)) {
      $data->{$referenceProperty} = $ref;
      $data->{$propertyId} = $actual;
    }
    else {
      unset($data->{$propertyId});
    }
  }

  /**
   * Replaces a property reference with its actual value, general case.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param string|array $uuid
   *   A single reference uuid string, or an array of reference uuids.
   *
   * @return mixed
   *   An array of dereferenced values, a single one, or NULL.
   */
  private function dereferencePropertyUuid(string $property_id, $uuid) {
    if (is_array($uuid)) {
      return $this->dereferenceMultiple($property_id, $uuid);
    }
    elseif (is_string($uuid) && $this->getUuidService()->isValid($uuid)) {
      return $this->dereferenceSingle($property_id, $uuid);
    }
    else {
      $this->logger->error('Unexpected data type when dereferencing property_id: @property_id with uuid: @uuid', [
        '@property_id' => $property_id,
        '@uuid' => var_export($uuid, TRUE),
      ]);
      return NULL;
    }
  }

  /**
   * Replaces a property reference with its actual value, array case.
   *
   * @param string $property_id
   *   A dataset property id.
   * @param array $uuids
   *   An array of reference uuids.
   *
   * @return array
   *   An array of dereferenced values.
   */
  private function dereferenceMultiple(string $property_id, array $uuids) : array {
    $result = [];
    $reference = [];
    $ref = NULL;
    $actual = NULL;
    foreach ($uuids as $uuid) {
      [$ref, $actual] = $this->dereferenceSingle($property_id, $uuid);
      if (NULL !== $ref && NULL !== $actual) {
        $result[] = $actual;
        $reference[] = $ref;
      }
    }
    return [$reference, $result];
  }

  /**
   * Replaces a property reference with its actual value, string or object case.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param string $uuid
   *   Either a uuid or an actual json value.
   *
   * @return object|string
   *   The data from this reference.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function dereferenceSingle(string $property_id, string $uuid) {
    $storage = $this->storageFactory->getInstance($property_id);
    try {
      $value = $storage->retrieve($uuid);
    }
    catch (MissingObjectException) {
      $value = FALSE;
    }

    if ($value) {
      $metadata = json_decode((string) $value);
      return [$metadata, $metadata->data];
    }

    // If a property node was not found, it most likely means it was deleted
    // while still being referenced.
    $this->logger->error('Property @property_id reference @uuid not found', [
      '@property_id' => $property_id,
      '@uuid' => var_export($uuid, TRUE),
    ]);

    return [NULL, NULL];
  }

  /**
   * For each distribution in the dataset, dereference downloadURL, describedBy.
   *
   * @param object $data
   *   The json metadata object.
   */
  public function dereferenceDistributions($data) {
    if (!isset($data->distribution) || !is_array($data->distribution)) {
      return;
    }
    foreach ($data->distribution as &$distribution) {
      $this->dereferenceResource($distribution);
      $this->dereferenceDataDictionary($distribution);
    }
    unset($distribution);
  }

  /**
   * Dereference a distribution resource.
   *
   * @param object $distribution
   *   The distribution object.
   */
  public function dereferenceResource($distribution) {
    if (!isset($distribution->downloadURL)) {
      return;
    }

    $downloadUrl = $distribution->downloadURL;

    if (!empty($downloadUrl) && filter_var($downloadUrl, FILTER_VALIDATE_URL) === FALSE) {
      $ref = NULL;
      $original = NULL;
      [$ref, $original] = $this->retrieveDownloadUrlFromResourceMapper($downloadUrl);

      $downloadUrl = $original ?? "";
      $distribution->{self::REF_PREFIX . "downloadURL"} = count($ref) == 0 ? NULL : $ref;
    }
    if (is_string($downloadUrl)) {
      $downloadUrl = UrlHostTokenResolver::resolve($downloadUrl);
    }
    $unset_downloadUrl = $this->configService->get('dkan_metastore.settings')
      ->get('unset_download_url_if_empty') ?? FALSE;
    if (!$downloadUrl && $unset_downloadUrl) {
      unset($distribution->downloadURL);
    }
    else {
      $distribution->downloadURL = $downloadUrl;
    }
  }

  /**
   * Dereference/normalize a distribution data dictionary.
   *
   * @param object $distribution
   *   The distribution object.
   */
  public function dereferenceDataDictionary($distribution) {
    if (!isset($distribution->describedBy)) {
      return;
    }

    // If describedBy contains dkan:// URI, convert to absolute URL.
    if (StreamWrapperManager::getScheme($distribution->describedBy ?? '') == MetastoreUrlGenerator::DKAN_SCHEME) {
      $distribution->describedBy = $this->metastoreUrlGenerator->absoluteString($distribution->describedBy);
    }
  }

  /**
   * Get a download URL.
   *
   * @param string $resourceIdentifier
   *   Identifier for resource.
   *
   * @return array
   *   Array of reference and original.
   */
  protected function retrieveDownloadUrlFromResourceMapper(string $resourceIdentifier) {
    $reference = [];
    $original = NULL;

    $info = DataResource::parseUniqueIdentifier($resourceIdentifier);

    // Load resource object.
    $sourceResource = $this->resourceMapper->get($info['identifier'], DataResource::DEFAULT_SOURCE_PERSPECTIVE, $info['version']);

    if (!$sourceResource) {
      return [$reference, $original];
    }

    $reference[] = $this->createResourceReference($sourceResource);
    $perspective = $this->configService->get('dkan_metastore.settings')->get('resource_perspective_display')
      ?: DataResource::DEFAULT_SOURCE_PERSPECTIVE;
    $resource = $sourceResource;

    $new = $this->resourceMapper->get($info['identifier'], $perspective, $info['version']);
    if ($perspective != DataResource::DEFAULT_SOURCE_PERSPECTIVE && $new) {
      $resource = $new;
      $reference[] = $this->createResourceReference($resource);
    }
    $original = $resource->getFilePath();

    return [$reference, $original];
  }

  /**
   * Create a resource reference object.
   *
   * @param \Drupal\dkan_common\DataResource $resource
   *   The data resource.
   *
   * @return object
   *   The resource reference object.
   */
  protected function createResourceReference(DataResource $resource): object {
    return (object) [
      "identifier" => $resource->getUniqueIdentifier(),
      "data" => $resource,
    ];
  }

  /**
   * Validates data.
   *
   * @param object $data
   *   The json metadata object.
   *
   * @throws \Exception
   */
  private function validate($data) {
    if (!is_object($data)) {
      throw new \Exception("data must be an object.");
    }
  }

}
