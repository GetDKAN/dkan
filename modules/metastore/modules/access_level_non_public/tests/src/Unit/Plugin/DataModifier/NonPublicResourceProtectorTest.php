<?php

namespace Drupal\Tests\access_level_non_public\Unit;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\ConditionInterface;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\common\Plugin\DataModifierBase;
use Drupal\access_level_non_public\Plugin\DataModifier\NonPublicResourceProtector;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class NonPublicResourceProtectorTest extends TestCase {

  /**
   *
   */
  public function requiresModificationProvider() {
    return [
      'public dataset object' => [
        'dataset',
        (object) ['accessLevel' => 'public'],
        [],
        FALSE,
      ],
      'resource object with non-public parent dataset' => [
        'distribution',
        (object) ['identifier' => '1', 'data' => ['foo' => 'bar']],
        ['{"accessLevel":"non-public"}'],
        TRUE,
      ],
      'resource json string with public parent dataset' => [
        'distribution',
        '{"identifier":"1","data":{"foo":"bar"}}',
        ['{"accessLevel":"public"}'],
        FALSE,
      ],
      'resource uuid with public parent dataset' => [
        'distribution',
        '6b1f0bb4-60cd-5b27-8e03-5fecff4c7e2a',
        ['{"accessLevel":"non-public"}'],
        TRUE,
      ],
    ];
  }

  /**
   * @dataProvider requiresModificationProvider
   */
  public function testRequiresModification(string $schema, $data, $datasets, $expected) {
    $container = $this->getCommonMockChain()
      ->add(StatementInterface::class, 'fetchCol', $datasets);

    $plugin = NonPublicResourceProtector::create(
      $container->getMock(),
      [],
      'non_public_resource_protector',
      []
    );

    $this->assertEquals($expected, $plugin->requiresModification($schema, $data));
  }

  /**
   *
   */
  public function modifyProvider() {
    return [
      'dataset json string without resources' => [
        'dataset',
        '{"foo":"bar"}',
        '{"foo":"bar"}',
      ],
      'dataset object with empty distribution array' => [
        'dataset',
        (object) ["foo" => "bar", "distribution" => []],
        (object) ["foo" => "bar", "distribution" => []],
      ],
    ];
  }

  /**
   * @dataProvider modifyProvider
   */
  public function testModify($schema, $data, $expected) {
    $plugin = NonPublicResourceProtector::create(
      $this->getCommonMockChain()->getMock(),
      [],
      'non_public_resource_protector',
      []
    );

    $this->assertEquals($expected, $plugin->modify($schema, $data));
  }

  /**
   *
   */
  public function getCommonMockChain() {
    $pluginMessage = "Resource hidden since dataset access level is non-public.";

    $options = (new Options())
      ->add('database', Connection::class)
      ->add('current_route_match', RouteMatchInterface::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Connection::class, 'select', SelectInterface::class)
      ->add(SelectInterface::class, 'condition', ConditionInterface::class)
      ->add(ConditionInterface::class, 'condition', ConditionInterface::class)
      ->add(ConditionInterface::class, 'fields', SelectInterface::class)
      ->add(SelectInterface::class, 'execute', StatementInterface::class)
      ->add(StatementInterface::class, 'fetchCol', [])
      ->add(DataModifierBase::class, 'message', $pluginMessage);
  }

}
