<?php

namespace Drupal\harvest\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Database table factory.
 */
class HarvestHashesDatabaseTableFactory implements FactoryInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
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
   * @param string $identifier
   *   The plan ID.
   * @param array $config
   *   (Optional) Unused.
   */
  public function getInstance(string $identifier, array $config = []) {
    return new HarvestHashesEntityDatabaseTable($identifier, $this->entityTypeManager);
  }

}
