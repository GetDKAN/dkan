<?php

declare(strict_types=1);

namespace Drupal\Tests\harvest\Functional\Commands;

use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * @coversDefaultClass \Drupal\harvest\Commands\HarvestCommands
 *
 * @group dkan
 * @group harvest
 * @group btb
 * @group functional
 */
class HarvestCommandsTest extends BrowserTestBase {

  use DrushTestTrait;

  protected static $modules = [
    'harvest',
    'metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  /**
   * Minimally run all the commands.
   */
  public function testCommands() {
    // Run the commands with --help to ensure there are no fundamental errors.
    foreach ([
      'dkan:harvest:list',
      'dkan:harvest:register',
      'dkan:harvest:deregister',
      'dkan:harvest:run',
      'dkan:harvest:run-all',
      'dkan:harvest:info',
      'dkan:harvest:revert',
      'dkan:harvest:archive',
      'dkan:harvest:publish',
      'dkan:harvest:status',
      'dkan:harvest:orphan-datasets',
      'dkan:harvest:cleanup',
      'dkan:harvest:update',
    ] as $command) {
      $this->drush($command, ['--help']);
      // Ignore deprecation warnings.
      $this->assertErrorOutputEquals('', '/(Deprecated: |PHP Deprecated: ).*/s');
    }

    // Run the commands with no arguments, assert the response code.
    foreach ([
      'dkan:harvest:list' => 0,
      'dkan:harvest:register' => 0,
      'dkan:harvest:deregister' => 1,
      'dkan:harvest:run' => 1,
      'dkan:harvest:run-all' => 0,
      'dkan:harvest:info' => 1,
      'dkan:harvest:revert' => 1,
      'dkan:harvest:archive' => 1,
      'dkan:harvest:publish' => 1,
      'dkan:harvest:status' => 1,
      'dkan:harvest:orphan-datasets' => 1,
      'dkan:harvest:cleanup' => 0,
      'dkan:harvest:update' => 0,
    ] as $command => $expected_return) {
      $this->drush($command, [], [], NULL, NULL, $expected_return);
      // Exceptions will tell you which line in the Command class file.
      $this->assertStringNotContainsString(
        'In HarvestCommands.php line',
        $this->getSimplifiedErrorOutput()
      );
    }
  }

}
