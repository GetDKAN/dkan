<?php

namespace Drupal\Tests\dkan_datastore\Functional\Commands;

use Drupal\dkan_datastore\Service\ResourceLocalizer;
use Drupal\Tests\dkan_common\Traits\GetLocalDataTrait;
use Drupal\Tests\dkan_common\Traits\QueueRunnerTrait;
use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;
use Procrastinator\Result;

class DatastoreCommandsTest extends BrowserTestBase {
  use DrushTestTrait, GetLocalDataTrait, QueueRunnerTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['dkan_datastore', 'dkan_metastore'];

  protected $defaultTheme = 'stark';

  /**
   * Tests the dkan:datastore:import command.
   */
  public function testImportCommand() {
    // Create a single dataset. Do not run any queues, so the resource is not
    // yet localized or imported.
    $dataset_id = $this->createDataset('1.csv', 'test-dataset-import');
    $resource = $this->getResourceIdentifier($dataset_id);

    // Run the import command directly against the resource identifier. This
    // is not deferred, so the import should complete immediately.
    $this->drush('dkan:datastore:import', [$resource['resource_id']]);

    // Assert the drush command logged a successful run.
    $this->assertStringContainsString(
      'Ran import for ' . $resource['resource_id'],
      $this->getErrorOutput()
    );

    // Now that the import has run, get the full resource info (including the
    // table name) in a single call.
    $resource = $this->getResourceInfo($dataset_id);

    // ImportInfo should now report the import as done.
    $import_info = \Drupal::service('dkan.datastore.import_info');
    $item = $import_info->getItem($resource['resource_id'], $resource['resource_version']);
    $this->assertEquals(
      Result::DONE,
      $item->importerStatus,
      "ImportInfo should report a completed import for resource {$resource['resource_id']}."
    );

    // Verify the datastore table now exists.
    $this->assertTrue(
      \Drupal::database()->schema()->tableExists($resource['table_name']),
      "Datastore table {$resource['table_name']} should exist after import."
    );
  }

  /**
   * Tests the dkan:datastore:list command.
   */
  public function testListCommand() {
    // Check initial state is disabled.
    foreach (['1.csv', '2.csv'] as $n => $filename) {
      $this->createDataset($filename, "test-dataset-{$n}");
    }
    $this->runQueues(['localize_import', 'datastore_import']);
    $this->drush('dkan:datastore:list');
    $this->assertStringContainsString(
      'Resource UUID File Name FileFetcher Processed Importer Processed',
      $this->getSimplifiedOutput()
    );
    // Assert that the datasets are listed in the output.
    foreach (['1.csv', '2.csv'] as $filename) {
      $this->assertStringContainsString(
        "$filename",
        $this->getSimplifiedOutput()
      );
    }
  }

  /**
   * Tests the dkan:datastore:drop command.
   */
  public function testDropCommand() {
    // Create a single dataset and import it.
    $dataset_id = $this->createDataset('1.csv', 'test-dataset-drop');
    $resource = $this->getResourceIdentifier($dataset_id);
    $this->drush('dkan:datastore:import', [$resource['resource_id']]);

    // Get the full resource info now that the import has run.
    $resource = $this->getResourceInfo($dataset_id);

    // Verify the table exists, and ImportInfo reflects a completed import,
    // before dropping.
    $connection = \Drupal::database();
    $this->assertTrue(
      $connection->schema()->tableExists($resource['table_name']),
      "Datastore table {$resource['table_name']} should exist after import."
    );
    $import_info = \Drupal::service('dkan.datastore.import_info');
    $item = $import_info->getItem($resource['resource_id'], $resource['resource_version']);
    $this->assertEquals(
      Result::DONE,
      $item->importerStatus,
      "ImportInfo should report a completed import for resource {$resource['resource_id']}."
    );

    // Run the drop command.
    $this->drush('dkan:datastore:drop', [$resource['resource_id']]);

    // Assert the drop command logged both success notices.
    $this->assertStringContainsString(
      'Successfully dropped the datastore for resource ' . $resource['resource_id'],
      $this->getErrorOutput()
    );
    $this->assertStringContainsString(
      'Successfully removed the post import job status for resource ' . $resource['resource_id'],
      $this->getErrorOutput()
    );

    // Verify the table no longer exists, and ImportInfo no longer reflects a
    // completed import.
    $this->assertFalse(
      $connection->schema()->tableExists($resource['table_name']),
      "Datastore table {$resource['table_name']} should not exist after drop."
    );
    $item = $import_info->getItem($resource['resource_id'], $resource['resource_version']);
    $this->assertNotEquals(
      Result::DONE,
      $item->importerStatus,
      "ImportInfo should not report a completed import for resource {$resource['resource_id']} after drop."
    );
    $this->assertNotEquals(
      Result::DONE,
      $item->fileFetcherStatus,
      "ImportInfo should not report a completed file fetch for resource {$resource['resource_id']} after drop."
    );
  }

  /**
   * Tests the dkan:datastore:drop-all command.
   */
  public function testDropAllCommand() {
    // Create datasets.
    $dataset_ids = [];
    foreach (['1.csv', '2.csv'] as $n => $filename) {
      $dataset_ids[] = $this->createDataset($filename, "test-dataset-dropall-{$n}");
    }
    $this->runQueues(['localize_import', 'datastore_import']);

    // Collect resource info (table name, identifier, version) now that
    // resources are localized.
    $resource_infos = array_map([$this, 'getResourceInfo'], $dataset_ids);

    // Verify the tables exist, and ImportInfo reflects a completed import,
    // before dropping.
    $connection = \Drupal::database();
    $import_info = \Drupal::service('dkan.datastore.import_info');
    foreach ($resource_infos as $info) {
      $this->assertTrue(
        $connection->schema()->tableExists($info['table_name']),
        "Datastore table {$info['table_name']} should exist after import."
      );
      $item = $import_info->getItem($info['resource_id'], $info['resource_version']);
      $this->assertEquals(
        Result::DONE,
        $item->importerStatus,
        "ImportInfo should report a completed import for resource {$info['resource_id']}."
      );
    }

    // Run the drop-all command.
    $this->drush('dkan:datastore:drop-all');

    // Assert that the drop command's "removed" notice was logged for each
    // dropped resource.
    $this->assertStringContainsString(
      'Successfully removed the post import job status for resource',
      $this->getErrorOutput()
    );

    // Verify the tables no longer exist, and ImportInfo no longer reflects a
    // completed import.
    foreach ($resource_infos as $info) {
      $this->assertFalse(
        $connection->schema()->tableExists($info['table_name']),
        "Datastore table {$info['table_name']} should not exist after drop-all."
      );
      $item = $import_info->getItem($info['resource_id'], $info['resource_version']);
      $this->assertNotEquals(
        Result::DONE,
        $item->importerStatus,
        "ImportInfo should not report a completed import for resource {$info['resource_id']} after drop-all."
      );
      $this->assertNotEquals(
        Result::DONE,
        $item->fileFetcherStatus,
        "ImportInfo should not report a completed file fetch for resource {$info['resource_id']} after drop-all."
      );
    }
  }

  /**
   * Tests the dkan:datastore:prepare-localized command.
   */
  public function testPrepareLocalizedCommand() {
    // Create a single dataset. Do not run any queues, so the resource is not
    // yet localized.
    $dataset_id = $this->createDataset('1.csv', 'test-dataset-prepare-localized');
    $resource = $this->getResourceIdentifier($dataset_id);

    // The resource should not yet be registered under the local file
    // perspective.
    $resource_mapper = \Drupal::service('dkan.metastore.resource_mapper');
    $this->assertNull(
      $resource_mapper->get($resource['resource_id'], ResourceLocalizer::LOCAL_FILE_PERSPECTIVE, $resource['resource_version']),
      "Resource {$resource['resource_id']} should not have a local file perspective registered before running prepare-localized."
    );

    // Run the prepare-localized command.
    $this->drush('dkan:datastore:prepare-localized', [$resource['resource_id']]);

    // Assert the command output the expected localization paths as JSON.
    $output = $this->getOutput();
    $this->assertStringContainsString('"source":', $output);
    $this->assertStringContainsString('"path_uri":', $output);
    $this->assertStringContainsString('"path":', $output);
    $this->assertStringContainsString('"file_uri":', $output);
    $this->assertStringContainsString('"file":', $output);

    // The resource should now be registered under the local file
    // perspective, though the file itself has not actually been fetched yet.
    $local_file_resource = $resource_mapper->get($resource['resource_id'], ResourceLocalizer::LOCAL_FILE_PERSPECTIVE, $resource['resource_version']);
    $this->assertNotNull(
      $local_file_resource,
      "Resource {$resource['resource_id']} should have a local file perspective registered after running prepare-localized."
    );
  }

  /**
   * Tests the dkan:datastore:localize command.
   */
  public function testLocalizeCommand() {
    // Create a single dataset. Do not run any queues, so the resource is not
    // yet localized.
    $dataset_id = $this->createDataset('1.csv', 'test-dataset-localize');
    $resource = $this->getResourceIdentifier($dataset_id);

    // The resource should not yet be registered under the local file
    // perspective.
    $resource_mapper = \Drupal::service('dkan.metastore.resource_mapper');
    $this->assertNull(
      $resource_mapper->get($resource['resource_id'], ResourceLocalizer::LOCAL_FILE_PERSPECTIVE, $resource['resource_version']),
      "Resource {$resource['resource_id']} should not have a local file perspective registered before running localize."
    );

    // Run the localize command directly against the resource identifier.
    $this->drush('dkan:datastore:localize', [$resource['resource_id']]);

    // Confirm the local file perspective is registered by reading it back
    // from the resource mapper.
    $localized_resource = $resource_mapper->get($resource['resource_id'], ResourceLocalizer::LOCAL_FILE_PERSPECTIVE, $resource['resource_version']);
    $this->assertNotNull(
      $localized_resource,
      "Resource {$resource['resource_id']} should be localized after running localize."
    );

    // Verify the localized file actually exists on disk.
    $this->assertFileExists(
      $localized_resource->getFilePath(),
      "Localized file {$localized_resource->getFilePath()} should exist after running localize."
    );
  }

  /**
   * Tests the dkan:datastore:reverse-dataset-lookup command.
   */
  public function testReverseDatasetLookupCommand() {
    // Create a single dataset and import it, so the datastore table exists.
    $dataset_id = $this->createDataset('1.csv', 'test-dataset-rdl');
    $resource = $this->getResourceIdentifier($dataset_id);
    $this->drush('dkan:datastore:import', [$resource['resource_id']]);

    // Get the full resource info now that the import has run, including the
    // table name.
    $resource = $this->getResourceInfo($dataset_id);

    // Run the reverse-dataset-lookup command against the datastore table
    // name, and confirm it works backwards to find the correct dataset.
    $this->drush('dkan:datastore:reverse-dataset-lookup', [$resource['table_name']]);
    $this->assertStringContainsString(
      'Dataset UUID = ' . $dataset_id,
      $this->getOutput()
    );

    // A table name that cannot be mapped to a resource throws an exception
    // (rather than the command's own "Can not map..." failure message,
    // since tableToResourceLookup() never returns an empty string).
    $this->drush('dkan:datastore:reverse-dataset-lookup', ['datastore_nonexistent'], [], NULL, NULL, 1);
    $this->assertStringContainsString(
      'Can not map datastore table name datastore_nonexistent',
      $this->getErrorOutput()
    );
  }

  /**
   * Create datasets and import resources for testing.
   *
   * @param string $filename
   *   The name of the resource file.
   * @param string $dataset_id
   *   An arbitrary dataset UUID.
   *
   * @return string
   *   The UUID of the created dataset.
   */
  private function createDataset(string $filename, string $dataset_id) {
    $json = $this->getDataset($dataset_id,'Test ' . $dataset_id, [$filename]);
    $valid_metadata_factory = $this->container->get('dkan.metastore.valid_metadata');
    $dataset = $valid_metadata_factory->get($json, 'dataset');
    return $this->container->get('dkan.metastore.service')->post('dataset', $dataset);
  }

  /**
   * Get the resource identifier and version for a dataset's first distribution.
   *
   * Unlike getResourceInfo(), this does not require the resource to already
   * be localized or imported, so it can be used before an import has run.
   *
   * @param string $dataset_id
   *   The dataset identifier.
   *
   * @return array
   *   An array with 'resource_id' and 'resource_version' keys.
   */
  private function getResourceIdentifier(string $dataset_id): array {
    $dataset_info = \Drupal::service('dkan.common.dataset_info')->gather($dataset_id);
    $distribution = $dataset_info['latest_revision']['distributions'][0] ?? [];
    if (empty($distribution['resource_id'])) {
      throw new \Exception("Could not determine resource identifier for dataset $dataset_id.");
    }
    return [
      'resource_id' => $distribution['resource_id'],
      'resource_version' => $distribution['resource_version'] ?? NULL,
    ];
  }

  /**
   * Get resource info for a dataset's first distribution.
   *
   * @param string $dataset_id
   *   The dataset identifier.
   *
   * @return array
   *   An array with 'resource_id', 'resource_version', and 'table_name' keys.
   */
  private function getResourceInfo(string $dataset_id): array {
    $dataset_info = \Drupal::service('dkan.common.dataset_info')->gather($dataset_id);
    $distribution = $dataset_info['latest_revision']['distributions'][0] ?? [];
    if (empty($distribution['table_name']) || empty($distribution['resource_id'])) {
      throw new \Exception("Could not determine resource info for dataset $dataset_id.");
    }
    return [
      'resource_id' => $distribution['resource_id'],
      'resource_version' => $distribution['resource_version'] ?? NULL,
      'table_name' => $distribution['table_name'],
    ];
  }

}