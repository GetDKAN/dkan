<?php

namespace Drupal\metastore\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityAccessControlHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\metastore\Factory\MetastoreEntityItemFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MetastoreAccessManager implements ContainerInjectionInterface {

  /**
   * Access control handler for data entities.
   */
  protected EntityAccessControlHandlerInterface $accessControlHandler;

  /**
   * Metastore Storage Factory service.
   */
  protected MetastoreEntityItemFactoryInterface $itemFactory;

  /**
   * Entity type ID for the items managed by this factory.
   */
  protected string $entityType;

  /**
   * Bundle ID for the items managed by this factory.
   *
   * Right now this class only supports a single bundle per factory,
   * although in theory the factory supports a mapping of schema IDs
   * to bundles. In the future, this could be extended to support
   * multiple bundles if needed.
   */
  protected string $bundle;

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    MetastoreEntityItemFactoryInterface $itemFactory,
  ) {
    $this->entityType = $itemFactory->getEntityType();
    $this->bundle = reset($itemFactory->getBundles());
    $this->accessControlHandler = $entityTypeManager->getAccessControlHandler($this->entityType);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('metastore.entity_item_factory'),
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
    return $this->accessControlHandler->createAccess($this->bundle, $account);
  }

  /**
   * Check if user can update an item from a schema.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   * @param string $schema_id
   *   The schema ID.
   * @param string $item_id
   *   The item ID.
   *
   * @return bool
   *   TRUE if the user can update the item, FALSE otherwise.
   */
  public function canUpdate(AccountInterface $account, string $schema_id, string $item_id): bool {
    // Check if the user has permission to update items of this schema.
    $entity = $this->getEntity($schema_id, $item_id);
    return $this->accessControlHandler->access($entity, "update", $account);
  }

  /**
   * Check if user can delete an item from a schema.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   * @param string $schema_id
   *   The schema ID.
   * @param string $item_id
   *   The item ID.
   *
   * @return bool
   *   TRUE if the user can delete the item, FALSE otherwise.
   */
  public function canDelete(AccountInterface $account, string $schema_id, string $item_id): bool {
    // Check if the user has permission to delete items of this schema.
    $entity = $this->getEntity($schema_id, $item_id);
    return $this->accessControlHandler->access($entity, "delete", $account);
  }

  protected function getEntity(string $schema_id, string $item_id): EntityInterface {
    // Load the entity based on schema ID and item ID.
    $item = $this->itemFactory->getInstance($item_id);
    if ($item && $item->getSchemaId() === $schema_id) {
      return $item->getEntity();
    }
    throw new \InvalidArgumentException(sprintf(
      'No entity found for schema ID %s and item ID %s.',
      $schema_id,
      $item_id
    ));
  }

}
