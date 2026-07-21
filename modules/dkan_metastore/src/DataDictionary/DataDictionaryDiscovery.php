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
   * Constructor.
   */
  public function __construct(
    ConfigFactoryInterface $configService,
    protected MetastoreService $metastore,
    protected ReferenceLookupInterface $lookup,
    protected MetastoreUrlGenerator $urlGenerator,
  ) {
    $this->config = $configService->get('dkan_metastore.settings');
    $this->setConfigService($configService);
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

    $distribution = $this->getDistributionObject($resource_id);
    if ($distribution === NULL) {
      return NULL;
    }
    if (!$this->hasValidDescribedBy($distribution)) {
      return NULL;
    }
    return $this->extractDictionaryId($distribution->describedBy);
  }

  /**
   * Get the distribution ID for a given resource ID.
   */
  private function getDistributionObject(string $resource_id): ?object {
    if ($this->distributionsAreReferenced()) {
      $referencers = $this->lookup->getReferencers('distribution', $resource_id, 'downloadURL');
      if (empty($referencers)) {
        throw new \RuntimeException("Distribution lookup: Can not map resource ID {$resource_id} to distribution UUID. Please make sure your resource exists in the database.");
      }
      $distribution = $this->metastore->get("distribution", $referencers[0]);
      return (object) $distribution->{"$.data"};
    }
    // Otherwise look for a dataset that references the resource ID.
    $referencers = $this->lookup->getReferencers('dataset', $resource_id, 'downloadURL');
    if (empty($referencers)) {
      throw new \RuntimeException("Dataset lookup: Can not map resource ID {$resource_id} to dataset UUID. Please make sure your resource exists in the database.");
    }
    $dataset = $this->metastore->get("dataset", $referencers[0]);
    foreach ($dataset->{"$.distribution"} ?? [] as $distribution) {
      if ($distribution['downloadURL'] ?? NULL === $resource_id) {
        return (object) $distribution;
      }
    }
    return NULL;
  }

  /**
   * Verify that the distribution has a valid describedBy URL.
   */
  private function hasValidDescribedBy(object $distribution): bool {
    return isset($distribution->describedBy) && (($distribution->describedByType ?? NULL) == self::DICT_MIMETYPE);
  }

  /**
   * Extract the data dictionary ID from the describedBy URL.
   */
  private function extractDictionaryId(string $describedBy): ?string {
    try {
      $uri = $this->urlGenerator->uriFromUrl($describedBy);
      return $this->urlGenerator->extractItemId($uri, "data-dictionary");
    }
    catch (\DomainException) {
      return NULL;
    }
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
