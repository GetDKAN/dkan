<?php

namespace Drupal\Tests\metastore_admin\Unit;

use Drupal\Core\Access\AccessResultInterface;
use PHPUnit\Framework\TestCase;
use Drupal\node\NodeStorageInterface;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\metastore_admin\Plugin\Action\HideCurrentRevisionAction;
use Drupal\node\Entity\Node;
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
      ->add(LoggerChannelFactoryInterface::class, 'get', LoggerChannel::class)
      ->add(LoggerChannel::class, 'notice', NULL)
      ->add(Messenger::class, 'addError', Messenger::class)
      ->add(TimeInterface::class, 'getRequestTime', 1653422144)
      ->getMock();

    return $container;

  }

  /**
   * Test the access method.
   *
   * @covers ::access
   */
  public function testAccess() {
    $container = $this->getContainer();
    $actionPlugin = HideCurrentRevisionAction::create($container, [], 'hide_current_revision_action', []);
    $object = (new Chain($this))
      ->add(Node::class, 'access', AccessResultInterface::class)
      ->add(Node::class, 'getEntityType', 'node')
      ->add(AccessResultInterface::class, 'andIf', AccessResultInterface::class)
      ->add(AccessResultInterface::class, 'isAllowed', TRUE)
      ->getMock();

    $this->assertTrue($actionPlugin->access($object, $container->get('current_user')));

  }

}
