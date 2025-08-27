<?php

namespace Drupal\Tests\datastore\Functional\Service;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\common\Traits\QueueRunnerTrait;
use Drupal\Tests\metastore\Unit\MetastoreServiceTest;

/**
 * Test ResourcePurger service.
 *
 * @coversDefaultClass \Drupal\datastore\Service\ResourcePurger
 *
 * @group dkan
 * @group datastore
 * @group functional
 * @group btb
 * @group functional1
 */
class ResourcePurgerTest extends BrowserTestBase {
  use GetDataTrait;
  use QueueRunnerTrait;

  protected static $modules = [
    'datastore',
    'metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  /**
   * DKAN dataset storage service.
   *
   * @var \Drupal\metastore\Storage\NodeData
   */
  protected $datasetStorage;

  /**
   * DKAN metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  protected $metastore;

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  public function setUp(): void {
    parent::setUp();
    // Initialize services.
    $this->datasetStorage = $this->container->get('dkan.metastore.storage')->getInstance('dataset');
    $this->metastore = $this->container->get('dkan.metastore.service');
    $this->validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);
  }

  /**
   * Test deleting a dataset doesn't delete other datasets sharing a resource.
   */
  public function testDatasetsWithSharedResourcesAreNotDeletedPrematurely(): void {
    // Make sure the default moderation state is "published".
    $this->config('workflows.workflow.dkan_publishing')
      ->set('type_settings.default_moderation_state', 'published')
      ->save();

    // Create 2 datasets with the same resource, and change the resource of one.
    $dataset = $this->validMetadataFactory->get($this->getDataset(123, 'Test #1', ['district_centerpoints_small.csv']), 'dataset');
    $this->metastore->post('dataset', $dataset);
    $this->assertNotEmpty($this->datasetStorage->retrieve(123));
    $this->runQueues(['localize_import', 'datastore_import']);
    $dataset = $this->validMetadataFactory->get($this->getDataset(123, 'Test #1', ['retirements_0.csv']), 'dataset');
    $this->metastore->patch('dataset', 123, $dataset);
    $this->assertNotEmpty($this->datasetStorage->retrieve(123));

    $dataset2 = $this->validMetadataFactory->get($this->getDataset(456, 'Test #2', ['district_centerpoints_small.csv']), 'dataset');
    $this->metastore->post('dataset', $dataset2);
    $this->assertNotEmpty($this->datasetStorage->retrieve(456));
    $this->runQueues(['localize_import', 'datastore_import']);

    // Ensure calling the resource purger on the updated dataset does not delete
    // the previously shared resource.
    $this->container->get('dkan.datastore.service.resource_purger')
      ->schedule([123], FALSE);
    $resources = $this->getResourcesForDataset(456);
    $resource = reset($resources);
    $this->assertNotEmpty(
      $this->container->get('dkan.datastore.service')
        ->getStorage($resource->identifier, $resource->version)
    );
  }

  /**
   * Get resources for the dataset belonging to the supplied dataset identifier.
   *
   * @param string $dataset_identifier
   *   A dataset UUID.
   *
   * @return object[]
   *   Resource objects containing a unique "identifier" and "version" pair.
   */
  protected function getResourcesForDataset(string $dataset_identifier): array {
    // Retrieve dataset metastore storage service.
    $metadata = $this->datasetStorage->retrieve($dataset_identifier);
    $distributions = json_decode((string) $metadata)->{'%Ref:distribution'} ?? [];

    $resources = [];
    foreach ($distributions as $distribution) {
      // Retrieve and validate the resource for this distribution before adding
      // it to the resources list.
      $resource = $distribution->data->{'%Ref:downloadURL'}[0] ?? NULL;
      if (isset($resource->data->identifier, $resource->data->version)) {
        $resources[] = $resource->data;
      }
    }

    return $resources;
  }

}
