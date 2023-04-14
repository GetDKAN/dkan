<?php

namespace Drupal\metastore\Plugin\MetastoreReferenceType;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;
use Drupal\metastore\Exception\MissingObjectException;

/**
 * Metastore item references that are simple URLs.
 *
 * @MetastoreReferenceType(
 *  id = "url",
 *  description = @Translation("Metastore URL reference definition.")
 * )
 */
class UrlItemReference extends ItemReference {

  /**
   * {@inheritdoc}
   */
  public function reference($value): string {
    if ($identifier = $this->checkExistingReference($value)) {
      return $identifier;
    }
    // As this is a URL field, if we can't reference we just pass through.
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function dereference(string $identifier, $showId = FALSE) {
    if (strpos($identifier, '://') !== FALSE) {
      // This is still a URL, and was never referenced.
      return $identifier;
    }

    $storage = $this->storageFactory->getInstance($this->schemaId());
    try {
      $storage->retrieve($identifier);
    }
    catch (MissingObjectException $exception) {
      $this->logger->notice(
        'Property @property_id reference @identifier not found. The referenced item may have been deleted.',
        [
          '@property_id' => $this->property(),
          '@identifier' => var_export($identifier, TRUE),
        ]
      );
      return NULL;
    }

    $itemUri = 'dkan://metastore/schemas/' . $this->schemaId() . '/items/' . $identifier;
    $value = Url::fromUri($itemUri)->toString();
    return $showId ? $this->createIdRef($value, $identifier) : $value;
  }

  /**
   * Create an identifier/data structure for $ref object.
   *
   * @param string $identifier
   *   The reference identifier to be dereferenced.
   * @param mixed $value
   *   The dereferenced value.
   */
  protected function createIdRef(string $identifier, $value) {
    return (object) [
      'identifier' => $identifier,
      'data' => $value,
    ];
  }

  /**
   * Checks for an existing value reference for that property id.
   *
   * @param string|object $value
   *   The property's value used to find an existing reference.
   *
   * @return string|false
   *   The existing reference's uuid, or FALSE if not found.
   */
  protected function checkExistingReference($value) {
    $parts = UrlHelper::parse($value);
    // We expect to see the path to the schema's metastore items.
    $expected = 'api/1/metastore/schemas/' . $this->schemaId() . '/items/';
    // String position of metastore path for schema items should be an integer.
    $pos = strpos($parts['path'], $expected);
    if ($pos === FALSE) {
      return FALSE;
    }
    // Identifier should be at end of path.
    $identifier = substr($value, ($pos + strlen($expected)));

    // If there is a metastore item by this schema and identifier, we're good.
    $storage = $this->storageFactory->getInstance($this->schemaId());
    try {
      $storage->retrieve($identifier);
    }
    catch (MissingObjectException $exception) {
      // If the URL was formatted correctly but no item was found, log it.
      $this->logger->notice(
        'Could not map URL to existing @schema item: @property_id with value: @value',
        [
          '@schema' => $this->schemaId(),
          '@property_id' => $this->property(),
          '@value' => var_export($value, TRUE),
        ]
      );
      $identifier = FALSE;
    }

    return $identifier;
  }

}
