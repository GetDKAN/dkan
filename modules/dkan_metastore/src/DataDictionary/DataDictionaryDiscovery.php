<?php

namespace Drupal\dkan_metastore\DataDictionary;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\dkan_metastore\Reference\HelperTrait;
use Drupal\dkan_metastore\Reference\MetastoreUrlGenerator;
use Drupal\dkan_metastore\ReferenceLookupInterface;
use Drupal\dkan_metastore\MetastoreService;
use RootedData\RootedJsonData;

/**
 * Data dictionary service.
 *
 * Find the correct data dictionary for a dataset or distribution.
 */
class DataDictionaryDiscovery implements DataDictionaryDiscoveryInterface {

  use HelperTrait;

  const DICT_MIMETYPE = 'application/vnd.tableschema+json';

  /**
   * Metastore settings config object.
   */
  protected Config $config;

  /**
   * Metastore service.
   */
  protected MetastoreService $metastore;

  /**
   * Reference lookup service.
   */
  protected ReferenceLookupInterface $lookup;

  /**
   * URL generator service.
   */
  protected MetastoreUrlGenerator $urlGenerator;

  /**
   * Constructor.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    MetastoreService $metastore,
    ReferenceLookupInterface $lookup,
    MetastoreUrlGenerator $urlGenerator,
  ) {
    $this->config = $configFactory->get('dkan_metastore.settings');
    $this->metastore = $metastore;
    $this->lookup = $lookup;
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public function dictionaryIdFromResource(string $resourceId, int $resourceIdVersion): ?string {
    $mode = $this->getDataDictionaryMode();
    return match ($mode) {
      self::MODE_NONE => "Disabled",
      self::MODE_SITEWIDE => $this->getSitewideDictionaryId(),
      self::MODE_REFERENCE => $this->getReferenceDictionaryId($resourceId, $resourceIdVersion),
      default => throw new \OutOfRangeException(sprintf('Unsupported data dictionary mode "%s"', $mode)),
    };
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceDictionaryId(string $resourceId, int $resourceIdVersion): ?string {
    $resource_id = $resourceId . "__" . $resourceIdVersion;
    $datasetId = $this->getDatasetId($resource_id);
    if ($datasetId === NULL) {
      return NULL;
    }
    $dataset = $this->metastore->get('dataset', $datasetId);
    return $this->extractDictionaryId($dataset);
  }

  /**
   * Get the dataset ID for a given resource ID.
   */
  private function getDatasetId(string $resource_id): ?string {
    $referencers = $this->lookup->getReferencers('dataset', $resource_id, 'downloadURL');
    if (empty($referencers)) {
      throw new \RuntimeException("Dataset lookup: Can not map resource ID {$resource_id} to dataset UUID. Please make sure your resource exists in the database.");
    }
    return $referencers[0] ?? NULL;
  }

  /**
   * Extract the data dictionary ID from the describedBy URL or a dataset.
   *
   * @param RootedData\RootedJsonData $dataset
   *   The dataset.
   *
   * @return string|null
   *   The data dictionary ID or NULL if none exists.
   */
  protected function extractDictionaryId(RootedJsonData $dataset): ?string {
    foreach ($dataset->{'$.distribution'} ?? [] as $distribution) {
      if ($distribution['describedByType'] ?? NULL === self::DICT_MIMETYPE) {
        $describedBy = $distribution['describedBy'] ?? '';
        try {
          $uri = $this->urlGenerator->uriFromUrl($describedBy);
          return $this->urlGenerator->extractItemId($uri, "data-dictionary");
        }
        catch (\DomainException) {
          continue;
        }
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSitewideDictionaryId(): string {
    if ($identifier = $this->config->get('data_dictionary_sitewide')) {
      return $identifier;
    }
    throw new \OutOfBoundsException("Attempted to retrieve a sitewide data dictionary, but none was set.");
  }

  /**
   * {@inheritdoc}
   */
  public function getDataDictionaryMode(): string {
    return $this->config->get('data_dictionary_mode') ?? self::MODE_NONE;
  }

}
