<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore_mysql_import\Functional;

use Drupal\Core\File\FileSystemInterface;
use Drupal\datastore\Controller\ImportController;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\common\Traits\QueueRunnerTrait;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use RootedData\RootedJsonData;
use Symfony\Component\HttpFoundation\Request;

/**
 * Ensure we can apply a data dictionary to a dataset with too many columns.
 *
 * @group dkan
 * @group datastore_mysql_import
 * @group functional
 * @group functional2
 *
 * @see \Drupal\Tests\datastore_mysql_import\Functional\DictionaryEnforcerTest
 */
class StrictModeOffDictionaryEnforcerTest extends BrowserTestBase {

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
  protected const TEST_DATA_PATH = __DIR__ . '/../../data/';

  protected static $modules = [
    'datastore_mysql_import',
    'node',
  ];

  protected $defaultTheme = 'stark';

  public function testPostImport() {
    // Enable strict mode disabled for datastore MySQL import.
    $this->config('datastore_mysql_import.settings')
      ->set('strict_mode_disabled', TRUE)
      ->save();

      // Dependencies.
    $resourceFile = 'very_wide.csv';
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

    // Create a data dictionary for very_wide.csv. The 300th (!) column will be
    // a datetime.
    $dictionary_id = $uuid->generate();
    $dictionary_fields = [
      [
        'name' => 'name_at_the_sixty_four_character_limit_including_the_number_300',
        'title' => 'Date Column',
        'type' => 'datetime',
      ],
    ];
    $data_dict = $validMetadataFactory->get(
      $this->getDataDictionary($dictionary_fields, [], $dictionary_id),
      'data-dictionary'
    );
    // Create data-dictionary.
    $this->assertEquals(
      $dictionary_id,
      $metastore->post('data-dictionary', $data_dict)
    );
    // Publish should return FALSE, because the node was already published.
    $this->assertFalse($metastore->publish('data-dictionary', $dictionary_id));

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
          'dkan://metastore/schemas/data-dictionary/items/' . $dictionary_id
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
    // The dataset references the dictionary. DescribedBy will contain the
    // https URL-style reference.
    $this->assertStringContainsString(
      $dictionary_id,
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
      count($dictionary_fields),
      $dictionary_enforcer->returnDataDictionaryFields($distribution_id)
    );

    $distribution_data = $dataset->{'$["%Ref:distribution"][0].data'} ?? NULL;
    $resource_identifier = $distribution_data['%Ref:downloadURL'][0]['data']['identifier'] ?? NULL;
    $resource_version = $distribution_data['%Ref:downloadURL'][0]['data']['version'] ?? NULL;

    // Run queue items to perform the import, except for post import.
    $this->runQueues(['localize_import', 'datastore_import']);

    // Use the dictionary enforcer to do the post import, so we can see
    // exceptions and the like.
    /** @var \Drupal\metastore\ResourceMapper $resource_mapper */
    $resource_mapper = $this->container->get('dkan.metastore.resource_mapper');
    $dictionary_enforcer->process(
      $resource_mapper->get(
        $resource_identifier,
        ResourceLocalizer::LOCAL_FILE_PERSPECTIVE,
        $resource_version
      )
    );

    // Retrieve schema for dataset resource.
    $response = $importController->summary(
      $distribution_id,
      Request::create('http://blah/api')
    );
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    $result = json_decode((string) $response->getContent(), TRUE);

    // 300 columns + record_number.
    $this->assertCount(
      301,
      $columns = $result['columns']
    );

    // Check numeric.
    $this->assertEquals(
      'name_at_the_sixty_four_character_limit_including_the_number_300',
      $columns['name_at_the_sixty_four_character_limit_including_the_number_300']['description'] ?? NULL
    );
    $this->assertEquals(
      'varchar',
      $columns['name_at_the_sixty_four_character_limit_including_the_number_300']['type'] ?? NULL
    );
    $this->assertEquals(
      'datetime',
      $columns['name_at_the_sixty_four_character_limit_including_the_number_300']['mysql_type'] ?? NULL
    );

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
