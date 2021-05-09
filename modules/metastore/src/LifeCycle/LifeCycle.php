<?php

namespace Drupal\metastore\LifeCycle;

use Drupal\common\EventDispatcherTrait;
use Drupal\common\Resource;
use Drupal\common\UrlHostTokenResolver;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\metastore\Events\PreReference;
use Drupal\metastore\NodeWrapper\Data;
use Drupal\metastore\Reference\Dereferencer;
use Drupal\metastore\Reference\OrphanChecker;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\Traits\ResourceMapperTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Data.
 */
class LifeCycle implements LifeCycleInterface, ContainerInjectionInterface {
  use ResourceMapperTrait;
  use EventDispatcherTrait;

  const EVENT_PRE_REFERENCE = 'dkan_metastore_metadata_pre_reference';

  /**
   * A metastore item.
   *
   * @var Drupal\metastore\NodeWrapper\Data
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
    DateFormatter $dateFormatter
  ) {
    $this->referencer = $referencer;
    $this->dereferencer = $dereferencer;
    $this->orphanChecker = $orphanChecker;
    $this->dateFormatter = $dateFormatter;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.referencer'),
      $container->get('dkan.metastore.dereferencer'),
      $container->get('dkan.metastore.orphan_checker'),
      $container->get('date.formatter')
    );
  }

  /**
   * Protected.
   */
  protected function go($stage) {
    $method = "{$this->data->getDataType()}{$stage}";
    if (method_exists($this, $method)) {
      $this->{$method}();
    }
  }

  /**
   * Load.
   */
  public function load(Data $data) {
    $this->go('Load');
  }

  /**
   * Presave.
   *
   * Activities to move a data node through during presave.
   */
  public function presave(Data $data) {
    $this->go('Presave');
  }

  /**
   * Predelete.
   */
  public function predelete(Data $data) {
    $this->go('Predelete');
  }

  /**
   * Protected.
   */
  protected function datasetPredelete(Data $data) {
    $raw = $this->data->getRawMetadata();

    if (is_object($raw)) {
      $referencer = \Drupal::service("dkan.metastore.orphan_checker");
      $referencer->processReferencesInDeletedDataset($raw);
    }
  }

  /**
   * Private.
   */
  protected function datasetLoad() {
    $metadata = $this->data->getMetaData();

    // Dereference dataset properties.
    $dereferencer = \Drupal::service("dkan.metastore.dereferencer");
    $metadata = $dereferencer->dereference($metadata);
    $metadata = $this->addNodeModifiedDate($metadata);

    $this->data->setMetadata($metadata);
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
   * Private.
   */
  private function retrieveDownloadUrlFromResourceMapper($resourceIdentifier) {
    $reference = [];
    $original = NULL;

    $fileMapperInfo = Resource::parseUniqueIdentifier($resourceIdentifier);

    /** @var \Drupal\common\Resource $sourceResource */
    $sourceResource = $this->getFileMapper()->get($fileMapperInfo['identifier'],
      Resource::DEFAULT_SOURCE_PERSPECTIVE, $fileMapperInfo['version']);
    if (!$sourceResource) {
      return [$reference, $original];
    }

    $reference[] = $this->createResourceReference($sourceResource);

    $perspective = resource_mapper_display();

    /** @var \Drupal\common\Resource $sourceResource */
    $resource = $sourceResource;

    if ($perspective != Resource::DEFAULT_SOURCE_PERSPECTIVE) {
      $new = $this->getFileMapper()->get(
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

    /** @var \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher */
    $eventDispatcher = \Drupal::service("event_dispatcher");
    $eventDispatcher->dispatch(self::EVENT_PRE_REFERENCE,
      new PreReference($this->data));

    $referencer = \Drupal::service("dkan.metastore.referencer");
    $metadata = $referencer->reference($metadata);

    $this->data->setMetadata($metadata);

    // Check for possible orphan property references when updating a dataset.
    if (!$this->data->isNew()) {
      $raw = $this->data->getRawMetadata();
      $orphanChecker = \Drupal::service("dkan.metastore.orphan_checker");
      $orphanChecker->processReferencesInUpdatedDataset(
        $raw,
        $metadata
      );
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
  private function addNodeModifiedDate($metadata) {
    $formattedChangedDate = \Drupal::service('date.formatter')
      ->format($this->data->getModifiedDate(), 'html_date');
    $metadata->{'%modified'} = $formattedChangedDate;
    return $metadata;
  }

}
