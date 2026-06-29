<?php

namespace Drupal\Tests\dkan_metastore\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the pre-reference event subscriber.
 *
 * @group dkan
 * @group dkan_metastore
 * @group functional
 * @group functional1
 */
class OnPreReferenceTest extends BrowserTestBase {

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

    // Set the "distribution" property list item to use references or not.
    $property_list = $this->config('dkan_metastore.settings')->get('property_list');
    $property_list['distribution'] = $distribution_reference;
    $this->config('dkan_metastore.settings')
      ->set('property_list', $property_list)
      ->save();

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

  /**
   * Two versions of metastore settings.
   *
   * Setting dkan_metastore.settings.property_list.distribution to "0" means
   * we don't reference distributions. Tests that the pre-reference event
   * subscriber still works in that case.
   */
  public static function distributionReferenceProvider() {
    return [
      ['distribution'],
      ['0'],
    ];
  }

}
