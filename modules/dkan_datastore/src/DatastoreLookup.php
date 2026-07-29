<?php

declare(strict_types=1);

namespace Drupal\dkan_datastore;

use Drupal\Core\Database\Connection;
use Drupal\dkan_metastore\Reference\ReferenceLookup;

/**
 * Implementation of various lookup utilities related to the datastore.
 */
class DatastoreLookup implements DatastoreLookupInterface {

  /**
   * DataStoreLookupService constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection service.
   * @param \Drupal\dkan_metastore\Reference\ReferenceLookup $referenceLookup
   *   Reference lookup service.
   */
  public function __construct(
    protected Connection $database,
    protected ReferenceLookup $referenceLookup,
  ) {
  }

  /**
   * {@inheritDoc}
   */
  public function tableToResourceLookup(string $table_name): string {
    // Establish DB connection.
    $resource_query = $this->database->select('dkan_metastore_resource_mapper', 'dm')
      ->fields('dm', ['identifier']);
    // Add the condition using a raw SQL expression.
    $resource_query->where(
      'CONCAT(\'datastore_\', MD5(CONCAT(identifier, \'__\', version, \'__\', perspective))) = :table_name',
      [':table_name' => $table_name]
    );
    // Fetch the first identifier directly for Drupal 10/11 compatibility.
    $resource_identifier = $resource_query->execute()->fetchField();
    if ($resource_identifier !== FALSE && $resource_identifier !== NULL) {
      return (string) $resource_identifier;
    }
    else {
      throw new \Exception("Resource lookup: Can not map datastore table name {$table_name} to resource ID. Please make sure your datastore table name exists as a table in the database.");
    }
  }

  /**
   * {@inheritDoc}
   */
  public function resourceToDistribution(string $resource_id): string {
    // Maps the resource ID to the distribution ID.
    $referencers = $this->referenceLookup->getReferencers('distribution', $resource_id, 'downloadURL');
    if (empty($referencers)) {
      throw new \RuntimeException("Distribution lookup: Can not map resource ID {$resource_id} to distribution UUID. Please make sure your resource exists in the database.");
    }

    return $referencers[0];
  }

  /**
   * {@inheritDoc}
   */
  public function resourceToDataset(string $resource_id): string {
    // Maps the resource ID to the dataset.
    $referencers = $this->referenceLookup->getReferencers('dataset', $resource_id, 'downloadURL');
    if (empty($referencers)) {
      throw new \RuntimeException("Dataset lookup: Can not map resource ID {$resource_id} to dataset UUID. Please make sure your resource exists in the database.");
    }

    return $referencers[0];
  }

  /**
   * {@inheritDoc}
   */
  public function distributionToDataset(string $distribution_uuid): string {
    // Maps the distribution ID to the dataset.
    if (strlen($distribution_uuid) !== 36) {
      throw new \InvalidArgumentException("Dataset lookup: Distribution UUID must be 36 characters.");
    }

    $referencers = $this->referenceLookup->getReferencers('dataset', $distribution_uuid, 'distribution');

    if (empty($referencers)) {
      throw new \RuntimeException("No dataset found for distribution ID: {$distribution_uuid}");
    }

    return $referencers[0];
  }

}
