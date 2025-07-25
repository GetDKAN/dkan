<?php

namespace Drupal\metastore\Controller;

use Contracts\FactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MetastoreAccessManager implements ContainerInjectionInterface {

  /**
   * The entity type manager service.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Metastore Storage Factory service.
   */
  protected FactoryInterface $storageFactory;

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    FactoryInterface $storageFactory,
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->storageFactory = $storageFactory;

    
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('metastore.storage')
    );
  }

  /**
   * Check if user can create an item from a schema.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   * @param string $schema_id
   *   The schema ID.
   *
   * @return bool
   *   TRUE if the user can create an item, FALSE otherwise.
   */
  public function canCreate(AccountInterface $account): bool {
    // Check if the user has permission to create items from the schema.
    return $this->accessControlHandler->createAccess('data', $account);
  }

}
