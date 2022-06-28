<?php

namespace Drupal\metastore\DataDictionary;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Data dictionary service.
 *
 * Find the correct data dictionary for a dataset or distribution.
 */
class DataDictionaryDiscovery implements DataDictionaryDiscoveryInterface {

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->config = $configFactory->get('metastore.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function dictionaryIdFromResource(string $resourceId, ?int $resourceIdVersion = NULL): ?string {
    $mode = $this->getDataDictionaryMode();
    // For now, we only support sitewide!
    switch ($mode) {
      case self::MODE_NONE:
        return NULL;

      case self::MODE_SITEWIDE:
        return $this->getSitewideDictionaryId();

      default:
        throw new \OutOfRangeException(sprintf('Unsupported data dictionary mode "%s"', $mode));
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
