<?php

namespace Drupal\Tests\metastore_admin\Unit;

use PHPUnit\Framework\TestCase;
use Drupal\node\NodeStorageInterface;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Session\AccountInterface;
use Drupal\metastore_admin\Plugin\Action\HideCurrentRevisionAction;
use MockChain\Chain;
use MockChain\Options;

/**
 * Unit tests for the hide bulk operations action plugin.
 *
 * @group metastore_admin
 */
class HideCurrentRevisionActionTest extends TestCase {

  public function getContainer() {

    $options = (new Options())
      ->add('logger.factory', LoggerChannelFactoryInterface::class)
      ->add('messenger', MessengerInterface::class)
      ->add('current_user', AccountInterface::class)
      ->add('datetime.time', TimeInterface::class)
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(AccountInterface::class, 'hasPermission', TRUE)
      ->getMock();

    return $container;

  }

  /**
   * Test the HideCurrentRevisionAction plugin.
   *
   * @covers ::hide
   */
  public function testHide() {
    $container = $this->getContainer();
    $actionPlugin = HideCurrentRevisionAction::create($container, [], 'hide_current_revision_action', []);
    $object = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', NodeStorage::class)
      ->add(NodeStorage::class, 'load')
      ->getMock();

    $this->assertTrue($actionPlugin->hide($object));

  }

}
