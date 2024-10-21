<?php

declare(strict_types=1);

namespace Drupal\Tests\harvest\Functional\Commands;

use Drupal\Tests\BrowserTestBase;
use Drush\Psysh\DrushCommand;
use Drush\TestTraits\DrushTestTrait;

/**
 * @group dkan
 * @group datastore
 * @group btb
 * @group functional
 */
class DatastoreCommandsTest extends BrowserTestBase {

  use DrushTestTrait;

  protected static $modules = [
    'datastore',
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
      'dkan:datastore:apply-dictionary',
      'dkan:datastore:drop',
      'dkan:datastore:drop-all',
      'dkan:datastore:import',
      'dkan:datastore:list',
      'dkan:datastore:localize',
      'dkan:datastore:prepare-localized',
      'dkan:datastore:purge',
      'dkan:datastore:purge-all',
      'dkan:datastore:reimport',
    ] as $command) {
      $this->drush($command, ['--help']);
      $this->assertErrorOutputEquals('');
    }

    // Run the commands with no arguments, assert the result.
    foreach ([
      'dkan:datastore:apply-dictionary' => DrushCommand::FAILURE,
      'dkan:datastore:drop' => DrushCommand::FAILURE,
      'dkan:datastore:drop-all' => DrushCommand::SUCCESS,
      'dkan:datastore:import' => DrushCommand::FAILURE,
      'dkan:datastore:list' => DrushCommand::SUCCESS,
      'dkan:datastore:localize' => DrushCommand::FAILURE,
      'dkan:datastore:prepare-localized' => DrushCommand::FAILURE,
      'dkan:datastore:purge' => DrushCommand::FAILURE,
      'dkan:datastore:purge-all' => DrushCommand::SUCCESS,
      'dkan:datastore:reimport' => DrushCommand::FAILURE,
    ] as $command => $expected_return) {
      $this->drush($command, [], [], NULL, NULL, $expected_return);
      // Exceptions will tell you which PHP file.
      $this->assertStringNotContainsString(
        '.php',
        $this->getSimplifiedErrorOutput()
      );
    }
  }

}
