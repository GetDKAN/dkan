<?php

namespace Drupal\Tests\dkan_metastore\Functional\Drush\Commands;

use Drupal\dkan_metastore\Reference\Dereferencer;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\dkan_common\Traits\DistributionReferenceModeTrait;
use Drupal\Tests\dkan_common\Traits\GetLocalDataTrait;
use Drush\TestTraits\DrushTestTrait;

/**
 * Functional test for dkan:metastore:unreference-datasets.
 *
 * @group dkan
 * @group dkan_metastore
 * @group functional
 * @group functional1
 */
class UnreferenceDatasetCommandsTest extends BrowserTestBase {

  use DrushTestTrait;
  use GetLocalDataTrait;
  use DistributionReferenceModeTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['dkan_datastore', 'dkan_metastore'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that the command unreferences distributions and updates state.
   *
   * @dataProvider unrefDatasetsOptionsProvider
   */
  public function testUnrefDatasets(array $options): void {
    // Start with referencing enabled for distribution.
    $this->setDistributionReferenceModeFromConfig('distribution');

    // Run the command targeting an invalid property.
    $this->setDistributionReferenceModeFromConfig('distribution');
    $this->drush('dkan:metastore:unreference-datasets', ['invalid_property'], ['yes' => TRUE]);
    $output = $this->getErrorOutput();
    $this->assertStringContainsString('Unknown property: invalid_property', $output);

    /** @var \Drupal\dkan_metastore\MetastoreService $metastore */
    $metastore = $this->container->get('dkan.metastore.service');
    $factory = $metastore->getValidMetadataFactory();

    // Post two datasets, each with one referenced distribution.
    $uuid1 = 'dataset-embed-test-001';
    $uuid2 = 'dataset-embed-test-002';

    $metastore->post('dataset', $factory->get($this->getDataset($uuid1, 'Embed Test 1', ['1.csv']), 'dataset'));
    $metastore->post('dataset', $factory->get($this->getDataset($uuid2, 'Embed Test 2', ['1.csv']), 'dataset'));

    // Verify distributions are stored as reference UUIDs before the command.
    $raw1 = json_decode($this->getRawDatasetJson($uuid1));
    $raw2 = json_decode($this->getRawDatasetJson($uuid2));
    $this->assertDistributionsAreReferenced($raw1, 'Dataset 1 should have referenced distributions before command.');
    $this->assertDistributionsAreReferenced($raw2, 'Dataset 2 should have referenced distributions before command.');
    $dist1_uuid = $raw1->{Dereferencer::REF_PREFIX . 'distribution'}[0]->identifier;
    $dist2_uuid = $raw2->{Dereferencer::REF_PREFIX . 'distribution'}[0]->identifier;

    // Check we get a confirmation before proceeding with the operation.
    $this->drush('dkan:metastore:unreference-datasets', ['distribution'], $options);
    $output = $this->getOutput() . $this->getErrorOutput();
    $this->assertStringContainsString('WARNING] You are about to overwrite references', $output);
    $this->assertStringContainsString('Operation cancelled.', $output);

    // Run the command targeting the distribution property.
    $this->drush('dkan:metastore:unreference-datasets', ['distribution'], $options + ['yes' => TRUE, 'xdebug' => NULL]);

    // Reset the config factory so we see what Drush wrote to the DB.
    $this->container->get('config.factory')->reset();
    // Reset entity cache so retrieve() reloads from DB.
    $this->container->get('entity_type.manager')->getStorage('node')->resetCache();

    // Referencing should now be disabled in config.
    $property_list = $this->config('dkan_metastore.settings')->get('property_list');
    $this->assertNotContains(
      'distribution',
      array_filter($property_list ?? []),
      'Referencing for distribution should be disabled after command.'
    );

    // Both datasets should now have embedded distribution objects.
    $raw1 = json_decode($this->getRawDatasetJson($uuid1));
    $raw2 = json_decode($this->getRawDatasetJson($uuid2));
    $this->assertDistributionsAreEmbedded($raw1, 'Dataset 1 distributions should be embedded after command.');
    $this->assertDistributionsAreEmbedded($raw2, 'Dataset 2 distributions should be embedded after command.');

    if (array_key_exists('delete', $options)) {
      // Assert the two original referenced distributions are now deleted.
      $this->assertDistributionsAreDeleted($dist1_uuid);
      $this->assertDistributionsAreDeleted($dist2_uuid);
    }
    else {
      // Assert the two original referenced distributions are now orphaned.
      $this->assertDistributionsAreOrphaned($dist1_uuid);
      $this->assertDistributionsAreOrphaned($dist2_uuid);
    }

    // Status output should mention both dataset titles and count.
    $output = $this->getOutput();
    $this->assertStringContainsString('Embed Test 1', $output);
    $this->assertStringContainsString('Embed Test 2', $output);
    $this->assertStringContainsString('1 distribution', $output);
  }

  /**
   * Data provider for testUnrefDatasets.
   *
   * Drush command options.
   *
   * @return array
   *   Test cases.
   */
  public static function unrefDatasetsOptionsProvider(): array {
    return [
      'default' => [
        'options' => [],
      ],
      'delete' => [
        'options' => ['delete' => NULL],
      ],
    ];
  }

  /**
   * Assert that a distribution node is orphaned.
   */
  private function assertDistributionsAreOrphaned(string $uuid): void {
    $node = $this->container->get('entity_type.manager')->getStorage('node')->loadByProperties(['uuid' => $uuid]);
    $node = reset($node);
    $this->assertNotNull($node, "Distribution node with UUID {$uuid} should exist.");
    $this->assertEquals('orphaned', $node->get('moderation_state')->value, "Distribution node with UUID {$uuid} should be orphaned.");
  }

  /**
   * Assert that a distribution node is deleted.
   */
  private function assertDistributionsAreDeleted(string $uuid): void {
    $node = $this->container->get('entity_type.manager')->getStorage('node')->loadByProperties(['uuid' => $uuid]);
    $node = reset($node);
    $this->assertFalse($node, "Distribution node with UUID {$uuid} should be deleted.");
  }

  /**
   * Assert that the dataset was stored with referenced distributions.
   *
   * When referenced, node-load dereferencing adds a '%Ref:distribution' key
   * containing metadata objects (with 'identifier' UUID and 'data' fields).
   */
  private function assertDistributionsAreReferenced(object $data, string $message): void {
    $ref_key = Dereferencer::REF_PREFIX . 'distribution';
    $this->assertTrue(isset($data->{$ref_key}), $message . " (expected '{$ref_key}' key to be present)");
    $refs = $data->{$ref_key};
    $this->assertIsArray($refs, $message . ' (reference key should be an array)');
    foreach ($refs as $ref) {
      $this->assertIsObject($ref, $message . ' (each %Ref entry should be an object)');
      $this->assertObjectHasProperty('identifier', $ref, $message . ' (%Ref entry should have an identifier)');
    }
  }

  /**
   * Assert every distribution in $data is an embedded object (not a UUID).
   */
  private function assertDistributionsAreEmbedded(object $data, string $message): void {
    $ref_key = Dereferencer::REF_PREFIX . 'distribution';
    $this->assertFalse(isset($data->{$ref_key}), $message . " ('{$ref_key}' key should be absent after embedding)");
    $this->assertIsArray($data->distribution ?? NULL, $message);
    foreach ($data->distribution as $dist) {
      $this->assertIsObject($dist, $message . ' (distribution should be an object, not a UUID string)');
      $this->assertObjectHasProperty('downloadURL', $dist, $message . ' (embedded distribution should have downloadURL)');
    }
  }

  /**
   * Retrieve the raw (unreferenced) JSON for a dataset directly from storage.
   */
  private function getRawDatasetJson(string $uuid): string {
    /** @var \Drupal\dkan_metastore\Storage\DataFactory $factory */
    $storage_factory = $this->container->get('dkan.metastore.storage');
    return $storage_factory->getInstance('dataset')->retrieve($uuid);
  }

}
