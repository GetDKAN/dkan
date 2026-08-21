<?php

namespace Drupal\Tests\dkan_datastore\Functional\Commands;

use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Tests for degraded mode Drush commands.
 *
 * @covers \Drupal\dkan_datastore\Commands\DegradedModeCommands
 * @coversDefaultClass \Drupal\dkan_datastore\Commands\DegradedModeCommands
 * @group dkan_datastore
 * @group functional
 * @group btb
 * @group functional2
 */
class DegradedModeCommandsTest extends BrowserTestBase {
  use DrushTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['dkan_datastore'];

  protected $defaultTheme = 'stark';

  /**
   * Tests the dkan:datastore:degraded-mode command.
   */
  public function testDegradedModeCommand() {
    // Check initial state is disabled.
    $this->drush('dkan:datastore:degraded-mode');
    $this->assertStringContainsString('Datastore degraded performance mode: disabled', $this->getSimplifiedOutput());

    // Enable degraded mode.
    $this->drush('dkan:datastore:degraded-mode', ['1']);
    $this->assertStringContainsString('Datastore degraded performance mode enabled.', $this->getSimplifiedOutput());

    // Check state is enabled.
    $this->drush('dkan:datastore:degraded-mode');
    $this->assertStringContainsString('Datastore degraded performance mode: enabled', $this->getSimplifiedOutput());

    // Disable degraded mode.
    $this->drush('dkan:datastore:degraded-mode', ['0']);
    $this->assertStringContainsString('Datastore degraded performance mode disabled.', $this->getSimplifiedOutput());
  }
}