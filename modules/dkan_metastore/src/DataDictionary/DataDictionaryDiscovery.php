<?php

namespace Drupal\metastore\DataDictionary;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\metastore\Reference\MetastoreUrlGenerator;
use Drupal\metastore\ReferenceLookupInterface;
use Drupal\metastore\MetastoreService;

/**
 * Data dictionary service.
 *
 * Find the correct data dictionary for a dataset or distribution.
 */
class DataDictionaryDiscovery implements DataDictionaryDiscoveryInterface {

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
    MetastoreUrlGenerator $urlGenerator
  ) {
    $this->config = $configFactory->get('metastore.settings');
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
    $distributionId = $this->getDistributionId($resource_id);
    if ($distributionId === NULL) {
      return NULL;
    }
    $distribution = $this->metastore->get('distribution', $distributionId);
    if (!$this->hasValidDescribedBy($distribution)) {
      return NULL;
    }
    return $this->extractDictionaryId($distribution->{"$.data.describedBy"});
  }

  /**
   * Get the distribution ID for a given resource ID.
   */
  private function getDistributionId(string $resource_id): ?string {
    $referencers = $this->lookup->getReferencers('distribution', $resource_id, 'downloadURL');
    if (empty($referencers)) {
      throw new \RuntimeException("Distribution lookup: Can not map resource ID {$resource_id} to distribution UUID. Please make sure your resource exists in the database.");
    }
    return $referencers[0] ?? NULL;
  }

  /**
   * Verify that the distribution has a valid describedBy URL.
   */
  private function hasValidDescribedBy($distribution): bool {
    return isset($distribution->{"$.data.describedBy"})
      && (($distribution->{"$.data.describedByType"} ?? NULL) === 'application/vnd.tableschema+json');
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
