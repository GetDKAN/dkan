<?php

namespace Drupal\Tests\metastore\Functional;

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
    'datastore',
    'metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  private $downloadUrl = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';

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

  public function test() {
    // Ensure the proper triggering properties are set for datastore comparison.
    $this->config('datastore.settings')
      ->set('triggering_properties', ['modified'])
      ->save();

    // Test posting a dataset to the metastore.
    $data = $this->getData($this->downloadUrl);
    /** @var \Drupal\metastore\MetastoreService $metastore */
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
