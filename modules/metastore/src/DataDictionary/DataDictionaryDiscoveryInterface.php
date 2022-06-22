<?php

namespace Drupal\metastore\DataDictionary;

/**
 * Provides interface for data dictionary discovery service.
 */
interface DataDictionaryDiscoveryInterface {

  const MODE_NONE = 0;
  const MODE_SITEWIDE = 1;
  const MODE_COLLECTION = 2;
  const MODE_GENERATE = 3;

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
  public function dictionaryIdFromResource(string $resourceId, ?int $resourceIdVersion = NULL): ?string;

  /**
   * Get the current data dictionary "mode" from DKAN config.
   *
   * Return values should represent modes like "single sitewide dictionary" or
   * "distribution-specific dictionaries." The setting will have implications
   * for behaviors in both the metastore and datastore sites of data dictionary
   * functionality.
   *
   * @return int
   *   Data dictionary mode. Returns one of the MODE_* constants.
   */
  public function getDataDictionaryMode(): int;

  /**
   * If a single sitewide data dictionary has been defined, return its ID.
   *
   * @return string
   *   Data dictionary identifier.
   */
  public function getSitewideDictionaryId(): string;

}
