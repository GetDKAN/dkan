<?php

namespace Drupal\Tests\dkan_metastore\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\dkan_common\Traits\DistributionReferenceModeTrait;

/**
 * Test the pre-reference event subscriber.
 *
 * @group dkan
 * @group dkan_metastore
 * @group functional
 * @group functional1
 */
class OnPreReferenceTest extends BrowserTestBase {
  use DistributionReferenceModeTrait;


  protected static $modules = [
    'dkan_datastore',
    'dkan_metastore',
  ];

  protected $defaultTheme = 'stark';

  private $downloadUrl = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';

  /**
   * Get the dataset metadata for testing.
   */
  private function getData(string $downloadUrl): string {
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
   * Test the pre-reference event subscriber.
   *
   * @dataProvider distributionReferenceProvider
   */
  public function testPreReference($distribution_reference) {
    // Ensure the proper triggering properties are set for datastore comparison.
    $this->config('dkan_datastore.settings')
      ->set('triggering_properties', ['modified'])
      ->save();

    $this->setDistributionReferenceModeFromConfig($distribution_reference);

    // Test posting a dataset to the metastore.
    $data = $this->getData($this->downloadUrl);
    /** @var \Drupal\dkan_metastore\MetastoreService $metastore */
    $metastore = $this->container->get('dkan.metastore.service');
    $dataset = $metastore->getValidMetadataFactory()->get($data, 'dataset');
    $metastore->post('dataset', $dataset);

    $decoded = json_decode((string) $data);
    $decoded->modified = '06-04-2021';
    $edited = json_encode($decoded);

    $dataset = $metastore->getValidMetadataFactory()->get($edited, 'dataset');
    $metastore->patch('dataset', '123', $dataset);

    $rev = drupal_static('metastore_resource_mapper_new_revision');
    $this->assertEquals(1, $rev);
  }

}
