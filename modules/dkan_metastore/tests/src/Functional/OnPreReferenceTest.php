<?php

namespace Drupal\Tests\dkan_metastore\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * @group dkan
 * @group metastore
 * @group functional
 * @group btb
 * @group functional1
 */
class OnPreReferenceTest extends BrowserTestBase {

  protected static $modules = [
    'dkan_datastore',
    'dkan_metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  private $downloadUrl = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';

  /**
   *
   */
  private function getData($downloadUrl) {
    return '
    {
      "title": "Test #1",
      "description": "Yep",
      "identifier": "123",
      "accessLevel": "public",
      "modified": "06-04-2020",
      "keyword": ["hello"],
        "distribution": [
          {
            "title": "blah",
            "downloadURL": "' . $downloadUrl . '",
            "mediaType": "text/csv"
          }
        ]
    }';
  }

  /**
   *
   */
  public function test() {
    // Ensure the proper triggering properties are set for datastore comparison.
    $this->config('dkan_datastore.settings')
      ->set('triggering_properties', ['modified'])
      ->save();

    // Test posting a dataset to the metastore.
    $data = $this->getData($this->downloadUrl);
    /** @var \Drupal\dkan_metastore\MetastoreService $metastore */
    $metastore = $this->container->get('dkan.metastore.service');
    $dataset = $metastore->getValidMetadataFactory()->get($data, 'dataset');
    $metastore->post('dataset', $dataset);
    $beforeRef = $this->getDistributionDownloadUrlReference('123');

    $decoded = json_decode((string) $data);
    $decoded->modified = '06-04-2021';
    $edited = json_encode($decoded);

    $dataset = $metastore->getValidMetadataFactory()->get($edited, 'dataset');
    $metastore->patch('dataset', '123', $dataset);

    $afterRef = $this->getDistributionDownloadUrlReference('123');
    $this->assertNotSame($beforeRef, $afterRef);
  }

  /**
   * Get the raw downloadURL reference stored on a dataset's first distribution.
   */
  private function getDistributionDownloadUrlReference(string $datasetUuid): string {
    $storage = $this->container->get('entity_type.manager')->getStorage('node');

    $result = $storage->loadByProperties(['type' => 'data', 'uuid' => $datasetUuid]);
    $datasetNode = reset($result);
    $this->assertNotFalse($datasetNode);

    $query = $this->container->get('database')->query(
      'SELECT field_json_metadata_value FROM {node__field_json_metadata} WHERE entity_id = :entity_id',
      [':entity_id' => $datasetNode->id()]
    );
    $datasetRaw = json_decode($query->fetchField(), TRUE);

    $distributionUuid = $datasetRaw['distribution'][0] ?? NULL;
    $this->assertNotNull($distributionUuid);

    $result = $storage->loadByProperties(['type' => 'data', 'uuid' => $distributionUuid]);
    $distributionNode = reset($result);
    $this->assertNotFalse($distributionNode);

    $query = $this->container->get('database')->query(
      'SELECT field_json_metadata_value FROM {node__field_json_metadata} WHERE entity_id = :entity_id',
      [':entity_id' => $distributionNode->id()]
    );
    $distributionRaw = json_decode($query->fetchField(), TRUE);

    return $distributionRaw['data']['downloadURL'] ?? '';
  }

}
