<?php

namespace Drupal\metastore\DataDictionary;

/**
 * Provides interface for data dictionary discovery service.
 */
interface DataDictionaryDiscoveryInterface {

  public const MODE_NONE = 'none';
  public const MODE_SITEWIDE = 'sitewide';
  public const MODE_REFERENCE = 'reference';

  /**
   * Return the item ID for the appropriate data dictionary for a resource.
   *
   * @param string $resourceId
   *   DKAN datastore resource identifier.
   * @param int|null $resourceIdVersion
   *   DKAN datastore resource version ID.
   *
   * @return string|null
   *   The data dictionary identifier or NULL if none exists.
   */
  public function dictionaryIdFromResource(string $resourceId, int $resourceIdVersion): ?string;

  /**
   * Look for a data dictionary from a metastore reference.
   *
   * @param string $resourceId
   *   DKAN datastore resource identifier.
   * @param int $resourceIdVersion
   *   DKAN datastore resource version ID.
   *
   * @throws \RuntimeException
   *   If no distribution is found.
   *
   * @return string|null
   *   The data dictionary identifier or NULL if none exists.
   */
  public function getReferenceDictionaryId(string $resourceId, int $resourceIdVersion): ?string;

  /**
   * Get the current data dictionary "mode" from DKAN config.
   *
   * Return values should represent modes like "single sitewide dictionary" or
   * "distribution-specific dictionaries." The setting will have implications
   * for behaviors in both the metastore and datastore sites of data dictionary
   * functionality.
   *
   * @return string
   *   Data dictionary mode. Returns one of the MODE_* constants.
   */
  public function getDataDictionaryMode(): string;

  /**
   * If a single sitewide data dictionary has been defined, return its ID.
   *
   * @return string|null
   *   Data dictionary identifier.
   */
  public function getSitewideDictionaryId(): ?string;

}
