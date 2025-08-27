<?php

declare(strict_types=1);

namespace Drupal\metastore\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests update functions for the metastore module.
 *
 * @group metastore
 * @group update
 * @group functional3
 */
class MetastoreUpdatePathTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../tests/fixtures/update/update-2.20.0.php.gz',
    ];
  }

  /**
   * Test metastore module update 8010.
   */
  public function testUpdates8010on(): void {
    $config = \Drupal::configFactory()->getEditable('metastore.settings');
    $data_form_config = \Drupal::configFactory()->getEditable('core.entity_form_display.node.data.default');

    // Get a baseline for pre-8010.
    $this->assertNull($config->get('redirect_to_datasets'));
    // Pre-8011
    $field_settings = $data_form_config->get('content.field_json_metadata');
    $this->assertEquals('json_form_widget', $field_settings['type']);

    $this->runUpdates();

    // Confirm results of update 8010.
    $config = \Drupal::configFactory()->getEditable('metastore.settings');
    $this->assertTrue($config->get('redirect_to_datasets'));

    // Confirm results of update 8011.
    $data_form_config = \Drupal::configFactory()->getEditable('core.entity_form_display.node.data.default');
    $field_settings = $data_form_config->get('content.field_json_metadata');
    $this->assertEquals('dkan_json_form_widget', $field_settings['type']);
  }
}
