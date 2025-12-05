<?php

namespace Drupal\metastore\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityAccessControlHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Factory\MetastoreEntityItemFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manages access control for Metastore items in API endpoints.
 *
 * This class provides methods to check if a user can create, update, or delete
 * items based on their schema ID and item ID.
 */
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
   * Entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

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
    $this->itemFactory = $itemFactory;
    $this->entityType = $itemFactory::getEntityType();
    $bundles = $itemFactory::getBundles();
    $this->bundle = reset($bundles);
    $this->entityTypeManager = $entityTypeManager;
    $this->accessControlHandler = $entityTypeManager->getAccessControlHandler($this->entityType);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('dkan.metastore.metastore_item_factory'),
    );
  }

  /**
   * Check if user can create an item from a schema.
   *
   * @param string $schema_id
   *   The schema ID.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   An access result object indicating whether the user can create an item.
   */
  public function canCreate(string $schema_id, AccountInterface $account): AccessResult {
    // Check legacy API access permission.
    if ($account->hasPermission('post put delete datasets through the api')) {
      return AccessResult::allowed();
    }

    // Check entity-specific create access.
    return $this->accessControlHandler->createAccess($this->bundle, $account, [], TRUE);
  }

  /**
   * Check if user can update an item from a schema.
   *
   * @param string $schema_id
   *   The schema ID.
   * @param string $identifier
   *   The item ID.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   An access result object indicating whether the user can update the item.
   */
  public function canUpdate(
    string $schema_id,
    string $identifier,
    AccountInterface $account,
    Request $request,
  ): AccessResult {
    // Check legacy API access permission.
    if ($account->hasPermission('post put delete datasets through the api')) {
      return AccessResult::allowed();
    }
    $method = $request->getMethod();
    // Check if the user has permission to update items of this schema.
    try {
      $entity = $this->getEntity($schema_id, $identifier);
      return $this->accessControlHandler->access($entity, "update", $account, TRUE);
    }
    catch (MissingObjectException | \InvalidArgumentException $e) {
      // If this is a PUT, we should check if user has CREATE permission.
      if ($method === 'PUT' && !$this->canCreate($schema_id, $account)->isAllowed()) {
        return AccessResult::forbidden();
      }
      // If the the item does not exist, assume "allowed" and let the controller
      // handle the 404 response.
      return AccessResult::allowed();
    }
  }

  /**
   * Check if user can delete an item from a schema.
   *
   * @param string $schema_id
   *   The schema ID.
   * @param string $identifier
   *   The item ID.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   An access result object indicating whether the user can delete the item.
   */
  public function canDelete(string $schema_id, string $identifier, AccountInterface $account): AccessResult {
    // Check legacy API access permission.
    if ($account->hasPermission('post put delete datasets through the api')) {
      return AccessResult::allowed();
    }

    try {
      $entity = $this->getEntity($schema_id, $identifier);
      return $this->accessControlHandler->access($entity, "delete", $account, TRUE);
    }
    catch (MissingObjectException | \InvalidArgumentException $e) {
      // If the item does not exist, assume "allowed" and let the controller
      // handle the 404 response.
      return AccessResult::allowed();
    }
  }

  /**
   * Check if user can view the revision list for an item.
   *
   * @param string $schema_id
   *   The schema ID.
   * @param string $identifier
   *   The item ID.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Access result object.
   */
  public function canViewRevisionList(
    string $schema_id,
    string $identifier,
    AccountInterface $account,
  ): AccessResult {
    // Check legacy API access permission.
    if ($account->hasPermission('post put delete datasets through the api')) {
      return AccessResult::allowed();
    }
    try {
      /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
      $entity = $this->getEntity($schema_id, $identifier);
      return $this->accessControlHandler->access($entity, "view all revisions", $account, TRUE);
    }
    catch (MissingObjectException | \InvalidArgumentException $e) {
      // If the item does not exist, assume "allowed" and let the controller
      // handle the 404 response.
      return AccessResult::allowed();
    }
  }

  /**
   * Check if user can view a specific revision of an item.
   *
   * @param string $schema_id
   *   The schema ID.
   * @param string $identifier
   *   The item ID.
   * @param int $revision_id
   *   The revision ID.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Access result object.
   */
  public function canViewRevision(
    string $schema_id,
    string $identifier,
    int $revision_id,
    AccountInterface $account,
  ): AccessResult {
    // Check legacy API access permission.
    if ($account->hasPermission('post put delete datasets through the api')) {
      return AccessResult::allowed();
    }
    try {
      /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
      $storage = $this->entityTypeManager->getStorage($this->entityType);
      $revision = $storage->loadRevision($revision_id);
      if (!$revision || $revision->uuid() !== $identifier) {
        throw new MissingObjectException();
      }
      return $this->accessControlHandler->access($revision, "view", $account, TRUE);
    }
    catch (MissingObjectException $e) {
      // If the item does not exist, assume "allowed" and let the controller
      // handle the 404 response.
      return AccessResult::allowed();
    }
  }

  /**
   * Get the entity for a given schema ID and item ID.
   *
   * @param string $schema_id
   *   The schema ID.
   * @param string $identifier
   *   The item ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity corresponding to the schema and item ID.
   *
   * @throws \Drupal\metastore\Exception\MissingObjectException
   *   If no entity is found for the given schema ID and item ID.
   */
  protected function getEntity(string $schema_id, string $identifier): EntityInterface {
    // Load the entity based on schema ID and item ID.
    try {
      $item = $this->itemFactory->getInstance($identifier, ['schema_id' => $schema_id]);
    }
    catch (\Throwable $e) {
      throw new MissingObjectException("No item found for schema ID '$schema_id' and identifier '$identifier'.", 0, $e);
    }
    assert($item->getSchemaId() === $schema_id, MissingObjectException::class);
    return $item->getEntity();
  }

}
