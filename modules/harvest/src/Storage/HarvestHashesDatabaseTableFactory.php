<?php

namespace Drupal\harvest\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Database table factory.
 *
 * This is the service dkan.harvest.storage.hashes_database_table.
 *
 * @todo Remove this in a refactor of the harvester.
 *
 * @internal
 */
class HarvestHashesDatabaseTableFactory implements FactoryInterface {

  /**
   * Entity type manager service.
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   *
   * @param string $identifier
   *   The plan ID. Do not use the table name, but the harvest plan ID.
   * @param array $config
   *   (Optional) Unused.
   */
  public function getInstance(string $identifier, array $config = []) {
    return new HarvestHashesEntityDatabaseTable($identifier, $this->entityTypeManager);
  }

}
