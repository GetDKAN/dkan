<?php

namespace Drupal\Tests\metastore\Controller\Kernel;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore\Controller\MetastoreAccessManager;
use Drupal\metastore\NodeWrapper\Data;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use Drupal\node\NodeInterface;
use Drupal\Tests\user\Traits\UserCreationTrait;
use MockChain\Chain;
use MockChain\Options;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the MetastoreAccessManager.
 *
 * Note, no current test for the canViewRevision() method, because it relies
 * on static methods from the Data wrapper, which can't be mocked.
 *
 * @coversDefaultClass \Drupal\metastore\Controller\MetastoreAccessManager
 *
 * @group dkan
 * @group metastore
 */
class MetastoreAccessManagerTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * The MetastoreAccessManager service.
   */
  protected MetastoreAccessManager $accessManager;

  /**
   * A user with permissions to work with the metastore.
   */
  protected AccountInterface $priviledgedUser;

  /**
   * A user without permissions to work with the metastore.
   */
  protected AccountInterface $unpriviledgedUser;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'metastore',
    'common',
    'dkan',
    'field',
    'node',
    'user',
    'workflows',
    'content_moderation',
    'system',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    // Set up the necessary services and configurations for the test.
    $this->installConfig(['node', 'metastore']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');

    // Mock the NodeDataFactory to return a Data wrapper with a data node mock.
    $itemFactory = (new Chain($this))
      ->add(NodeDataFactory::class, 'getInstance', (new Options)
        ->add('123', Data::class)
        ->add('345', NULL)
        ->index(0)
      )
      ->add(Data::class, 'fix', NULL)
      ->add(Data::class, 'getEntity', NodeInterface::class)
      ->add(NodeInterface::class, 'bundle', 'data')
      ->add(NodeInterface::class, 'get', FieldItemListInterface::class)
      ->add(NodeInterface::class, 'language', LanguageInterface::class)
      ->add(NodeInterface::class, 'getEntityType', EntityTypeInterface::class)
      ->add(EntityTypeInterface::class, 'id', 'node')
      ->add(LanguageInterface::class, 'getId', 'en')
      ->add(FieldItemListInterface::class, 'getString', 'dataset')
      ->getMock();

    $this->accessManager = new MetastoreAccessManager(
      $this->container->get('entity_type.manager'),
      $itemFactory
    );

    $this->priviledgedUser = $this->createUser([
      'access content',
      'create data content',
      'edit own data content',
      'delete own data content',
      'use dkan_publishing transition publish',
      'use dkan_publishing transition archive',
    ], 'privileged_user');
    $this->unpriviledgedUser = $this->createUser([
      'access content',
    ], 'unprivileged_user');
  }

  /**
   * Tests the canCreate() method.
   *
   * @covers ::canCreate
   */
  public function testCanCreate(): void {
    $schema_id = 'example_schema';
    $can_create = $this->accessManager->canCreate($schema_id, $this->priviledgedUser);
    $this->assertTrue($can_create->isAllowed());

    $can_create = $this->accessManager->canCreate($schema_id, $this->unpriviledgedUser);
    $this->assertFalse($can_create->isAllowed());
  }

  /**
   * Tests the canUpdate method.
   *
   * @covers ::canUpdate
   */
  public function testCanUpdate(): void {
    $schema_id = 'dataset';
    $item_id = '123';
    // Create a dummy post request
    $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'POST']);

    $can_update = $this->accessManager->canUpdate($schema_id, $item_id, $this->priviledgedUser, $request);
    $this->assertTrue($can_update->isAllowed());
    $can_update = $this->accessManager->canUpdate($schema_id, $item_id, $this->unpriviledgedUser, $request);
    $this->assertFalse($can_update->isAllowed());

    // We should be "allowed" to update a non-existant item, the controller will
    // handle the 404 response.
    $can_update = $this->accessManager->canUpdate($schema_id, '345', $this->priviledgedUser, $request);
    $this->assertTrue($can_update->isAllowed());
    $can_update = $this->accessManager->canUpdate($schema_id, '345', $this->unpriviledgedUser, $request);
    $this->assertTrue($can_update->isAllowed());

    // Now try with a PUT request, which should check for create permissions if 
    // non-existant node.
    $request->setMethod('PUT');
    $can_update = $this->accessManager->canUpdate($schema_id, '123', $this->priviledgedUser, $request);
    $this->assertTrue($can_update->isAllowed());
    $can_update = $this->accessManager->canUpdate($schema_id, '123', $this->unpriviledgedUser, $request);
    $this->assertFalse($can_update->isAllowed());

    $request->setMethod('PUT');
    $can_update = $this->accessManager->canUpdate($schema_id, '345', $this->priviledgedUser, $request);
    $this->assertTrue($can_update->isAllowed());
    $can_update = $this->accessManager->canUpdate($schema_id, '345', $this->unpriviledgedUser, $request);
    $this->assertFalse($can_update->isAllowed());

    // Now try with a PATCH request
    $request->setMethod('PATCH');
    $can_update = $this->accessManager->canUpdate($schema_id, '123', $this->priviledgedUser, $request);
    $this->assertTrue($can_update->isAllowed());
    $can_update = $this->accessManager->canUpdate($schema_id, '123', $this->unpriviledgedUser, $request);
    $this->assertFalse($can_update->isAllowed());
  }

  /**
   * Tests the canDelete method.
   *
   * @covers ::canDelete
   */
  public function testCanDelete(): void {
    $schema_id = 'dataset';
    $item_id = '123';

    $can_delete = $this->accessManager->canDelete($schema_id, $item_id, $this->priviledgedUser);
    $this->assertTrue($can_delete->isAllowed());
    $can_delete = $this->accessManager->canDelete($schema_id, $item_id, $this->unpriviledgedUser);
    $this->assertFalse($can_delete->isAllowed());

    // Test with a non-existant item. Should always be allowed, controller handles.
    $can_delete = $this->accessManager->canDelete($schema_id, '345', $this->priviledgedUser);
    $this->assertTrue($can_delete->isAllowed());
    $can_delete = $this->accessManager->canDelete($schema_id, '345', $this->unpriviledgedUser);
    $this->assertTrue($can_delete->isAllowed());
  }

  /**
   * Tests if a user can view the revision list of a dataset.
   *
   * @covers ::canViewRevisionList
   */
  public function testCanViewRevisionList(): void {
    $schema_id = 'dataset';
    $item_id = '123';

    $can_view = $this->accessManager->canViewRevisionList($schema_id, $item_id, $this->priviledgedUser);
    $this->assertTrue($can_view->isAllowed());

    $can_view = $this->accessManager->canViewRevisionList($schema_id, $item_id, $this->unpriviledgedUser);
    $this->assertFalse($can_view->isAllowed());

    // Non-existant item should be allowed, controller handles.
    $can_view = $this->accessManager->canViewRevisionList($schema_id, '345', $this->unpriviledgedUser);
    $this->assertTrue($can_view->isAllowed());
  }

}
