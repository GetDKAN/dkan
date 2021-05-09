<?php

namespace Drupal\metastore\LifeCycle;

use Drupal\common\EventDispatcherTrait;
use Drupal\common\Resource;
use Drupal\common\UrlHostTokenResolver;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\metastore\Events\DatasetUpdate;
use Drupal\metastore\Events\PreReference;
use Drupal\metastore\MetastoreItemInterface;
use Drupal\metastore\Reference\Dereferencer;
use Drupal\metastore\Reference\OrphanChecker;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Traits\ResourceMapperTrait;
use Drupal\metastore\Storage\MetastoreEntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Data.
 */
class LifeCycle implements ContainerInjectionInterface {
  use EventDispatcherTrait;

  const EVENT_PRE_REFERENCE = 'dkan_metastore_metadata_pre_reference';

  /**
   * A metastore item.
   *
   * @var \Drupal\metastore\MetastoreItemInterface
   */
  protected $data;

  /**
   * Referencer service.
   *
   * @var \Drupal\metastore\Reference\Referencer
   */
  protected $referencer;

  /**
   * Dereferencer.
   *
   * @var \Drupal\metastore\Reference\Dereferencer
   */
  protected $dereferencer;

  /**
   * OrphanChecker service.
   *
   * @var \Drupal\metastore\Reference\OrphanChecker
   */
  protected $orphanChecker;

  /**
   * ResourceMapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected $resourceMapper;

  /**
   * DateFormatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Constructor.
   */
  public function __construct(
    Referencer $referencer,
    Dereferencer $dereferencer,
    OrphanChecker $orphanChecker,
    ResourceMapper $resourceMapper,
    DateFormatter $dateFormatter
  ) {
    $this->referencer = $referencer;
    $this->dereferencer = $dereferencer;
    $this->orphanChecker = $orphanChecker;
    $this->resourceMapper = $resourceMapper;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * Factory method.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.referencer'),
      $container->get('dkan.metastore.dereferencer'),
      $container->get('dkan.metastore.orphan_checker'),
      $container->get('dkan.metastore.resource_mapper'),
      $container->get('date.formatter')
    );
  }

  /**
   * Entry point for LifeCycle functions.
   *
   * @param string $stage
   *   Stage or hook name for execution.
   * @param Drupal\metastore\MetastoreItemInterface $data
   *   Metastore item object.
   */
  public function go($stage, MetastoreItemInterface $data) {
    $this->data = $data;
    $stage = ucwords($stage);
    $method = "{$this->data->getSchemaId()}{$stage}";
    if (method_exists($this, $method)) {
      $this->{$method}();
    }
  }

  /**
   * Dataset preDelete.
   */
  protected function datasetPredelete() {
    $raw = $this->data->getRawMetadata();

    if (is_object($raw)) {
      $referencer = \Drupal::service("dkan.metastore.orphan_checker");
      $referencer->processReferencesInDeletedDataset($raw);
    }
  }

  /**
   * Dataset load.
   */
  protected function datasetLoad() {
    $metadata = $this->data->getMetaData();

    // Dereference dataset properties.
    $metadata = $this->dereferencer->dereference($metadata);
    $metadata = $this->addDatasetModifiedDate($metadata);

    $this->data->setMetadata($metadata);
  }

  /**
   * Purge resources (if unneeded) of any updated dataset.
   */
  protected function datasetUpdate() {
    $this->dispatchEvent(
      MetastoreEntityStorageInterface::EVENT_DATASET_UPDATE,
      new DatasetUpdate($this->data)
    );
  }

  /**
   * Private.
   *
   * @todo Decouple "resource" functionality from specific dataset properties.
   */
  protected function distributionLoad() {
    $metadata = $this->data->getMetaData();

    if (!isset($metadata->data->downloadURL)) {
      return;
    }

    $downloadUrl = $metadata->data->downloadURL;

    if (isset($downloadUrl) && !filter_var($downloadUrl, FILTER_VALIDATE_URL)) {
      $resourceIdentifier = $downloadUrl;
      $ref = NULL;
      $original = NULL;
      [$ref, $original] = $this->retrieveDownloadUrlFromResourceMapper($resourceIdentifier);

      $downloadUrl = isset($original) ? $original : "";

      $refProperty = "%Ref:downloadURL";
      $metadata->data->{$refProperty} = count($ref) == 0 ? NULL : $ref;
    }

    if (is_string($downloadUrl)) {
      $downloadUrl = UrlHostTokenResolver::resolve($downloadUrl);
    }

    $metadata->data->downloadURL = $downloadUrl;

    $this->data->setMetadata($metadata);
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
  private function retrieveDownloadUrlFromResourceMapper(string $resourceIdentifier) {
    $reference = [];
    $original = NULL;

    $fileMapperInfo = Resource::parseUniqueIdentifier($resourceIdentifier);

    // Load resource object.
    $sourceResource = $this->resourceMapper->get(
      $fileMapperInfo['identifier'],
      Resource::DEFAULT_SOURCE_PERSPECTIVE,
      $fileMapperInfo['version']
    );

    if (!$sourceResource) {
      return [$reference, $original];
    }

    $reference[] = $this->createResourceReference($sourceResource);

    $perspective = resource_mapper_display();

    $resource = $sourceResource;

    if ($perspective != Resource::DEFAULT_SOURCE_PERSPECTIVE) {
      $new = $this->resourceMapper->get(
        $fileMapperInfo['identifier'],
        $perspective,
        $fileMapperInfo['version']);
      if ($new) {
        $resource = $new;
        $reference[] = $this->createResourceReference($resource);
      }
    }
    $original = $resource->getFilePath();

    return [$reference, $original];
  }

  /**
   * Private.
   */
  private function createResourceReference(Resource $resource): object {
    return (object) [
      "identifier" => $resource->getUniqueIdentifier(),
      "data" => $resource,
    ];
  }

  /**
   * Private.
   */
  protected function datasetPresave() {
    $metadata = $this->data->getMetaData();

    $title = isset($metadata->title) ? $metadata->title : $metadata->name;
    $this->data->setTitle($title);

    // If there is no uuid add one.
    if (!isset($metadata->identifier)) {
      $metadata->identifier = $this->data->getIdentifier();
    }
    // If one exists in the uuid it should be the same in the table.
    else {
      $this->data->setIdentifier($metadata->identifier);
    }

    $this->dispatchEvent(self::EVENT_PRE_REFERENCE, new PreReference($this->data));

    $metadata = $this->referencer->reference($metadata);

    $this->data->setMetadata($metadata);

    // Check for possible orphan property references when updating a dataset.
    if (!$this->data->isNew()) {
      $raw = $this->data->getRawMetadata();
      $this->orphanChecker->processReferencesInUpdatedDataset($raw, $metadata);
    }

  }

  /**
   * Private.
   */
  protected function distributionPresave() {
    $metadata = $this->data->getMetaData();
    $this->data->setMetadata($metadata);
  }

  /**
   * Private.
   */
  private function addDatasetModifiedDate($metadata) {
    $formattedChangedDate = $this->dateFormatter->format($this->data->getModifiedDate(), 'html_date');
    $metadata->{'%modified'} = $formattedChangedDate;
    return $metadata;
  }

}
