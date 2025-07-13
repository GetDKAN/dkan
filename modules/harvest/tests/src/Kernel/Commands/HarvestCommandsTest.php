<?php

declare(strict_types=1);

namespace Drupal\Tests\harvest\Kernel\Commands;

use Drupal\KernelTests\KernelTestBase;
use Drupal\harvest\Commands\HarvestCommands;
use Drupal\harvest\ETL\Extract\DataJson;
use Drupal\harvest\Entity\HarvestPlanRepository;
use Drupal\harvest\Load\Dataset;
use Drush\Log\DrushLoggerManager;

/**
 * @covers \Drupal\harvest\Commands\HarvestCommands
 * @coversDefaultClass \Drupal\harvest\Commands\HarvestCommands
 *
 * @group dkan
 * @group harvest
 * @group kernel
 */
class HarvestCommandsTest extends KernelTestBase {

  /**
   * {@inheritDoc}
   */
  protected static $modules = [
    'common',
    'harvest',
    'metastore',
  ];

  /**
   * Happy path test.
   */
  public function testBuildPlanFromOpts() {
    $plan_repository = $this->getMockBuilder(HarvestPlanRepository::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['storePlan'])
      ->getMock();
    $plan_repository->expects($this->once())
      ->method('storePlan');

    $this->container->set('dkan.harvest.harvest_plan_repository', $plan_repository);

    $harvest_commands = new HarvestCommands(
      $this->container->get('dkan.harvest.service'),
      $this->container->get('dkan.harvest.utility'),
    );
    $logger = $this->getMockBuilder(DrushLoggerManager::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['notice'])
      ->getMock();
    $logger->expects($this->once())
      ->method('notice');
    $harvest_commands->setLogger($logger);

    // Call with the command default options, plus an identifier.
    $harvest_commands->register('', [
      'identifier' => 'id',
      'extract-type' => DataJson::class,
      'extract-uri' => 'uri',
      'transform' => [],
      'load-type' => Dataset::class,
    ]);
  }

  /**
   * Test building a plan with bad options.
   *
   * @dataProvider providePlanOpts
   */
  public function testBuildPlanFromOptsBadArgs($expected_error_message, $opts) {
    $plan_repository = $this->getMockBuilder(HarvestPlanRepository::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['storePlan'])
      ->getMock();
    // Never store the plan because it should not validate.
    $plan_repository->expects($this->never())
      ->method('storePlan');

    $this->container->set('dkan.harvest.harvest_plan_repository', $plan_repository);

    $harvest_commands = new HarvestCommands(
      $this->container->get('dkan.harvest.service'),
      $this->container->get('dkan.harvest.utility'),
    );
    $logger = $this->getMockBuilder(DrushLoggerManager::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['error', 'debug'])
      ->getMock();
    // Set the error logger to expect our error messages.
    $logger->expects($this->once())
      ->method('error')
      ->with($expected_error_message);
    $harvest_commands->setLogger($logger);

    // Call with our buggy options.
    $harvest_commands->register('', $opts);
  }

  /**
   * Provide command option arrays.
   *
   * 'Missing' values have keys so that we can try and fool the input
   * validation.
   */
  public static function providePlanOpts(): array {
    return [
      'no identifier key' => [
        'Invalid harvest plan.  {"missing":"identifier"}', [
          'extract-type' => DataJson::class,
          'extract-uri' => 'uri',
          'transform' => [],
          'load-type' => Dataset::class,
        ],
      ],
      'no identifier' => [
        'Invalid harvest plan.  {"missing":"identifier"}', [
          'identifier' => '',
          'extract-type' => DataJson::class,
          'extract-uri' => 'uri',
          'transform' => [],
          'load-type' => Dataset::class,
        ],
      ],
      'no extract-type' => [
        'Invalid harvest plan. extract {"missing":"type"}', [
          'identifier' => 'id',
          'extract-uri' => '',
          'extract-type' => '',
          'transform' => [],
          'load-type' => Dataset::class,
        ],
      ],
      'no extract-uri' => [
        'Invalid harvest plan. extract {"missing":"uri"}', [
          'identifier' => 'id',
          'extract-uri' => '',
          'extract-type' => DataJson::class,
          'transform' => [],
          'load-type' => Dataset::class,
        ],
      ],
      'no load' => [
        'Invalid harvest plan. load {"missing":"type"}', [
          'identifier' => 'id',
          'extract-type' => DataJson::class,
          'extract-uri' => 'uri',
          'transform' => [],
          'load-type' => '',
        ],
      ],
    ];
  }

}
