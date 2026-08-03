<?php

namespace Drupal\Tests\dkan_metastore\Functional\Commands;

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

  protected static $modules = ['dkan_datastore', 'dkan_metastore'];

  protected $defaultTheme = 'stark';

  /**
   * Tests that the command unreferences distributions and updates state.
   */
  public function testUnrefDatasets(): void {
    // Start with referencing enabled for distribution.
    $this->setDistributionReferenceModeFromConfig('distribution');

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

    // Run the command targeting the distribution property.
    $this->drush('dkan:metastore:unreference-datasets', ['distribution'], ['xdebug' => NULL]);

    // Reset the config factory so we see what Drush wrote to the DB.
    $this->container->get('config.factory')->reset();
    // Reset entity cache so retrieve() reloads from DB rather than serving stale data.
    $this->container->get('entity_type.manager')->getStorage('node')->resetCache();

    // Referencing should now be disabled in config.
    $property_list = $this->config('dkan_metastore.settings')->get('property_list');
    $this->assertNotContains(
      'distribution',
      array_filter($property_list ?? []),
      'Referencing for distribution should be disabled after command.'
    );

    // Both datasets should now have embedded (non-UUID) distribution objects.
    $raw1 = json_decode($this->getRawDatasetJson($uuid1));
    $raw2 = json_decode($this->getRawDatasetJson($uuid2));
    $this->assertDistributionsAreEmbedded($raw1, 'Dataset 1 distributions should be embedded after command.');
    $this->assertDistributionsAreEmbedded($raw2, 'Dataset 2 distributions should be embedded after command.');

    // Status output should mention both dataset titles and count.
    $output = $this->getOutput();
    $this->assertStringContainsString('Embed Test 1', $output);
    $this->assertStringContainsString('Embed Test 2', $output);
    $this->assertStringContainsString('1 distribution', $output);
  }

  /**
   * Assert that the dataset was stored with referenced distributions.
   *
   * When referenced, node-load dereferencing adds a '%Ref:distribution' key
   * containing metadata objects (with 'identifier' UUID and 'data' fields).
   */
  private function assertDistributionsAreReferenced(object $data, string $message): void {
    $ref_key = '%Ref:distribution';
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
    $ref_key = '%Ref:distribution';
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