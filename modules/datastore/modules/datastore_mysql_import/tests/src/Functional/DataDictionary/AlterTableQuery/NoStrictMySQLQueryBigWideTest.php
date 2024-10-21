<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore_mysql_import\Functional\DataDictionary\AlterTableQuery;

use Drupal\common\DataResource;
use Drupal\Core\File\FileSystemInterface;
use Drupal\datastore\Controller\ImportController;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\common\Traits\QueueRunnerTrait;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use RootedData\RootedJsonData;
use Symfony\Component\HttpFoundation\Request;

/**
 * Ensure we can apply a data dictionary to a MySQL dataset.
 *
 * @group dkan
 * @group datastore_mysql_import
 * @group functional
 *
 * @see \Drupal\Tests\datastore_mysql_import\Functional\Storage\MySqlDatabaseTableTest
 */
class NoStrictMySQLQueryBigWideTest extends BrowserTestBase {

  use GetDataTrait, QueueRunnerTrait;

  /**
   * Uploaded resource file destination.
   *
   * @var string
   */
  protected const UPLOAD_LOCATION = 'public://uploaded_resources/';

  /**
   * Test data file path.
   *
   * @var string
   */
  protected const TEST_DATA_PATH = __DIR__ . '/../../../../data/';

  protected static $modules = [
    'datastore_mysql_import',
    'node',
  ];

  protected $defaultTheme = 'stark';

  public function testPostImport() {
    // Dependencies.
    $resourceFile = 'research.csv';
    $uuid = $this->container->get('uuid');
    /** @var \Drupal\metastore\ValidMetadataFactory $validMetadataFactory */
    $validMetadataFactory = $this->container->get('dkan.metastore.valid_metadata');
    /** @var \Drupal\metastore\MetastoreService $metastore */
    $metastore = $this->container->get('dkan.metastore.service');
    $resourceUrl = $this->setUpResourceFile($resourceFile);
    $importController = ImportController::create(\Drupal::getContainer());

    // Set per-reference data dictionary in metastore config.
    $this->config('metastore.settings')
      ->set('data_dictionary_mode', DataDictionaryDiscovery::MODE_REFERENCE)
      ->save();
    $this->assertEquals(
      DataDictionaryDiscovery::MODE_REFERENCE,
      $this->config('metastore.settings')->get('data_dictionary_mode')
    );

    // Create a data dictionary for research.csv.
    // Build data-dictionary.
    $dict_id = $uuid->generate();
    $dict_fields = [
      [
        'name' => 'related_product_indicator',
        'title' => 'rpi',
        'type' => 'boolean',
      ],
      [
        'name' => 'total_amount_of_payment_usdollars',
        'title' => 'taopu',
        'type' => 'number',
      ],
      [
        'name' => 'date_of_payment',
        'title' => 'dop',
        'type' => 'date',
      ],
    ];
    $data_dict = $validMetadataFactory->get(
      $this->getDataDictionary($dict_fields, [], $dict_id),
      'data-dictionary'
    );
    // Create data-dictionary.
    $this->assertEquals(
      $dict_id,
      $metastore->post('data-dictionary', $data_dict)
    );
    // Publish should return FALSE, because the node was already published.
    $this->assertFalse($metastore->publish('data-dictionary', $dict_id));

    // Create a dataset node with our data dictionary.
    $dataset_id = $uuid->generate();
    $this->assertInstanceOf(
      RootedJsonData::class,
      $dataset = $validMetadataFactory->get(
        $this->getDataset(
          $dataset_id,
          'Test ' . $dataset_id,
          [$resourceUrl],
          TRUE,
          'dkan://metastore/schemas/data-dictionary/items/' . $dict_id
        ),
        'dataset'
      )
    );
    // Create dataset node.
    $this->assertEquals(
      $dataset_id,
      $metastore->post('dataset', $dataset)
    );
    // Publish should return FALSE, because the node was already published.
    $this->assertFalse($metastore->publish('dataset', $dataset_id));

    // Retrieve dataset.
    $this->assertInstanceOf(
      RootedJsonData::class,
      $dataset = $metastore->get('dataset', $dataset_id)
    );
    // The dataset references the dictionary. DescribedBy will contain the https
    // URL-style reference.
    $this->assertStringContainsString(
      $dict_id,
      $dataset->{'$["%Ref:distribution"][0].data.describedBy'}
    );
    // Get the distribution ID.
    $this->assertNotEmpty(
      $distribution_id = $dataset->{'$["%Ref:distribution"][0].identifier'} ?? NULL
    );

    // Dictionary fields are applied to the dataset.
    /** @var \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer $dictionary_enforcer */
    $dictionary_enforcer = $this->container->get('dkan.datastore.service.resource_processor.dictionary_enforcer');
    $this->assertCount(
      count($dict_fields),
      $dictionary_enforcer->returnDataDictionaryFields($distribution_id)
    );

//            $this->assertEquals('asdf', print_r($dataset, true));
    $distribution_id = $dataset->{'$["%Ref:distribution"][0].data'} ?? NULL;
//    $data_resource_id = $dataset->{'$["%Ref:distribution"][0].data.%Ref:downloadURL'};
    $this->assertEquals('asdf', print_r($distribution_id, true));
    //    ][0]['data']['%Ref:downloadURL'][0]['identifier']);


    // Run queue items to perform the import.
    // @todo Use the dictionary enforcer to do the post import, so we can
    //   see exceptions and the like.
    $this->runQueues(['localize_import', 'datastore_import', 'post_import']);


//    $dictionary_enforcer->process($dataset->);

    // Retrieve schema for dataset resource.
    $response = $importController->summary(
      $distribution_id,
      Request::create('http://blah/api')
    );
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    $result = json_decode($response->getContent(), TRUE);

    // 252 columns + record_number.
    $this->assertCount(
      253,
      $columns = $result['columns']
    );

    // Bool is int and tinyint.
    $this->assertEquals(
        'Related_Product_Indicator',
        $columns['related_product_indicator']['description'] ?? NULL
      );
    $this->assertEquals(
        'int',
        $columns['related_product_indicator']['type'] ?? NULL
      );
    $this->assertEquals(
      'tinyint',
      $columns['related_product_indicator']['mysql_type'] ?? NULL
    );

    // Check numeric.
    $this->assertEquals(
      'Total_Amount_of_Payment_USDollars',
      $columns['total_amount_of_payment_usdollars']['description'] ?? NULL
    );
    $this->assertEquals(
      'numeric',
      $columns['total_amount_of_payment_usdollars']['type'] ?? NULL
    );

    // $this->assertEquals(
    //      'Date_of_Payment',
    //      $columns['date_of_payment']['description'] ?? NULL
    //    );
    //    $this->assertEquals(
    //      'numeric',
    //      $columns['date_of_payment']['type'] ?? NULL
    //    );
    // @todo Look at the DB and see if it's right.
  }

  /**
   * Move a data test file to the public:// directory.
   *
   * @param string $resourceFile
   *   The file name only of the data resource file.
   *
   * @return string
   *   The resource URL of the moved resource.
   *
   * @todo Turn this into a trait or add it to a base class.
   */
  protected function setUpResourceFile(string $resourceFile) : string {
    // Copy resource file to uploads directory.
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = $this->container->get('file_system');
    $upload_path = $file_system->realpath(self::UPLOAD_LOCATION);
    $file_system->prepareDirectory($upload_path, FileSystemInterface::CREATE_DIRECTORY);
    $file_system->copy(self::TEST_DATA_PATH . $resourceFile, $upload_path, FileSystemInterface::EXISTS_REPLACE);
    return $this->container->get('stream_wrapper_manager')
      ->getViaUri(self::UPLOAD_LOCATION . $resourceFile)
      ->getExternalUrl();
  }

}
