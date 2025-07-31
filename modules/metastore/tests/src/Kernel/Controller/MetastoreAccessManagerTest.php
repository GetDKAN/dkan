<?php

namespace Drupal\Tests\metastore\Controller\Kernel;

use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore\Controller\MetastoreAccessManager;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use Drupal\metastore\Storage\NodeData;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Tests\user\Traits\UserCreationTrait;
use MockChain\Chain;
use MockChain\Options;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the MetastoreAccessManager.
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

  public function setUp(): void {
    parent::setUp();
    // Set up the necessary services and configurations for the test.
    $this->installConfig(['node', 'metastore']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');


    $repositoryOptions = (new Options)
      ->add('123', NodeInterface::class)
      ->index(0);
    $entityRepository = (new Chain($this))
      ->add(EntityRepository::class, 'loadEntityByUuid', NodeInterface::class)
      ->add(NodeInterface::class, 'bundle', 'data')
      ->add(NodeInterface::class, 'get', FieldItemListInterface::class)
      ->add(FieldItemListInterface::class, 'getString', 'dataset')
      ->getMock();

    $metastoreItemFactory = new NodeDataFactory(
      $entityRepository,
      $this->container->get('entity_type.manager')
    );

    $this->accessManager = new MetastoreAccessManager(
      $this->container->get('entity_type.manager'),
      $metastoreItemFactory
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
   * Tests the MetastoreAccessManager.
   */
  public function testCanCreate(): void {
    $schema_id = 'example_schema';
    $can_create = $this->accessManager->canCreate($schema_id, $this->priviledgedUser);
    $this->assertTrue($can_create->isAllowed());

    $can_create = $this->accessManager->canCreate($schema_id, $this->unpriviledgedUser);
    $this->assertFalse($can_create->isAllowed());
  }

  public function testCanUpdate(): void {
    $schema_id = 'dataset';
    $item_id = '123';
    // Create a dummy post request
    $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'POST']);

    $can_update = $this->accessManager->canUpdate($schema_id, $item_id, $this->priviledgedUser, $request);
    $this->assertTrue($can_update->isAllowed());

    $can_update = $this->accessManager->canUpdate($schema_id, $item_id, $this->unpriviledgedUser, $request);
    $this->assertFalse($can_update->isAllowed());
  }

}

